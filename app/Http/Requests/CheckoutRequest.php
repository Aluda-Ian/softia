<?php

namespace App\Http\Requests;

use App\Enums\CustomerType;
use App\Enums\PaymentMethod;
use App\Support\Cart;
use App\Support\PaymentOptions;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isBusiness = $this->input('customer_type') === CustomerType::Business->value;
        $needsPayment = ! $isBusiness && ! app(Cart::class)->hasQuoteOnlyItems() && PaymentOptions::retailCheckoutAvailable();

        return [
            'customer_type' => ['required', Rule::enum(CustomerType::class)],
            'customer_name' => ['required', 'string', 'max:255'],
            'company_name' => [Rule::requiredIf($isBusiness), 'nullable', 'string', 'max:255'],
            'kra_pin' => ['nullable', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'delivery_address' => ['required', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'payment_method' => [
                Rule::requiredIf($needsPayment),
                'nullable',
                Rule::enum(PaymentMethod::class)->only(PaymentOptions::retail() ?: [PaymentMethod::PayOnDelivery]),
            ],
            'payment_reference' => [
                Rule::requiredIf($needsPayment && $this->input('payment_method') === PaymentMethod::Mpesa->value),
                'nullable',
                'string',
                'max:30',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Please enter your company or institution name.',
            'payment_reference.required' => 'Enter the M-Pesa confirmation code from your payment SMS.',
        ];
    }
}
