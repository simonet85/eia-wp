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

    // Course single page styles
    if (is_singular('lp_course')) {
        wp_enqueue_style('eia-course-single', get_template_directory_uri() . '/assets/css/course-single.css', array('eia-style'), '1.0.0');
    }

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
 * Only load on non-course pages
 */
function eia_theme_tailwind_config() {
    // Don't load on course pages (they don't use Tailwind)
    if (is_singular('lp_course')) {
        return;
    }

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
 * Enable LearnPress template override
 */
add_filter('learn-press/override-templates', '__return_true');

/**
 * Disable header/footer on course pages
 */
add_action('template_redirect', function() {
    if (is_singular('lp_course')) {
        remove_action('learn-press/template-header', 'learn_press_get_header', 10);
        remove_action('learn-press/template-footer', 'learn_press_get_footer', 10);

        // Remove all WordPress headers/footers
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'wlwmanifest_link');

        // Override the page template
        add_filter('template_include', function($template) {
            if (is_singular('lp_course')) {
                $custom_template = locate_template('learnpress/page-course-fullwidth.php');
                if ($custom_template) {
                    return $custom_template;
                }
            }
            return $template;
        }, 99);
    }
});

/**
 * Dequeue EIA LMS Core frontend scripts on course pages
 */
add_action('wp_print_scripts', function() {
    if (is_singular('lp_course')) {
        wp_dequeue_script('eia-lms-core-frontend');
        wp_deregister_script('eia-lms-core-frontend');
    }
}, 100);

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
    // Don't load LMS scripts on course single pages (they have their own minimal JS)
    if (is_singular('lp_course')) {
        return;
    }

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
            $classes[] = 'eia-course-fullwidth';
        }
    }

    return $classes;
}
add_filter('body_class', 'eia_lms_body_classes');

/**
 * Add custom styles for course single pages
 */
function eia_course_single_styles() {
    if (is_singular('lp_course')) {
        echo '<style>
            /* Hide header/footer containers for course pages */
            body.eia-course-fullwidth .site-header,
            body.eia-course-fullwidth .site-footer,
            body.eia-course-fullwidth .breadcrumb,
            body.eia-course-fullwidth #wpadminbar {
                display: none !important;
            }

            body.eia-course-fullwidth {
                margin: 0 !important;
                padding: 0 !important;
            }

            body.eia-course-fullwidth .site-content {
                margin: 0 !important;
                padding: 0 !important;
                max-width: none !important;
            }

            body.eia-course-fullwidth #content {
                margin: 0 !important;
                padding: 0 !important;
            }
        </style>';
    }
}
add_action('wp_head', 'eia_course_single_styles');

/**
 * Customize login page for LMS
 */
function eia_lms_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('student', $user->roles)) {
            // Redirect students to their LearnPress profile
            $profile_url = learn_press_user_profile_link($user->ID);
            return $profile_url . 'courses/';
        } elseif (in_array('instructor', $user->roles)) {
            // Redirect instructors to courses management
            return admin_url('edit.php?post_type=lp_course');
        } elseif (in_array('lms_manager', $user->roles)) {
            // Redirect LMS managers to admin
            return admin_url();
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'eia_lms_login_redirect', 10, 3);

/**
 * Customize admin bar for different user roles
 */
function eia_customize_admin_bar() {
    global $wp_admin_bar;

    if (!is_user_logged_in()) {
        return;
    }

    $user = wp_get_current_user();

    // Students: Limited admin bar
    if (in_array('student', $user->roles)) {
        // Remove items students shouldn't see
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('new-content');
        $wp_admin_bar->remove_node('comments');
        $wp_admin_bar->remove_node('customize');
        $wp_admin_bar->remove_node('updates');

        // Get pages
        $courses_page_id = learn_press_get_page_id('courses');
        $my_courses_page = get_page_by_path('mes-cours');

        // Use custom "Mes Cours" page if it exists, otherwise fallback
        if ($my_courses_page) {
            $my_courses_url = get_permalink($my_courses_page->ID);
        } else {
            $my_courses_url = home_url('/mes-cours/');
        }

        // Add student-specific items
        $wp_admin_bar->add_node(array(
            'id'    => 'student-dashboard',
            'title' => '<span class="ab-icon dashicons dashicons-welcome-learn-more"></span> Mes Cours',
            'href'  => $my_courses_url,
            'meta'  => array('class' => 'student-menu-item')
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'student-all-courses',
            'title' => '<span class="ab-icon dashicons dashicons-book"></span> Tous les Cours',
            'href'  => $courses_page_id ? get_permalink($courses_page_id) : home_url('/courses/'),
        ));

        $wp_admin_bar->add_node(array(
            'id'     => 'student-profile',
            'parent' => 'user-actions',
            'title'  => 'Mon Profil',
            'href'   => $profile_url,
        ));
    }

    // Instructors: More permissions
    if (in_array('instructor', $user->roles)) {
        // Remove admin-only items
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('customize');
        $wp_admin_bar->remove_node('updates');

        // Add instructor-specific items
        $wp_admin_bar->add_node(array(
            'id'    => 'instructor-dashboard',
            'title' => '<span class="ab-icon dashicons dashicons-welcome-learn-more"></span> Tableau de Bord',
            'href'  => admin_url('index.php'),
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'instructor-courses',
            'title' => '<span class="ab-icon dashicons dashicons-book-alt"></span> Mes Cours',
            'href'  => admin_url('edit.php?post_type=lp_course'),
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'instructor-lessons',
            'title' => '<span class="ab-icon dashicons dashicons-welcome-write-blog"></span> Leçons',
            'href'  => admin_url('edit.php?post_type=lp_lesson'),
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'instructor-quizzes',
            'title' => '<span class="ab-icon dashicons dashicons-forms"></span> Quiz',
            'href'  => admin_url('edit.php?post_type=lp_quiz'),
        ));

        $wp_admin_bar->add_node(array(
            'id'    => 'instructor-students',
            'title' => '<span class="ab-icon dashicons dashicons-groups"></span> Étudiants',
            'href'  => admin_url('users.php?role=student'),
        ));
    }

    // Admins: Keep all permissions (no changes needed)
    // The default WordPress admin bar is fine for administrators
}
add_action('wp_before_admin_bar_render', 'eia_customize_admin_bar');

