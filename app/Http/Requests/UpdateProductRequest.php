<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends StoreProductRequest
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Product $product */
        $product = $this->route('product');

        $rules = parent::rules();
        $rules['sku'] = [
            'required',
            'string',
            'max:50',
            Rule::unique('products', 'sku')->ignore($product->id),
        ];
        $rules['barcode'] = [
            'nullable',
            'string',
            'max:64',
            Rule::unique('products', 'barcode')->ignore($product->id),
        ];

        return $rules;
    }
}
