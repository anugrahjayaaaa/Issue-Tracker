<?php

use Laravel\Pennant\Feature;

if (! function_exists('featureLabel')) {
    /**
     * Human label for a feature slug, from config/pennant.php metadata.
     * (Pennant has no label concept — flags are slug + value only.)
     */
    function featureLabel(string $slug): string
    {
        return config("pennant.features.{$slug}.label", $slug);
    }
}

if (! function_exists('parseMentions')) {
    /**
     * Extract @username tokens from rich-text/HTML body.
     * Returns flattened list of usernames (without @).
     */
    function parseMentions(string $body): array
    {
        // strip tags first so @name inside markup still matches
        $text = strip_tags($body);
        preg_match_all('/@([a-zA-Z0-9_.-]+)/', $text, $matches);

        // ponytail: naive single-pass match; enough for @username mention.
        // Upgrade to configurable regex / display-name if needed.
        return array_values(array_unique($matches[1] ?? []));
    }
}

if (! function_exists('sanitizeRichText')) {
    /**
     * Strip rich-text HTML to a safe allowlist. Shared by Project/Issue/Comment
     * mutators (single source of truth — don't re-implement per model).
     *
     * Security: strip_tags keeps attributes, so we additionally drop any image
     * whose src is not a local /storage/ path (blocks javascript:, data:, external).
     * ponytail: no HTML-sanitizer lib installed; this allowlist+src-check is the
     * ceiling. Swap for spatie/laravel-html or mews/purifier if richer HTML needed.
     */
    function sanitizeRichText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $allowed = '<p><br><strong><em><u><s><ul><ol><li><a><blockquote><code><pre><h3><h4><span><img>';
        $html = strip_tags($value, $allowed);

        // Guard <img src> to local uploads only (blocks javascript:, data:, external).
        $html = preg_replace_callback('/<img\b[^>]*>/i', function (array $m): string {
            if (preg_match('/\ssrc\s*=\s*("|\')(.*?)\1/i', $m[0], $s)
                && ! str_starts_with($s[2], '/storage/projects/')) {
                return ''; // drop unsafe image
            }

            return $m[0];
        }, $html);

        return $html;
    }
}

if (! function_exists('storagePathsFromHtml')) {
    /**
     * Extract on-disk public-disk paths of local images referenced in rich text.
     * src "/storage/projects/..." -> "projects/..." (what Storage::disk('public') uses).
     */
    function storagePathsFromHtml(?string $html): array
    {
        if (! $html) {
            return [];
        }
        $paths = [];
        preg_match_all('/<img\b[^>]*\ssrc\s*=\s*("|\')(.*?)\1/i', $html, $matches, PREG_SET_ORDER);
        foreach ($matches as $m) {
            if (str_starts_with($m[2], '/storage/')) {
                $paths[] = substr($m[2], strlen('/storage/'));
            }
        }

        return array_values(array_unique($paths));
    }
}

if (! function_exists('pruneUnusedImages')) {
    /**
     * Delete image files referenced in $oldHtml but no longer in $newHtml.
     * ponytail: O(n) set-diff on extracted paths; deletes from public disk only.
     * Caller must pass a scoped $oldHtml/$newHtml pair for one entity.
     */
    function pruneUnusedImages(?string $oldHtml, ?string $newHtml): void
    {
        $removed = array_diff(
            storagePathsFromHtml($oldHtml),
            storagePathsFromHtml($newHtml)
        );

        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        foreach ($removed as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}

if (! function_exists('deleteStorageFolder')) {
    /**
     * Recursively delete a public-disk folder (e.g. an entity's image dir on delete).
     * ponytail: no sub-folder depth assumption; allFiles + delete is enough.
     */
    function deleteStorageFolder(string $diskPrefix): void
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        if (! $disk->exists($diskPrefix)) {
            return;
        }
        foreach ($disk->allFiles($diskPrefix) as $path) {
            $disk->delete($path);
        }
        $disk->deleteDirectory($diskPrefix);
    }
}
