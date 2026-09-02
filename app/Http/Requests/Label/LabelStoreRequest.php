<?php

namespace App\Http\Requests\Label;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LabelStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('project.manage');
    }

    public function attributes(): array
    {
        return ['label_name' => 'label name'];
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'label_name' => [
                'required', 'string', 'max:50',
                Rule::unique('labels')->where('project_id', $project->id),
            ],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
