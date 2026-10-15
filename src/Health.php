<?hh // strict

/**
 * Builds the service health payload.
 *
 * This is a small domain-level helper; an HTTP route is not wired up yet.
 */
function api_sentinel_health_payload(): dict<string, string> {
  return dict[
    'status' => 'ok',
    'service' => 'API Sentinel',
  ];
}
