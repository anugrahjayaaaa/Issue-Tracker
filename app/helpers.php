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
