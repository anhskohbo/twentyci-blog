@extends('layouts.dashboard')

@section('page_title', 'Create new post')

@section('content')
    <div id="page-wrapper" data-page="edit-post">
        <form class="edit-post" method="POST" action="{{ route('dashboard.posts.store') }}">
            @csrf

            @include('dashboard.posts._form', compact('post'))
        </form>
    </div>
@stop
