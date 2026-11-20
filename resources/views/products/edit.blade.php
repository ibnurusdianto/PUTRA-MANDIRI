@extends('adminlte::page')

@section('title', 'Ubah Barang')

@section('content_header')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h3 class="mb-0">Ubah Barang: {{ $product->name }}</h3>
        @if ($product->type->isPhysical())
            <a href="{{ route('products.stock', $product) }}" class="btn btn-outline-secondary">
                <i class="bi bi-boxes me-1"></i> Kelola Stok
            </a>
        @endif
    </div>
@stop

@section('content')
    @include('partials.flash')

    <div class="card">
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
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