/**
 * Force show admin bar for logged in users on course pages
 */
function eia_show_admin_bar_on_courses($show) {
    if (is_singular('lp_course') && is_user_logged_in()) {
        return true;
    }
    return $show;
}
add_filter('show_admin_bar', 'eia_show_admin_bar_on_courses');

/**
 * Add custom styles to admin bar
 */
function eia_admin_bar_custom_styles() {
    if (!is_user_logged_in()) {
        return;
    }

    echo '<style>
        /* Custom admin bar styles for LMS */
        #wpadminbar {
            background: #2D4FB3 !important;
        }

        #wpadminbar .ab-item,
        #wpadminbar a.ab-item,
        #wpadminbar > #wp-toolbar span.ab-label,
        #wpadminbar > #wp-toolbar span.noticon {
            color: #ffffff !important;
        }

        #wpadminbar .ab-icon:before,
        #wpadminbar .ab-item:before {
            color: #ffffff !important;
        }

        #wpadminbar .ab-top-menu > li:hover > .ab-item,
        #wpadminbar .ab-top-menu > li.hover > .ab-item,
        #wpadminbar .ab-top-menu > li > .ab-item:focus {
            background: #1e3a8a !important;
            color: #ffffff !important;
        }

        #wpadminbar .ab-submenu {
            background: #1e3a8a !important;
        }

        #wpadminbar .ab-submenu .ab-item {
            color: #ffffff !important;
        }

        #wpadminbar .ab-submenu > li:hover > .ab-item {
            background: #2D4FB3 !important;
        }

        /* Student-specific styles */
        body.user-student #wpadminbar {
            background: #10B981 !important;
        }

        body.user-student #wpadminbar .ab-top-menu > li:hover > .ab-item {
            background: #059669 !important;
        }

        /* Instructor-specific styles */
        body.user-instructor #wpadminbar {
            background: #F59E0B !important;
        }

        body.user-instructor #wpadminbar .ab-top-menu > li:hover > .ab-item {
            background: #D97706 !important;
        }
    </style>';
}
add_action('wp_head', 'eia_admin_bar_custom_styles');
add_action('admin_head', 'eia_admin_bar_custom_styles');

/**
 * Handle course enrollment from single course page
 */
function eia_handle_course_enrollment() {
    // Check if enrollment form was submitted
    if (!isset($_POST['eia_enroll_action'])) {
        return;
    }

    // Verify nonce
    if (!isset($_POST['eia_enroll_nonce']) || !wp_verify_nonce($_POST['eia_enroll_nonce'], 'eia_enroll_course')) {
        wp_die('Sécurité: Nonce invalide');
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
        exit;
    }

    // Get course ID
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

    if (!$course_id) {
        return;
    }

    // Get current user
    $user_id = get_current_user_id();

    // Check if already enrolled
    global $wpdb;
    $already_enrolled = $wpdb->get_var($wpdb->prepare(
        "SELECT user_item_id FROM {$wpdb->prefix}learnpress_user_items
        WHERE user_id = %d AND item_id = %d AND item_type = 'lp_course'",
        $user_id,
        $course_id
    ));

    if ($already_enrolled) {
        // Already enrolled, redirect back to course
        wp_redirect(get_permalink($course_id) . '?enrolled=already');
        exit;
    }

    // Enroll the user
    $result = $wpdb->insert(
        $wpdb->prefix . 'learnpress_user_items',
        array(
            'user_id' => $user_id,
            'item_id' => $course_id,
            'item_type' => 'lp_course',
            'status' => 'enrolled',
            'start_time' => current_time('mysql'),
            'end_time' => null,
            'graduation' => 'in-progress',
            'ref_id' => 0,
            'parent_id' => 0,
            'ref_type' => ''
        ),
        array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
    );

    if ($result) {
        // Success! Redirect with success message
        wp_redirect(get_permalink($course_id) . '?enrolled=success');
    } else {
        // Error
        wp_redirect(get_permalink($course_id) . '?enrolled=error');
    }
    exit;
}
add_action('template_redirect', 'eia_handle_course_enrollment');

/**
 * Show enrollment notification messages
 */
