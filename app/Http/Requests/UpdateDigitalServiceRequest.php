<?php

namespace App\Http\Requests;

use App\Models\DigitalServiceDetail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDigitalServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var DigitalServiceDetail|null $detail */
        $detail = $this->route('digitalService');

        return $detail instanceof DigitalServiceDetail
            && ($this->user()?->can('update', $detail) ?? false);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::in(['completed', 'failed'])],
            'provider_reference' => ['nullable', 'string', 'max:100'],
            'token_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500', 'required_if:status,failed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status layanan wajib dipilih.',
            'status.in' => 'Status layanan tidak valid.',
            'provider_reference.max' => 'Nomor referensi provider maksimal 100 karakter.',
            'token_number.max' => 'Nomor token maksimal 100 karakter.',
            'notes.required_if' => 'Catatan wajib diisi ketika layanan ditandai gagal.',
            'notes.max' => 'Catatan maksimal 500 karakter.',
        ];
    }
}
