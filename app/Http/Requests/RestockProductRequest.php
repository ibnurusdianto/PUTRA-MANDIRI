<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestockProductRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.required' => 'Jumlah penambahan stok wajib diisi.',
            'quantity.integer' => 'Jumlah penambahan stok harus bilangan bulat.',
            'quantity.min' => 'Jumlah penambahan stok minimal 1.',
            'note.max' => 'Catatan maksimal 255 karakter.',
        ];
    }
}
