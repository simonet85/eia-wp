<?php
/**
 * EIA Theme functions and definitions
 *
 * @package EIA_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme setup
 */
function eia_theme_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'eia-theme'),
        'mobile'  => __('Mobile Menu', 'eia-theme'),
        'footer'  => __('Footer Menu', 'eia-theme'),
    ));

    // Set default image sizes
    set_post_thumbnail_size(400, 300, true);
    add_image_size('eia-hero', 1200, 800, true);
    add_image_size('eia-card', 400, 250, true);
}
add_action('after_setup_theme', 'eia_theme_setup');

/**
 * Enqueue scripts and styles
 */
function eia_theme_scripts() {
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');

    // Main theme stylesheet
    wp_enqueue_style('eia-style', get_stylesheet_uri(), array(), '1.0.1');

    // Comment reply script
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}
add_action('wp_enqueue_scripts', 'eia_theme_scripts');

/**
 * Register widget areas
 */
function eia_theme_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'eia-theme'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here.', 'eia-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Area 1', 'eia-theme'),
        'id'            => 'footer-1',
        'description'   => __('Add widgets here.', 'eia-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Footer Area 2', 'eia-theme'),
        'id'            => 'footer-2',
        'description'   => __('Add widgets here.', 'eia-theme'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));
}
add_action('widgets_init', 'eia_theme_widgets_init');

/**
 * Custom excerpt length
 */
function eia_theme_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'eia_theme_excerpt_length');

/**
 * Custom excerpt more text
 */
function eia_theme_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'eia_theme_excerpt_more');

/**
 * Add custom body classes
 */
function eia_theme_body_classes($classes) {
    if (!is_singular()) {
        $classes[] = 'hfeed';
    }

    if (is_front_page()) {
        $classes[] = 'front-page';
    }

    return $classes;
}
add_filter('body_class', 'eia_theme_body_classes');

/**
 * Custom navigation walker for main menu
 */
class EIA_Walker_Nav_Menu extends Walker_Nav_Menu {

    function start_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\">\n";
    }

    function end_lvl(&$output, $depth = 0, $args = null) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $indent = ($depth) ? str_repeat("\t", $depth) : '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
        $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

        $id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args);
        $id = $id ? ' id="' . esc_attr($id) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names .'>';

        $attributes = ! empty($item->attr_title) ? ' title="'  . esc_attr($item->attr_title) .'"' : '';
        $attributes .= ! empty($item->target)     ? ' target="' . esc_attr($item->target     ) .'"' : '';
        $attributes .= ! empty($item->xfn)        ? ' rel="'    . esc_attr($item->xfn        ) .'"' : '';
        $attributes .= ! empty($item->url)        ? ' href="'   . esc_attr($item->url        ) .'"' : '';

        $item_output = isset($args->before) ? $args->before : '';
        $item_output .= '<a' . $attributes . ' style="color: white; padding: 0.75rem 1rem; display: block; text-decoration: none; transition: background-color 0.3s;">';
        $item_output .= (isset($args->link_before) ? $args->link_before : '') . apply_filters('the_title', $item->title, $item->ID) . (isset($args->link_after) ? $args->link_after : '');
        $item_output .= '</a>';
        $item_output .= isset($args->after) ? $args->after : '';

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }

    function end_el(&$output, $item, $depth = 0, $args = null) {
        $output .= "</li>\n";
    }
}

/**
 * Get template directory URI for use in templates
 */
function eia_get_asset_url($path) {
    return get_template_directory_uri() . '/' . ltrim($path, '/');
}

/**
 * Add Tailwind config and custom CSS
 */
function eia_theme_tailwind_config() {
    echo '<script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        "eia-blue": "#2D4FB3",
                        "eia-orange": "#F59E0B",
                    },
                },
            },
        };
    </script>';
}
add_action('wp_head', 'eia_theme_tailwind_config', 5);

/**
 * Customize the login page
 */
function eia_theme_login_logo() {
    echo '<style type="text/css">
        .login h1 a {
            background-image: none;
            background-color: var(--eia-blue);
            color: white;
            text-decoration: none;
            width: 200px;
            height: 60px;
            text-align: center;
            line-height: 60px;
            font-weight: bold;
            border-radius: 0 30px 30px 0;
        }
        .login h1 a:before {
            content: "E.I.A";
            font-size: 24px;
        }
        .login form {
            border: 1px solid var(--eia-blue);
        }
        .wp-core-ui .button-primary {
            background: var(--eia-orange);
            border-color: var(--eia-orange);
        }
        .wp-core-ui .button-primary:hover {
            background: #d97706;
            border-color: #d97706;
        }
    </style>';
}
add_action('login_head', 'eia_theme_login_logo');

/**
 * Change login logo URL
 */
function eia_theme_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'eia_theme_login_logo_url');

/**
 * Change login logo title
 */
function eia_theme_login_logo_url_title() {
    return get_bloginfo('name');
}
add_filter('login_headertext', 'eia_theme_login_logo_url_title');

/**
 * Add support for custom colors in the editor
 */
