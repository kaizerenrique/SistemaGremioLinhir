<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BloggerService
{
    protected ?string $apiKey;
    protected ?string $blogId;

    protected string $baseUrl = 'https://www.googleapis.com/blogger/v3';

    /**
     * TTLs por tipo de recurso (segundos).
     */
    protected int $ttlPosts       = 900;    // 15 min: los listados cambian con frecuencia
    protected int $ttlPost        = 3600;   // 1 h: un post individual cambia poco
    protected int $ttlBlogInfo    = 86400;  // 24 h: nombre/descripción cambian casi nunca
    protected int $ttlSitemapList = 21600;  // 6 h: el sitemap no necesita frescura extrema

    public function __construct()
    {
        $this->apiKey = config('services.blogger.key');
        $this->blogId = config('services.blogger.blog_id');
    }

    /**
     * Obtiene la lista de posts con paginación.
     *
     * @param  string|null  $pageToken  Token de paginación (null = primera página).
     * @param  int          $maxResults Cantidad de posts (1–500).
     * @param  array        $options    Opciones adicionales:
     *                                  - fetch_bodies: bool (default true)
     *                                  - fetch_images: bool (default true)
     *                                  - ttl: int segundos (default según propósito)
     * @return array{items: array, nextPageToken: ?string}
     */
    public function getPosts($pageToken = null, $maxResults = 10, array $options = []): array
    {
        $maxResults   = max(1, min(500, (int) $maxResults)); // API admite 1..500
        $fetchBodies  = $options['fetch_bodies']  ?? true;
        $fetchImages  = $options['fetch_images']  ?? true;
        $ttl          = $options['ttl']           ?? $this->ttlPosts;

        // La clave incluye TODO lo que afecta al resultado.
        $cacheKey = 'blogger_posts_' . md5(json_encode([
            'blog'    => $this->blogId,
            'page'    => $pageToken,
            'max'     => $maxResults,
            'bodies'  => (bool) $fetchBodies,
            'images'  => (bool) $fetchImages,
        ]));

        return Cache::remember($cacheKey, $ttl, function () use ($pageToken, $maxResults, $fetchBodies, $fetchImages) {
            return $this->fetchPostsFromApi($pageToken, $maxResults, $fetchBodies, $fetchImages);
        });
    }

    /**
     * Petición cruda a la API de Blogger.
     */
    protected function fetchPostsFromApi($pageToken, int $maxResults, bool $fetchBodies, bool $fetchImages): array
    {
        if (empty($this->apiKey) || empty($this->blogId)) {
            Log::warning('BloggerService: falta BLOGGER_API_KEY o BLOGGER_BLOG_ID.');
            return ['items' => [], 'nextPageToken' => null];
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 500)
                ->get("{$this->baseUrl}/blogs/{$this->blogId}/posts", [
                    'key'          => $this->apiKey,
                    'maxResults'   => $maxResults,
                    'pageToken'    => $pageToken,
                    'fetchBodies'  => $fetchBodies,
                    'fetchImages'  => $fetchImages,
                ]);

            if ($response->successful()) {
                $json = $response->json() ?? [];
                return [
                    'items'         => $json['items'] ?? [],
                    'nextPageToken' => $json['nextPageToken'] ?? null,
                ];
            }

            Log::warning('BloggerService: respuesta no exitosa al listar posts.', [
                'status' => $response->status(),
                'body'   => \Illuminate\Support\Str::limit($response->body(), 500),
            ]);

            return ['items' => [], 'nextPageToken' => null];

        } catch (\Throwable $e) {
            Log::error('BloggerService: excepción al listar posts.', [
                'error' => $e->getMessage(),
            ]);
            return ['items' => [], 'nextPageToken' => null];
        }
    }

    /**
     * Obtiene un post específico por su ID.
     *
     * @return array|null null si no existe o hubo fallo.
     */
    public function getPost($postId): ?array
    {
        if (empty($this->apiKey) || empty($this->blogId) || empty($postId)) {
            return null;
        }

        $cacheKey = 'blogger_post_' . md5((string) $postId);

        return Cache::remember($cacheKey, $this->ttlPost, function () use ($postId) {
            try {
                $response = Http::timeout(10)
                    ->retry(2, 500)
                    ->get("{$this->baseUrl}/blogs/{$this->blogId}/posts/{$postId}", [
                        'key' => $this->apiKey,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('BloggerService: post no encontrado o error.', [
                    'post_id' => $postId,
                    'status'  => $response->status(),
                ]);

                return null;

            } catch (\Throwable $e) {
                Log::error('BloggerService: excepción al obtener post.', [
                    'post_id' => $postId,
                    'error'   => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Obtiene información del blog (nombre, descripción, etc.).
     *
     * @return array|null null si hubo fallo.
     */
    public function getBlogInfo(): ?array
    {
        if (empty($this->apiKey) || empty($this->blogId)) {
            return null;
        }

        return Cache::remember('blogger_info_' . md5((string) $this->blogId), $this->ttlBlogInfo, function () {
            try {
                $response = Http::timeout(10)
                    ->retry(2, 500)
                    ->get("{$this->baseUrl}/blogs/{$this->blogId}", [
                        'key' => $this->apiKey,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                Log::warning('BloggerService: respuesta no exitosa al obtener blog info.', [
                    'status' => $response->status(),
                ]);

                return null;

            } catch (\Throwable $e) {
                Log::error('BloggerService: excepción al obtener blog info.', [
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        });
    }

    /**
     * Helper específico para el sitemap. Devuelve todos los posts
     * con un TTL más alto y sin cuerpos (ahorra cuota y ancho de banda).
     */
    public function getPostsForSitemap(): array
    {
        return $this->getPosts(null, 500, [
            'fetch_bodies' => false,
            'fetch_images' => false,
            'ttl'          => $this->ttlSitemapList,
        ]);
    }

    /**
     * Invalida manualmente las cachés del blog (útil tras publicar).
     */
    public function flushCache(): void
    {
        // Laravel no permite borrar por prefijo sin conocer las claves exactas,
        // así que las claves que conocemos se borran explícitamente y el resto
        // expirará por TTL. Si usas Redis puedes hacer SCAN/DEL.
        Cache::forget('blogger_info_' . md5((string) $this->blogId));

        // Nota: las claves de listados contienen un hash con los parámetros;
        // si necesitas invalidarlas todas, considera usar un "cache tag".
        // Alternativa rápida: versionar con un "blogger_v" que se incremente aquí.
    }
}
