<?php

namespace App\Livewire\Blog;

use Livewire\Component;
use App\Services\BloggerService;

class BlogIndex extends Component
{
    public $posts = [];
    public $nextPageToken = null;
    public $currentPageToken = null;
    public $blogInfo = null;
    public $loading = false;

    public function mount(BloggerService $blogger)
    {
        // Obtener información del blog (título, descripción)
        $this->blogInfo = $blogger->getBlogInfo();
        // Cargar primera página
        $this->loadPosts($blogger);
    }

    public function loadPosts(BloggerService $blogger, $pageToken = null)
    {
        $this->loading = true;
        $data = $blogger->getPosts($pageToken, 9); // 9 posts por página
        $this->posts = $data['items'] ?? [];
        $this->nextPageToken = $data['nextPageToken'] ?? null;
        $this->currentPageToken = $pageToken;
        $this->loading = false;
    }

    public function nextPage(BloggerService $blogger)
    {
        if ($this->nextPageToken) {
            $this->loadPosts($blogger, $this->nextPageToken);
        }
    }

    public function render()
    {
        return view('livewire.blog.blog-index', [
            'posts' => $this->posts,
            'blogInfo' => $this->blogInfo,
            'nextPageToken' => $this->nextPageToken,
            'loading' => $this->loading,
        ])->layout('layouts.blog'); // Usa el layout principal de Linhir
    }
}
