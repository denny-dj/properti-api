<?php

namespace App\Support;

final class DeliverableUrl
{
    /** Hanya https ke host yang diizinkan (config concierge.deliverable_hosts); tanpa kredensial di URL. */
    public static function isAllowed(string $url): bool
    {
        $parts = parse_url(trim($url));

        if (! is_array($parts) || ($parts['scheme'] ?? '') !== 'https' || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $host = strtolower((string) ($parts['host'] ?? ''));

        if ($host === '') {
            return false;
        }

        foreach ((array) config('concierge.deliverable_hosts', []) as $allowed) {
            $allowed = strtolower((string) $allowed);

            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }
}
