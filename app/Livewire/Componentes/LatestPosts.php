<?php

namespace App\Livewire\Componentes;

use Livewire\Component;
use App\Services\BloggerService;

class LatestPosts extends Component
{
    public $posts = [];
    public $blogTitle = 'Blog';

    public function mount(BloggerService $blogger)
    {
        // Obtener información del blog para mostrar el título (opcional)
        $blogInfo = $blogger->getBlogInfo();
        if ($blogInfo && isset($blogInfo['blog']['name'])) {
            $this->blogTitle = $blogInfo['blog']['name'];
        }

        // Obtener los últimos 3 posts
        $data = $blogger->getPosts(null, 3);
        $this->posts = $data['items'] ?? [];
    }

    public function render()
    {
        return view('livewire.componentes.latest-posts', [
            'posts' => $this->posts,
            'blogTitle' => $this->blogTitle,
        ]);
    }
}
