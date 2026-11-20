<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-catalog') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'stock.required' => 'Stok hasil penyesuaian wajib diisi.',
            'stock.integer' => 'Stok hasil penyesuaian harus bilangan bulat.',
            'stock.min' => 'Stok tidak boleh negatif.',
            'reason.required' => 'Alasan penyesuaian stok wajib diisi.',
            'reason.max' => 'Alasan penyesuaian maksimal 255 karakter.',
        ];
    }
}