function eia_enrollment_messages() {
    if (!is_singular('lp_course')) {
        return;
    }

    if (isset($_GET['enrolled'])) {
        $status = $_GET['enrolled'];

        echo '<div style="position: fixed; top: 50px; right: 20px; z-index: 99999; max-width: 400px;">';

        if ($status === 'success') {
            echo '<div style="background: #10B981; color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 0.75rem;">
                <svg style="width: 24px; height: 24px; fill: white;" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <div style="font-weight: 600;">Inscription réussie!</div>
                    <div style="font-size: 0.875rem; opacity: 0.9;">Vous pouvez maintenant accéder au contenu du cours.</div>
                </div>
            </div>';
        } elseif ($status === 'already') {
            echo '<div style="background: #F59E0B; color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-weight: 600;">Vous êtes déjà inscrit à ce cours</div>
            </div>';
        } elseif ($status === 'error') {
            echo '<div style="background: #EF4444; color: white; padding: 1rem 1.5rem; border-radius: 0.5rem; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-weight: 600;">Erreur lors de l\'inscription</div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Veuillez réessayer ou contacter l\'administrateur.</div>
            </div>';
        }

        echo '</div>';

        // Auto-hide notification after 5 seconds
        echo '<script>
            setTimeout(function() {
                var notification = document.querySelector(\'div[style*="position: fixed"]\');
                if (notification) {
                    notification.style.transition = "opacity 0.5s";
                    notification.style.opacity = "0";
                    setTimeout(function() { notification.remove(); }, 500);
                }
            }, 5000);
        </script>';
    }
}
add_action('wp_footer', 'eia_enrollment_messages');

/**
 * Shortcode to display user's enrolled courses
 */
function eia_my_courses_shortcode() {
    if (!is_user_logged_in()) {
        return '<div style="text-align: center; padding: 3rem;">
            <p>Vous devez être connecté pour voir vos cours.</p>
            <a href="' . wp_login_url(get_permalink()) . '" style="display: inline-block; padding: 0.75rem 2rem; background: #2D4FB3; color: white; text-decoration: none; border-radius: 0.5rem; margin-top: 1rem;">Se connecter</a>
        </div>';
    }

    $user_id = get_current_user_id();

    // Get user enrolled courses
    global $wpdb;
    $courses = $wpdb->get_results($wpdb->prepare(
        "SELECT ui.*, p.post_title, p.ID as course_id
        FROM {$wpdb->prefix}learnpress_user_items ui
        INNER JOIN {$wpdb->posts} p ON ui.item_id = p.ID
        WHERE ui.user_id = %d
        AND ui.item_type = 'lp_course'
        AND p.post_status = 'publish'
        ORDER BY ui.start_time DESC",
        $user_id
    ));

    // Get assignments for enrolled courses
    $enrolled_course_ids = array_map(function($c) { return $c->course_id; }, $courses);

    $assignments = array();
    if (!empty($enrolled_course_ids)) {
        $assignments = get_posts(array(
            'post_type' => 'lp_assignment',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => array(
                array(
                    'key' => '_assignment_course_id',
                    'value' => $enrolled_course_ids,
                    'compare' => 'IN',
                ),
            ),
        ));
    }

    ob_start();
    ?>
    <div class="eia-my-courses-page" style="padding: 2rem 0;">
        <!-- Dashboard Header -->
        <div style="margin-bottom: 3rem;">
            <h1 style="margin-bottom: 0.5rem; color: #1f2937; font-size: 2.5rem; font-weight: 700;">
                Tableau de bord
            </h1>
            <p style="color: #6b7280; font-size: 1.125rem;">
                Bienvenue, <?php echo esc_html(wp_get_current_user()->display_name); ?> !
            </p>
        </div>

        <!-- Quick Stats -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 3rem;">
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 1.5rem; border-radius: 12px; color: white;">
                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo count($courses); ?>
                </div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Cours inscrits</div>
            </div>
            <div style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); padding: 1.5rem; border-radius: 12px; color: white;">
                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php echo count($assignments); ?>
                </div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Devoirs disponibles</div>
            </div>
            <div style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); padding: 1.5rem; border-radius: 12px; color: white;">
                <div style="font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem;">
                    <?php
                    $completed = 0;
                    foreach ($courses as $c) {
                        if ($c->graduation === 'finished' || $c->graduation === 'passed') {
                            $completed++;
                        }
                    }
                    echo $completed;
                    ?>
                </div>
                <div style="font-size: 0.875rem; opacity: 0.9;">Cours terminés</div>
            </div>
        </div>

        <!-- Badges & XP Section -->
        <?php
        $gamification = EIA_Gamification::get_instance();
        $user_points = $gamification->get_user_points($user_id);
        $user_badges = $gamification->get_user_badges($user_id);
        $user_rank = $gamification->get_user_rank($user_id);

        if (!$user_points) {
            $user_points = (object) array('points' => 0, 'level' => 1, 'total_xp' => 0);
        }

        $next_level_xp = $gamification->get_xp_for_next_level($user_points->level);
        $progress_to_next = 0;
        if ($next_level_xp) {
            $current_level_xp = $user_points->level > 1 ? $gamification->get_xp_for_next_level($user_points->level - 1) : 0;
            $progress_to_next = (($user_points->total_xp - $current_level_xp) / ($next_level_xp - $current_level_xp)) * 100;
        }
        ?>
        <div style="background: white; border-radius: 12px; padding: 2rem; margin-bottom: 3rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
                <!-- XP & Level -->
                <div>
                    <div style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 2rem; border-radius: 12px; color: white;">
                        <div style="font-size: 3rem; font-weight: 700; margin-bottom: 0.5rem;">
                            Niveau <?php echo $user_points->level; ?>
                        </div>
                        <div style="opacity: 0.9; margin-bottom: 1rem;">
                            <i class="fas fa-star" style="margin-right: 0.5rem;"></i><?php echo number_format($user_points->total_xp); ?> XP
                        </div>
                        <div style="background: rgba(255,255,255,0.2); border-radius: 9999px; height: 8px; overflow: hidden; margin-bottom: 0.5rem;">
                            <div style="background: white; height: 100%; width: <?php echo min($progress_to_next, 100); ?>%; transition: width 0.3s;"></div>
                        </div>
                        <div style="font-size: 0.875rem; opacity: 0.8;">
                            <?php if ($next_level_xp) : ?>
                                <?php echo number_format($next_level_xp - $user_points->total_xp); ?> XP pour niveau <?php echo $user_points->level + 1; ?>
                            <?php else : ?>
                                Niveau maximum atteint !
                            <?php endif; ?>
                        </div>
                        <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.2);">
                            <div style="font-size: 0.875rem; opacity: 0.8; margin-bottom: 0.25rem;">Classement</div>
                            <div style="font-size: 1.5rem; font-weight: 600; margin-bottom: 1rem;">
                                <i class="fas fa-trophy" style="margin-right: 0.5rem; color: #F59E0B;"></i>#<?php echo $user_rank; ?>
                            </div>
                            <a href="<?php echo site_url('/classement/'); ?>" style="
                                display: inline-block;
                                width: 100%;
                                padding: 0.75rem;
                                background: rgba(255,255,255,0.2);
                                color: white;
                                text-decoration: none;
                                border-radius: 8px;
                                text-align: center;
                                font-weight: 600;
                                font-size: 0.875rem;
                                transition: all 0.2s;
                            " onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                                <i class="fas fa-medal"></i> Voir le classement
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Badges -->
                <div>
                    <h2 style="margin: 0 0 1.5rem 0; color: #2D4FB3; font-size: 1.5rem; font-weight: 700;">
                        <i class="fas fa-medal" style="margin-right: 0.5rem; color: #F59E0B;"></i>Mes Badges
                    </h2>
                    <?php if (!empty($user_badges)) : ?>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 1rem;">
                            <?php foreach (array_slice($user_badges, 0, 6) as $badge) :
                                $metadata = json_decode($badge->metadata, true);
                            ?>
                                <div style="background: #f9fafb; border-radius: 8px; padding: 1rem; text-align: center; border: 2px solid <?php echo $metadata['color']; ?>20;">
                                    <div style="font-size: 2.5rem; color: <?php echo $metadata['color']; ?>; margin-bottom: 0.5rem;">
                                        <i class="<?php echo $metadata['icon']; ?>"></i>
                                    </div>
                                    <div style="font-weight: 600; color: #1f2937; font-size: 0.875rem; margin-bottom: 0.25rem;">
                                        <?php echo esc_html($badge->badge_name); ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #6b7280;">
                                        <?php echo date('d/m/Y', strtotime($badge->earned_date)); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($user_badges) > 6) : ?>
                            <div style="text-align: center; margin-top: 1rem;">
                                <a href="#" style="color: #2D4FB3; text-decoration: none; font-weight: 600; font-size: 0.875rem;">
                                    Voir tous les badges (<?php echo count($user_badges); ?>) <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    <?php else : ?>
                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                            <div style="font-size: 3rem; opacity: 0.3; margin-bottom: 1rem;">
                                <i class="fas fa-medal"></i>
                            </div>
                            <p style="margin: 0;">Aucun badge gagné pour le moment</p>
                            <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">Complétez des cours et des devoirs pour gagner des badges !</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Devoirs à faire -->
        <?php if (!empty($assignments)) :
            // Séparer les devoirs actifs et complétés
            $active_assignments = array();
            $completed_assignments = array();

            foreach ($assignments as $assignment) {
                $assignment_id = $assignment->ID;
                $submission = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}eia_assignment_submissions
                     WHERE assignment_id = %d AND user_id = %d
                     ORDER BY submitted_date DESC LIMIT 1",
                    $assignment_id,
                    $user_id
                ));

                $is_graded = !empty($submission) && $submission->status === 'graded';

                if ($is_graded) {
                    $completed_assignments[] = $assignment;
                } else {
                    $active_assignments[] = $assignment;
                }
            }
        ?>
            <div style="margin-bottom: 3rem;">
                <h2 style="margin-bottom: 1.5rem; color: #1f2937; font-size: 1.75rem; font-weight: 700; border-bottom: 3px solid #2D4FB3; padding-bottom: 0.75rem;">
                    <i class="fas fa-clipboard-list" style="margin-right: 0.5rem; color: #2D4FB3;"></i>Mes Devoirs
                </h2>

                <!-- Onglets -->
                <div style="margin-bottom: 1.5rem; border-bottom: 2px solid #e5e7eb;">
                    <div style="display: flex; gap: 0;">
                        <button onclick="switchAssignmentTab('active')" id="tab-active" class="assignment-tab active" style="padding: 1rem 2rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #6b7280; border-bottom: 3px solid transparent; transition: all 0.2s;">
                            <i class="fas fa-fire" style="margin-right: 0.5rem;"></i>Actifs (<?php echo count($active_assignments); ?>)
                        </button>
                        <button onclick="switchAssignmentTab('completed')" id="tab-completed" class="assignment-tab" style="padding: 1rem 2rem; border: none; background: none; cursor: pointer; font-weight: 600; color: #6b7280; border-bottom: 3px solid transparent; transition: all 0.2s;">
                            <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>Complétés (<?php echo count($completed_assignments); ?>)
                        </button>
                    </div>
                </div>

                <!-- Devoirs Actifs -->
                <div id="content-active" class="assignment-content" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                    <?php
                    if (empty($active_assignments)) {
                        echo '<div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: white; border-radius: 12px; color: #6b7280;">';
                        echo '<div style="font-size: 3rem; margin-bottom: 1rem;"><i class="fas fa-trophy" style="color: #F59E0B;"></i></div>';
                        echo '<h3 style="color: #1f2937; margin-bottom: 0.5rem;">Aucun devoir actif</h3>';
                        echo '<p>Tous vos devoirs sont complétés!</p>';
                        echo '</div>';
                    }

                    $displayed_assignments = 0;
                    foreach ($active_assignments as $assignment) :
                        if ($displayed_assignments >= 6) break;

                        $assignment_id = $assignment->ID;
                        $course_id = get_post_meta($assignment_id, '_assignment_course_id', true);
                        $due_date = get_post_meta($assignment_id, '_assignment_due_date', true);
                        $max_grade = get_post_meta($assignment_id, '_assignment_max_grade', true);

                        // Get submission
                        $submission = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}eia_assignment_submissions
                             WHERE assignment_id = %d AND user_id = %d
                             ORDER BY submitted_date DESC LIMIT 1",
                            $assignment_id,
                            $user_id
                        ));

                        $is_overdue = $due_date && strtotime($due_date) < current_time('timestamp');
                        $is_submitted = !empty($submission);
                        $is_graded = $is_submitted && $submission->status === 'graded';

                        $displayed_assignments++;
                    ?>
                        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid <?php echo $is_graded ? '#10B981' : ($is_submitted ? '#3B82F6' : ($is_overdue ? '#EF4444' : '#F59E0B')); ?>;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <h3 style="margin: 0; font-size: 1.125rem; color: #1f2937; flex: 1;">
                                    <?php echo esc_html($assignment->post_title); ?>
                                </h3>
                                <?php if ($is_graded) : ?>
                                    <div style="background: #10B981; color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; text-align: center;">
                                        <div style="font-size: 1.25rem;"><?php echo $submission->grade; ?></div>
                                        <div style="font-size: 0.75rem; opacity: 0.9;">/<?php echo $max_grade; ?></div>
                                    </div>
                                <?php elseif ($is_submitted) : ?>
                                    <span style="background: #3B82F6; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">Soumis</span>
                                <?php elseif ($is_overdue) : ?>
                                    <span style="background: #EF4444; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">Dépassé</span>
                                <?php else : ?>
                                    <span style="background: #F59E0B; color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.875rem; font-weight: 600;">À faire</span>
                                <?php endif; ?>
                            </div>

                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.75rem;">
                                <i class="fas fa-graduation-cap" style="margin-right: 0.5rem;"></i><?php echo get_the_title($course_id); ?>
                            </div>

                            <?php if ($due_date) : ?>
                                <div style="color: #4b5563; font-size: 0.875rem; margin-bottom: 1rem;">
                                    <i class="far fa-calendar-alt" style="margin-right: 0.5rem;"></i>Échéance: <?php echo date('d/m/Y à H:i', strtotime($due_date)); ?>
                                </div>
                            <?php endif; ?>

                            <a href="<?php echo get_permalink($assignment_id); ?>" style="display: block; text-align: center; padding: 0.75rem; background: #2D4FB3; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='#1e3a8a'" onmouseout="this.style.background='#2D4FB3'">
                                <?php echo $is_submitted ? 'Voir les détails' : 'Commencer'; ?>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Devoirs Complétés -->
                <div id="content-completed" class="assignment-content" style="display: none; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                    <?php
                    if (empty($completed_assignments)) {
                        echo '<div style="grid-column: 1/-1; text-align: center; padding: 3rem; background: white; border-radius: 12px; color: #6b7280;">';
                        echo '<div style="font-size: 3rem; margin-bottom: 1rem;"><i class="fas fa-book-open" style="color: #6b7280;"></i></div>';
                        echo '<h3 style="color: #1f2937; margin-bottom: 0.5rem;">Aucun devoir complété</h3>';
                        echo '<p>Vos devoirs notés apparaîtront ici</p>';
                        echo '</div>';
                    }

                    foreach ($completed_assignments as $assignment) :
                        $assignment_id = $assignment->ID;
                        $course_id = get_post_meta($assignment_id, '_assignment_course_id', true);
                        $max_grade = get_post_meta($assignment_id, '_assignment_max_grade', true);

                        // Get submission
                        $submission = $wpdb->get_row($wpdb->prepare(
                            "SELECT * FROM {$wpdb->prefix}eia_assignment_submissions
                             WHERE assignment_id = %d AND user_id = %d
                             ORDER BY submitted_date DESC LIMIT 1",
                            $assignment_id,
                            $user_id
                        ));

                        $percentage = ($submission->grade / $max_grade) * 100;
                        $grade_color = $percentage >= 70 ? '#10B981' : ($percentage >= 50 ? '#F59E0B' : '#EF4444');
                    ?>
                        <div style="border: 1px solid #e5e7eb; border-radius: 12px; padding: 1.5rem; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); border-left: 4px solid <?php echo $grade_color; ?>; opacity: 0.95;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                                <h3 style="margin: 0; font-size: 1.125rem; color: #1f2937; flex: 1;">
                                    <?php echo esc_html($assignment->post_title); ?>
                                </h3>
                                <div style="background: <?php echo $grade_color; ?>; color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; text-align: center;">
                                    <div style="font-size: 1.25rem;"><?php echo number_format($submission->grade, 1); ?></div>
                                    <div style="font-size: 0.75rem; opacity: 0.9;">/<?php echo $max_grade; ?></div>
                                </div>
                            </div>

                            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.75rem;">
                                <i class="fas fa-graduation-cap" style="margin-right: 0.5rem;"></i><?php echo get_the_title($course_id); ?>
                            </div>

                            <div style="color: #10B981; font-size: 0.875rem; margin-bottom: 1rem; font-weight: 600;">
                                <i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>Noté le <?php echo date('d/m/Y', strtotime($submission->graded_date)); ?>
                            </div>

                            <a href="<?php echo get_permalink($assignment_id); ?>" style="display: block; text-align: center; padding: 0.75rem; background: white; color: #2D4FB3; border: 2px solid #2D4FB3; text-decoration: none; border-radius: 8px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#2D4FB3'; this.style.color='white'" onmouseout="this.style.background='white'; this.style.color='#2D4FB3'">
                                <i class="far fa-file-alt" style="margin-right: 0.5rem;"></i>Revoir le devoir
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <script>
                function switchAssignmentTab(tab) {
                    // Update tabs
                    document.querySelectorAll('.assignment-tab').forEach(t => {
                        t.classList.remove('active');
                        t.style.color = '#6b7280';
                        t.style.borderBottomColor = 'transparent';
                    });

                    document.getElementById('tab-' + tab).classList.add('active');
                    document.getElementById('tab-' + tab).style.color = '#2D4FB3';
                    document.getElementById('tab-' + tab).style.borderBottomColor = '#2D4FB3';

                    // Update content
                    document.querySelectorAll('.assignment-content').forEach(c => {
                        c.style.display = 'none';
                    });

                    document.getElementById('content-' + tab).style.display = 'grid';
                }

                // Set active tab style on load
                document.getElementById('tab-active').style.color = '#2D4FB3';
                document.getElementById('tab-active').style.borderBottomColor = '#2D4FB3';
                </script>
            </div>
        <?php endif; ?>

        <!-- Mes Certificats -->
        <?php
        if (class_exists('EIA_Certificates')) {
            $certificates_manager = EIA_Certificates::get_instance();
            $certificates = $certificates_manager->get_user_certificates($user_id);
        ?>
            <div style="margin-bottom: 3rem;">
                <h2 style="margin-bottom: 1.5rem; color: #1f2937; font-size: 1.75rem; font-weight: 700; border-bottom: 3px solid #10B981; padding-bottom: 0.75rem;">
                    <i class="fas fa-certificate" style="margin-right: 0.5rem; color: #10B981;"></i>Mes Certificats
                </h2>

                <?php if (!empty($certificates)) : ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
                        <?php foreach ($certificates as $cert) : ?>
                            <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border-left: 4px solid #10B981; position: relative; overflow: hidden;">
                                <!-- Decorative corner -->
                                <div style="position: absolute; top: 0; right: 0; width: 60px; height: 60px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); opacity: 0.1; border-radius: 0 12px 0 100%;"></div>

                                <div style="position: relative;">
                                    <!-- Icon -->
                                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                        <i class="fas fa-certificate" style="font-size: 1.75rem; color: white;"></i>
                                    </div>

                                    <!-- Course Title -->
                                    <h3 style="margin: 0 0 0.75rem 0; font-size: 1.125rem; color: #1f2937; font-weight: 600; line-height: 1.4;">
                                        <?php echo esc_html($cert->course_title); ?>
                                    </h3>

                                    <!-- Date -->
                                    <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.75rem;">
                                        <i class="far fa-calendar-check" style="margin-right: 0.5rem; color: #10B981;"></i>
                                        Complété le <?php echo date('d/m/Y', strtotime($cert->completion_date)); ?>
                                    </div>

                                    <!-- Grade -->
                                    <?php if ($cert->grade_percentage) : ?>
                                        <div style="margin-bottom: 1rem;">
                                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                                <span style="font-size: 0.875rem; color: #6b7280;">Note finale</span>
                                                <span style="font-size: 1.125rem; font-weight: 700; color: #10B981;">
                                                    <?php echo number_format($cert->grade_percentage, 1); ?>%
                                                </span>
                                            </div>
                                            <div style="background: #e5e7eb; border-radius: 9999px; height: 6px; overflow: hidden;">
                                                <div style="background: linear-gradient(90deg, #10B981 0%, #059669 100%); height: 100%; width: <?php echo $cert->grade_percentage; ?>%; transition: width 0.3s;"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Certificate Code -->
                                    <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.75rem; margin-bottom: 1rem;">
                                        <div style="font-size: 0.75rem; color: #16a34a; margin-bottom: 0.25rem; font-weight: 600;">Code de vérification</div>
                                        <div style="font-family: monospace; font-size: 0.875rem; color: #15803d; font-weight: 700;">
                                            <?php echo esc_html($cert->certificate_code); ?>
                                        </div>
                                    </div>

                                    <!-- Action Button -->
                                    <a href="#" onclick="window.open('<?php echo site_url('/verification-certificat/?code=' . $cert->certificate_code); ?>', '_blank', 'width=1200,height=900'); return false;" style="
                                        display: block;
                                        text-align: center;
                                        padding: 0.75rem;
                                        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                                        color: white;
                                        text-decoration: none;
                                        border-radius: 8px;
                                        font-weight: 600;
                                        transition: all 0.2s;
                                        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
                                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(16, 185, 129, 0.4)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 4px rgba(16, 185, 129, 0.3)'">
                                        <i class="fas fa-download" style="margin-right: 0.5rem;"></i>Télécharger le certificat
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <div style="font-size: 5rem; opacity: 0.3; margin-bottom: 1.5rem; color: #10B981;">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3 style="color: #1f2937; font-weight: 600; margin-bottom: 1rem; font-size: 1.5rem;">Aucun certificat pour le moment</h3>
                        <p style="color: #6b7280; margin-bottom: 2rem; font-size: 1.125rem;">Complétez vos cours pour obtenir des certificats officiels EIA</p>
                        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.5rem; max-width: 500px; margin: 0 auto;">
                            <p style="margin: 0; color: #15803d; font-size: 0.875rem; line-height: 1.6;">
                                <i class="fas fa-info-circle" style="margin-right: 0.5rem;"></i>
                                Un certificat est automatiquement généré lorsque vous terminez un cours avec succès
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php } ?>

        <!-- Mes Cours -->
        <h2 style="margin-bottom: 1.5rem; color: #1f2937; font-size: 1.75rem; font-weight: 700; border-bottom: 3px solid #2D4FB3; padding-bottom: 0.75rem;">
            <i class="fas fa-book-reader" style="margin-right: 0.5rem; color: #2D4FB3;"></i>Mes Cours
        </h2>

        <?php if (!empty($courses)) : ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2rem;">
                <?php foreach ($courses as $course_data) :
                    $course_id = $course_data->course_id;
                    $course = learn_press_get_course($course_id);
                    if (!$course) continue;

                    $thumbnail = get_the_post_thumbnail_url($course_id, 'medium');
                    $progress = 0;

                    if ($course_data->graduation === 'finished' || $course_data->graduation === 'passed') {
                        $progress = 100;
                    } elseif ($course_data->graduation === 'in-progress') {
                        $progress = rand(20, 80);
                    }

                    $status_color = $course_data->graduation === 'passed' ? '#10B981' : ($course_data->graduation === 'in-progress' ? '#F59E0B' : '#6B7280');
                    $status_text = $course_data->graduation === 'passed' ? 'Terminé' : ($course_data->graduation === 'in-progress' ? 'En cours' : 'Non commencé');
                ?>
                    <div style="border: 1px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-8px)'; this.style.boxShadow='0 12px 24px rgba(0,0,0,0.1)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)'">

                        <!-- Thumbnail -->
                        <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative;">
                            <?php if ($thumbnail) : ?>
                                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($course->get_title()); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else : ?>
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: white; font-size: 4rem;"><i class="fas fa-book"></i></div>
                            <?php endif; ?>

                            <div style="position: absolute; top: 1rem; right: 1rem; background: <?php echo $status_color; ?>; color: white; padding: 0.375rem 1rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                                <?php echo $status_text; ?>
                            </div>
                        </div>

                        <!-- Content -->
                        <div style="padding: 1.5rem;">
                            <h3 style="margin: 0 0 1rem 0; font-size: 1.25rem; font-weight: 600; color: #1f2937; line-height: 1.4;">
                                <?php echo esc_html($course->get_title()); ?>
                            </h3>

                            <!-- Progress -->
                            <div style="margin: 1.25rem 0;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                    <span style="font-size: 0.875rem; color: #6b7280; font-weight: 500;">Progression</span>
                                    <span style="font-size: 0.875rem; font-weight: 700; color: <?php echo $status_color; ?>;"><?php echo $progress; ?>%</span>
                                </div>
                                <div style="width: 100%; height: 10px; background: #e5e7eb; border-radius: 9999px; overflow: hidden;">
                                    <div style="height: 100%; background: <?php echo $status_color; ?>; width: <?php echo $progress; ?>%; transition: width 0.5s ease;"></div>
                                </div>
                            </div>

                            <!-- Meta -->
                            <div style="display: flex; gap: 1.5rem; margin: 1.25rem 0; font-size: 0.875rem; color: #6b7280;">
                                <?php
                                $duration = get_post_meta($course_id, '_lp_duration', true);
                                if ($duration) :
                                ?>
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                        </svg>
                                        <?php echo $duration; ?> min
                                    </div>
                                <?php endif; ?>

                                <?php if ($course_data->start_time) : ?>
                                    <div style="display: flex; align-items: center; gap: 0.375rem;">
                                        <svg style="width: 18px; height: 18px; fill: currentColor;" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                        <?php echo date_i18n('j M', strtotime($course_data->start_time)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Button -->
                            <a href="<?php echo get_permalink($course_id); ?>" style="
                                display: block;
                                text-align: center;
                                padding: 0.875rem;
                                background: <?php echo $progress === 100 ? '#10B981' : '#2D4FB3'; ?>;
                                color: white;
                                text-decoration: none;
                                border-radius: 0.5rem;
                                font-weight: 600;
                                font-size: 0.9375rem;
                                transition: all 0.2s;
                                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                            " onmouseover="this.style.opacity='0.9'; this.style.transform='scale(1.02)'" onmouseout="this.style.opacity='1'; this.style.transform='scale(1)'">
                                <?php echo $progress === 100 ? '<i class="fas fa-check-circle" style="margin-right: 0.5rem;"></i>Revoir le cours' : '<i class="fas fa-arrow-right" style="margin-right: 0.5rem;"></i>Continuer'; ?>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else : ?>
            <div style="text-align: center; padding: 4rem 2rem; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                <div style="font-size: 5rem; margin-bottom: 1.5rem; opacity: 0.5;"><i class="fas fa-book-open"></i></div>
                <h2 style="color: #1f2937; font-weight: 600; margin-bottom: 1rem; font-size: 1.5rem;">Aucun cours pour le moment</h2>
                <p style="color: #6b7280; margin-bottom: 2rem; font-size: 1.125rem;">Inscrivez-vous à des cours pour commencer votre apprentissage</p>
                <a href="<?php echo get_permalink(learn_press_get_page_id('courses')); ?>" style="
                    display: inline-block;
                    padding: 1rem 2.5rem;
                    background: #2D4FB3;
                    color: white;
                    text-decoration: none;
                    border-radius: 0.5rem;
                    font-weight: 600;
                    font-size: 1.125rem;
                    box-shadow: 0 4px 8px rgba(45, 79, 179, 0.3);
                    transition: all 0.3s;
                " onmouseover="this.style.background='#1e3a8a'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(45, 79, 179, 0.4)'" onmouseout="this.style.background='#2D4FB3'; this.style.transform=''; this.style.boxShadow='0 4px 8px rgba(45, 79, 179, 0.3)'">
                    Parcourir les cours
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('eia_my_courses', 'eia_my_courses_shortcode');

/**
 * AJAX handler for marking lesson/quiz as complete
 */
function eia_mark_item_complete() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'eia_mark_complete')) {
        wp_send_json_error(array('message' => 'Nonce invalide'));
    }

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Vous devez être connecté'));
    }

    $item_id = intval($_POST['item_id']);
    $item_type = sanitize_text_field($_POST['item_type']);
    $course_id = intval($_POST['course_id']);
    $user_id = get_current_user_id();

    // Validate item type
    if (!in_array($item_type, array('lp_lesson', 'lp_quiz'))) {
        wp_send_json_error(array('message' => 'Type d\'élément invalide'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'learnpress_user_items';

    // Check if user is enrolled in course
    $enrolled = $wpdb->get_var($wpdb->prepare(
        "SELECT user_item_id FROM $table_name
        WHERE user_id = %d AND item_id = %d AND item_type = 'lp_course'",
        $user_id,
        $course_id
    ));

    if (!$enrolled) {
        wp_send_json_error(array('message' => 'Vous devez être inscrit au cours'));
    }

    // Check if item already exists in user_items
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name
        WHERE user_id = %d AND item_id = %d AND item_type = %s",
        $user_id,
        $item_id,
        $item_type
    ));

    if ($existing) {
        // Update status to completed
        $updated = $wpdb->update(
            $table_name,
            array(
                'status' => 'completed',
                'end_time' => current_time('mysql'),
                'graduation' => 'passed'
            ),
            array(
                'user_id' => $user_id,
                'item_id' => $item_id,
                'item_type' => $item_type
            ),
            array('%s', '%s', '%s'),
            array('%d', '%d', '%s')
        );
    } else {
        // Insert new completion record
        $inserted = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'item_id' => $item_id,
                'item_type' => $item_type,
                'status' => 'completed',
                'start_time' => current_time('mysql'),
                'end_time' => current_time('mysql'),
                'graduation' => 'passed',
                'ref_id' => $course_id,
                'parent_id' => $enrolled,
                'ref_type' => 'lp_course'
            ),
            array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
        );
    }

    if ($updated !== false || $inserted !== false) {
        wp_send_json_success(array('message' => 'Marqué comme terminé'));
    } else {
        wp_send_json_error(array('message' => 'Erreur lors de la sauvegarde'));
    }
}
add_action('wp_ajax_eia_mark_item_complete', 'eia_mark_item_complete');

