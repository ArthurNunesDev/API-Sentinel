<?hh // strict

/**
 * Validates endpoint registration data without performing network requests.
 *
 * DNS and network safety checks are performed again by the HTTP checker when
 * a check is actually executed. This helper does not persist endpoint data.
 */
function api_sentinel_validate_endpoint_payload(
  dict<string, mixed> $payload,
): dict<string, mixed> {
  $name = $payload['name'] ?? null;
  $url = $payload['url'] ?? null;
  $interval = $payload['interval_seconds'] ?? 300;

  if (!\is_string($name)) {
    return dict['valid' => false, 'error' => 'Endpoint name is required'];
  }

  $name = \trim($name);
  if ($name === '' || \strlen($name) > 100) {
    return dict[
      'valid' => false,
      'error' => 'Endpoint name must contain between 1 and 100 characters',
    ];
  }

  if (!\is_string($url) || \trim($url) === '') {
    return dict['valid' => false, 'error' => 'Endpoint URL is required'];
  }

  $url = \trim($url);
  $url_validation = api_sentinel_validate_target_url($url);
  if (($url_validation['valid'] ?? false) !== true) {
    return dict[
      'valid' => false,
      'error' => $url_validation['error'] ?? 'Invalid endpoint URL',
    ];
  }

  if (!\is_int($interval) || $interval < 60 || $interval > 86400) {
    return dict[
      'valid' => false,
      'error' => 'Interval must be an integer between 60 and 86400 seconds',
    ];
  }

  return dict[
    'valid' => true,
    'endpoint' => dict[
      'name' => $name,
      'url' => $url,
      'interval_seconds' => $interval,
      'enabled' => true,
    ],
  ];
}
