<?php

    namespace _lib\actions\fetch\fetch_leaderboards;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchLeaderboardsAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public ?string $build_id = null,
            public int $page = 1
        ) {}

        protected function perform(): FetchLeaderboardsActionResult
        {
            $tenant_slug = $this->community['tenant_slug']
                ?? throw new ValueError("Missing tenant_slug in community data");

            // Resolve Next.js build ID if not provided
            $build_id = $this->build_id;
            if (!$build_id) {
                $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, 'leaderboards');
            }

            // Build the Next.js data URL
            $url = sprintf(
                "https://www.skool.com/_next/data/%s/%s/-/leaderboards.json?group=%s&page=%d",
                urlencode($build_id),
                urlencode($tenant_slug),
                urlencode($tenant_slug),
                $this->page
            );

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'nextjs');

            $result = new FetchLeaderboardsActionResult(success: true);
            $result->message = "Successfully fetched leaderboards data";
            $result->content = $response;

            return $result;
        }
    }
