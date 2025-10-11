<?php

    use _lib\utils\SkoolFetcher;

    /**
     * Fetches community page data from Skool Next.js API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string|null $build_id Optional build ID (will be resolved if null)
     * @param string $nextjs_path Path for Next.js data (e.g., "" for index or "-/feed")
     * @param array|null $query Additional query parameters
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_community(
        array   $user,
        array   $community,
        ?string $build_id = null,
        string  $nextjs_path = "",
        ?array  $query = null
    ): array
    {

        $tenant_slug = $community['tenant_slug']
            ?? throw new ValueError("Missing tenant_slug in community data");

        // Resolve Next.js build ID if not provided
        if (!$build_id) {
            // Infer page key from path (default to 'index' if empty)
            $trimmed_path = trim($nextjs_path, '/');
            if (!empty($trimmed_path)) {
                $path_parts = explode('/', $trimmed_path);
                $page_key = end($path_parts);
            } else {
                $page_key = 'index';
            }
            $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, $page_key);
        }

        $q = $query ?? [];
        if (!isset($q['group'])) {
            $q['group'] = $tenant_slug;
        }

        // Normalize nextjs_path
        $path = trim($nextjs_path, '/');
        $suffix = !empty($path) ? "/{$path}.json" : ".json";

        $url = sprintf(
            "https://www.skool.com/_next/data/%s/%s%s",
            urlencode($build_id),
            urlencode($tenant_slug),
            $suffix
        );

        $query_string = http_build_query($q);
        if ($query_string) {
            $url .= "?{$query_string}";
        }

        return SkoolFetcher::perform_request_to_skool($user, $community, $url, 'nextjs');
    }
