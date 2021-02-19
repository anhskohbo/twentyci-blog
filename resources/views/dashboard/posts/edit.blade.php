@extends('layouts.dashboard')

@section('page_title', 'Edit Post')

@section('content')
    <div id="page-wrapper" data-page="edit-post">
        <form class="edit-post" method="POST" action="{{ route('dashboard.posts.update', $post) }}">
            @csrf
            @method('PATCH')

            @include('dashboard.posts._form', compact('post'))
        </form>
    </div>
@stop
