<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BloggerService
{
    protected $apiKey;
    protected $blogId;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $this->apiKey = config('services.blogger.key');
        $this->blogId = config('services.blogger.blog_id');
    }

    /**
     * Obtiene la lista de posts con paginación.
     *
     * @param string|null $pageToken
     * @param int $maxResults
     * @return array
     */
    public function getPosts($pageToken = null, $maxResults = 10)
    {
        $cacheKey = 'blogger_posts_' . ($pageToken ?? 'first');
        return Cache::remember($cacheKey, 900, function () use ($pageToken, $maxResults) {
            $response = Http::get("https://www.googleapis.com/blogger/v3/blogs/{$this->blogId}/posts", [
                'key' => $this->apiKey,
                'maxResults' => $maxResults,
                'pageToken' => $pageToken,
                'fetchBodies' => true,   // Trae el contenido completo
                'fetchImages' => true,   // Trae las imágenes destacadas
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Manejo de errores: podrías loguear o devolver un array vacío
            return ['items' => [], 'nextPageToken' => null];
        });
    }

    /**
     * Obtiene un post específico por su ID.
     *
     * @param string $postId
     * @return array|null
     */
    public function getPost($postId)
    {
        $cacheKey = 'blogger_post_' . $postId;
        return Cache::remember($cacheKey, 3600, function () use ($postId) {
            $response = Http::get("https://www.googleapis.com/blogger/v3/blogs/{$this->blogId}/posts/{$postId}", [
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }

    /**
     * Obtiene información del blog (nombre, descripción, etc.).
     *
     * @return array|null
     */
    public function getBlogInfo()
    {
        return Cache::remember('blogger_info', 86400, function () {
            $response = Http::get("https://www.googleapis.com/blogger/v3/blogs/{$this->blogId}", [
                'key' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        });
    }
}
