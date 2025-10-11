<?php

    /**
     * Fetches discovery data from Skool Next.js API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string|null $build_id Optional build ID (will be resolved if null)
     * @param int $page Page number (default: 1)
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_discovery(
        array   $user,
        array   $community,
        ?string $build_id = null,
        int     $page = 1
    ): array
    {

        $tenant_slug = $community['tenant_slug']
            ?? throw new ValueError("Missing tenant_slug in community data");

        // Resolve Next.js build ID if not provided
        if (!$build_id) {
            $build_id = fetch_lib::reolve_nextjs_build_id($tenant_slug, 'discovery');
        }

        // Build the Next.js data URL
        $url = sprintf(
            "https://www.skool.com/_next/data/%s/%s/-/discovery.json?group=%s&page=%d",
            urlencode($build_id),
            urlencode($tenant_slug),
            urlencode($tenant_slug),
            $page
        );

        return fetch_lib::perform_request_to_skool($user, $community, $url, 'nextjs');
    }
