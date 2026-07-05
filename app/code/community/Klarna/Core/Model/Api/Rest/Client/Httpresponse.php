<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

declare(strict_types=1);

/**
 * Lightweight HTTP response value object.
 *
 * Replaces Zend_Http_Response (dropped in Maho) while preserving the small subset of its
 * interface the Klarna API layer relies on: getStatus(), getBody(), getHeader(), isSuccessful()
 * and the two debug helpers. Instances are produced by Klarna_Core_Model_Api_Rest_Client from a
 * Symfony HttpClient response, or constructed directly to represent a transport-level failure.
 */
class Klarna_Core_Model_Api_Rest_Client_Httpresponse
{
    /** @var array<string, list<string>> Header values keyed by lowercased header name */
    private array $normalizedHeaders = [];

    /**
     * @param array<string, list<string>|string> $headers
     */
    public function __construct(
        private readonly int $status,
        array $headers = [],
        private readonly string $body = '',
    ) {
        foreach ($headers as $name => $values) {
            $this->normalizedHeaders[strtolower($name)] = is_array($values) ? array_values($values) : [$values];
        }
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * Whether the response carries a 2xx status code.
     */
    public function isSuccessful(): bool
    {
        return $this->status >= 200 && $this->status < 300;
    }

    /**
     * Get the first value of a header (case-insensitive), or null if absent.
     */
    public function getHeader(string $name): ?string
    {
        return $this->normalizedHeaders[strtolower($name)][0] ?? null;
    }

    /**
     * Render all headers as a single string (used for debug logging).
     */
    public function getHeadersAsString(): string
    {
        $lines = ["HTTP/1.1 {$this->status}"];
        foreach ($this->normalizedHeaders as $name => $values) {
            foreach ($values as $value) {
                $lines[] = "{$name}: {$value}";
            }
        }

        return implode("\n", $lines);
    }

    /**
     * Render the full response (headers + body) as a string (used for debug logging).
     */
    public function asString(): string
    {
        return $this->getHeadersAsString() . "\n\n" . $this->body;
    }
}