/**
 * Add notification badge to admin bar for pending assignments
 */
function eia_admin_bar_notifications($wp_admin_bar) {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'eia_assignment_submissions';

    // Count pending submissions (submitted but not graded)
    $pending_count = $wpdb->get_var(
        "SELECT COUNT(*) FROM $table_name WHERE status = 'submitted'"
    );

    if ($pending_count == 0) {
        return;
    }

    // Add notifications menu
    $wp_admin_bar->add_node(array(
        'id'    => 'eia-notifications',
        'title' => '<span class="ab-icon dashicons dashicons-bell"></span><span class="eia-notification-badge">' . $pending_count . '</span>',
        'href'  => admin_url('admin.php?page=eia-lms-pending-assignments'),
        'meta'  => array(
            'class' => 'eia-notifications-menu',
            'title' => $pending_count . ' devoir(s) en attente de notation'
        ),
    ));

    // Add sub-items
    $wp_admin_bar->add_node(array(
        'parent' => 'eia-notifications',
        'id'     => 'pending-assignments',
        'title'  => '<i class="fas fa-clipboard-list" style="margin-right: 0.5rem;"></i>' . $pending_count . ' devoir(s) en attente',
        'href'   => site_url('/notation-devoirs/'),
    ));

    $wp_admin_bar->add_node(array(
        'parent' => 'eia-notifications',
        'id'     => 'all-assignments',
        'title'  => '<i class="fas fa-list" style="margin-right: 0.5rem;"></i>Voir tous les devoirs',
        'href'   => admin_url('edit.php?post_type=lp_assignment'),
    ));
}
add_action('admin_bar_menu', 'eia_admin_bar_notifications', 999);

