<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const PF_CONTENT_REGISTRY = [
    'topbar.badge_1' => ['type' => 'text', 'multiline' => false],
    'topbar.badge_2' => ['type' => 'text', 'multiline' => false],
    'topbar.badge_3' => ['type' => 'text', 'multiline' => false],
    'topbar.badge_4' => ['type' => 'text', 'multiline' => false],
    'hero.overline' => ['type' => 'text', 'multiline' => false],
    'hero.title' => ['type' => 'text', 'multiline' => false],
    'hero.subtitle' => ['type' => 'text', 'multiline' => true],
    'hero.button_secondary_label' => ['type' => 'text', 'multiline' => false],
    'hero.image' => ['type' => 'image', 'multiline' => false],
    'intro.overline' => ['type' => 'text', 'multiline' => false],
    'intro.heading' => ['type' => 'text', 'multiline' => false],
    'intro.paragraph' => ['type' => 'text', 'multiline' => true],
    'services.overline' => ['type' => 'text', 'multiline' => false],
    'services.heading' => ['type' => 'text', 'multiline' => false],
    'services.1.title' => ['type' => 'text', 'multiline' => false],
    'services.1.body' => ['type' => 'text', 'multiline' => true],
    'services.2.title' => ['type' => 'text', 'multiline' => false],
    'services.2.body' => ['type' => 'text', 'multiline' => true],
    'services.3.title' => ['type' => 'text', 'multiline' => false],
    'services.3.body' => ['type' => 'text', 'multiline' => true],
    'about.overline' => ['type' => 'text', 'multiline' => false],
    'about.heading' => ['type' => 'text', 'multiline' => false],
    'about.paragraph' => ['type' => 'text', 'multiline' => true],
    'about.list.1' => ['type' => 'text', 'multiline' => false],
    'about.list.2' => ['type' => 'text', 'multiline' => false],
    'about.list.3' => ['type' => 'text', 'multiline' => false],
    'about.button_label' => ['type' => 'text', 'multiline' => false],
    'about.image' => ['type' => 'image', 'multiline' => false],
    'gallery.1.image' => ['type' => 'image', 'multiline' => false],
    'gallery.2.image' => ['type' => 'image', 'multiline' => false],
    'gallery.3.image' => ['type' => 'image', 'multiline' => false],
    'gallery.4.image' => ['type' => 'image', 'multiline' => false],
    'properties.1.image' => ['type' => 'image', 'multiline' => false],
    'properties.1.name' => ['type' => 'text', 'multiline' => false],
    'properties.1.description' => ['type' => 'text', 'multiline' => true],
    'properties.2.image' => ['type' => 'image', 'multiline' => false],
    'properties.2.name' => ['type' => 'text', 'multiline' => false],
    'properties.2.description' => ['type' => 'text', 'multiline' => true],
    'properties.3.image' => ['type' => 'image', 'multiline' => false],
    'properties.3.name' => ['type' => 'text', 'multiline' => false],
    'properties.3.description' => ['type' => 'text', 'multiline' => true],
    'properties.4.image' => ['type' => 'image', 'multiline' => false],
    'properties.4.name' => ['type' => 'text', 'multiline' => false],
    'properties.4.description' => ['type' => 'text', 'multiline' => true],
    'contact.overline' => ['type' => 'text', 'multiline' => false],
    'contact.heading' => ['type' => 'text', 'multiline' => false],
    'contact.paragraph' => ['type' => 'text', 'multiline' => true],
    'contact.button_label' => ['type' => 'text', 'multiline' => false],
    'contact.phone' => ['type' => 'text', 'multiline' => false],
    'contact.email' => ['type' => 'text', 'multiline' => false],
    'contact.address' => ['type' => 'text', 'multiline' => true],
    'contact.bankgiro' => ['type' => 'text', 'multiline' => false],
    'footer.org_number' => ['type' => 'text', 'multiline' => false],
    'footer.tax_status' => ['type' => 'text', 'multiline' => false],
];

function pf_load_content(PDO $pdo): array
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }
    $rows = $pdo->query('SELECT `key`, `value` FROM content')->fetchAll();
    $cache = [];
    foreach ($rows as $row) {
        $cache[$row['key']] = $row['value'];
    }
    return $cache;
}

function pf_key_type(string $key): ?string
{
    return PF_CONTENT_REGISTRY[$key]['type'] ?? null;
}

function pf_key_multiline(string $key): bool
{
    return PF_CONTENT_REGISTRY[$key]['multiline'] ?? false;
}

function pf_text(array $content, string $key): string
{
    $value = $content[$key] ?? '';
    $escaped = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    return pf_key_multiline($key) ? nl2br($escaped) : $escaped;
}

function pf_edit_attrs(array $content, string $key): string
{
    $raw = $content[$key] ?? '';
    $attrs = ' data-edit-key="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '"';
    $attrs .= ' data-raw-value="' . htmlspecialchars($raw, ENT_QUOTES, 'UTF-8') . '"';
    if (pf_key_multiline($key)) {
        $attrs .= ' data-multiline="1"';
    }
    return $attrs;
}

function pf_image_url(array $content, string $key): string
{
    return htmlspecialchars($content[$key] ?? '', ENT_QUOTES, 'UTF-8');
}

function pf_tel_href(string $phone): string
{
    $digits = ltrim((string) preg_replace('/\D+/', '', $phone), '0');
    return 'tel:+46' . $digits;
}

function pf_mailto_href(string $email): string
{
    return 'mailto:' . $email;
}
