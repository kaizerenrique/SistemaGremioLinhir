<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckUploads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lin:check-uploads';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnostica la configuración de subida de imágenes (PHP + Livewire + Storage)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {

        $this->info('🔍 Diagnóstico de subidas de archivos');
        $this->newLine();
        $allOk = true;

        // ===== 1. Límites PHP =====
        $this->line('<fg=cyan;options=bold>1. Límites PHP</>');
        $uploadMax = $this->parseIniSize(ini_get('upload_max_filesize'));
        $postMax   = $this->parseIniSize(ini_get('post_max_size'));
        $memory    = $this->parseIniSize(ini_get('memory_limit'));

        $this->line("   upload_max_filesize : " . $this->fmt($uploadMax));
        $this->line("   post_max_size       : " . $this->fmt($postMax));
        $this->line("   memory_limit        : " . ($memory < 0 ? 'sin límite' : $this->fmt($memory)));

        $appLimit  = 5 * 1024 * 1024;
        $effective = min($uploadMax, $postMax, $appLimit);

        if ($uploadMax < $appLimit) {
            $this->warn("   ⚠️  upload_max_filesize < 5 MB. Archivos entre "
                . $this->fmt($uploadMax) . " y 5 MB fallarán ANTES de llegar a Laravel.");
            $allOk = false;
        }
        if ($postMax < $uploadMax) {
            $this->warn("   ⚠️  post_max_size < upload_max_filesize. Ajusta post_max_size.");
            $allOk = false;
        }
        $this->line("   → Límite efectivo: <fg=yellow>{$this->fmt($effective)}</>");
        $this->newLine();

        // ===== 2. Extensiones PHP =====
        $this->line('<fg=cyan;options=bold>2. Extensiones PHP</>');
        foreach (['fileinfo', 'gd', 'mbstring', 'exif'] as $ext) {
            $ok = extension_loaded($ext);
            $this->line('   ' . ($ok ? '<fg=green>✅</>' : '<fg=red>❌</>') . " {$ext}");
            if (!$ok && in_array($ext, ['fileinfo', 'gd'])) {
                $allOk = false;
            }
        }
        $this->newLine();

        // ===== 3. Directorios Livewire =====
        $this->line('<fg=cyan;options=bold>3. Directorios Livewire</>');
        $livewireTmp = storage_path('app/private/livewire-tmp');
        if (!is_dir($livewireTmp)) {
            @mkdir($livewireTmp, 0755, true);
            $this->line("   ✅ Creado {$livewireTmp}");
        }
        $writable = is_writable($livewireTmp);
        $this->line('   ' . ($writable ? '<fg=green>✅</>' : '<fg=red>❌</>') . " Escritura en livewire-tmp");
        if (!$writable) {
            $allOk = false;
            $this->warn('      Ejecuta: sudo chown -R www-data:www-data storage && sudo chmod -R 775 storage');
        }
        $this->newLine();

        // ===== 4. Storage público =====
        $this->line('<fg=cyan;options=bold>4. Storage público</>');
        $symlink = public_path('storage');
        if (is_link($symlink)) {
            $this->line('   ✅ Symlink public/storage existe → ' . readlink($symlink));
        } else {
            $allOk = false;
            $this->error('   ❌ Falta el symlink public/storage.');
            $this->line('      Ejecuta: <fg=yellow>php artisan storage:link</>');
        }
        $ctaDir = storage_path('app/public/ctas');
        if (!is_dir($ctaDir)) {
            @mkdir($ctaDir, 0755, true);
            $this->line("   ✅ Creado {$ctaDir}");
        }
        $this->newLine();

        // ===== 5. Variables relevantes =====
        $this->line('<fg=cyan;options=bold>5. Configuración</>');
        $this->line('   APP_URL             : ' . config('app.url'));
        $this->line('   FILESYSTEM_DISK     : ' . config('filesystems.default'));
        $this->line('   CTA_ANNOUNCE_CHANNEL: ' . (config('app.cta_announce_channel_id') ?: '(vacío)'));
        $this->newLine();

        if ($allOk) {
            $this->info('✅ Todo en orden.');
        } else {
            $this->warn('⚠️  Corrige los puntos marcados antes de probar de nuevo.');
        }

        if ($uploadMax < 10 * 1024 * 1024) {
            $this->newLine();
            $this->line('<fg=yellow;options=bold>Recomendación para el servidor:</>');
            $this->line('   Edita php.ini (o crea .user.ini en la raíz del proyecto):');
            $this->line('   <fg=green>upload_max_filesize = 10M</>');
            $this->line('   <fg=green>post_max_size       = 12M</>');
            $this->line('   <fg=green>memory_limit        = 256M</>');
            $this->line('   Y reinicia: <fg=yellow>sudo systemctl restart php8.2-fpm</>');
        }

        return $allOk ? Command::SUCCESS : Command::FAILURE;
    }

    private function parseIniSize(string $val): int
    {
        $val = trim($val);
        if ($val === '' || $val === '-1') return PHP_INT_MAX;
        $unit = strtolower($val[strlen($val) - 1]);
        $num  = (int) $val;
        return match ($last = $unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => $num,
        };
    }

    private function fmt(int $bytes): string
    {
        if ($bytes === PHP_INT_MAX) return 'sin límite';
        if ($bytes >= 1024 * 1024)  return round($bytes / (1024 * 1024), 1) . ' MB';
        if ($bytes >= 1024)         return round($bytes / 1024, 0) . ' KB';
        return "{$bytes} B";
    }
}