/**
 * Add CSS for notification badge
 */
function eia_admin_bar_notifications_css() {
    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        return;
    }
    ?>
    <style>
        /* Notification badge - Improved positioning */
        .eia-notification-badge {
            position: absolute;
            top: 3px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            font-size: 10px;
            font-weight: bold;
            line-height: 18px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: pulse-notification 2s infinite;
        }

        /* Wrapper for icon + badge positioning */
        #wpadminbar .eia-notifications-menu > .ab-item {
            position: relative;
            padding-right: 12px !important;
        }

        @keyframes pulse-notification {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
                box-shadow: 0 2px 4px rgba(0,0,0,0.3);
            }
            50% {
                opacity: 0.85;
                transform: scale(1.1);
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.5);
            }
        }

        #wpadminbar .eia-notifications-menu .ab-icon {
            font-size: 20px !important;
            margin-top: 2px;
            width: auto !important;
            height: auto !important;
        }

        #wpadminbar .eia-notifications-menu:hover .eia-notification-badge {
            background: #dc2626;
            box-shadow: 0 2px 8px rgba(220, 38, 38, 0.5);
        }

        /* Highlight on hover */
        #wpadminbar .eia-notifications-menu:hover {
            background: #F59E0B !important;
        }

        /* Submenu styling */
        #wpadminbar .eia-notifications-menu .ab-submenu {
            min-width: 250px;
        }

        #wpadminbar .eia-notifications-menu .ab-submenu a {
            padding: 10px 15px;
        }

        #wpadminbar .eia-notifications-menu .ab-submenu a:hover {
            background: #f3f4f6;
        }
    </style>
    <?php
}
add_action('wp_head', 'eia_admin_bar_notifications_css');
add_action('admin_head', 'eia_admin_bar_notifications_css');

