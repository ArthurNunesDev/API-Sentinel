<?hh // strict

<<__EntryPoint>>
function main(): void {
  require_once __DIR__ . '/../src/UrlValidator.php';

  $valid_https = api_sentinel_validate_target_url('https://example.com/status');
  invariant($valid_https['valid'] === true, 'Public HTTPS URL should be accepted');

  $valid_http = api_sentinel_validate_target_url('http://example.com');
  invariant($valid_http['valid'] === true, 'Public HTTP URL should be accepted');

  $invalid_scheme = api_sentinel_validate_target_url('ftp://example.com');
  invariant($invalid_scheme['valid'] === false, 'Non-HTTP schemes should be rejected');

  $localhost = api_sentinel_validate_target_url('http://localhost:8080');
  invariant($localhost['valid'] === false, 'Localhost should be rejected');

  $private_ip = api_sentinel_validate_target_url('http://192.168.1.10');
  invariant($private_ip['valid'] === false, 'Private IP addresses should be rejected');

  $credentials = api_sentinel_validate_target_url('https://user:pass@example.com');
  invariant($credentials['valid'] === false, 'Embedded credentials should be rejected');

  echo "URL validation tests passed.\n";
}
