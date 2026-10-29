<?php
function icon(string $name, int $size = 22): string
{
    $paths = [
        'home'     => '<path d="M3 11.5 12 4l9 7.5"/><path d="M5.5 10v9.5h13V10"/><path d="M10 19.5v-5h4v5"/>',
        'file'     => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4"/><path d="M10 12h5M10 16h5"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4.5 20c.8-4 3.8-6 7.5-6s6.7 2 7.5 6"/>',
        'users'    => '<circle cx="9" cy="8.5" r="3.5"/><path d="M2.5 19.5c.6-3.4 3.2-5 6.5-5s5.9 1.6 6.5 5"/><path d="M16 5.2a3.5 3.5 0 0 1 0 6.6M18 14.8c2 .6 3.2 2.1 3.5 4.7"/>',
        'logout'   => '<path d="M10 4H5v16h5"/><path d="M9 12h11M16 8l4 4-4 4"/>',
        'bell'     => '<path d="M6 17V11a6 6 0 0 1 12 0v6l1.5 2h-15z"/><path d="M10 21h4"/>',
        'check'    => '<circle cx="12" cy="12" r="9"/><path d="m8 12.5 3 3 5-6"/>',
        'tick'     => '<path d="m5 12.5 4.5 4.5L19 7"/>',
        'camera'   => '<path d="M4 8h3l1.5-2.5h7L17 8h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'chevron'  => '<path d="m9 5 7 7-7 7"/>',
        'search'   => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
        'back'     => '<path d="M9 8 4 12l5 4"/><path d="M4 12h11a5 5 0 0 1 0 8h-3"/>',
        'store'    => '<path d="M4 9.5 5.5 4h13L20 9.5"/><path d="M4 9.5a2.7 2.7 0 0 0 5.3 0 2.7 2.7 0 0 0 5.4 0 2.7 2.7 0 0 0 5.3 0"/><path d="M5.5 12.5V20h13v-7.5"/><path d="M10 20v-4.5h4V20"/>',
        'card'     => '<rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="11" r="2"/><path d="M6.5 16c.5-1.6 1.5-2.2 2.5-2.2s2 .6 2.5 2.2M14 10h4M14 13.5h4"/>',
        'id'       => '<rect x="5" y="3" width="14" height="18" rx="2.5"/><circle cx="12" cy="10" r="2.5"/><path d="M8.5 17c.6-2 1.8-2.8 3.5-2.8s2.9.8 3.5 2.8"/>',
        'download' => '<path d="M12 4v11M7.5 10.5 12 15l4.5-4.5"/><path d="M5 19.5h14"/>',
        'edit'     => '<path d="m4 20 1-4 11-11 3 3L8 19z"/><path d="m14 7 3 3"/>',
        'trash'    => '<path d="M5 7h14M9 7V4h6v3"/><path d="M7 7l1 13h8l1-13"/><path d="M10 11v6M14 11v6"/>',
        'lock'     => '<rect x="5" y="10.5" width="14" height="10" rx="2"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5"/><path d="M12 14.5v2.5"/>',
        'pin'      => '<path d="M12 21s6.5-5.7 6.5-11A6.5 6.5 0 0 0 5.5 10c0 5.3 6.5 11 6.5 11z"/><circle cx="12" cy="10" r="2.3"/>',
        'mail'     => '<rect x="3" y="5.5" width="18" height="13" rx="2.5"/><path d="m4 7.5 8 6 8-6"/>',
        'phone'    => '<path d="M6 3.5h3l1.5 4-2 1.5a11 11 0 0 0 6.5 6.5l1.5-2 4 1.5v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4 5.7 2 2 0 0 1 6 3.5z"/>',
        'grid'     => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
    ];

    $body = $paths[$name] ?? $paths['file'];

    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" '
        . 'stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" '
        . 'aria-hidden="true">' . $body . '</svg>';
}