function eia_theme_editor_color_palette() {
    add_theme_support('editor-color-palette', array(
        array(
            'name'  => __('EIA Blue', 'eia-theme'),
            'slug'  => 'eia-blue',
            'color' => '#2D4FB3',
        ),
        array(
            'name'  => __('EIA Orange', 'eia-theme'),
            'slug'  => 'eia-orange',
            'color' => '#F59E0B',
        ),
        array(
            'name'  => __('Dark Gray', 'eia-theme'),
            'slug'  => 'dark-gray',
            'color' => '#111827',
        ),
        array(
            'name'  => __('Light Gray', 'eia-theme'),
            'slug'  => 'light-gray',
            'color' => '#f9fafb',
        ),
    ));
}
add_action('after_setup_theme', 'eia_theme_editor_color_palette');

/**
 * Remove unnecessary WordPress features
 */
function eia_theme_clean_wp() {
    // Remove unnecessary meta tags
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');

    // Remove emoji scripts
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
}
add_action('init', 'eia_theme_clean_wp');

/**
 * Security enhancements
 */
function eia_theme_security() {
    // Hide WordPress version
    function remove_wp_version() {
        return '';
    }
    add_filter('the_generator', 'remove_wp_version');

    // Remove version from scripts and styles
    function remove_version_css_js($src) {
        if (strpos($src, 'ver=')) {
            $src = remove_query_arg('ver', $src);
        }
        return $src;
    }
    add_filter('style_loader_src', 'remove_version_css_js', 9999);
    add_filter('script_loader_src', 'remove_version_css_js', 9999);
}
add_action('init', 'eia_theme_security');

/**
 * ============================================================================
 * LMS EXTENSIONS - Phase 1: Préparation
 * ============================================================================
 */

/**
 * Add LMS support to theme
 */
function eia_lms_support() {
    // Support pour LearnPress
    add_theme_support('learnpress');

    // Support pour BuddyPress
    add_theme_support('buddypress');

    // Support pour bbPress (forums)
    add_theme_support('bbpress');

    // Support pour WooCommerce (e-commerce)
    add_theme_support('woocommerce');

    // Nouveaux menus pour LMS
    register_nav_menus(array(
        'student-menu' => __('Menu Étudiant', 'eia-theme'),
        'instructor-menu' => __('Menu Formateur', 'eia-theme'),
        'lms-dashboard-menu' => __('Menu Dashboard LMS', 'eia-theme'),
    ));

    // Nouvelles tailles d'images pour LMS
    add_image_size('course-thumbnail', 400, 300, true);
    add_image_size('course-hero', 1200, 400, true);
    add_image_size('instructor-avatar', 150, 150, true);
}
add_action('after_setup_theme', 'eia_lms_support');

/**
 * Include LMS files
 */
require_once get_template_directory() . '/inc/user-roles.php';
require_once get_template_directory() . '/inc/lms-functions.php';
require_once get_template_directory() . '/inc/ajax-handlers.php';

/**
 * Enqueue LMS scripts and styles
 */
function eia_lms_scripts() {
    // LMS CSS
    wp_enqueue_style('eia-lms-style', get_template_directory_uri() . '/assets/css/lms-styles.css', array(), '1.0.0');

    // LMS JavaScript
    wp_enqueue_script('eia-lms-script', get_template_directory_uri() . '/assets/js/lms-scripts.js', array('jquery'), '1.0.0', true);

    // Localize script for AJAX
    wp_localize_script('eia-lms-script', 'eiaLMS', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eia-lms-nonce'),
        'strings' => array(
            'loading' => __('Chargement...', 'eia-theme'),
            'error' => __('Une erreur est survenue', 'eia-theme'),
            'success' => __('Opération réussie', 'eia-theme'),
        )
    ));
}
add_action('wp_enqueue_scripts', 'eia_lms_scripts');

/**
 * Add body classes for LMS pages
 */
function eia_lms_body_classes($classes) {
    if (is_user_logged_in()) {
        $user = wp_get_current_user();

        if (in_array('student', $user->roles)) {
            $classes[] = 'user-student';
        }

        if (in_array('instructor', $user->roles)) {
            $classes[] = 'user-instructor';
        }

        if (in_array('lms_manager', $user->roles)) {
            $classes[] = 'user-lms-manager';
        }
    }

    // Add class for LMS pages
    if (function_exists('learn_press_is_course')) {
        if (learn_press_is_course()) {
            $classes[] = 'lms-course-page';
        }
    }

    return $classes;
}
add_filter('body_class', 'eia_lms_body_classes');

/**
 * Customize login page for LMS
 */
function eia_lms_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('student', $user->roles)) {
            // Redirect students to their dashboard
            return home_url('/student-dashboard/');
        } elseif (in_array('instructor', $user->roles)) {
            // Redirect instructors to their dashboard
            return home_url('/instructor-dashboard/');
        } elseif (in_array('lms_manager', $user->roles)) {
            // Redirect LMS managers to admin
            return admin_url();
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'eia_lms_login_redirect', 10, 3);
?>