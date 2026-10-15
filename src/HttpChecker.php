<?hh // strict

require_once __DIR__ . '/UrlValidator.php';

/**
 * Performs one bounded HTTP check against a previously validated public IP.
 *
 * cURL's RESOLVE option pins the connection to the chosen IP while retaining
 * the URL hostname for the Host header and TLS certificate/SNI verification.
 * Redirects are intentionally not followed; each redirect target must be
 * validated independently before another request is made.
 */
function api_sentinel_check_url(string $url): dict<string, mixed> {
  $url_validation = api_sentinel_validate_target_url($url);
  if (($url_validation['valid'] ?? false) !== true) {
    return dict[
      'ok' => false,
      'error' => $url_validation['error'] ?? 'Invalid URL',
    ];
  }

  $parts = \parse_url($url);
  if ($parts === false || !\is_array($parts)) {
    return dict['ok' => false, 'error' => 'Invalid URL'];
  }

  $host = $parts['host'] ?? '';
  if (!\is_string($host) || $host === '') {
    return dict['ok' => false, 'error' => 'Invalid URL hostname'];
  }

  $normalized_host = \strtolower(\rtrim($host, '.'));
  $ip_host = $normalized_host;
  if (
    \strlen($ip_host) >= 2 &&
    \substr($ip_host, 0, 1) === '[' &&
    \substr($ip_host, \strlen($ip_host) - 1) === ']'
  ) {
    $ip_host = \substr($ip_host, 1, \strlen($ip_host) - 2);
  }

  $is_ip_literal = \filter_var($ip_host, FILTER_VALIDATE_IP) !== false;
  if ($is_ip_literal) {
    $validated = api_sentinel_validate_resolved_ips(vec[$ip_host]);
    if (($validated['valid'] ?? false) !== true) {
      return dict['ok' => false, 'error' => 'Private or reserved IP addresses are not allowed'];
    }
    $addresses = vec[$ip_host];
  } else {
    $resolved = api_sentinel_resolve_and_validate_host($normalized_host);
    if (($resolved['valid'] ?? false) !== true) {
      return dict[
        'ok' => false,
        'error' => $resolved['error'] ?? 'DNS validation failed',
      ];
    }

    $addresses = $resolved['addresses'] ?? vec[];
    if (!\is_vec($addresses) || \count($addresses) === 0) {
      return dict['ok' => false, 'error' => 'DNS returned no validated addresses'];
    }
  }

  if (!\function_exists('curl_init') || !\defined('CURLOPT_RESOLVE')) {
    return dict['ok' => false, 'error' => 'Safe HTTP client is unavailable'];
  }

  $scheme = $parts['scheme'] ?? 'https';
  $port = $parts['port'] ?? ($scheme === 'https' ? 443 : 80);
  $address = $addresses[0];
  // DNS names are pinned with CURLOPT_RESOLVE; IP literals are already direct.
  $resolve_address = \strpos($address, ':') !== false
    ? '['.$address.']'
    : $address;
  $resolve_entry = $normalized_host.':'.$port.':'.$resolve_address;

  $handle = \curl_init($url);
  if ($handle === false) {
    return dict['ok' => false, 'error' => 'Could not initialize HTTP client'];
  }

  \curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  \curl_setopt($handle, CURLOPT_FOLLOWLOCATION, false);
  \curl_setopt($handle, CURLOPT_MAXREDIRS, 0);
  \curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  \curl_setopt($handle, CURLOPT_TIMEOUT, 10);
  if (!$is_ip_literal) {
    \curl_setopt($handle, CURLOPT_RESOLVE, vec[$resolve_entry]);
  }
  \curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, true);
  \curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, 2);
  \curl_setopt($handle, CURLOPT_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);
  \curl_setopt($handle, CURLOPT_REDIR_PROTOCOLS, CURLPROTO_HTTP | CURLPROTO_HTTPS);

  $started_at = \microtime(true);
  $body = \curl_exec($handle);
  $elapsed_ms = (\microtime(true) - $started_at) * 1000;
  $status_code = \curl_getinfo($handle, CURLINFO_HTTP_CODE);
  $curl_error = \curl_error($handle);
  \curl_close($handle);

  if ($body === false) {
    return dict[
      'ok' => false,
      'status_code' => (int)$status_code,
      'latency_ms' => (int)\round($elapsed_ms),
      'error' => $curl_error !== '' ? $curl_error : 'HTTP request failed',
    ];
  }

  return dict[
    'ok' => $status_code >= 200 && $status_code < 400,
    'status_code' => (int)$status_code,
    'latency_ms' => (int)\round($elapsed_ms),
    'redirect' => $status_code >= 300 && $status_code < 400,
    'error' => null,
  ];
}