/**
 * =====================================================
 * LESSON VIDEO PLAYER
 * =====================================================
 */

// Include lesson video functions
$lesson_video_functions = get_template_directory() . '/inc/lesson-video-functions.php';
if (file_exists($lesson_video_functions)) {
    require_once $lesson_video_functions;
}

/**
 * Enqueue lesson video assets
 */
function eia_enqueue_lesson_video_assets() {
    // Only on lesson pages
    if (!is_singular('lp_lesson')) {
        return;
    }

    // CSS
    wp_enqueue_style(
        'eia-lesson-video',
        get_template_directory_uri() . '/assets/css/lesson-video.css',
        array(),
        '1.0.0'
    );

    // JavaScript
    wp_enqueue_script(
        'eia-lesson-video',
        get_template_directory_uri() . '/assets/js/lesson-video.js',
        array('jquery'),
        '1.0.0',
        true
    );

    // Localize script
    wp_localize_script('eia-lesson-video', 'eiaLesson', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eia-lesson-nonce'),
        'lessonId' => get_the_ID(),
        'courseId' => get_post_meta(get_the_ID(), '_lp_course', true)
    ));
}
add_action('wp_enqueue_scripts', 'eia_enqueue_lesson_video_assets');

/**
 * Add lesson-id and course-id data attributes to wrapper
 */
function eia_add_lesson_data_attributes() {
    if (!is_singular('lp_lesson')) {
        return;
    }

    $lesson_id = get_the_ID();
    $course_id = get_post_meta($lesson_id, '_lp_course', true);

    echo '<script>
        jQuery(document).ready(function($) {
            $(".eia-lesson-wrapper").attr({
                "data-lesson-id": ' . $lesson_id . ',
                "data-course-id": ' . $course_id . '
            });
        });
    </script>';
}
add_action('wp_footer', 'eia_add_lesson_data_attributes');
?>