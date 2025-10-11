<?php

    namespace _lib\actions\fetch\fetch_members;

    use _lib\core\Action;
    use _lib\utils\SkoolFetcher;
    use ValueError;

    final class FetchMembersAction extends Action
    {
        public function __construct(
            public array $user,
            public array $community,
            public ?string $build_id = null,
            public int $p = 1,
            public string $t = "active",
            public ?string $online = null,
            public ?string $levels = null,
            public ?string $price = null,
            public ?string $course_ids = null,
            public ?string $sort_type = null,
            public ?bool $monthly = null,
            public ?bool $annual = null,
            public ?bool $trials = null
        ) {}

        protected function perform(): FetchMembersActionResult
        {
            $tenant_slug = $this->community['tenant_slug']
                ?? throw new ValueError("Missing tenant_slug in community data");

            // Resolve Next.js build ID if not provided
            $build_id = $this->build_id;
            if (!$build_id) {
                $build_id = SkoolFetcher::resolve_nextjs_build_id($tenant_slug, 'members');
            }

            $params = [
                't' => $this->t,
                'p' => (string)$this->p,
                'group' => $tenant_slug,
            ];

            if ($this->online !== null) {
                $params['online'] = $this->online;
            }
            if ($this->levels !== null) {
                $params['levels'] = $this->levels;
            }
            if ($this->price !== null) {
                $params['price'] = $this->price;
            }
            if ($this->course_ids !== null) {
                $params['courseIds'] = $this->course_ids;
            }
            if ($this->sort_type !== null) {
                $params['sortType'] = $this->sort_type;
            }
            if ($this->monthly !== null) {
                $params['monthly'] = $this->monthly ? 'true' : 'false';
            }
            if ($this->annual !== null) {
                $params['annual'] = $this->annual ? 'true' : 'false';
            }
            if ($this->trials !== null) {
                $params['trials'] = $this->trials ? 'true' : 'false';
            }

            // Build query string with special encoding for commas
            $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

            $url = sprintf(
                "https://www.skool.com/_next/data/%s/%s/-/members.json?%s",
                urlencode($build_id),
                urlencode($tenant_slug),
                $query
            );

            $response = SkoolFetcher::perform_request_to_skool($this->user, $this->community, $url, 'nextjs');

            $result = new FetchMembersActionResult(success: true);
            $result->message = "Successfully fetched members data";
            $result->content = $response;

            return $result;
        }
    }
