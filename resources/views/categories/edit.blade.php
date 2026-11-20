@extends('adminlte::page')

@section('title', 'Ubah Kategori')

@section('content_header')
    <h3 class="mb-0">Ubah Kategori: {{ $category->name }}</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('categories.update', $category) }}" method="POST">
                @csrf
                @method('PUT')
                @include('categories._form')
            </form>
        </div>
    </div>
@stop
