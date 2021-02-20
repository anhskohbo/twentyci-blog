@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @foreach($posts as $post)
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="h4 card-title">
                                <a href="{{ route('post', $post->slug) }}">{{ $post->title }}</a>
                            </h2>

                            <p class="card-subtitle mb-2 text-muted">
                                Posted by: {{ $post->user->name ?? '' }}
                            </p>

                            <div class="card-text">
                                {{ $post->getContent() }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-center">
            {{ $posts->links() }}
        </div>
    </div>
@endsection
