<?php



    use Random\RandomException;

    class control_lib
    {
        /**
         * Sends a framed email using PHP's mail().
         *
         * @param string $to Recipient email (single address)
         * @param string $subject Subject line (will be sanitized)
         * @param string $htmlBody Your inner HTML content (no <html> or <body> needed)
         * @param string $from From address ("Name <email@domain>" or "email@domain")
         * @param string|null $replyTo Optional reply-to address
         * @param array|null $warnings Filled with preflight warnings (strings). Empty means "looks fine"
         * @return bool True if handed off to MTA, false otherwise
         * @throws RandomException
         */
        public static function sendFramedMail(
            int     $user_id,
            int     $community_id,
            string  $to,
            string  $subject,
            string  $htmlBody,
            string  $from,
            ?string $replyTo = null,
            ?array  &$warnings = null
        ): bool
        {
            try
            {
                $warnings ??= [];

                // Inline helpers
                $sanitizeHeaderValue = function (string $value): string
                {
                    $value = preg_replace('/[\r\n\0]+/', ' ', $value) ?? '';
                    return trim($value);
                };

                $sanitizeSubject = function (string $subject) use ($sanitizeHeaderValue): string
                {
                    $subject = $sanitizeHeaderValue($subject);
                    $subject = preg_replace('/\s+/', ' ', $subject) ?? $subject;
                    return mb_strimwidth($subject, 0, 255, '…', 'UTF-8');
                };

                $buildPlainTextAlternative = function (string $htmlInner): string
                {
                    $text = html_entity_decode(strip_tags($htmlInner), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $text = preg_replace('/[ \t]+\n/', "\n", $text) ?? $text;
                    $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
                    return wordwrap(trim($text), 78, "\r\n");
                };

                // Renders the HTML email with a tiny PHP template
                $buildHtmlFramedEmail = function (string $htmlInner): string
                {
                    $inner = trim($htmlInner);      // trusted inner HTML
                    $year = date('Y');

                    ob_start();
                    ?>
                    <!doctype html>
                    <html lang="en">
                    <head>
                        <meta charset="utf-8">
                        <meta name="x-ua-compatible" content="ie=edge">
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <title></title>
                    </head>
                    <body style="margin:0; padding:0; background:#f4f6f8;">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                           style="background:#f4f6f8; padding:24px 0;">
                        <tr>
                            <td align="center">
                                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%"
                                       style="max-width:600px; background:#ffffff; border:1px solid #e5e7eb; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.06);">
                                    <tr>
                                        <td style="padding:24px 28px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, 'Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol'; color:#111827; line-height:1.6; font-size:16px;">
                                            <div
                                                style="border-bottom:1px solid #e5e7eb; padding-bottom:12px; margin-bottom:20px;">
                                                <div style="font-size:18px; font-weight:600; color:#111827;">
                                                    Notification
                                                </div>
                                            </div>

                                            <div style="color:#111827;"><?= $inner ?></div>

                                            <div
                                                style="border-top:1px solid #f3f4f6; margin-top:24px; padding-top:12px; font-size:12px; color:#6b7280;">
                                                This message was sent automatically. If you didn’t expect it, you can
                                                safely
                                                ignore this email.
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <div style="height:24px; line-height:24px;">&#8202;</div>
                                <div style="font-family:Arial,Helvetica,sans-serif; font-size:12px; color:#9ca3af;">
                                    © <?= htmlspecialchars($year, ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                                    https://cat-knows.com
                                </div>
                            </td>
                        </tr>
                    </table>
                    </body>
                    </html>
                    <?php
                    return ob_get_clean();
                };

                $extractEmailOnly = function (string $addr): ?string
                {
                    if (preg_match('/<([^>]+)>/', $addr, $m))
                    {
                        return $m[1];
                    }
                    return trim($addr) !== '' ? trim($addr) : null;
                };

                $looksAllCaps = function (string $s): bool
                {
                    $letters = preg_replace('/[^a-zA-Z]+/', '', $s) ?? '';
                    if ($letters === '') return false;
                    return strtoupper($letters) === $letters;
                };

                $collectDeliverabilityWarnings = function (
                    string  $to,
                    string  $from,
                    string  $subject,
                    string  $htmlBody,
                    ?string $replyTo = null
                ) use ($extractEmailOnly, $looksAllCaps, $buildPlainTextAlternative): array
                {
                    $warn = [];

                    foreach (['To' => $to, 'From' => $extractEmailOnly($from), 'Reply-To' => $replyTo] as $label => $addr)
                    {
                        if ($addr === null || $addr === '') continue;
                        if (!filter_var($addr, FILTER_VALIDATE_EMAIL))
                        {
                            $warn[] = "$label address looks invalid: $addr";
                        }
                    }

                    if ($subject === '' || mb_strlen(trim($subject)) === 0)
                    {
                        $warn[] = 'Subject is empty.';
                    }
                    if (mb_strlen($subject) > 150)
                    {
                        $warn[] = 'Subject is very long (>150 chars).';
                    }
                    if ($looksAllCaps($subject))
                    {
                        $warn[] = 'Subject is ALL CAPS; likely to hurt deliverability.';
                    }
                    if (substr_count($subject, '!') > 3)
                    {
                        $warn[] = 'Subject has many exclamation marks.';
                    }

                    $text = $buildPlainTextAlternative($htmlBody);
                    if (mb_strlen($text) < 20)
                    {
                        $warn[] = 'Body is extremely short; may look spammy.';
                    }

                    $linkCount = preg_match_all('/https?:\/\/[^\s"<>()]+/i', $htmlBody) ?: 0;
                    $imgCount = preg_match_all('/<img\b[^>]*>/i', $htmlBody) ?: 0;
                    if ($linkCount > 10) $warn[] = 'Contains a high number of links (>10).';
                    if ($imgCount > 5) $warn[] = 'Contains many images (>5); consider fewer.';

                    $spamSignals = ['100% free', 'earn money fast', 'guaranteed', 'risk-free', 'winner', 'act now', 'urgent', 'click here'];
                    $lc = mb_strtolower($htmlBody);
                    foreach ($spamSignals as $term)
                    {
                        if (str_contains($lc, $term))
                        {
                            $warn[] = "Body contains spam-trigger phrase: \"$term\".";
                        }
                    }

                    if (!preg_match('/unsubscribe/i', $htmlBody))
                    {
                        $warn[] = 'No visible unsubscribe text. If this is bulk or marketing, add it.';
                    }

                    $fromEmail = $extractEmailOnly($from);
                    if ($fromEmail)
                    {
                        $fromDomain = substr(strrchr($fromEmail, "@") ?: '', 1);
                        if ($fromDomain === '' || !str_contains($fromDomain, '.'))
                        {
                            $warn[] = 'From domain looks suspicious.';
                        }
                    }

                    return $warn;
                };

                // Main logic
                $to = trim($to);
                $from = $sanitizeHeaderValue($from);
                $subject = $sanitizeSubject($subject);
                $replyTo = $replyTo ? $sanitizeHeaderValue($replyTo) : null;

                $warnings = $collectDeliverabilityWarnings($to, $from, $subject, $htmlBody, $replyTo);

                $boundary = 'bnd_' . bin2hex(random_bytes(12));

                $headers = [];
                $headers[] = 'MIME-Version: 1.0';
                $headers[] = 'From: ' . $from;
                if ($replyTo) $headers[] = 'Reply-To: ' . $replyTo;
                $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';
                $headersStr = implode("\r\n", $headers);

                $textPart = $buildPlainTextAlternative($htmlBody);
                $htmlPart = $buildHtmlFramedEmail($htmlBody);

                $body = '';
                $body .= '--' . $boundary . "\r\n";
                $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $textPart . "\r\n";
                $body .= '--' . $boundary . "\r\n";
                $body .= "Content-Type: text/html; charset=UTF-8\r\n";
                $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
                $body .= $htmlPart . "\r\n";
                $body .= '--' . $boundary . "--\r\n";

                $result = mail($to, $subject, $body, $headersStr);

                lib::insert(
                    "SentMail",
                    [
                        "user_id" => $user_id,
                        "community_id" => $community_id,
                        "subject" => $subject,
                        "body" => $body,
                        "warnings" => json_encode($warnings),
                        "trace" => new Exception()->getTraceAsString(),
                        "success" => 1,
                        "created_at" => time(),
                    ]
                );

                return $result;
            }
            catch (Throwable $e)
            {
                lib::insert(
                    "SentMail",
                    [
                        "user_id" => $user_id,
                        "community_id" => $community_id,
                        "subject" => $subject,
                        "body" => $body,
                        "warnings" => json_encode($warnings),
                        "trace" => $e->getMessage() . "\n" . $e->getTraceAsString(),
                        "success" => 0,
                        "created_at" => time(),
                    ]
                );
                lib::insert(
                    "EventLog",
                    [
                        "event_type" => "Sending Email Failed",
                        "priority" => "critical",
                        "event_description" => ob_get_clean(),
                        "user_id" => lib::current_user()["id"],
                        "community_id" => $community_id,
                        "event_data" => [
                            "to" => $to,
                            "subject" => $subject,
                            "body" => $body,
                            "warnings" => $warnings,
                        ],
                        "trace" => "",
                        "created_at" => time(),
                    ],
                    create_event_log: false
                );
                throw $e;
            }
        }
    }

