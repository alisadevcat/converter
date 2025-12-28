<?php

namespace Modules\Currency\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Currency\Services\CurrencyConfigService;

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
        $configService = app(CurrencyConfigService::class);
        $minAmount = config('currency.conversion.min_amount', 0.01);
        $maxAmount = config('currency.conversion.max_amount', 999999999.99);

        return [
            'amount' => [
                'required',
                'numeric',
                'min:' . $minAmount,
                'max:' . $maxAmount,
            ],
            'from_currency' => [
                'required',
                'string',
                'size:3',
                function ($attribute, $value, $fail) use ($configService) {
                    if (!$configService->isSupported($value)) {
                        $fail("The selected from currency '{$value}' is not supported.");
                    }
                },
            ],
            'to_currency' => [
                'required',
                'string',
                'size:3',
                function ($attribute, $value, $fail) use ($configService) {
                    if (!$configService->isSupported($value)) {
                        $fail("The selected to currency '{$value}' is not supported.");
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
        $minAmount = config('currency.conversion.min_amount', 0.01);
        $maxAmount = config('currency.conversion.max_amount', 999999999.99);

        return [
            'amount.required' => 'The amount field is required.',
            'amount.numeric' => 'The amount must be a valid number.',
            'amount.min' => "The amount must be at least {$minAmount}.",
            'amount.max' => "The amount must not exceed {$maxAmount}.",
            'from_currency.required' => 'The from currency field is required.',
            'from_currency.string' => 'The from currency must be a string.',
            'from_currency.size' => 'The from currency must be exactly 3 characters.',
            'to_currency.required' => 'The to currency field is required.',
            'to_currency.string' => 'The to currency must be a string.',
            'to_currency.size' => 'The to currency must be exactly 3 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'amount' => 'amount',
            'from_currency' => 'from currency',
            'to_currency' => 'to currency',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize currency codes to uppercase
        if ($this->has('from_currency')) {
            $this->merge([
                'from_currency' => strtoupper(trim($this->input('from_currency'))),
            ]);
        }

        if ($this->has('to_currency')) {
            $this->merge([
                'to_currency' => strtoupper(trim($this->input('to_currency'))),
            ]);
        }
    }

    /**
     * Get validated data with normalized values.
     *
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        // Ensure amount is float
        if (isset($validated['amount'])) {
            $validated['amount'] = (float) $validated['amount'];
        }

        return $validated;
    }
}

