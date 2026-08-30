<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class ProjectStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'key' => 'required|string|max:10|unique:projects,key|regex:/^[A-Z0-9]+$/',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
        ];
    }
}
