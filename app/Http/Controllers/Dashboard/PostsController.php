<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response|mixed
     */
    public function create()
    {
        $this->authorize('start', Post::class);

        $post = Post::start('', current_user());

        return view('dashboard.posts.create', compact('post'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response|mixed
     */
    public function store(Request $request)
    {
        $this->authorize('start', Post::class);

        $values = $request->validate(
            [
                'title' => 'required|max:255',
                'content' => 'required',
            ]
        );

        $post = Post::start(sanitize_text_field($values['title']), current_user());
        $post->content = sanitize_textarea_field($values['content']);

        $post->saveOrFail();

        return redirect()
            ->route('dashboard.posts.edit', [$post->getKey()])
            ->with('success', __('Post Created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param Post $post
     * @return Response
     */
    public function show(Post $post)
    {
        return response('');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Post $post
     * @return Response|mixed
     */
    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return view('dashboard.posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Post $post
     * @return Response|mixed
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $values = $request->validate(
            [
                'title' => 'required|max:255',
                'content' => 'required',
            ]
        );

        $post->title = sanitize_text_field($values['title']);
        $post->content = sanitize_textarea_field($values['content']);

        $post->saveOrFail();

        return redirect()
            ->back()
            ->with('success', __('Post Updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @return Response|mixed
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('dashboard.posts.index');
    }
}
