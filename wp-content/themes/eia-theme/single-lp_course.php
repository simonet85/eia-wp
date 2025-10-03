<?php
/**
 * Template for single course (lp_course post type)
 * This template has NO header/footer
 *
 * @package EIA_Theme
 */

defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>

    <style>
        /* Hide EVERYTHING except course content and admin bar */
        body > *:not(.eia-course-container):not(script):not(style):not(#wpadminbar) {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
        }

        /* Force hide header/footer but KEEP admin bar */
        header:not(#wpadminbar *),
        footer,
        .site-header,
        .site-footer,
        nav:not(#wpadminbar *),
        .navbar,
        .navigation,
        .breadcrumb,
        .header,
        .footer,
        [class*="header"]:not(#wpadminbar):not(#wpadminbar *),
        [class*="footer"],
        [id*="header"]:not(#wpadminbar),
        [id*="footer"],
        [id*="nav"]:not(#wpadminbar):not(#wpadminbar *) {
            display: none !important;
            visibility: hidden !important;
            position: absolute !important;
            left: -9999px !important;
            width: 0 !important;
            height: 0 !important;
            opacity: 0 !important;
        }

        /* Show admin bar for logged in users */
        #wpadminbar {
            display: block !important;
            visibility: visible !important;
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 32px !important;
            opacity: 1 !important;
            z-index: 99999 !important;
        }

        /* Adjust body margin when admin bar is present */
        body.admin-bar {
            margin-top: 32px !important;
            padding: 0 !important;
        }

        body.admin-bar .eia-course-container {
            margin-top: 0 !important;
        }

        /* Body reset */
        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-x: hidden !important;
        }

        /* Ensure course content is visible */
        .eia-course-container,
        .eia-course-container * {
            display: block !important;
            visibility: visible !important;
            position: relative !important;
        }
    </style>

    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<?php
// Load course content template
while (have_posts()) {
    the_post();
    learn_press_get_template('content-single-course');
}
?>

<?php wp_footer(); ?>
</body>
</html>
