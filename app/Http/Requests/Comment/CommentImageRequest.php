<?php

namespace App\Http\Requests\Comment;

use App\Models\Comment;
use App\Services\PlanService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;

class CommentImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('comment.edit')
            || $this->user()->can('comment.create');
    }

    /** @return array<string,mixed> */
    public function rules(): array
    {
        return [
            'file' => ['required', 'image', 'max:5120'], // 5MB hardcap per file
        ];
    }

    /**
     * Quota check scoped to the comment's project folder (same rule as issue/project).
     */
    public function after(): array
    {
        return [
            function (/* Validator $validator */) {
                /** @var Comment $comment */
                $comment = $this->route('comment');
                $quotaMb = PlanService::for()->storageQuotaMb();

                if ($quotaMb <= 0) {
                    return; // unlimited
                }

                $prefix = 'projects/'.$comment->issue->project->folder()
                    .'/issues/'.$comment->issue->code.'/comments/'.$comment->id;
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
