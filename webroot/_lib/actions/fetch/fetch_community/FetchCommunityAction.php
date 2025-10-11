<?php

    namespace _lib\actions\fetch\fetch_community;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchCommunityAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public ?string $build_id = null,
            public string $nextjs_path = "",
            public ?array $query = null
        ) {}

        protected function perform(): FetchCommunityActionResult
        {
            $tenant_slug = $this->community['tenant_slug']
                ?? throw new ValueError("Missing tenant_slug in community data");

            // Resolve Next.js build ID if not provided
            $build_id = $this->build_id;
            if (!$build_id) {
                // Infer page key from path (default to 'index' if empty)
                $trimmed_path = trim($this->nextjs_path, '/');
                if (!empty($trimmed_path)) {
                    $path_parts = explode('/', $trimmed_path);
                    $page_key = end($path_parts);
                } else {
                    $page_key = 'index';
                }
                $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, $page_key);
            }

            $q = $this->query ?? [];
            if (!isset($q['group'])) {
                $q['group'] = $tenant_slug;
            }

            // Normalize nextjs_path
            $path = trim($this->nextjs_path, '/');
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

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'nextjs');

            $result = new FetchCommunityActionResult(success: true);
            $result->message = "Successfully fetched community data";
            $result->content = $response;

            return $result;
        }
    }
