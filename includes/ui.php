<?php

/**
 * Shared UI pieces for the signup and calculator pages.
 *
 * Presentation only - nothing in here touches the session or the database.
 */

require_once __DIR__ . "/icons.php";


/**
 * "Check Your HOMA-IR" heading, tagline and the 2-step progress bar.
 *
 * @param int $step  1 = Registration is the current step,
 *                   2 = Registration is done, Calculator is the current step.
 */
function hu_page_header(int $step = 1): void
{
    $step = $step === 2 ? 2 : 1;
    ?>
    <header class="hu-header">

        <h1 class="hu-title">Check Your <span>HOMA-IR</span></h1>

        <p class="hu-tagline">Simple. Accurate. A step towards a healthier you.</p>

        <span class="hu-rule" aria-hidden="true"></span>

        <ol class="hu-steps hu-steps--<?php echo $step; ?>" aria-label="Progress">

            <li
                class="hu-step <?php echo $step === 1 ? "is-current" : "is-done"; ?>"
                <?php echo $step === 1 ? 'aria-current="step"' : ""; ?>
            >
                <span class="hu-step__dot">
                    <?php echo $step === 1 ? "1" : hu_icon("check"); ?>
                </span>
                <span class="hu-step__label">Registration</span>
            </li>

            <li
                class="hu-step <?php echo $step === 2 ? "is-current" : ""; ?>"
                <?php echo $step === 2 ? 'aria-current="step"' : ""; ?>
            >
                <span class="hu-step__dot">2</span>
                <span class="hu-step__label">HOMA-IR Calculator</span>
            </li>

        </ol>

        <div class="hu-note" aria-hidden="true">
            <span class="hu-note__l1">Know</span>
            <span class="hu-note__l2">Understand</span>
            <span class="hu-note__l3">Take Control</span>
            <svg class="hu-note__arrow" viewBox="0 0 80 46" width="80" height="46" focusable="false">
                <path d="M74 4C66 26 44 40 8 38" fill="none" stroke="#8d9db5" stroke-width="1.4" stroke-linecap="round"/>
                <path d="M17 31 7 38l11 6" fill="none" stroke="#8d9db5" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>

    </header>
    <?php
}


/**
 * Small glucometer + blood-drop illustration for the calculator card.
 * Static markup, safe to print without escaping.
 */
function hu_illustration(): string
{
    return '<svg class="hu-illustration" viewBox="0 0 128 118" width="128" height="118" aria-hidden="true" focusable="false">'
        . '<defs>'
        . '<linearGradient id="hu-meter" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#5b96d6"/><stop offset="1" stop-color="#2f6db5"/></linearGradient>'
        . '<linearGradient id="hu-drop" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f57a6a"/><stop offset="1" stop-color="#d63a2c"/></linearGradient>'
        . '</defs>'
        // soft background blob + bubbles
        . '<path d="M42 6C30 28 6 46 6 76c0 24 17 38 36 38s36-14 36-38C78 46 54 28 42 6Z" fill="#dcebfb" opacity=".85"/>'
        . '<circle cx="66" cy="10" r="3.6" fill="#cfe2f8"/>'
        . '<circle cx="118" cy="98" r="4" fill="#cfe2f8"/>'
        . '<circle cx="14" cy="52" r="2.6" fill="#cfe2f8"/>'
        // blood drop
        . '<path d="M40 44c-9 13-19 22-19 34a19 19 0 0 0 38 0c0-12-10-21-19-34Z" fill="url(#hu-drop)"/>'
        . '<path d="M30 74c.4 6.2 4.4 10.3 10.2 11" fill="none" stroke="#fff" stroke-opacity=".55" stroke-width="3" stroke-linecap="round"/>'
        // glucometer body
        . '<rect x="68" y="14" width="46" height="78" rx="12" fill="url(#hu-meter)"/>'
        . '<rect x="74" y="21" width="34" height="27" rx="6" fill="#d7e9fa"/>'
        . '<rect x="79" y="28" width="24" height="4" rx="2" fill="#8db5e0"/>'
        . '<rect x="79" y="36" width="16" height="4" rx="2" fill="#8db5e0"/>'
        . '<circle cx="82" cy="62" r="5" fill="#8fbaea"/>'
        . '<circle cx="100" cy="62" r="5" fill="#8fbaea"/>'
        . '<rect x="86" y="72" width="10" height="6" rx="3" fill="#8fbaea"/>'
        // test strip
        . '<rect x="86" y="90" width="10" height="24" rx="3" fill="#4d7fae"/>'
        . '<rect x="88.5" y="96" width="5" height="9" rx="2" fill="#a9c7e6"/>'
        . '</svg>';
}
