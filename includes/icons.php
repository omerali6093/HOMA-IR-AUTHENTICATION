<?php

/**
 * Inline SVG icons for the signup / calculator UI.
 *
 * Usage:  <?= hu_icon("user", "hu-input__icon") ?>
 *
 * Icons are static markup defined below (never user input), so they are
 * safe to print without escaping.
 */

function hu_icon(string $name, string $class = ""): string
{
    // Outline icons share these attributes (24 x 24 grid, drawn with currentColor).
    $line = 'fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"';

    $icons = [

        // --- form field icons ---------------------------------------------
        "user" =>
            '<circle cx="12" cy="8" r="4" ' . $line . '/>'
            . '<path d="M4.5 20.5c.6-4 3.6-6 7.5-6s6.9 2 7.5 6" ' . $line . '/>',

        "mail" =>
            '<rect x="3" y="5" width="18" height="14" rx="2.6" ' . $line . '/>'
            . '<path d="m4.2 7.6 7.8 5.6 7.8-5.6" ' . $line . '/>',

        "lock" =>
            '<rect x="4.8" y="10.5" width="14.4" height="10" rx="2.6" ' . $line . '/>'
            . '<path d="M8.2 10.5V8a3.8 3.8 0 0 1 7.6 0v2.5" ' . $line . '/>'
            . '<circle cx="12" cy="15.5" r="1.1" fill="currentColor"/>',

        "eye" =>
            '<path d="M2.2 12S5.7 5.6 12 5.6 21.8 12 21.8 12 18.3 18.4 12 18.4 2.2 12 2.2 12Z" ' . $line . '/>'
            . '<circle cx="12" cy="12" r="3" ' . $line . '/>',

        "eye-off" =>
            '<path d="M9.9 5.8a9.6 9.6 0 0 1 2.1-.2c6.3 0 9.8 6.4 9.8 6.4a17 17 0 0 1-3 3.9M6.4 7.2C3.6 8.9 2.2 12 2.2 12s3.5 6.4 9.8 6.4c1.5 0 2.8-.3 4-.9" ' . $line . '/>'
            . '<path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" ' . $line . '/>'
            . '<path d="m3.5 3.5 17 17" ' . $line . '/>',

        // --- actions --------------------------------------------------------
        "arrow-right" =>
            '<path d="M5 12h14m-6-6 6 6-6 6" ' . $line . '/>',

        "chevron-up" =>
            '<path d="m6.5 14.5 5.5-5.5 5.5 5.5" ' . $line . '/>',

        "chevron-down" =>
            '<path d="m6.5 9.5 5.5 5.5 5.5-5.5" ' . $line . '/>',

        // --- status ---------------------------------------------------------
        "alert" =>
            '<circle cx="12" cy="12" r="9.2" ' . $line . '/>'
            . '<path d="M12 7.6v5.4" ' . $line . '/>'
            . '<circle cx="12" cy="16.4" r="1.1" fill="currentColor"/>',

        "check-circle" =>
            '<circle cx="12" cy="12" r="11" fill="#12803f"/>'
            . '<path d="m7.2 12.4 3.3 3.2 6.3-6.6" fill="none" stroke="#fff" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>',

        "check" =>
            '<path d="m5.5 12.5 4.3 4.2 8.7-9.2" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>',

        "info" =>
            '<circle cx="12" cy="12" r="11" fill="#1b86e6"/>'
            . '<circle cx="12" cy="7.3" r="1.35" fill="#fff"/>'
            . '<path d="M12 11v6.2" stroke="#fff" stroke-width="2.2" stroke-linecap="round"/>',

        // --- badges (filled) -----------------------------------------------
        "user-solid" =>
            '<circle cx="12" cy="8" r="4.3" fill="currentColor"/>'
            . '<path d="M3.6 21c.5-4.5 3.9-6.8 8.4-6.8s7.9 2.3 8.4 6.8Z" fill="currentColor"/>',

        // white calculator body, blue details (used inside the blue badge)
        "calc-solid" =>
            '<rect x="4" y="2" width="16" height="20" rx="3.6" fill="#fff"/>'
            . '<rect x="7" y="5" width="10" height="4" rx="1.2" fill="#1b86e6"/>'
            . '<g fill="#1b86e6">'
            . '<rect x="7.2" y="11.2" width="2.6" height="2.6" rx=".7"/><rect x="10.7" y="11.2" width="2.6" height="2.6" rx=".7"/><rect x="14.2" y="11.2" width="2.6" height="2.6" rx=".7"/>'
            . '<rect x="7.2" y="15.2" width="2.6" height="2.6" rx=".7"/><rect x="10.7" y="15.2" width="2.6" height="2.6" rx=".7"/><rect x="14.2" y="15.2" width="2.6" height="2.6" rx=".7"/>'
            . '</g>',

        // white outline calculator (used inside the "Calculate" button)
        "calc" =>
            '<rect x="4.5" y="2.5" width="15" height="19" rx="3.2" ' . $line . '/>'
            . '<rect x="7.6" y="5.6" width="8.8" height="3.4" rx="1" ' . $line . '/>'
            . '<g fill="currentColor">'
            . '<circle cx="8.6" cy="12.6" r="1.05"/><circle cx="12" cy="12.6" r="1.05"/><circle cx="15.4" cy="12.6" r="1.05"/>'
            . '<circle cx="8.6" cy="16.2" r="1.05"/><circle cx="12" cy="16.2" r="1.05"/><circle cx="15.4" cy="16.2" r="1.05"/>'
            . '</g>',

        // --- calculator field icons (own colours) ---------------------------
        "tube" =>
            '<rect x="8" y="2.2" width="8" height="3.2" rx="1.6" fill="#fff" stroke="#1b86e6" stroke-width="1.7"/>'
            . '<path d="M9.4 5.4V18a2.6 2.6 0 0 0 5.2 0V5.4" fill="#e3f0fd" stroke="#1b86e6" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>'
            . '<path d="M9.5 12.2h5" stroke="#1b86e6" stroke-width="1.7" stroke-linecap="round"/>'
            . '<path d="M9.5 12.2V18a2.5 2.5 0 0 0 5 0v-5.8Z" fill="#8cc2f5"/>',

        "drop" =>
            '<path d="M12 2.6c-3.1 4.5-6.6 7.7-6.6 11.7a6.6 6.6 0 0 0 13.2 0c0-4-3.5-7.2-6.6-11.7Z" fill="#dcecfd" stroke="#1b86e6" stroke-width="1.7" stroke-linejoin="round"/>'
            . '<path d="M8.4 14.6c.2 1.9 1.5 3.2 3.4 3.5" fill="none" stroke="#1b86e6" stroke-width="1.7" stroke-linecap="round"/>'
            . '<path d="M6.6 14.5c.1 2.4 2.5 4.4 5.4 4.4s5.3-2 5.4-4.4c-1.6.9-3.4.9-5.4.1-2-.8-3.8-.8-5.4.3Z" fill="#8cc2f5"/>',

        // three ascending bars (interpretation panel heading)
        "bars" =>
            '<rect x="2.5" y="12" width="5.2" height="9.5" rx="1.6" fill="#4aa0ee"/>'
            . '<rect x="9.4" y="3" width="5.2" height="18.5" rx="1.6" fill="#1b86e6"/>'
            . '<rect x="16.3" y="8" width="5.2" height="13.5" rx="1.6" fill="#4aa0ee"/>',
    ];

    if (!isset($icons[$name])) {
        return "";
    }

    $cls = $class !== "" ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : "";

    return '<svg' . $cls . ' viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">'
        . $icons[$name]
        . '</svg>';
}

/**
 * The multicolour Google "G" (official brand colours) for the sign-up button.
 */
function hu_google_logo(string $class = ""): string
{
    $cls = $class !== "" ? ' class="' . htmlspecialchars($class, ENT_QUOTES) . '"' : "";

    return '<svg' . $cls . ' viewBox="0 0 48 48" width="24" height="24" aria-hidden="true" focusable="false">'
        . '<path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>'
        . '<path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>'
        . '<path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>'
        . '<path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>'
        . '</svg>';
}
