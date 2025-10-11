<?php

    use _lib\utils\SkoolFetcher;

    /**
     * Fetches comments data from Skool Next.js API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string|null $build_id Optional build ID (will be resolved if null)
     * @param int $page Page number (default: 1)
     * @param string $nextjs_path Path for Next.js data (default: "-/comments")
     * @param array|null $extra_params Additional query parameters
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_comments(
        array   $user,
        array   $community,
        ?string $build_id = null,
        int     $page = 1,
        string  $nextjs_path = "-/comments",
        ?array  $extra_params = null
    ): array
    {

        $tenant_slug = $community['tenant_slug']
            ?? throw new ValueError("Missing tenant_slug in community data");

        // Resolve Next.js build ID if not provided
        if (!$build_id) {
            // Map path to a page key for resolve_build_id; default to last segment or 'comments'
            $path_parts = array_filter(explode('/', trim($nextjs_path, '/')));
            $page_key = !empty($path_parts) ? end($path_parts) : 'comments';
            $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, $page_key);
        }

        $path = trim($nextjs_path, '/');
        $base = sprintf(
            "https://www.skool.com/_next/data/%s/%s/%s.json",
            urlencode($build_id),
            urlencode($tenant_slug),
            $path
        );

        $params = [
            'group' => $tenant_slug,
            'page' => (string)$page
        ];
        if ($extra_params) {
            foreach ($extra_params as $k => $v) {
                $params[$k] = (string)$v;
            }
        }

        $query_string = http_build_query($params);
        $url = $query_string ? "{$base}?{$query_string}" : $base;

        return SkoolFetcher::perform_request_to_skool($user, $community, $url, 'nextjs');
    }
