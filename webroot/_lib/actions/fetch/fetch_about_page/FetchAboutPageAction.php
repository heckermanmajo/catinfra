<?php

    namespace _lib\actions\fetch\fetch_about_page;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchAboutPageAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community
        ) {}

        protected function perform(): FetchAboutPageActionResult
        {
            $tenant_slug = $this->community['tenant_slug']
                ?? throw new ValueError("Missing tenant_slug in community data");

            // Resolve Next.js build ID for the about page
            $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, 'about');

            // Build the Next.js data URL
            $url = sprintf(
                "https://www.skool.com/_next/data/%s/%s/about.json?group=%s",
                urlencode($build_id),
                urlencode($tenant_slug),
                urlencode($tenant_slug)
            );

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'nextjs');

            $result = new FetchAboutPageActionResult(success: true);
            $result->message = "Successfully fetched about page data";
            $result->content = $response;

            return $result;
        }
    }
