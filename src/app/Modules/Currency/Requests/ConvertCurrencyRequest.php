<?php

namespace App\Modules\Currency\Requests;

use App\Modules\Currency\Services\CurrencyConfigService;
use Illuminate\Foundation\Http\FormRequest;

class ConvertCurrencyRequest extends FormRequest
{
    private const CURRENCY_CODE_LENGTH = 3;
    private const MAX_AMOUNT = 999999999;

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
                'max:' . self::MAX_AMOUNT, // Prevent extremely large numbers
            ],
            'from_currency' => [
                'required',
                'string',
                'size:' . self::CURRENCY_CODE_LENGTH,
                'uppercase',
                function ($attribute, $value, $fail) {
                    if (!CurrencyConfigService::isValidCode($value)) {
                        $fail('The selected source currency code is not supported. Please select a valid currency.');
                    }
                },
            ],
            'to_currency' => [
                'required',
                'string',
                'size:' . self::CURRENCY_CODE_LENGTH,
                'uppercase',
                function ($attribute, $value, $fail) {
                    if (!CurrencyConfigService::isValidCode($value)) {
                        $fail('The selected target currency code is not supported. Please select a valid currency.');
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
            'amount.max' => 'The amount is too large. Maximum value is ' . number_format(self::MAX_AMOUNT) . '.',
            'from_currency.required' => 'Please select a source currency.',
            'from_currency.size' => 'The source currency code must be exactly 3 characters (e.g., USD, EUR).',
            'from_currency.uppercase' => 'The source currency code must be in uppercase letters.',
            'to_currency.required' => 'Please select a target currency.',
            'to_currency.size' => 'The target currency code must be exactly 3 characters (e.g., USD, EUR).',
            'to_currency.uppercase' => 'The target currency code must be in uppercase letters.',
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
