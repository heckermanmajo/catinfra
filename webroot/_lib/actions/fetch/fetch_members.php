<?php

    /**
     * Fetches members data from Skool Next.js API
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string|null $build_id Optional build ID (will be resolved if null)
     * @param int $p Page number (default: 1)
     * @param string $t Filter type (default: "active")
     * @param string|null $online Online filter
     * @param string|null $levels Levels filter
     * @param string|null $price Price filter
     * @param string|null $course_ids Course IDs filter
     * @param string|null $sort_type Sort type
     * @param bool|null $monthly Monthly filter
     * @param bool|null $annual Annual filter
     * @param bool|null $trials Trials filter
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_members(
        array   $user,
        array   $community,
        ?string $build_id = null,
        int     $p = 1,
        string  $t = "active",
        ?string $online = null,
        ?string $levels = null,
        ?string $price = null,
        ?string $course_ids = null,
        ?string $sort_type = null,
        ?bool   $monthly = null,
        ?bool   $annual = null,
        ?bool   $trials = null
    ): array
    {

        $tenant_slug = $community['tenant_slug']
            ?? throw new ValueError("Missing tenant_slug in community data");

        // Resolve Next.js build ID if not provided
        if (!$build_id) {
            $build_id = fetch_lib::reolve_nextjs_build_id($tenant_slug, 'members');
        }

        $params = [
            't' => $t,
            'p' => (string)$p,
            'group' => $tenant_slug,
        ];

        if ($online !== null) {
            $params['online'] = $online;
        }
        if ($levels !== null) {
            $params['levels'] = $levels;
        }
        if ($price !== null) {
            $params['price'] = $price;
        }
        if ($course_ids !== null) {
            $params['courseIds'] = $course_ids;
        }
        if ($sort_type !== null) {
            $params['sortType'] = $sort_type;
        }
        if ($monthly !== null) {
            $params['monthly'] = $monthly ? 'true' : 'false';
        }
        if ($annual !== null) {
            $params['annual'] = $annual ? 'true' : 'false';
        }
        if ($trials !== null) {
            $params['trials'] = $trials ? 'true' : 'false';
        }

        // Build query string with special encoding for commas
        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $url = sprintf(
            "https://www.skool.com/_next/data/%s/%s/-/members.json?%s",
            urlencode($build_id),
            urlencode($tenant_slug),
            $query
        );

        return fetch_lib::perform_request_to_skool($user, $community, $url, 'nextjs');
    }
