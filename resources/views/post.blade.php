@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h1 class="h1 card-title">{{ $post->title }}</h1>

                        <p class="card-subtitle mb-2 text-muted">
                            Posted by: {{ $post->user->name ?? '' }}
                        </p>

                        <div class="card-text">
                            {{ $post->getContent() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
