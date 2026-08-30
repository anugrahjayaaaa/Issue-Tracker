<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;

class TranslationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Translations exist for every configured locale (en, id, ...).
        return collect(Config::get('app.available_locales', ['en', 'id']))->mapWithKeys(
            fn ($locale) => [$locale => 'required|string']
        )->toArray();
    }
}
