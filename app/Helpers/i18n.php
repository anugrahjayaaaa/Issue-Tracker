<?php

use Illuminate\Support\Facades\Lang;

if (! function_exists('ui')) {
    /**
     * Shorthand for page-level UI copy in lang/{locale}/ui.php.
     * Falls back to the key so missing translations are obvious, not blank.
     */
    function ui(string $key, array $replace = []): string
    {
        return Lang::get("ui.$key", $replace);
    }
}
