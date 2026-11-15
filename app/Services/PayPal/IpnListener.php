<?php

namespace App\Services\PayPal;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * PayPal IPN listener.
 *
 * A modernised port of PHP-PayPal-IPN (Micah Carrick, 2012). Changes from the
 * original library:
 *
 *  - Posts back to PayPal's dedicated IPN verification hosts
 *    (ipnpb.paypal.com / ipnpb.sandbox.paypal.com) instead of www.paypal.com.
 *  - Forces TLS 1.2+ and verifies against the system CA store. The original
 *    forced SSLv3 and shipped a 2012 certificate bundle, both of which PayPal
 *    now rejects.
 *  - Sends a User-Agent header, which PayPal requires on verification requests.
 *  - Echoes the raw request body back verbatim so field order and encoding are
 *    preserved exactly as PayPal sent them (re-encoding $_POST can break
 *    verification for non-ASCII payloads).
 *  - Uses Laravel's HTTP client with explicit connect/overall timeouts, and
 *    compares the response body exactly rather than with strpos().
 *  - Drops the fsockopen() and plain-HTTP fallbacks, which PayPal no longer accepts.
 */
class IpnListener
{
    public const LIVE_ENDPOINT = 'https://ipnpb.paypal.com/cgi-bin/webscr';

    public const SANDBOX_ENDPOINT = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';

    public const VERIFIED = 'VERIFIED';

    public const INVALID = 'INVALID';

    private bool $sandbox = false;

    private int $timeout = 30;

    private ?string $lastResponse = null;

    private ?int $lastStatus = null;

    public function useSandbox(bool $sandbox = true): static
    {
        $this->sandbox = $sandbox;

        return $this;
    }

    public function timeout(int $seconds): static
    {
        $this->timeout = max(5, $seconds);

        return $this;
    }

    public function endpoint(): string
    {
        return $this->sandbox ? self::SANDBOX_ENDPOINT : self::LIVE_ENDPOINT;
    }

    /**
     * Verify an IPN message with PayPal.
     *
     * @param  string  $rawBody  The raw, url-encoded request body exactly as PayPal sent it.
     * @return bool true when PayPal answers VERIFIED, false when INVALID.
     *
     * @throws IpnVerificationException when PayPal cannot be reached or answers unexpectedly.
     */
    public function verify(string $rawBody): bool
    {
        if (trim($rawBody) === '') {
            throw new IpnVerificationException('No IPN data received.');
        }

        // PayPal asks for "cmd=_notify-validate" to precede the original message.
        $payload = 'cmd=_notify-validate&'.ltrim($rawBody, '&');

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'SiedeStudios-IPN-Verification/1.0',
                'Connection' => 'close',
            ])
                ->withBody($payload, 'application/x-www-form-urlencoded')
                ->withOptions([
                    'verify' => true,
                    'curl' => [CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2],
                ])
                ->connectTimeout(10)
                ->timeout($this->timeout)
                ->post($this->endpoint());
        } catch (ConnectionException $e) {
            throw new IpnVerificationException('Could not reach PayPal: '.$e->getMessage(), previous: $e);
        }

        $this->lastStatus = $response->status();
        $this->lastResponse = trim($response->body());

        if (! $response->successful()) {
            throw new IpnVerificationException("PayPal answered HTTP {$this->lastStatus}.");
        }

        return match ($this->lastResponse) {
            self::VERIFIED => true,
            self::INVALID => false,
            default => throw new IpnVerificationException('Unexpected response from PayPal: '.mb_substr($this->lastResponse, 0, 100)),
        };
    }

    /**
     * Rebuild a url-encoded body from parsed fields, for callers without access
     * to the raw request (e.g. replaying a stored IPN).
     */
    public static function encode(array $fields): string
    {
        return collect($fields)
            ->map(fn ($value, $key) => rawurlencode((string) $key).'='.rawurlencode((string) $value))
            ->implode('&');
    }

    public function lastResponse(): ?string
    {
        return $this->lastResponse;
    }

    public function lastStatus(): ?int
    {
        return $this->lastStatus;
    }
}
