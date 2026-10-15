<?hh // strict

<<__EntryPoint>>
function main(): void {
  require_once __DIR__ . '/../src/Health.php';

  $payload = api_sentinel_health_payload();

  invariant($payload['status'] === 'ok', 'Health status should be ok');
  invariant(
    $payload['service'] === 'API Sentinel',
    'Health payload should identify the service',
  );

  echo "Health payload tests passed.\n";
}
