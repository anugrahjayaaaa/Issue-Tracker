<?php

namespace App\Observers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class AttachmentObserver
{
    public function deleting(Attachment $attachment): void
    {
        // ponytail: root-cause cleanup — file removed whenever an attachment is deleted
        // (controller/bulk/console all route through model delete).
        if ($attachment->path && Storage::disk('public')->exists($attachment->path)) {
            Storage::disk('public')->delete($attachment->path);
        }
    }
}
