<?php

namespace Modules\Core\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MakeModule extends BaseCommand
{
    protected $group       = 'Modules';
    protected $name        = 'make:module';
    protected $description = 'Membuat struktur module baru di folder Modules/<Name>.';
    protected $usage       = 'make:module <Name>';

    public function run(array $params)
    {
        $name = $params[0] ?? null;
        if (! $name) {
            CLI::error('Nama module wajib diisi');
            return;
        }

        $base = ROOTPATH . 'Modules/' . $name;
        $dirs = [
            $base,
            "$base/Controllers",
            "$base/Models",
            "$base/Views",
            "$base/Services",
        ];
        foreach ($dirs as $dir) {
            if (! is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        CLI::write("Module $name berhasil dibuat di $base", 'green');
    }
}


