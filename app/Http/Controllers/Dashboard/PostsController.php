<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Repository\PostRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return Response|mixed
     */
    public function index(Request $request)
    {
        $repository = app(PostRepository::class);

        $query = $repository->query(current_user());

        // Hold the filter data.
        $currentFilters = [];

        $filters = [
            function ($query) use ($request, &$currentFilters) {
                $_filters = $request->get('filters');

                if (isset($_filters['created_at']) /*&& is_valid_date($_filters['created_at'])*/) {
                    try {
                        $date = Carbon::createFromFormat('Y-m-d', $_filters['created_at']);
                        $query->whereDate('created_at', '=', $date);
                        $currentFilters['created_at'] = $date->format('Y-m-d');
                    } catch (Exception $e) {
                        // TODO: Catch the exception
                    }
                }

                if (isset($_filters['status']) && in_array($_filters['status'], ['draft', 'publish'])) {
                    $query->where('status', '=', $_filters['status']);
                    $currentFilters['status'] = $_filters['status'];
                }
            },
        ];

        $orderings = [];
        if (in_array($sortBy = $request->get('sortBy'), ['id', 'title', 'created_at', 'updated_at'], true)) {
            $orderings = [$sortBy => strtolower($request->get('sortDirection', 'desc'))];
        }

        $repository->buildIndexQuery(
            $query,
            sanitize_text_field($request->get('term')),
            $filters,
            $orderings
        );

        $unPublishPosts = null;
        if (current_user()->isSuperAdmin()) {
            $unPublishPosts = $repository
                ->query(current_user())
                ->unPublish()
                ->with('user')
                ->get();
        }

        if ($unPublishPosts && $unPublishPosts->isNotEmpty()) {
            $query->whereNotIn('id', $unPublishPosts->pluck('id'));
        }

        $posts = $query
            ->paginate()
            ->appends($request->only('term', 'filters', 'sortBy', 'sortDirection'));

        return view(
            'dashboard.posts.index',
            compact('posts', 'currentFilters', 'orderings', 'unPublishPosts')
        );
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
                'status' => 'required',
                'published_at' => 'nullable|date',
            ]
        );

        $post = Post::start(sanitize_text_field($values['title']), current_user());
        $post->content = sanitize_textarea_field($values['content']);
        $post->status = ($values['status'] === 'publish' && current_user()->can('publish', $post))
            ? Post::STATUS_PUBLISH
            : Post::STATUS_DRAFT;

        if (current_user()->can('publish', $post)) {
            $post->published_at = $values['published_at'] ?? null;
        }

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
                'status' => 'required',
                'published_at' => 'nullable|date',
            ]
        );

        $post->title = sanitize_text_field($values['title']);
        $post->content = sanitize_textarea_field($values['content']);
        $post->status = ($values['status'] === 'publish' && current_user()->can('publish', $post))
            ? Post::STATUS_PUBLISH
            : Post::STATUS_DRAFT;

        if (current_user()->can('publish', $post)) {
            $post->published_at = $values['published_at'] ?? null;
        }

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

        return redirect()
            ->route('dashboard.posts.index')
            ->with('success', __('Post Deleted'));
    }
}
