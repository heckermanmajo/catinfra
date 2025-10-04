<?php

    namespace _lib\actions;
    final class fetch_lib
    {

        public static array $logs = [];

        private function __construct() {}

        static function log(mixed $data): void
        {
            if (is_string($data))
            {
                self::$logs[] = $data;
            }
            else
            {
                self::$logs[] = print_r($data, true);
            }
        }

        # expects json as return fields...

        /**
         * @throws JsonException
         */
        static function perform_request_to_skool(
            array  $user,
            array  $community,
            string $url
        ): array
        {

            $get_headers
                = function (
                string  $kind,
                ?string $community_name
            ) use ($user, $community)
            {
                $tenant_name = $community['tenent_name']
                    ?? throw new ValueError("Missing tenant name");

                $h = [
                    "accept" => "application/json, text/plain, * /*",
                    "accept-language" => "de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7",
                    "user-agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36",
                    "origin" => "https://www.skool.com",
                    "referer" => "https://www.skool.com/",
                    "priority" => "u=1, i",
                    "sec-ch-ua" => '"Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
                ];
                if ($kind == "nextjs")
                {
                    $h["x-nextjs-data"] = "1";
                }
                if ($community_name)
                {
                    if (!isset($community_name))
                    {
                        throw new ValueError("Community '{$community_name}' not found in tenant '{$tenant_name}'");
                    }
                }
                if ($kind == "api")
                {
                    $h["content-type"] = "application/json";
                }
                $h["x-aws-waf-token"] = $user['SKOOL_WAF_HEADER'];
                $h["authorization"] = "Bearer {$user['SKOOL_AUTH_TOKEN']}";
                return $h;
            };

            $get_cookies
                = function () use ($user, $community)
            {
                $cookies = [];
                if ($user['SKOOL_AUTH_TOKEN'])
                {
                    $cookies['auth_token'] = $user['SKOOL_AUTH_TOKEN'];
                }
                if ($user['SKOOL_CLIENT_ID'])
                {
                    $cookies['client_id'] = $user['SKOOL_CLIENT_ID'];
                }

                if ($user['SKOOL_GA'])
                {
                    $cookies['_ga'] = $user['SKOOL_GA'];
                }
                if ($user['SKOOL_GA_B9'])
                {
                    $cookies['_ga_B9PRVRDF3W'] = $user['SKOOL_GA_B9'];
                }
                if ($user['SKOOL_GA_D0XK'])
                {
                    $cookies['_ga_D0XK49HQ9K'] = $user['SKOOL_GA_D0XK'];
                }
                if ($user['SKOOL_GCL_AU'])
                {
                    $cookies['_gcl_au'] = $user['SKOOL_GCL_AU'];
                }
                if ($user['SKOOL_FBP'])
                {
                    $cookies['_fbp'] = $user['SKOOL_FBP'];
                }
                if ($user['SKOOL_AJS_ANON'])
                {
                    $a = $user['SKOOL_AJS_ANON'];
                    if (!str_starts_with($a, '"') || !str_ends_with($a, '"'))
                    {
                        $a = '"' . $a . '"';
                        $cookies['ajs_anonymous_id'] = $a;
                    }
                }

                if ($user['SKOOL_WAF_COOKIE'])
                {
                    $cookies['aws-waf-token'] = $user['SKOOL_WAF_COOKIE'];
                }

                return $cookies;
            };


            # perform request
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $get_headers($community['kind'], $community['name']));
            curl_setopt($ch, CURLOPT_COOKIE, http_build_query($get_cookies()));
            $html = curl_exec($ch);
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($errno)
            {
                throw new RuntimeException("[performRequestToSkool] cURL error {$errno}: {$err}");
            }

            if ($code !== 200 || !$html)
            {
                throw new RuntimeException("[performRequestToSkool] Non-200 status: {$code} for {$url}");
            }

            return json_decode($html, true, 512, JSON_THROW_ON_ERROR);

        }

        static function reolve_nextjs_build_id(string $tenant, string $page = 'members'): ?string
        {
            if (str_starts_with($page, '@'))
            {
                $url = "https://www.skool.com/{$page}?g=" . rawurlencode($tenant);
            }
            elseif (in_array($page, ['about', 'calendar', 'classroom'], true))
            {
                $url = "https://www.skool.com/{$tenant}/{$page}";
            }
            else
            {
                $url = "https://www.skool.com/{$tenant}/-/{$page}";
            }

            $headers = [
                'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36',
                'accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            ];

            $ch = curl_init($url);
            $hdr = [];
            foreach ($headers as $k => $v)
            {
                $hdr[] = $k . ': ' . $v;
            }
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTPHEADER => $hdr,
            ]);

            $html = curl_exec($ch);
            $errno = curl_errno($ch);
            $err = curl_error($ch);
            $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
            curl_close($ch);

            if ($errno)
            {
                throw new RuntimeException("[resolveBuildId] cURL error {$errno}: {$err}");
            }
            if ($code !== 200 || !$html)
            {
                throw new RuntimeException("[resolveBuildId] Non-200 status: {$code} for {$url}");
            }

            // __NEXT_DATA__-Script extrahieren
            if (!preg_match('#<script[^>]+id=["\']__NEXT_DATA__["\'][^>]*>(.*?)</script>#s', $html, $m))
            {
                throw new RuntimeException("[resolveBuildId] __NEXT_DATA__ not found in {$url}");
            }

            $json = $m[1] ?? '';
            $data = json_decode($json, true);
            if (!is_array($data))
            {
                throw new RuntimeException("[resolveBuildId] JSON parse failed");
            }
            $buildId = $data['buildId'] ?? null;
            if (!$buildId)
            {
                throw new RuntimeException("[resolveBuildId] buildId missing in __NEXT_DATA__ for {$url}");
            }
            return $buildId;
        }

    }