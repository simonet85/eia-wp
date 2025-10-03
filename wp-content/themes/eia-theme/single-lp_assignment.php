<?php
/**
 * Template for single assignment page
 *
 * @package EIA_Theme
 */

// Show admin bar
show_admin_bar(true);

$assignment_id = get_the_ID();

// Don't use get_header() - output minimal HTML
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php the_title(); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<style>
    /* Hide everything except assignment content and admin bar */
    body > *:not(.eia-assignment-page):not(script):not(style):not(#wpadminbar) {
        display: none !important;
    }

    /* Show admin bar */
    #wpadminbar {
        display: block !important;
        visibility: visible !important;
        position: fixed !important;
        z-index: 99999 !important;
    }

    /* Adjust body margin when admin bar is present */
    body.admin-bar {
        margin-top: 32px !important;
    }

    @media screen and (max-width: 782px) {
        body.admin-bar {
            margin-top: 46px !important;
        }
    }

    /* Page container */
    .eia-assignment-page {
        min-height: 100vh;
        background: #f9fafb;
        display: block !important;
        visibility: visible !important;
    }

    /* Breadcrumb */
    .assignment-breadcrumb {
        background: white;
        padding: 1rem 2rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
    }

    .assignment-breadcrumb a {
        color: #2D4FB3;
        text-decoration: none;
    }

    .assignment-breadcrumb a:hover {
        text-decoration: underline;
    }

    .assignment-breadcrumb .separator {
        color: #9ca3af;
    }
</style>

<div class="eia-assignment-page">
    <!-- Breadcrumb -->
    <div class="assignment-breadcrumb">
        <a href="<?php echo home_url('/mes-cours/'); ?>">Tableau de bord</a>
        <span class="separator">›</span>
        <?php
        $course_id = get_post_meta($assignment_id, '_assignment_course_id', true);
        if ($course_id) :
        ?>
            <a href="<?php echo get_permalink($course_id); ?>">
                <?php echo get_the_title($course_id); ?>
            </a>
            <span class="separator">›</span>
        <?php endif; ?>
        <span><?php the_title(); ?></span>
    </div>

    <!-- Assignment Content -->
    <div style="padding: 2rem;">
        <?php
        // Display the submission form
        echo do_shortcode('[eia_assignment_submit id="' . $assignment_id . '"]');
        ?>
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
