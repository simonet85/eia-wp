<?php
/**
 * Full page template for courses (no header/footer)
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
        /* Force hide all WordPress wrapper elements */
        body.eia-course-fullwidth-page .site-header,
        body.eia-course-fullwidth-page header.site-header,
        body.eia-course-fullwidth-page .site-footer,
        body.eia-course-fullwidth-page footer.site-footer,
        body.eia-course-fullwidth-page nav.navbar,
        body.eia-course-fullwidth-page .breadcrumb,
        body.eia-course-fullwidth-page #wpadminbar {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            opacity: 0 !important;
        }

        body.eia-course-fullwidth-page {
            margin: 0 !important;
            padding: 0 !important;
        }
    </style>

    <?php wp_head(); ?>
</head>
<body <?php body_class('eia-course-fullwidth-page'); ?>>

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
