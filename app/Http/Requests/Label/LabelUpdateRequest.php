<?php

namespace App\Http\Requests\Label;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabelUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'name' => [
                'required', 'string', 'max:50',
                Rule::unique('labels', 'name')->where('project_id', $project->id)
                    ->ignore($this->route('label')),
            ],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
