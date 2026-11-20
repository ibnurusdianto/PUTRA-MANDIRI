@extends('adminlte::page')

@section('title', 'Catat Pengeluaran')

@section('content_header')
    <h3 class="mb-0">Catat Pengeluaran Operasional</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('expenses.store') }}" method="POST">
                @csrf
                @include('expenses._form', ['categories' => $categories])
            </form>
        </div>
    </div>
@stop
