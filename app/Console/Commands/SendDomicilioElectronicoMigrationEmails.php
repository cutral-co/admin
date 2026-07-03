<?php

namespace App\Console\Commands;

use App\Mail\UerSolicitud\EmailDomicilioElectronicoMigracion;
use App\Models\EmailLog;
use App\Models\Person;
use App\Models\User;
use App\Services\Auth\PasswordGenerator;
use App\Services\Email\EmailLogService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Throwable;

class SendDomicilioElectronicoMigrationEmails extends Command
{
    private const TEMPLATE_KEY = 'domicilio-electronico.migracion-credenciales';

    /**
     * Lista base de CUITs excluidos del proceso.
     *
     * @var array<int, string>
     */
    private const EXCEPTION_CUITS = [
        // '20123456789',
        // '20999888777',
    ];

    protected $signature = 'users:send-domicilio-electronico-migration
        {--cuit= : Limita el proceso a un CUIT especÃ­fico}
        {--terms-url= : URL pública de términos y condiciones del domicilio electrónico}
        {--override-email= : Reemplaza el destinatario real por un correo de prueba}
        {--chunk=100 : Cantidad de usuarios por lote}
        {--limit= : Limita la cantidad total de usuarios a procesar}
        {--dry-run : Muestra el universo a procesar sin enviar correos ni renovar contraseñas}';

    protected $description = 'Renueva contraseñas y envía el correo masivo de implementación del domicilio electrónico';

