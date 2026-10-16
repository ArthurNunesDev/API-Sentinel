<?hh // strict

<<__EntryPoint>>
function main(): void {
  require_once __DIR__ . '/../src/UrlValidator.php';
  require_once __DIR__ . '/../src/Endpoint.php';

  $valid = api_sentinel_validate_endpoint_payload(dict[
    'name' => 'Production API',
    'url' => 'https://example.com/health',
    'interval_seconds' => 300,
  ]);
  invariant($valid['valid'] === true, 'Valid endpoint payload should be accepted');
  $endpoint = $valid['endpoint'] ?? dict[];
  invariant($endpoint['name'] === 'Production API', 'Endpoint name should be trimmed and preserved');
  invariant($endpoint['interval_seconds'] === 300, 'Interval should be preserved');
  invariant($endpoint['enabled'] === true, 'New endpoints should default to enabled');

  $missing_name = api_sentinel_validate_endpoint_payload(dict[
    'url' => 'https://example.com/health',
  ]);
  invariant($missing_name['valid'] === false, 'Missing endpoint name should be rejected');

  $blank_name = api_sentinel_validate_endpoint_payload(dict[
    'name' => '   ',
    'url' => 'https://example.com/health',
  ]);
  invariant($blank_name['valid'] === false, 'Blank endpoint name should be rejected');

  $long_name = api_sentinel_validate_endpoint_payload(dict[
    'name' => \str_repeat('a', 101),
    'url' => 'https://example.com/health',
  ]);
  invariant($long_name['valid'] === false, 'Endpoint names over 100 characters should be rejected');

  $invalid_url = api_sentinel_validate_endpoint_payload(dict[
    'name' => 'Bad target',
    'url' => 'ftp://example.com',
  ]);
  invariant($invalid_url['valid'] === false, 'Non-HTTP endpoint URLs should be rejected');

  $private_url = api_sentinel_validate_endpoint_payload(dict[
    'name' => 'Private target',
    'url' => 'http://192.168.1.10',
  ]);
  invariant($private_url['valid'] === false, 'Private IP endpoint URLs should be rejected');

  $too_short = api_sentinel_validate_endpoint_payload(dict[
    'name' => 'Too frequent',
    'url' => 'https://example.com',
    'interval_seconds' => 59,
  ]);
  invariant($too_short['valid'] === false, 'Intervals below 60 seconds should be rejected');

  $too_long = api_sentinel_validate_endpoint_payload(dict[
    'name' => 'Too infrequent',
    'url' => 'https://example.com',
    'interval_seconds' => 86401,
  ]);
  invariant($too_long['valid'] === false, 'Intervals over 86400 seconds should be rejected');

  $string_interval = api_sentinel_validate_endpoint_payload(dict[
    'name' => 'String interval',
    'url' => 'https://example.com',
    'interval_seconds' => '300',
  ]);
  invariant($string_interval['valid'] === false, 'String intervals should be rejected');

  echo "Endpoint payload tests passed.\\n";
}
