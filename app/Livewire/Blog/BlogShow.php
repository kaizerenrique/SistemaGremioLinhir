<?php

namespace App\Livewire\Blog;

use Livewire\Component;
use App\Services\BloggerService;
use Illuminate\Support\Str;

class BlogShow extends Component
{
    public $post = null;
    public $postId;

    public function mount($id, BloggerService $blogger)
    {
        $this->postId = $id;
        $this->post = $blogger->getPost($id);
        if (!$this->post) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.blog.blog-show', [
            'post' => $this->post,
        ])->layout('layouts.blog', [
            'title' => $this->post['title'] . ' - Linhir Blog',
            'description' => Str::limit(strip_tags($this->post['content']), 160),
            'ogImage' => $this->post['images'][0]['url'] ?? asset('imagenes/escudo_512x512.png'),
        ]);
    }
}
