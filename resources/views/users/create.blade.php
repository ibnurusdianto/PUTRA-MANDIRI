@extends('adminlte::page')

@section('title', 'Tambah Pengguna')

@section('content_header')
    <h3 class="mb-0">Tambah Pengguna</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                @include('users._form')
            </form>
        </div>
    </div>
@stop
