<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const PF_PROPERTY_MAX_COUNT = 24;

function pf_load_properties(PDO $pdo): array
{
    return $pdo->query('SELECT id, image, name, description FROM properties ORDER BY sort_order, id')->fetchAll();
}

/**
 * Parses a dynamic property field key like "property.5.name" into its id and field.
 * Returns null if the key doesn't match that shape.
 */
function pf_property_field_key(string $key): ?array
{
    if (!preg_match('/^property\.([1-9][0-9]*)\.(image|name|description)$/', $key, $m)) {
        return null;
    }
    return ['id' => (int) $m[1], 'field' => $m[2]];
}

function pf_property_exists(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM properties WHERE id = ?');
    $stmt->execute([$id]);
    return (bool) $stmt->fetchColumn();
}

function pf_render_text(string $raw, bool $multiline): string
{
    $escaped = htmlspecialchars($raw, ENT_QUOTES, 'UTF-8');
    return $multiline ? nl2br($escaped) : $escaped;
}

function pf_render_edit_attrs(string $key, string $raw, bool $multiline): string
{
    $attrs = ' data-edit-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '"';
    $attrs .= ' data-raw-value="' . htmlspecialchars($raw, ENT_QUOTES, 'UTF-8') . '"';
    if ($multiline) {
        $attrs .= ' data-multiline="1"';
    }
    return $attrs;
}
