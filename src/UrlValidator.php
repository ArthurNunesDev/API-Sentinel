<?hh // strict

/**
 * Validates the basic shape of a URL before it can be monitored.
 *
 * This does not make a network request. Network-level SSRF protections must
 * also validate DNS answers, pin the connection to a validated address, and
 * validate every redirect.
 */

/**
 * Validates every address returned by a DNS lookup before connecting.
 *
 * An empty result, malformed address, or any private/reserved address rejects
 * the entire set. Callers must validate all A and AAAA answers.
 */
function api_sentinel_validate_resolved_ips(
  vec<string> $addresses,
): dict<string, mixed> {
  if (\count($addresses) === 0) {
    return dict['valid' => false, 'error' => 'DNS returned no addresses'];
  }

  $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
  foreach ($addresses as $address) {
    if (\filter_var($address, FILTER_VALIDATE_IP, $flags) === false) {
      return dict[
        'valid' => false,
        'error' => 'DNS resolved to a private, reserved, or invalid IP address',
      ];
    }
  }

  return dict['valid' => true, 'addresses' => $addresses];
}

/**
 * Resolves a target hostname and validates all IPv4 and IPv6 answers.
 *
 * This is a validation stage only: callers must still connect to one of the
 * returned validated addresses directly to avoid DNS rebinding, preserve the
 * original hostname for HTTP Host/TLS SNI, and disable or revalidate redirects.
 */
function api_sentinel_resolve_and_validate_host(
  string $host,
): dict<string, mixed> {
  $normalized_host = \strtolower(\rtrim($host, '.'));
  if (
    $normalized_host === '' ||
    \filter_var($normalized_host, FILTER_VALIDATE_IP) !== false ||
    $normalized_host === 'localhost' ||
    $normalized_host === 'localhost.localdomain' ||
    (\strlen($normalized_host) >= 10 &&
      \substr($normalized_host, \strlen($normalized_host) - 10) === '.localhost')
  ) {
    return dict[
      'valid' => false,
      'error' => 'A public DNS hostname is required',
    ];
  }

  if (!\function_exists('dns_get_record')) {
    return dict[
      'valid' => false,
      'error' => 'DNS resolution is not available in this runtime',
    ];
  }

  $records = \dns_get_record($normalized_host, DNS_A | DNS_AAAA);
  if (!\is_array($records) || \count($records) === 0) {
    return dict['valid' => false, 'error' => 'DNS lookup failed or returned no addresses'];
  }

  $addresses = vec[];
  foreach ($records as $record) {
    if (!\is_array($record)) {
      return dict['valid' => false, 'error' => 'DNS returned an invalid record'];
    }

    $address = null;
    if (isset($record['ip']) && \is_string($record['ip'])) {
      $address = $record['ip'];
    } else if (isset($record['ipv6']) && \is_string($record['ipv6'])) {
      $address = $record['ipv6'];
    }

    if ($address !== null) {
      $addresses[] = $address;
    }
  }

  return api_sentinel_validate_resolved_ips($addresses);
}

function api_sentinel_validate_target_url(string $url): dict<string, mixed> {
  $parts = \parse_url($url);

  if ($parts === false || !\is_array($parts)) {
    return dict['valid' => false, 'error' => 'Invalid URL'];
  }

  $scheme = $parts['scheme'] ?? '';
  $host = $parts['host'] ?? '';

  if (
    ($scheme !== 'http' && $scheme !== 'https') ||
    !\is_string($host) ||
    $host === ''
  ) {
    return dict[
      'valid' => false,
      'error' => 'Only absolute HTTP and HTTPS URLs are allowed',
    ];
  }

  if (isset($parts['user']) || isset($parts['pass'])) {
    return dict[
      'valid' => false,
      'error' => 'URLs containing embedded credentials are not allowed',
    ];
  }

  $normalized_host = \strtolower(\rtrim($host, '.'));
  $host_length = \strlen($normalized_host);
  $is_localhost_subdomain = $host_length >= 10 &&
    \substr($normalized_host, $host_length - 10) === '.localhost';

  if (
    $normalized_host === 'localhost' ||
    $normalized_host === 'localhost.localdomain' ||
    $is_localhost_subdomain
  ) {
    return dict[
      'valid' => false,
      'error' => 'Localhost targets are not allowed',
    ];
  }

  // parse_url() keeps IPv6 brackets in the host; remove them before IP checks.
  $ip_host = $host;
  if (
    \strlen($ip_host) >= 2 &&
    \substr($ip_host, 0, 1) === '[' &&
    \substr($ip_host, \strlen($ip_host) - 1) === ']'
  ) {
    $ip_host = \substr($ip_host, 1, \strlen($ip_host) - 2);
  }

  if (\filter_var($ip_host, FILTER_VALIDATE_IP) !== false) {
    $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
    if (\filter_var($ip_host, FILTER_VALIDATE_IP, $flags) === false) {
      return dict[
        'valid' => false,
        'error' => 'Private or reserved IP addresses are not allowed',
      ];
    }
  }

  return dict['valid' => true, 'url' => $url];
}
