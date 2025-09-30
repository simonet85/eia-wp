<?php
/**
 * AJAX Handlers for EIA LMS
 *
 * @package EIA_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX: Enroll user in course
 */
function eia_ajax_enroll_course() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté pour vous inscrire', 'eia-theme')
        ));
    }

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $user_id = get_current_user_id();

    if (!$course_id) {
        wp_send_json_error(array(
            'message' => __('ID du cours invalide', 'eia-theme')
        ));
    }

    // Enroll user using LearnPress
    if (function_exists('learn_press_user_enroll_course')) {
        $result = learn_press_user_enroll_course($user_id, $course_id);

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Inscription réussie !', 'eia-theme'),
                'redirect' => get_permalink($course_id)
            ));
        }
    }

    wp_send_json_error(array(
        'message' => __('Erreur lors de l\'inscription', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_enroll_course', 'eia_ajax_enroll_course');

/**
 * AJAX: Load more courses
 */
function eia_ajax_load_more_courses() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    $level = isset($_POST['level']) ? sanitize_text_field($_POST['level']) : '';

    $args = array(
        'post_type' => 'lp_course',
        'posts_per_page' => 9,
        'paged' => $paged,
        'post_status' => 'publish',
    );

    // Add category filter
    if ($category) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'course_category',
                'field' => 'slug',
                'terms' => $category,
            ),
        );
    }

    // Add level filter
    if ($level) {
        $args['meta_query'] = array(
            array(
                'key' => '_lp_level',
                'value' => $level,
                'compare' => '=',
            ),
        );
    }

    $courses = new WP_Query($args);

    if ($courses->have_posts()) {
        ob_start();

        while ($courses->have_posts()) {
            $courses->the_post();
            get_template_part('templates/content', 'course');
        }

        $html = ob_get_clean();
        wp_reset_postdata();

        wp_send_json_success(array(
            'html' => $html,
            'has_more' => $courses->max_num_pages > $paged,
        ));
    }

    wp_send_json_error(array(
        'message' => __('Aucun cours trouvé', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_load_more_courses', 'eia_ajax_load_more_courses');
add_action('wp_ajax_nopriv_eia_load_more_courses', 'eia_ajax_load_more_courses');

/**
 * AJAX: Mark lesson as completed
 */
function eia_ajax_complete_lesson() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté', 'eia-theme')
        ));
    }

    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $user_id = get_current_user_id();

    if (!$lesson_id || !$course_id) {
        wp_send_json_error(array(
            'message' => __('Données invalides', 'eia-theme')
        ));
    }

    // Mark lesson as completed using LearnPress
    if (function_exists('learn_press_get_user')) {
        $user = learn_press_get_user($user_id);
        if ($user) {
            $result = $user->complete_lesson($lesson_id, $course_id);

            if ($result) {
                wp_send_json_success(array(
                    'message' => __('Leçon terminée !', 'eia-theme'),
                    'progress' => eia_get_course_progress($course_id, $user_id)
                ));
            }
        }
    }

    wp_send_json_error(array(
        'message' => __('Erreur lors de la validation', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_complete_lesson', 'eia_ajax_complete_lesson');

/**
 * AJAX: Submit quiz
 */
function eia_ajax_submit_quiz() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté', 'eia-theme')
        ));
    }

    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $answers = isset($_POST['answers']) ? $_POST['answers'] : array();

    if (!$quiz_id) {
        wp_send_json_error(array(
            'message' => __('Quiz invalide', 'eia-theme')
        ));
    }

    // Process quiz submission with LearnPress
    if (function_exists('learn_press_get_quiz')) {
        // Quiz submission logic here
        wp_send_json_success(array(
            'message' => __('Quiz soumis avec succès', 'eia-theme'),
            'redirect' => get_permalink($quiz_id)
        ));
    }

    wp_send_json_error(array(
        'message' => __('Erreur lors de la soumission', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_submit_quiz', 'eia_ajax_submit_quiz');

/**
 * AJAX: Add to wishlist
 */
function eia_ajax_add_to_wishlist() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté', 'eia-theme')
        ));
    }

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $user_id = get_current_user_id();

    if (!$course_id) {
        wp_send_json_error(array(
            'message' => __('Cours invalide', 'eia-theme')
        ));
    }

    // Get current wishlist
    $wishlist = get_user_meta($user_id, 'eia_course_wishlist', true);
    if (!is_array($wishlist)) {
        $wishlist = array();
    }

    // Toggle wishlist
    if (in_array($course_id, $wishlist)) {
        // Remove from wishlist
        $wishlist = array_diff($wishlist, array($course_id));
        $message = __('Retiré de la liste de souhaits', 'eia-theme');
        $in_wishlist = false;
    } else {
        // Add to wishlist
        $wishlist[] = $course_id;
        $message = __('Ajouté à la liste de souhaits', 'eia-theme');
        $in_wishlist = true;
    }

    update_user_meta($user_id, 'eia_course_wishlist', $wishlist);

    wp_send_json_success(array(
        'message' => $message,
        'in_wishlist' => $in_wishlist,
        'count' => count($wishlist)
    ));
}
add_action('wp_ajax_eia_add_to_wishlist', 'eia_ajax_add_to_wishlist');

