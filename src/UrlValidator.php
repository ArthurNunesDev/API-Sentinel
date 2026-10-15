<?hh // strict

/**
 * Validates the basic shape of a URL before it can be monitored.
 *
 * This intentionally does not perform a network request. Network-level SSRF
 * protections must also validate resolved IP addresses and every redirect.
 */
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