    public function handle(
        EmailLogService $emailLogService,
        PasswordGenerator $passwordGenerator,
    ): int {
        $termsUrl = $this->resolveTermsUrl();
        if (!$termsUrl) {
            $this->error('Debe indicar --terms-url o configurar APP_CLIENT_URL en el entorno.');

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $limit = $this->option('limit') !== null ? max(0, (int) $this->option('limit')) : null;
        $dryRun = (bool) $this->option('dry-run');
        $cuit = $this->resolveCuit();
        $exceptionCuits = self::EXCEPTION_CUITS;
        $overrideEmail = $this->resolveOverrideEmail();

        $baseQuery = User::query()
            ->with('person')
            ->where('is_verified', true)
            ->whereNotNull('cuit')
            ->whereHas('person', function (Builder $query) {
                $query->whereNotNull('email')
                    ->where('email', '<>', '');
            });

        if ($cuit) {
            $baseQuery->where('cuit', $cuit);
        }

        if (!empty($exceptionCuits)) {
            $baseQuery->whereNotIn('cuit', $exceptionCuits);
        }

        $eligibleQuery = clone $baseQuery;
        $eligibleCount = (clone $eligibleQuery)->count();
        $alreadySentCount = (clone $baseQuery)
            ->whereIn('id', $this->sentUserIdsQuery())
            ->count();
        $pendingCount = max($eligibleCount - $alreadySentCount, 0);

        $this->info('Inicio de proceso masivo de domicilio electrónico');
        $this->line("Usuarios elegibles: {$eligibleCount}");
        $this->line("Ya enviados: {$alreadySentCount}");
        $this->line("Pendientes: {$pendingCount}");
        if ($cuit) {
            $this->line("Filtro por CUIT: {$cuit}");
        }
        $this->line('CUITs excluidos: ' . (empty($exceptionCuits) ? 'ninguno' : implode(', ', $exceptionCuits)));
        if ($overrideEmail) {
            $this->warn("Modo prueba activo. Todos los correos se enviarán a {$overrideEmail}.");
        }

        if ($dryRun) {
            $this->comment('Dry run finalizado sin cambios.');

            return self::SUCCESS;
        }

        if ($pendingCount === 0 || $limit === 0) {
            $this->comment('No hay usuarios pendientes para procesar.');

            return self::SUCCESS;
        }

        $processed = 0;
        $sent = 0;
        $skipped = 0;
        $failed = 0;

        $processQuery = clone $baseQuery;
        $processQuery
            ->whereNotIn('id', $this->sentUserIdsQuery())
            ->orderBy('id');

        $stop = false;

        $processQuery->chunkById($chunkSize, function ($users) use (
            &$processed,
            &$sent,
            &$skipped,
            &$failed,
            &$stop,
            $limit,
            $termsUrl,
            $overrideEmail,
            $emailLogService,
            $passwordGenerator,
        ) {
            foreach ($users as $user) {
                if ($limit !== null && $processed >= $limit) {
                    $stop = true;

                    return false;
                }

                $processed++;

                if (!$user->person || !$user->person->email) {
                    $skipped++;
                    $this->warn("Usuario {$user->id} omitido: sin email asociado.");
                    continue;
                }

                try {
                    $this->sendMigrationEmail($user, $termsUrl, $overrideEmail, $emailLogService, $passwordGenerator);
                    $sent++;
                    $targetEmail = $overrideEmail ?: $user->person->email;
                    $this->line("Enviado {$sent}: {$user->cuit} <{$targetEmail}>");
                } catch (Throwable $exception) {
                    $failed++;
                    $this->error("Fallo {$user->cuit}: {$exception->getMessage()}");
                }
            }
        });

        if ($stop) {
            $this->comment('Se alcanzó el límite configurado.');
        }

        $this->newLine();
        $this->info('Proceso finalizado');
        $this->line("Procesados: {$processed}");
        $this->line("Enviados: {$sent}");
        $this->line("Omitidos: {$skipped}");
        $this->line("Fallidos: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function sendMigrationEmail(
        User $user,
        string $termsUrl,
        ?string $overrideEmail,
        EmailLogService $emailLogService,
        PasswordGenerator $passwordGenerator,
    ): void {
        $plainPassword = $passwordGenerator->generate();
        $previousPasswordHash = $user->password;
        $targetEmail = $overrideEmail ?: $user->person->email;

        DB::transaction(function () use ($user, $plainPassword) {
            $user->forceFill([
                'password' => Hash::make($plainPassword),
            ])->save();
        });

        try {
            $emailLogService->send(
                $targetEmail,
                new EmailDomicilioElectronicoMigracion($user->cuit, $plainPassword, $termsUrl),
                User::class,
                $user->id,
                null,
                $this->resolvePersonName($user->person),
            );
        } catch (Throwable $exception) {
            $this->restorePassword($user, $previousPasswordHash, $exception);
        }
    }

    private function restorePassword(User $user, string $previousPasswordHash, Throwable $previousException): never
    {
        try {
            DB::transaction(function () use ($user, $previousPasswordHash) {
                $user->forceFill([
                    'password' => $previousPasswordHash,
                ])->save();
            });
        } catch (Throwable $restoreException) {
            throw new \RuntimeException(
                "No se pudo enviar el correo y tampoco restaurar la contraseña anterior del usuario {$user->cuit}. " .
                "Error envío: {$previousException->getMessage()}. Error restauración: {$restoreException->getMessage()}",
                0,
                $restoreException,
            );
        }

        throw $previousException;
    }

    private function resolveTermsUrl(): ?string
    {
        $defaultTermsUrl = rtrim((string) env('APP_CLIENT_URL', ''), '/') . '#/legal/user-registration.terms-and-conditions';
        $termsUrl = trim((string) ($this->option('terms-url') ?: $defaultTermsUrl));

        return $termsUrl !== '' ? $termsUrl : null;
    }

    private function resolveOverrideEmail(): ?string
    {
        $overrideEmail = trim((string) $this->option('override-email'));

        return $overrideEmail !== '' ? $overrideEmail : null;
    }

    private function resolveCuit(): ?string
    {
        $cuit = trim((string) $this->option('cuit'));

        return $cuit !== '' ? $cuit : null;
    }

    private function sentUserIdsQuery()
    {
        return EmailLog::query()
            ->select('related_id')
            ->where('related_type', User::class)
            ->where('template_key', self::TEMPLATE_KEY)
            ->where('status', 'sent')
            ->whereNotNull('related_id');
    }

    private function resolvePersonName(?Person $person): ?string
    {
        if (!$person) {
            return null;
        }

        $fullName = trim(implode(' ', array_filter([
            $person->name,
            $person->lastname,
        ])));

        return $fullName !== '' ? $fullName : null;
    }
}
