<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-expenses') ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999.99'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'expense_date.required' => 'Tanggal pengeluaran wajib diisi.',
            'expense_date.date' => 'Tanggal pengeluaran tidak valid.',
            'expense_date.before_or_equal' => 'Tanggal pengeluaran tidak boleh melebihi hari ini.',
            'category.required' => 'Kategori pengeluaran wajib diisi.',
            'category.max' => 'Kategori pengeluaran maksimal 50 karakter.',
            'amount.required' => 'Nominal pengeluaran wajib diisi.',
            'amount.numeric' => 'Nominal pengeluaran harus berupa angka.',
            'amount.min' => 'Nominal pengeluaran harus lebih dari 0.',
            'description.max' => 'Keterangan maksimal 500 karakter.',
        ];
    }
}
