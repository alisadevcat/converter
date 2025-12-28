<?php

namespace App\Modules\Currency\Requests;

use App\Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertCurrencyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint, no authentication required
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0',
                'max:999999999', // Prevent extremely large numbers
            ],
            'from_currency' => [
                'required',
                'string',
                'size:3',
                'uppercase',
                function ($attribute, $value, $fail) {
                    if (!CurrencyConfigService::isValidCode($value)) {
                        $fail('The selected source currency is invalid or not available.');
                    }
                },
            ],
            'to_currency' => [
                'required',
                'string',
                'size:3',
                'uppercase',
                function ($attribute, $value, $fail) {
                    if (!CurrencyConfigService::isValidCode($value)) {
                        $fail('The selected target currency is invalid or not available.');
                    }
                },
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter an amount to convert.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => 'The amount must be at least 0.',
            'amount.max' => 'The amount is too large. Maximum value is 999,999,999.',
            'from_currency.required' => 'Please select a source currency.',
            'from_currency.size' => 'The source currency code must be exactly 3 characters.',
            'from_currency.uppercase' => 'The source currency code must be uppercase.',
            'from_currency.exists' => 'The selected source currency is invalid or not available.',
            'to_currency.required' => 'Please select a target currency.',
            'to_currency.size' => 'The target currency code must be exactly 3 characters.',
            'to_currency.uppercase' => 'The target currency code must be uppercase.',
            'to_currency.exists' => 'The selected target currency is invalid or not available.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert currency codes to uppercase if provided
        if ($this->has('from_currency')) {
            $this->merge([
                'from_currency' => strtoupper($this->from_currency),
            ]);
        }

        if ($this->has('to_currency')) {
            $this->merge([
                'to_currency' => strtoupper($this->to_currency),
            ]);
        }
    }
}
