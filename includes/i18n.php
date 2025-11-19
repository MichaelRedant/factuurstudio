<?php

// Simple i18n helper for front-end (Elementor partials)
// Activates French when '/fr/' is present in the request URI.

function octo_is_fr_locale(): bool {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    return (stripos($uri, '/fr/') !== false);
}

function octo_t(string $nl, ?string $fr = null): string {
    if (octo_is_fr_locale() && !empty($fr)) {
        return $fr;
    }
    return $nl;
}

