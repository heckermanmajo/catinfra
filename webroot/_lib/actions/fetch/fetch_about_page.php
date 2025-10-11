<?php

    /**
     * Fetches about page data from Skool Next.js API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_about_page(
        array $user,
        array $community
    ): array
    {

        $tenant_slug = $community['tenant_slug']
            ?? throw new ValueError("Missing tenant_slug in community data");

        // Resolve Next.js build ID for the about page
        $build_id = fetch_lib::reolve_nextjs_build_id($tenant_slug, 'about');

        // Build the Next.js data URL
        $url = sprintf(
            "https://www.skool.com/_next/data/%s/%s/about.json?group=%s",
            urlencode($build_id),
            urlencode($tenant_slug),
            urlencode($tenant_slug)
        );

        return fetch_lib::perform_request_to_skool($user, $community, $url, 'nextjs');
    }