<?php

    use _lib\utils\SkoolFetcher;

    /**
     * Fetches profile page data from Skool Next.js API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string $handle User handle (e.g., "@username")
     * @param string|null $build_id Optional build ID (will be resolved if null)
     * @param int $page Page number (default: 1)
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_profile_page(
        array   $user,
        array   $community,
        string  $handle,
        ?string $build_id = null,
        int     $page = 1
    ): array
    {

        $tenant_slug = $community['tenant_slug']
            ?? throw new ValueError("Missing tenant_slug in community data");

        if (empty($handle)) {
            throw new ValueError("handle is required");
        }

        // Ensure handle starts with @
        $h = str_starts_with($handle, '@') ? $handle : "@{$handle}";

        // Resolve Next.js build ID if not provided
        if (!$build_id) {
            $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, $h);
        }

        // Build the Next.js data URL
        $url = sprintf(
            "https://www.skool.com/_next/data/%s/%s/%s.json?g=%s&page=%d",
            urlencode($build_id),
            urlencode($tenant_slug),
            urlencode($h),
            urlencode($tenant_slug),
            $page
        );

        return SkoolFetcher::perform_request_to_skool($user, $community, $url, 'nextjs');
    }