/**
 * AJAX: Send message to instructor
 */
function eia_ajax_send_instructor_message() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté', 'eia-theme')
        ));
    }

    $instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 0;
    $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';

    if (!$instructor_id || !$message) {
        wp_send_json_error(array(
            'message' => __('Données invalides', 'eia-theme')
        ));
    }

    $user = wp_get_current_user();
    $instructor = get_userdata($instructor_id);

    if (!$instructor) {
        wp_send_json_error(array(
            'message' => __('Formateur introuvable', 'eia-theme')
        ));
    }

    // Send email
    $to = $instructor->user_email;
    $subject = $subject ? $subject : __('Nouveau message d\'un étudiant', 'eia-theme');
    $body = sprintf(
        __('Message de %s (%s):', 'eia-theme') . "\n\n%s",
        $user->display_name,
        $user->user_email,
        $message
    );

    $headers = array('Content-Type: text/plain; charset=UTF-8');

    if (wp_mail($to, $subject, $body, $headers)) {
        wp_send_json_success(array(
            'message' => __('Message envoyé avec succès', 'eia-theme')
        ));
    }

    wp_send_json_error(array(
        'message' => __('Erreur lors de l\'envoi du message', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_send_instructor_message', 'eia_ajax_send_instructor_message');

/**
 * AJAX: Rate course
 */
function eia_ajax_rate_course() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Vous devez être connecté', 'eia-theme')
        ));
    }

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review = isset($_POST['review']) ? sanitize_textarea_field($_POST['review']) : '';

    if (!$course_id || $rating < 1 || $rating > 5) {
        wp_send_json_error(array(
            'message' => __('Données invalides', 'eia-theme')
        ));
    }

    $user_id = get_current_user_id();

    // Check if user has enrolled in this course
    if (!eia_is_user_enrolled($course_id, $user_id)) {
        wp_send_json_error(array(
            'message' => __('Vous devez être inscrit au cours pour le noter', 'eia-theme')
        ));
    }

    // Save review using LearnPress
    if (function_exists('learn_press_add_course_review')) {
        $result = learn_press_add_course_review($course_id, array(
            'user_id' => $user_id,
            'rate' => $rating,
            'title' => '',
            'content' => $review,
        ));

        if ($result) {
            wp_send_json_success(array(
                'message' => __('Évaluation enregistrée', 'eia-theme'),
                'rating' => eia_get_course_rating($course_id)
            ));
        }
    }

    wp_send_json_error(array(
        'message' => __('Erreur lors de l\'enregistrement', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_rate_course', 'eia_ajax_rate_course');

/**
 * AJAX: Get user notifications
 */
function eia_ajax_get_notifications() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Non autorisé', 'eia-theme')
        ));
    }

    $user_id = get_current_user_id();

    // Get notifications (custom implementation or from plugin)
    $notifications = get_user_meta($user_id, 'eia_notifications', true);
    if (!is_array($notifications)) {
        $notifications = array();
    }

    wp_send_json_success(array(
        'notifications' => $notifications,
        'count' => count($notifications)
    ));
}
add_action('wp_ajax_eia_get_notifications', 'eia_ajax_get_notifications');

/**
 * AJAX: Mark notification as read
 */
function eia_ajax_mark_notification_read() {
    // Verify nonce
    check_ajax_referer('eia-lms-nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'message' => __('Non autorisé', 'eia-theme')
        ));
    }

    $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
    $user_id = get_current_user_id();

    if (!$notification_id) {
        wp_send_json_error(array(
            'message' => __('Notification invalide', 'eia-theme')
        ));
    }

    // Mark as read
    $notifications = get_user_meta($user_id, 'eia_notifications', true);
    if (is_array($notifications) && isset($notifications[$notification_id])) {
        $notifications[$notification_id]['read'] = true;
        update_user_meta($user_id, 'eia_notifications', $notifications);

        wp_send_json_success(array(
            'message' => __('Notification marquée comme lue', 'eia-theme')
        ));
    }

    wp_send_json_error(array(
        'message' => __('Notification introuvable', 'eia-theme')
    ));
}
add_action('wp_ajax_eia_mark_notification_read', 'eia_ajax_mark_notification_read');
?>