@extends('adminlte::page')

@section('title', 'Tambah Barang')

@section('content_header')
    <h3 class="mb-0">Tambah Barang</h3>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('products._form')
            </form>
        </div>
    </div>
@stop

@push('js')
    <script>
        (() => {
            const typeSelect = document.getElementById('product_type');
            if (!typeSelect) return;
            const toggle = () => {
                const digital = typeSelect.value !== 'physical';
                document.querySelectorAll('.digital-field').forEach((el) => el.classList.toggle('d-none', !digital));
                document.querySelectorAll('.physical-field').forEach((el) => el.classList.toggle('d-none', digital));
            };
            typeSelect.addEventListener('change', toggle);
            toggle();
        })();
    </script>
@endpush
