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

  $relative_url = api_sentinel_validate_target_url('/health');
  invariant($relative_url['valid'] === false, 'Relative URLs should be rejected');

  $localhost = api_sentinel_validate_target_url('http://localhost:8080');
  invariant($localhost['valid'] === false, 'Localhost should be rejected');

  $localhost_with_dot = api_sentinel_validate_target_url('http://localhost.');
  invariant(
    $localhost_with_dot['valid'] === false,
    'Trailing-dot localhost should be rejected',
  );

  $localhost_subdomain = api_sentinel_validate_target_url('http://api.localhost');
  invariant(
    $localhost_subdomain['valid'] === false,
    'Localhost subdomains should be rejected',
  );

  $loopback_ipv4 = api_sentinel_validate_target_url('http://127.0.0.1');
  invariant($loopback_ipv4['valid'] === false, 'IPv4 loopback should be rejected');

  $private_ipv6 = api_sentinel_validate_target_url('http://[fc00::1]');
  invariant(
    $private_ipv6['valid'] === false,
    'IPv6 unique-local addresses should be rejected',
  );

  $private_ip = api_sentinel_validate_target_url('http://192.168.1.10');
  invariant($private_ip['valid'] === false, 'Private IP addresses should be rejected');

  $loopback_ipv6 = api_sentinel_validate_target_url('http://[::1]:8080');
  invariant(
    $loopback_ipv6['valid'] === false,
    'IPv6 loopback addresses should be rejected',
  );

  $credentials = api_sentinel_validate_target_url('https://user:pass@example.com');
  invariant($credentials['valid'] === false, 'Embedded credentials should be rejected');

  $public_dns_ips = api_sentinel_validate_resolved_ips(vec['8.8.8.8', '2001:4860:4860::8888']);
  invariant(
    $public_dns_ips['valid'] === true,
    'Public IPv4 and IPv6 DNS answers should be accepted',
  );

  $mixed_dns_ips = api_sentinel_validate_resolved_ips(vec['8.8.8.8', '127.0.0.1']);
  invariant(
    $mixed_dns_ips['valid'] === false,
    'A public answer mixed with loopback must reject the whole DNS result',
  );

  $private_dns_ips = api_sentinel_validate_resolved_ips(vec['10.0.0.5']);
  invariant(
    $private_dns_ips['valid'] === false,
    'Private DNS answers should be rejected',
  );

  $empty_dns_ips = api_sentinel_validate_resolved_ips(vec[]);
  invariant(
    $empty_dns_ips['valid'] === false,
    'An empty DNS result should be rejected',
  );

  echo "URL validation tests passed.\n";
}
