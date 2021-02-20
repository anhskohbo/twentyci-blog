@extends('layouts.dashboard')

@section('page_title', 'Posts')

@section('page_actions')
    <a href="{{ route('dashboard.posts.create') }}" class="btn btn-link">Add New Post</a>
@endsection

@php
    /** @var array $orderings */
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator $posts */
    $baseUrl = $posts->url(1); // always start at page 1.

    $sortUrl = function ($column, $direction) use ($baseUrl) {
        return add_query_arg(['sortBy' => $column, 'sortDirection' => $direction], $baseUrl);
    };

    $sortByColumn = function ($column) use ($sortUrl, $orderings) {
        $directions = ['asc' => 'desc', 'desc' => 'asc'];

        if (isset($orderings[$column])) {
            $toDirection = $directions[$orderings[$column]];
            $arrow = $orderings[$column] === 'desc' ? '&#8675;' : '&#8673;';
        } else {
            $toDirection = 'asc';
            $arrow = '&#8693;';
        }

        return sprintf('<a href="%1$s"><span>%2$s</span></a>', $sortUrl($column, $toDirection), $arrow);
    };
@endphp

@section('content')
    <div id="page-wrapper" data-page="index-posts">

        <div class="row mb-5">
            <div class="col-md-8">
                <form action="" method="GET" class="d-block">
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center">
                            <label for="filter_status" class="mr-1 mt-1">Status</label>
                            <select id="filter_status" name="filters[status]" class="form-control" style="width: 150px;">
                                <option value="">All</option>
                                <option value="publish" {{ ($currentFilters['status'] ?? '') === 'publish' ? 'selected' : '' }}>Published</option>
                                <option value="draft" {{ ($currentFilters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
                            </select>
                        </div>

                        <div class="d-flex align-items-center ml-2">
                            <label for="filter_created_at" class="mr-1 mt-1">Created date</label>
                            <input
                                type="text"
                                name="filters[created_at]"
                                value="{{ $currentFilters['created_at'] ?? '' }}"
                                id="filter_created_at"
                                class="form-control bg-white"
                                data-init="flatpickr"
                                style="width: 150px;"
                            >
                        </div>

                        <div class="ml-2">
                            <button class="btn btn-outline-secondary" type="submit">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-4">
                <form action="" method="GET" class="d-block">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search posts..." name="term" value="{{ request()->term }}">

                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">Search</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if( current_user()->isSuperAdmin() && $unPublishPosts && $unPublishPosts->isNotEmpty())
            <h3>UnPublish Posts</h3>

            <table class="table table-dark">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Title</th>
                        <th scope="col">Status</th>
                        <th scope="col">Author</th>
                        <th scope="col">Publish Date</th>
                        <th scope="col">Created</th>
                        <th scope="col">Last Modified</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($unPublishPosts as $post)
                        <tr class="">
                            <th scope="row">{{ $post->id }}</th>
                            <td style="width: 30%">
                                <a href="{{ route('dashboard.posts.edit', $post) }}"><strong>{{ $post->title }}</strong></a>

                                <div class="mt-2" style="font-size: 13px;">
                                    <a href="{{ route('post', $post->slug) }}">View</a>
                                    <span style="color: #ddd" class="d-inline-block ml-1 mr-1 user-select-none">|</span>

                                    <a href="{{ route('dashboard.posts.edit', $post) }}">Publish Post</a>
                                    <span style="color: #ddd" class="d-inline-block ml-1 mr-1 user-select-none">|</span>

                                    <a href="#" class="delete-action text-danger" data-action="{{ route('dashboard.posts.destroy', $post->id) }}">Delete</a>
                                </div>
                            </td>
                            <td>{{ __('Unpublished') }}</td>
                            <td>{{ $post->user->name ?? '' }}</td>
                            <td>{{ $post->published_at ? $post->published_at->shortRelativeToNowDiffForHumans() : '' }}</td>
                            <td>{{ $post->created_at }}</td>
                            <td>{{ $post->updated_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">
                        # {!! $sortByColumn('id') !!}
                    </th>
                    <th scope="col">Title {!! $sortByColumn('title') !!}</th>
                    <th scope="col">Status</th>
                    <th scope="col">Author</th>
                    <th scope="col">Created {!! $sortByColumn('created_at') !!}</th>
                    <th scope="col">Last Modified {!! $sortByColumn('updated_at') !!}</th>
                </tr>
            </thead>

            <tbody>
                @if($posts->isEmpty())
                    <tr>
                        <td colspan="6">
                            <p class="text-center">No entries found</p>
                        </td>
                    </tr>
                @endif

                @foreach($posts as $post)
                    <tr>
                        <th scope="row">{{ $post->id }}</th>
                        <td style="width: 50%">
                            <a href="{{ route('dashboard.posts.edit', $post) }}"><strong>{{ $post->title }}</strong></a>

                            <div class="mt-2" style="font-size: 13px;">
                                <a href="{{ route('post', $post->slug) }}">View</a>
                                <span style="color: #ddd" class="d-inline-block ml-1 mr-1 user-select-none">|</span>

                                <a href="{{ route('dashboard.posts.edit', $post) }}">Edit</a>
                                <span style="color: #ddd" class="d-inline-block ml-1 mr-1 user-select-none">|</span>

                                <a href="#" class="delete-action text-danger" data-action="{{ route('dashboard.posts.destroy', $post->id) }}">Delete</a>
                            </div>
                        </td>
                        <td>{{ $post->status }}</td>
                        <td>{{ $post->user->name ?? '' }}</td>
                        <td>{{ $post->created_at }}</td>
                        <td>{{ $post->updated_at }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>
@stop
