<?php

    /**
     * Generic API call helper for "like" or similar endpoints
     *
     * @param array $user User data from database
     * @param array $community Community data from database
     * @param string $api_url API endpoint URL (required)
     * @param string $method HTTP method (default: "GET")
     * @param array|null $payload Request payload for POST/PUT/PATCH methods
     * @param array|null $query Additional query parameters
     * @return array JSON response from Skool API
     * @throws JsonException
     */
    function fetch_like(
        array   $user,
        array   $community,
        string  $api_url,
        string  $method = "GET",
        ?array  $payload = null,
        ?array  $query = null
    ): array
    {

        if (empty($api_url)) {
            throw new ValueError("api_url is required");
        }

        $url = $api_url;
        if ($query) {
            $query_params = [];
            foreach ($query as $k => $v) {
                $query_params[$k] = (string)$v;
            }
            $query_string = http_build_query($query_params);
            $url = "{$api_url}?{$query_string}";
        }

        // For non-GET methods with payload, we need to handle the request differently
        $method = strtoupper($method);

        // Build headers
        $get_headers = function (string $kind) use ($user, $community)
        {
            $headers = [
                "accept: application/json, text/plain, */*",
                "accept-language: de-DE,de;q=0.9,en-US;q=0.8,en;q=0.7",
                "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36",
                "origin: https://www.skool.com",
                "referer: https://www.skool.com/",
                "priority: u=1, i",
                'sec-ch-ua: "Chromium";v="140", "Not=A?Brand";v="24", "Google Chrome";v="140"',
                "content-type: application/json",
            ];

            if ($user['SKOOL_WAF_HEADER'])
            {
                $headers[] = "x-aws-waf-token: {$user['SKOOL_WAF_HEADER']}";
            }
            if ($user['SKOOL_AUTH_TOKEN'])
            {
                $headers[] = "authorization: Bearer {$user['SKOOL_AUTH_TOKEN']}";
            }
            return $headers;
        };

        $get_cookies = function () use ($user)
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
                }
                $cookies['ajs_anonymous_id'] = $a;
            }
            if ($user['SKOOL_WAF_COOKIE'])
            {
                $cookies['aws-waf-token'] = $user['SKOOL_WAF_COOKIE'];
            }

            return $cookies;
        };

        // Build cookie string
        $cookie_string = [];
        foreach ($get_cookies() as $key => $value)
        {
            if ($value)
            {
                $cookie_string[] = "{$key}={$value}";
            }
        }

        // Perform request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $get_headers('api'));
        curl_setopt($ch, CURLOPT_COOKIE, implode("; ", $cookie_string));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        // Add payload for non-GET methods
        if ($method !== 'GET' && $payload !== null) {
            $json_data = json_encode($payload, JSON_THROW_ON_ERROR);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        }

        $html = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($errno)
        {
            throw new RuntimeException("[fetch_like] cURL error {$errno}: {$err}");
        }

        // Handle 204 No Content
        if ($code === 204) {
            return ['status' => 204, 'ok' => true];
        }

        if ($code < 200 || $code >= 300)
        {
            throw new RuntimeException("[fetch_like] HTTP {$code} for {$url}");
        }

        // Try to parse JSON, if fails return status
        if (!$html) {
            return ['status' => $code, 'ok' => true];
        }

        try {
            return json_decode($html, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return ['status' => $code, 'ok' => true];
        }
    }
