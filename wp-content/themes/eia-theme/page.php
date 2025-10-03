<?php
/**
 * Template for displaying all pages
 *
 * @package EIA_Theme
 */

// Show admin bar for logged in users
show_admin_bar(true);

get_header();
?>

<style>
    /* Ensure admin bar is visible */
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

    /* Container for page content */
    .eia-page-container {
        min-height: 100vh;
        background: #f9fafb;
        padding: 2rem 1rem;
    }

    .eia-page-content {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .eia-page-title {
        font-size: 2rem;
        font-weight: 700;
        color: #2D4FB3;
        margin-bottom: 1.5rem;
        border-bottom: 3px solid #2D4FB3;
        padding-bottom: 0.75rem;
    }

    .eia-page-content .entry-content {
        line-height: 1.8;
        color: #1f2937;
    }
</style>

<div class="eia-page-container">
    <?php
    while (have_posts()) :
        the_post();
    ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('eia-page-content'); ?>>

            <?php if (!is_front_page()) : ?>
                <h1 class="eia-page-title"><?php the_title(); ?></h1>
            <?php endif; ?>

            <div class="entry-content">
                <?php
                the_content();

                wp_link_pages(
                    array(
                        'before' => '<div class="page-links">' . esc_html__('Pages:', 'eia-theme'),
                        'after'  => '</div>',
                    )
                );
                ?>
            </div>

        </article>
    <?php
    endwhile;
    ?>
</div>

<?php
get_footer();
