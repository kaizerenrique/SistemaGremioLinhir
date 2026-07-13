<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BloggerService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckNewBlogPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:check-new-posts {--test : Modo prueba, no envía notificación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprueba si hay nuevos posts en Blogger y envía notificación a Discord';

    /**
     * Execute the console command.
     */
    public function handle(BloggerService $blogger)
    {
        $this->info('🔍 Comprobando nuevos posts en Blogger...');

        // Obtener el post más reciente (solo 1)
        $data = $blogger->getPosts(null, 1);
        if (empty($data['items'])) {
            $this->warn('No se encontraron posts en el blog.');
            return Command::SUCCESS;
        }

        $latestPost = $data['items'][0];
        $postId = $latestPost['id'];
        $cacheKey = 'blogger_last_post_id';

        // Obtener el último ID almacenado en caché
        $lastId = Cache::get($cacheKey);

        if ($lastId === $postId) {
            $this->info('✅ No hay nuevos posts. Último ID: ' . $postId);
            return Command::SUCCESS;
        }

        // Si es la primera vez (no hay caché) o es un post nuevo
        if ($lastId === null) {
            $this->info('📝 Primera ejecución. Guardando ID del último post: ' . $postId);
            Cache::forever($cacheKey, $postId);
            return Command::SUCCESS;
        }

        // --- Nuevo post detectado ---
        $this->info('🆕 Nuevo post detectado: ' . $latestPost['title']);

        // Si es modo prueba, no enviamos
        if ($this->option('test')) {
            $this->info('🧪 Modo prueba: no se enviará notificación.');
            $this->line('Título: ' . $latestPost['title']);
            $this->line('URL: ' . $latestPost['url'] ?? route('blog.show', $postId));
            // Actualizar caché igualmente para futuras comparaciones
            Cache::forever($cacheKey, $postId);
            return Command::SUCCESS;
        }

        // Enviar notificación a Discord
        $this->sendDiscordNotification($latestPost);

        // Actualizar caché con el nuevo ID
        Cache::forever($cacheKey, $postId);
        $this->info('✅ Notificación enviada y caché actualizada.');

        return Command::SUCCESS;
    }

    /**
     * Envía un embed a Discord con la información del nuevo post.
     */
    private function sendDiscordNotification(array $post): void
    {
        $webhookUrl = config('services.discord.blog_webhook');
        if (empty($webhookUrl)) {
            $this->error('❌ Webhook de Discord no configurado. Define DISCORD_BLOG_WEBHOOK_URL en .env');
            return;
        }

        $title = $post['title'];
        $content = Str::limit(strip_tags($post['content']), 500);
        $published = Carbon::parse($post['published'])->format('d/m/Y H:i');
        $postUrl = $post['url'] ?? route('blog.show', $post['id']); // Blogger devuelve 'url' en el item
        $imageUrl = $post['images'][0]['url'] ?? null;

        // Construir embed
        $embed = [
            'title' => "📢 ¡Nuevo artículo en el blog!",
            'description' => "**{$title}**\n\n{$content}",
            'color' => 0xC54B47, // Color primario de Linhir
            'url' => $postUrl,
            'timestamp' => Carbon::now()->toIso8601String(),
            'footer' => [
                'text' => 'Linhir • Blog',
                'icon_url' => asset('imagenes/escudo_512x512.png'),
            ],
            'author' => [
                'name' => $post['author']['displayName'] ?? 'Blogger',
            ],
            'fields' => [
                [
                    'name' => '📅 Publicado',
                    'value' => $published,
                    'inline' => true,
                ],
                [
                    'name' => '🔗 Enlace',
                    'value' => "[Leer más]($postUrl)",
                    'inline' => true,
                ],
            ],
        ];

        if ($imageUrl) {
            $embed['image'] = ['url' => $imageUrl];
        }

        // Datos del mensaje
        $payload = [
            'username' => 'Linhir Blog Notifier',
            'avatar_url' => asset('imagenes/linhir_escudo_180.png'),
            'embeds' => [$embed],
        ];

        // Enviar
        try {
            $response = Http::post($webhookUrl, $payload);
            if ($response->successful()) {
                $this->info('✅ Notificación enviada a Discord.');
            } else {
                $this->error('❌ Error al enviar: ' . $response->status());
                $this->error($response->body());
            }
        } catch (\Exception $e) {
            $this->error('❌ Excepción: ' . $e->getMessage());
        }
    }
}
