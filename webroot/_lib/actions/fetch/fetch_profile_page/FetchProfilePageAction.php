<?php

    namespace _lib\actions\fetch\fetch_profile_page;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchProfilePageAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public string $handle,
            public ?string $build_id = null,
            public int $page = 1
        ) {}

        protected function perform(): FetchProfilePageActionResult
        {
            $tenant_slug = $this->community['tenant_slug']
                ?? throw new ValueError("Missing tenant_slug in community data");

            if (empty($this->handle)) {
                throw new ValueError("handle is required");
            }

            // Ensure handle starts with @
            $h = str_starts_with($this->handle, '@') ? $this->handle : "@{$this->handle}";

            // Resolve Next.js build ID if not provided
            $build_id = $this->build_id;
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
                $this->page
            );

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'nextjs');

            $result = new FetchProfilePageActionResult(success: true);
            $result->message = "Successfully fetched profile page data";
            $result->content = $response;

            return $result;
        }
    }
