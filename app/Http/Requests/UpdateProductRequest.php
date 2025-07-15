<?php

namespace App\Http\Requests;

use App\Models\SubCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'price' => 'required|numeric|min:0',
            'supplier_price' => 'nullable|numeric|min:0|lte:price',
            'stock' => 'required|integer|min:0',
            'discount' => 'nullable|numeric|between:0,100',
            'colors' => 'required|array',
            'main_category_id' => 'nullable|exists:main_categories,id',
            'sub_category_id' => 'required|exists:sub_categories,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg|max:30720', //30MB
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:image_items,id',
        ];
        $locales = config('app.locales');
        // Add translation rules for each locale
        foreach ($locales as $locale) {
            $rules[$locale . '.name'] = 'required|string|max:255';
            $rules[$locale . '.description'] = 'nullable|string';
            $rules[$locale . '.details'] = 'nullable|string';
            $rules[$locale . '.instructions'] = 'nullable|array';
        }

        return $rules;
    }
    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        $data['main_category_id'] = SubCategory::find($data['sub_category_id'])->main_category_id;
        $data['colors'] = json_encode($data['colors']);
        $locales = config('app.locales');

        foreach ($locales as $locale) {
            if (isset($data[$locale]['instructions'])) {
                $data[$locale]['instructions'] = json_encode($data[$locale]['instructions']);
            }
        }


        return $data;
    }
}
