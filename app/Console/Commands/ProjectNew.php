<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProjectNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:new';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera la configuración iniciar para el proyecto';

    private $projectName;

    private $projectUrl;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $path = base_path('.env');
        if (!file_exists($path)) {
            $this->warn('No se encuentra: ' . $path);

            return Command::FAILURE;
        }

        if (env('APP_CREATED')) {
            $this->warn('El proyecto ya fue creado');

            return Command::SUCCESS;
        }

        /* Obtenemos el nombre del proyecto por al nombre de rootPath */
        $this->projectName = $this->getProjectName();
        $this->setEnv($path, $this->projectName, 'APP_NAME');

        $this->projectUrl = "https://webservicereplica.muninqn.gov.ar/{$this->projectName}/";
        $this->setEnv($path, $this->projectUrl, 'APP_URL');

        $db = str_replace('-', '', $this->projectName);
        $this->setEnv($path, $db, 'DB_DATABASE');
        $this->setEnv($path, '128.53.80.131', 'DB_HOST');
        $this->setEnv($path, "user{$db}", 'DB_USERNAME');
        $this->setEnv($path, 'user' . ucfirst($db) . '.2020', 'DB_PASSWORD');

        $this->setEnv($path, 'https://webservicereplica.muninqn.gov.ar/admin/', 'BASE_ADMIN_URL');

        $this->setEnv($path, "/mnt/serverdata/projects_files/{$this->projectName}/", 'STORAGE_PATH');

        $this->setEnv($path, 'true', 'APP_CREATED');

        //$this->showInfo();

        return Command::SUCCESS;
    }

    private function setEnv($path, $value, $key)
    {
        file_put_contents($path, str_replace(
            "{$key}=" . env($key),
            "{$key}=" . $value,
            file_get_contents($path)
        ));
    }

    private function getProjectName()
    {
        if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN') {
            $projectName = explode('\\', base_path());
        } else {
            $projectName = explode('/', base_path());
        }

        return end($projectName);
    }
}
