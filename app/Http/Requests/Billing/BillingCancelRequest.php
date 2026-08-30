<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillingCancelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('billing.cancel');
    }

    public function rules(): array
    {
        return [];
    }
}
