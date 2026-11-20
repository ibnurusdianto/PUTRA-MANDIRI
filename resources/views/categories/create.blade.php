@extends('adminlte::page')

@section('title', 'Tambah Kategori')

@section('content_header')
    <h3 class="mb-0">Tambah Kategori</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                @include('categories._form')
            </form>
        </div>
    </div>
@stop
