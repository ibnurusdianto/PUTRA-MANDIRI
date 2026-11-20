@extends('adminlte::page')

@section('title', 'Ubah Pengeluaran')

@section('content_header')
    <h3 class="mb-0">Ubah Pengeluaran Operasional</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('expenses.update', $expense) }}" method="POST">
                @csrf
                @method('PUT')
                @include('expenses._form')
            </form>
        </div>
    </div>
@stop
