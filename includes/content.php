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
    'hero.title_accent' => ['type' => 'text', 'multiline' => false],
    'hero.subtitle' => ['type' => 'text', 'multiline' => true],
    'hero.button_secondary_label' => ['type' => 'text', 'multiline' => false],
    'hero.image' => ['type' => 'image', 'multiline' => false],
    'hero.caption' => ['type' => 'text', 'multiline' => false],
    'hero.badge_1' => ['type' => 'text', 'multiline' => true],
    'hero.badge_2' => ['type' => 'text', 'multiline' => true],
    'hero.badge_3' => ['type' => 'text', 'multiline' => true],
    'intro.overline' => ['type' => 'text', 'multiline' => false],
    'intro.heading' => ['type' => 'text', 'multiline' => false],
    'intro.paragraph' => ['type' => 'text', 'multiline' => true],
    'services.overline' => ['type' => 'text', 'multiline' => false],
    'services.heading' => ['type' => 'text', 'multiline' => false],
    'services.1.title' => ['type' => 'text', 'multiline' => false],
    'services.1.body' => ['type' => 'text', 'multiline' => true],
    'services.1.image' => ['type' => 'image', 'multiline' => false],
    'services.2.title' => ['type' => 'text', 'multiline' => false],
    'services.2.body' => ['type' => 'text', 'multiline' => true],
    'services.2.image' => ['type' => 'image', 'multiline' => false],
    'services.3.title' => ['type' => 'text', 'multiline' => false],
    'services.3.body' => ['type' => 'text', 'multiline' => true],
    'services.3.image' => ['type' => 'image', 'multiline' => false],
    'services.4.title' => ['type' => 'text', 'multiline' => false],
    'services.4.body' => ['type' => 'text', 'multiline' => true],
    'services.4.image' => ['type' => 'image', 'multiline' => false],
    'about.overline' => ['type' => 'text', 'multiline' => false],
    'about.heading' => ['type' => 'text', 'multiline' => false],
    'about.paragraph' => ['type' => 'text', 'multiline' => true],
    'about.list.1' => ['type' => 'text', 'multiline' => false],
    'about.list.2' => ['type' => 'text', 'multiline' => false],
    'about.list.3' => ['type' => 'text', 'multiline' => false],
    'about.button_label' => ['type' => 'text', 'multiline' => false],
    'about.image' => ['type' => 'image', 'multiline' => false],
    'about.caption' => ['type' => 'text', 'multiline' => false],
    'about.map_label' => ['type' => 'text', 'multiline' => false],
    'about.feature_1.label' => ['type' => 'text', 'multiline' => false],
    'about.feature_1.text' => ['type' => 'text', 'multiline' => false],
    'about.feature_2.label' => ['type' => 'text', 'multiline' => false],
    'about.feature_2.text' => ['type' => 'text', 'multiline' => false],
    'about.feature_3.label' => ['type' => 'text', 'multiline' => false],
    'about.feature_3.text' => ['type' => 'text', 'multiline' => false],
    'about.feature_4.label' => ['type' => 'text', 'multiline' => false],
    'about.feature_4.text' => ['type' => 'text', 'multiline' => false],
    'gallery.1.image' => ['type' => 'image', 'multiline' => false],
    'gallery.1.name' => ['type' => 'text', 'multiline' => false],
    'gallery.1.location' => ['type' => 'text', 'multiline' => false],
    'gallery.2.image' => ['type' => 'image', 'multiline' => false],
    'gallery.2.name' => ['type' => 'text', 'multiline' => false],
    'gallery.2.location' => ['type' => 'text', 'multiline' => false],
    'gallery.3.image' => ['type' => 'image', 'multiline' => false],
    'gallery.3.name' => ['type' => 'text', 'multiline' => false],
    'gallery.3.location' => ['type' => 'text', 'multiline' => false],
    'gallery.4.image' => ['type' => 'image', 'multiline' => false],
    'gallery.4.name' => ['type' => 'text', 'multiline' => false],
    'gallery.4.location' => ['type' => 'text', 'multiline' => false],
    'trust.heading' => ['type' => 'text', 'multiline' => false],
    'trust.badge_4' => ['type' => 'text', 'multiline' => false],
    'trust.badge_5' => ['type' => 'text', 'multiline' => false],
    'contact.overline' => ['type' => 'text', 'multiline' => false],
    'contact.heading' => ['type' => 'text', 'multiline' => false],
    'contact.paragraph' => ['type' => 'text', 'multiline' => true],
    'contact.button_label' => ['type' => 'text', 'multiline' => false],
    'contact.caption' => ['type' => 'text', 'multiline' => false],
    'contact.image' => ['type' => 'image', 'multiline' => false],
    'contact.phone' => ['type' => 'text', 'multiline' => false],
    'contact.email' => ['type' => 'text', 'multiline' => false],
    'contact.address' => ['type' => 'text', 'multiline' => true],
    'contact.bankgiro' => ['type' => 'text', 'multiline' => false],
    'footer.org_number' => ['type' => 'text', 'multiline' => false],
    'footer.tax_status' => ['type' => 'text', 'multiline' => false],
    'footer.facebook_url' => ['type' => 'text', 'multiline' => false],
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

