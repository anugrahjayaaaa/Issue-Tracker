<?php

namespace App\Http\Requests\Issue;

use App\Models\Issue;
use App\Services\PlanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class IssueImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('issue.edit')
            || $this->user()->can('issue.create');
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'max:5120'], // 5MB hardcap per file
        ];
    }

    /**
     * Reject if the upload would exceed the active plan's storage quota.
     * ponytail: same quota rule as ProjectImageRequest; computed live from
     * bytes on disk under the issue's scoped folder.
     */
    public function after(): array
    {
        return [
            function (/* Validator $validator */) {
                /** @var Issue $issue */
                $issue = $this->route('issue');
                $quotaMb = PlanService::for()->storageQuotaMb();

                if ($quotaMb <= 0) {
                    return; // unlimited
                }

                $prefix = 'projects/'.$issue->project->folder().'/issues/'.$issue->code;
                $used = $this->bytesUnder($prefix);
                $incoming = $this->file('file')->getSize();

                if ($used + $incoming > $quotaMb * 1024 * 1024) {
                    $this->validator->errors()->add('file', __('messages.project_image_quota_exceeded'));
                }
            },
        ];
    }

    private function bytesUnder(string $prefix): int
    {
        $disk = Storage::disk('public');
        $bytes = 0;
        foreach ($disk->allFiles($prefix) as $path) {
            $bytes += $disk->size($path);
        }

        return $bytes;
    }
}
