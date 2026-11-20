@extends('adminlte::page')

@section('title', 'Ubah Pengguna')

@section('content_header')
    <h3 class="mb-0">Ubah Pengguna: {{ $user->name }}</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                @include('users._form')
            </form>
        </div>
    </div>
@stop
