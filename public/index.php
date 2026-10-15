<?hh // strict

<<__EntryPoint>>
function main(): void {
  require_once __DIR__ . '/../src/Health.php';

  $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
  $uri = $_SERVER['REQUEST_URI'] ?? '/';
  $path = \parse_url($uri, PHP_URL_PATH);

  \header('Content-Type: application/json; charset=utf-8');
  \header('Cache-Control: no-store');

  if ($method !== 'GET') {
    \header('Allow: GET');
    \http_response_code(405);
    echo \json_encode(dict['error' => 'Method not allowed']);
    return;
  }

  if ($path === '/health') {
    \http_response_code(200);
    echo \json_encode(api_sentinel_health_payload());
    return;
  }

  if ($path === '/' || $path === '/index.php') {
    \http_response_code(200);
    echo \json_encode(dict[
      'name' => 'API Sentinel',
      'status' => 'running',
      'health' => '/health',
    ]);
    return;
  }

  \http_response_code(404);
  echo \json_encode(dict['error' => 'Not found']);
}
