<?php

    namespace _lib\actions\fetch\fetch_comments;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchCommentsAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public ?string $build_id = null,
            public int $page = 1,
            public string $nextjs_path = "-/comments",
            public ?array $extra_params = null
        ) {}

        protected function perform(): FetchCommentsActionResult
        {
            $tenant_slug = $this->community['tenant_slug']
                ?? throw new ValueError("Missing tenant_slug in community data");

            // Resolve Next.js build ID if not provided
            $build_id = $this->build_id;
            if (!$build_id) {
                // Map path to a page key for resolve_build_id; default to last segment or 'comments'
                $path_parts = array_filter(explode('/', trim($this->nextjs_path, '/')));
                $page_key = !empty($path_parts) ? end($path_parts) : 'comments';
                $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, $page_key);
            }

            $path = trim($this->nextjs_path, '/');
            $base = sprintf(
                "https://www.skool.com/_next/data/%s/%s/%s.json",
                urlencode($build_id),
                urlencode($tenant_slug),
                $path
            );

            $params = [
                'group' => $tenant_slug,
                'page' => (string)$this->page
            ];
            if ($this->extra_params) {
                foreach ($this->extra_params as $k => $v) {
                    $params[$k] = (string)$v;
                }
            }

            $query_string = http_build_query($params);
            $url = $query_string ? "{$base}?{$query_string}" : $base;

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'nextjs');

            $result = new FetchCommentsActionResult(success: true);
            $result->message = "Successfully fetched comments data";
            $result->content = $response;

            return $result;
        }
    }
