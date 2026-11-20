<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $type = (string) $this->input('type', 'physical');

        $merge = [
            'type' => $type,
            'sku' => Str::upper(trim((string) $this->input('sku'))),
            'barcode' => filled($this->input('barcode')) ? trim((string) $this->input('barcode')) : null,
            'provider' => filled($this->input('provider')) ? trim((string) $this->input('provider')) : null,
            'nominal' => filled($this->input('nominal')) ? $this->input('nominal') : null,
            'is_active' => $this->boolean('is_active'),
        ];

        if ($type !== 'physical') {
            $merge['stock'] = 0;
            $merge['min_stock'] = 0;
        }

        $this->merge($merge);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:64', 'unique:products,barcode'],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::in(['physical', 'pulsa', 'token'])],
            'unit' => ['required', 'string', 'max:30'],
            'provider' => ['nullable', 'string', 'max:50', 'required_if:type,pulsa', 'required_if:type,token'],
            'nominal' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99', 'required_if:type,pulsa', 'required_if:type,token'],
            'cost_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'sell_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'stock' => ['nullable', 'integer', 'min:0', 'max:1000000', 'required_if:type,physical'],
            'min_stock' => ['nullable', 'integer', 'min:0', 'max:1000000', 'required_if:type,physical'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak ditemukan.',
            'sku.required' => 'Kode barang (SKU) wajib diisi.',
            'sku.unique' => 'Kode barang (SKU) sudah digunakan.',
            'sku.max' => 'Kode barang maksimal 50 karakter.',
            'barcode.unique' => 'Barcode sudah digunakan produk lain.',
            'barcode.max' => 'Barcode maksimal 64 karakter.',
            'name.required' => 'Nama barang wajib diisi.',
            'name.max' => 'Nama barang maksimal 150 karakter.',
            'type.required' => 'Jenis produk wajib dipilih.',
            'type.in' => 'Jenis produk tidak valid.',
            'unit.required' => 'Satuan wajib diisi.',
            'provider.required_if' => 'Provider wajib diisi untuk layanan digital.',
            'nominal.required_if' => 'Nominal wajib diisi untuk layanan digital.',
            'nominal.numeric' => 'Nominal harus berupa angka.',
            'cost_price.required' => 'Harga modal wajib diisi.',
            'cost_price.numeric' => 'Harga modal harus berupa angka.',
            'cost_price.min' => 'Harga modal tidak boleh negatif.',
            'sell_price.required' => 'Harga jual wajib diisi.',
            'sell_price.numeric' => 'Harga jual harus berupa angka.',
            'sell_price.min' => 'Harga jual tidak boleh negatif.',
            'stock.required_if' => 'Stok wajib diisi untuk barang fisik.',
            'stock.integer' => 'Stok harus berupa bilangan bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'min_stock.required_if' => 'Batas minimum stok wajib diisi untuk barang fisik.',
            'min_stock.integer' => 'Batas minimum stok harus berupa bilangan bulat.',
            'min_stock.min' => 'Batas minimum stok tidak boleh negatif.',
            'image.image' => 'Foto barang harus berupa gambar.',
            'image.mimes' => 'Foto barang harus berformat jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran foto barang maksimal 2 MB.',
        ];
    }
}
