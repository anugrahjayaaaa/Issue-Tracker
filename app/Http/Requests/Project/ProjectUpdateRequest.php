<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $id = $this->route('project')->id;

        return [
            'key' => 'required|string|max:10|unique:projects,key,'.$id.'|regex:/^[A-Z0-9]+$/',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash|unique:projects,slug,'.$id,
            'description' => 'nullable|string|max:2000',
        ];
    }
}