const PF_ICONS = [
    'phone' => '<path d="M6.6 10.8c1.4 2.8 3.8 5.2 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.3 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.2c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1L6.6 10.8z"/>',
    'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
    'pin' => '<path d="M12 21s7-6.5 7-12a7 7 0 1 0-14 0c0 5.5 7 12 7 12z"/><circle cx="12" cy="9" r="2.4"/>',
    'arrow-right' => '<line x1="4" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/>',
    'people' => '<circle cx="8.5" cy="7.5" r="2.5"/><path d="M3.5 19c0-2.9 2.2-5 5-5s5 2.1 5 5"/><circle cx="16.5" cy="8.5" r="2"/><path d="M14.8 19c.1-2.3 1.6-4 3.7-4.2"/>',
    'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M8 12.5l2.5 2.5L16 9.5"/>',
    'document' => '<path d="M7 3h7l4 4v14a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4h4"/><path d="M9 13h6M9 16.5h6M9 9.5h3"/>',
    'shield' => '<path d="M12 3l7 3v6c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V6l7-3z"/><path d="M9 12.2l2 2 4-4.4"/>',
    'wrench' => '<path d="M14.7 6.3a4 4 0 0 0-5.4 4.9L4 16.5V20h3.5l5.3-5.3a4 4 0 0 0 4.9-5.4l-2.6 2.6-2-2 2.6-2.6z"/>',
    'group' => '<circle cx="12" cy="7.5" r="2.3"/><circle cx="5.5" cy="9.5" r="1.8"/><circle cx="18.5" cy="9.5" r="1.8"/><path d="M8 20c0-2.6 1.9-4.5 4-4.5s4 1.9 4 4.5"/><path d="M2.7 18.5c.2-1.8 1.4-3.1 2.8-3.3"/><path d="M21.3 18.5c-.2-1.8-1.4-3.1-2.8-3.3"/>',
    'badge-check' => '<path d="M12 2.5l2.2 1.6 2.7-.2 1 2.5 2.3 1.5-.6 2.6.6 2.6-2.3 1.5-1 2.5-2.7-.2L12 21.5l-2.2-1.6-2.7.2-1-2.5-2.3-1.5.6-2.6-.6-2.6 2.3-1.5 1-2.5 2.7.2z"/><path d="M8.5 12.3l2.2 2.2 4.5-4.8"/>',
    'diamond' => '<path d="M6 3h12l3.5 5.5L12 21 2.5 8.5 6 3z"/><path d="M2.5 8.5h19M9 3l-2.5 5.5L12 21M15 3l2.5 5.5L12 21"/>',
    'house' => '<path d="M4 11.5 12 4l8 7.5"/><path d="M6 10v9a1 1 0 0 0 1 1h3v-6h4v6h3a1 1 0 0 0 1-1v-9"/>',
    'chevron-up' => '<polyline points="5 15 12 8 19 15"/>',
];

/**
 * Renders a small inline SVG icon by name from PF_ICONS. Static, developer-defined
 * markup only (never user input), so no escaping is needed for the path data.
 */
function pf_icon(string $name, string $class = 'pf-icon'): string
{
    $paths = PF_ICONS[$name] ?? '';
    $classAttr = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
    return '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

function pf_icon_facebook(string $class = 'pf-icon'): string
{
    $classAttr = htmlspecialchars($class, ENT_QUOTES, 'UTF-8');
    return '<svg class="' . $classAttr . '" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13.5 21v-8h2.7l.4-3.1h-3.1V7.9c0-.9.25-1.5 1.6-1.5h1.7V3.6c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.5-4 4.2v2.3H7.6v3.1h2.8v8h3.1z"/></svg>';
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
