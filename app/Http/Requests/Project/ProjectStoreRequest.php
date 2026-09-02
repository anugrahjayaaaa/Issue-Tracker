<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    public function messages(): array
    {
        return [
            'required' => ui('validation_required'),
        ];
    }

    public function attributes(): array
    {
        return [
            'key' => ui('project_key'),
            'name' => ui('project'),
            'slug' => ui('slug'),
            'description' => ui('description'),
        ];
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'key' => 'required|string|max:10|unique:projects,key|regex:/^[A-Z0-9]+$/',
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|alpha_dash|unique:projects,slug',
            'description' => 'nullable|string|max:2000',
        ];
    }

    protected function prepareForValidation(): void
    {
        // ponytail: auto-fill slug from name if client didn't send it (disabled field / no JS)
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge([
                'slug' => \Str::slug($this->input('name')),
            ]);
        }
    }
}
