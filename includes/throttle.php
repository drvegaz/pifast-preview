<?php
declare(strict_types=1);

function pf_throttle_state_path(string $namespace): string
{
    return __DIR__ . '/../storage/' . $namespace . '_throttle.json';
}

function pf_throttle_state_read(string $namespace): array
{
    $file = pf_throttle_state_path($namespace);
    if (!is_file($file)) {
        return ['ips' => [], 'global' => ['count' => 0, 'windowStart' => time()]];
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data)) {
        return ['ips' => [], 'global' => ['count' => 0, 'windowStart' => time()]];
    }
    $data['ips'] = is_array($data['ips'] ?? null) ? $data['ips'] : [];
    $data['global'] = is_array($data['global'] ?? null) ? $data['global'] : ['count' => 0, 'windowStart' => time()];
    return $data;
}

function pf_throttle_state_write(string $namespace, array $data): void
{
    $file = pf_throttle_state_path($namespace);
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($file, json_encode($data), LOCK_EX);
}

/** Returns null if allowed, or an error message if blocked. Records the attempt as a side effect. */
function pf_throttle_check_and_record(string $namespace, string $ip): ?string
{
    $now = time();
    $state = pf_throttle_state_read($namespace);

    foreach ($state['ips'] as $key => $entry) {
        if (($now - ($entry['last'] ?? 0)) > 3600) {
            unset($state['ips'][$key]);
        }
    }

    if (($now - ($state['global']['windowStart'] ?? 0)) > 86400) {
        $state['global'] = ['count' => 0, 'windowStart' => $now];
    }

    if (($state['global']['count'] ?? 0) >= 100) {
        pf_throttle_state_write($namespace, $state);
        return 'För många förfrågningar just nu. Försök igen senare eller ring oss istället.';
    }

    $entry = $state['ips'][$ip] ?? ['count' => 0, 'first' => $now, 'last' => $now];
    if (($now - $entry['first']) > 3600) {
        $entry = ['count' => 0, 'first' => $now, 'last' => $now];
    }

    if ($entry['count'] >= 5) {
        $state['ips'][$ip] = $entry;
        pf_throttle_state_write($namespace, $state);
        return 'För många förfrågningar från din uppkoppling. Vänta en stund och försök igen.';
    }

    $entry['count']++;
    $entry['last'] = $now;
    $state['ips'][$ip] = $entry;
    $state['global']['count'] = ($state['global']['count'] ?? 0) + 1;
    pf_throttle_state_write($namespace, $state);
    return null;
}
