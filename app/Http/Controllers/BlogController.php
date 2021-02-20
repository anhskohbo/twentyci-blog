<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Repository\PostRepository;

class BlogController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function home()
    {
        $repository = app(PostRepository::class);

        $posts = $repository->query()
            ->with('user')
            ->published()
            ->paginate();

        return view('home', compact('posts'));
    }

    /**
     * Show the single post.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function post(Post $post)
    {
        abort_if(!$post->isPublished(), 404);

        return view('post', compact('post'));
    }
}
