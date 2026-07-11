<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Services\BloggerService;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for Linhir';

    /**
     * Execute the console command.
     */
    public function handle(BloggerService $blogger)
    {
        $sitemap = Sitemap::create();

        // Página principal - máxima prioridad
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            ->setLastModificationDate(now()));      

        // Páginas de autenticación - baja prioridad (no son contenido principal)
        $sitemap->add(Url::create('/login')
            ->setPriority(0.3)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));

        $sitemap->add(Url::create('/register')
            ->setPriority(0.3)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));

        $sitemap->add(Url::create('/forgot-password')
            ->setPriority(0.1)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY));
        // Añadir posts del blog
        try {
            $postsData = $blogger->getPosts(null, 500); // Obtener hasta 500 posts
            if (!empty($postsData['items'])) {
                foreach ($postsData['items'] as $post) {
                    $sitemap->add(Url::create("/blog/{$post['id']}")
                        ->setLastModificationDate(now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8));
                }
            }
            // También añadir la página principal del blog
            $sitemap->add(Url::create('/blog')
                ->setPriority(0.9)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setLastModificationDate(now()));
        } catch (\Exception $e) {
            // Log del error pero continuar
            $this->error('Error al añadir posts del blog al sitemap: ' . $e->getMessage());
        }

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap generated successfully!');
        $this->info('📍 Location: ' . public_path('sitemap.xml'));
        $this->info('🌐 URL: ' . config('app.url') . '/sitemap.xml');
        
        return Command::SUCCESS;
    }
}
