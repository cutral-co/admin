<?php

namespace App\Services\Email;

use App\Contracts\LogsEmailPayload;
use App\Models\EmailLog;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;
use ReflectionProperty;
use Throwable;

class EmailLogService
{
    public function send(
        string $toEmail,
        Mailable $mailable,
        ?string $relatedType = null,
        ?int $relatedId = null,
        ?int $triggeredByUserId = null,
        ?string $toName = null,
    ): EmailLog {
        if (!$mailable instanceof LogsEmailPayload) {
            throw new InvalidArgumentException(
                sprintf(
                    'El mailable %s debe implementar %s para poder auditarse.',
                    $mailable::class,
                    LogsEmailPayload::class,
                )
            );
        }

        $emailLog = EmailLog::create([
            'uuid' => (string) Str::uuid(),
            'to_email' => $toEmail,
            'to_name' => $toName,
            'mailable_class' => $mailable::class,
            'template_key' => $mailable->getEmailTemplateKey(),
            'view_name' => $this->resolveViewName($mailable),
            'subject' => $mailable->subject ?? null,
            'mailer_name' => config('mail.default'),
            'status' => 'pending',
            'payload_json' => $mailable->getEmailLogPayload(),
            'meta_json' => $mailable->getEmailLogMeta(),
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'triggered_by_user_id' => $triggeredByUserId,
        ]);

        try {
            Mail::to($toEmail, $toName)->send($mailable);

            $emailLog->update([
                'status' => 'sent',
                'sent_at' => Carbon::now(),
            ]);
        } catch (Throwable $exception) {
            $emailLog->update([
                'status' => 'failed',
                'failed_at' => Carbon::now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        return $emailLog->fresh();
    }

    private function resolveViewName(Mailable $mailable): ?string
    {
        if (!property_exists($mailable, 'view')) {
            return null;
        }

        $property = new ReflectionProperty($mailable, 'view');
        $property->setAccessible(true);

        $value = $property->getValue($mailable);

        return is_string($value) ? $value : null;
    }
}
