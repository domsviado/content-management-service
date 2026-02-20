<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SearchContentRequest extends FormRequest
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
        return [
            'q' => 'nullable|string|min:2',
            'tag' => 'nullable|string',
            'locale' => 'nullable|string|max:5',
        ];
        ;
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $filters = array_filter($this->only(['q', 'tag', 'locale']));

            if (empty($filters)) {
                $validator->errors()->add('q', 'Please provide at least one search criteria.');
            }
        });
    }
}
