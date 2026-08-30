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
