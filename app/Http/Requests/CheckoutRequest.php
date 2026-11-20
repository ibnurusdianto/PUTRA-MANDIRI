<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('use-pos') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        if (is_string($items)) {
            $decoded = json_decode($items, true);
            $this->merge(['items' => is_array($decoded) ? $decoded : []]);
        }

        if ($this->input('discount_value') === '') {
            $this->merge(['discount_value' => null]);
        }

        if ($this->input('paid_amount') === '') {
            $this->merge(['paid_amount' => 0]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'checkout_token' => ['required', 'string', 'min:8', 'max:64'],
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'items.*.phone_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s]{6,20}$/'],
            'items.*.customer_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9A-Za-z\-\s]{4,30}$/'],
            'items.*.meter_number' => ['nullable', 'string', 'max:30', 'regex:/^[0-9\-\s]{6,20}$/'],
            'discount_type' => ['nullable', 'in:nominal,percent'],
            'discount_value' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'payment_method' => ['required', 'in:cash,transfer,qris'],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function ($validator): void {
                if ($this->input('discount_type') === 'percent' && (float) $this->input('discount_value') > 100) {
                    $validator->errors()->add('discount_value', 'Persentase diskon harus antara 0 sampai 100.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'checkout_token.required' => 'Token checkout tidak ditemukan. Muat ulang halaman kasir.',
            'items.required' => 'Keranjang belanja masih kosong.',
            'items.min' => 'Keranjang belanja masih kosong.',
            'items.*.product_id.required' => 'Produk pada keranjang tidak valid.',
            'items.*.product_id.exists' => 'Produk pada keranjang tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah barang wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah barang harus bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah barang minimal 1.',
            'items.*.phone_number.regex' => 'Format nomor HP tidak valid.',
            'items.*.customer_id.regex' => 'Format ID pelanggan tidak valid.',
            'items.*.meter_number.regex' => 'Format nomor meter tidak valid.',
            'discount_type.in' => 'Jenis diskon tidak valid.',
            'discount_value.numeric' => 'Nilai diskon harus berupa angka.',
            'discount_value.min' => 'Diskon tidak boleh negatif.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'paid_amount.numeric' => 'Jumlah pembayaran harus berupa angka.',
            'paid_amount.min' => 'Jumlah pembayaran tidak boleh negatif.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
