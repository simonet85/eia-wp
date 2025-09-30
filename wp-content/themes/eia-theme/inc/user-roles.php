<?php
/**
 * User Roles Management for EIA LMS
 *
 * @package EIA_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add custom LMS roles on theme activation
 */
function eia_add_lms_roles() {
    // Student Role
    add_role('student', __('Étudiant', 'eia-theme'), array(
        'read' => true,
        'level_0' => true,
        // LMS specific capabilities
        'view_courses' => true,
        'take_courses' => true,
        'view_lessons' => true,
        'take_quizzes' => true,
        'view_certificates' => true,
        'participate_forum' => true,
    ));

    // Instructor Role
    add_role('instructor', __('Formateur', 'eia-theme'), array(
        'read' => true,
        'level_1' => true,
        // Course management
        'create_courses' => true,
        'edit_courses' => true,
        'delete_courses' => true,
        'publish_courses' => true,
        'edit_published_courses' => true,
        // Lesson management
        'create_lessons' => true,
        'edit_lessons' => true,
        'delete_lessons' => true,
        // Quiz management
        'create_quizzes' => true,
        'edit_quizzes' => true,
        'delete_quizzes' => true,
        // Student management
        'view_students' => true,
        'manage_students' => true,
        'grade_students' => true,
        'view_student_progress' => true,
        // Communication
        'send_announcements' => true,
        'moderate_forum' => true,
    ));

    // LMS Manager Role (Super Instructor)
    add_role('lms_manager', __('Gestionnaire LMS', 'eia-theme'), array(
        'read' => true,
        'level_7' => true,
        // All instructor capabilities
        'create_courses' => true,
        'edit_courses' => true,
        'delete_courses' => true,
        'publish_courses' => true,
        'edit_published_courses' => true,
        'edit_others_courses' => true,
        'delete_others_courses' => true,
        // Advanced management
        'manage_lms_settings' => true,
        'view_lms_analytics' => true,
        'manage_instructors' => true,
        'manage_students' => true,
        'export_data' => true,
        'manage_certificates' => true,
        'manage_badges' => true,
        // Access to admin
        'edit_posts' => true,
        'edit_pages' => true,
        'edit_others_posts' => true,
        'edit_published_posts' => true,
        'publish_posts' => true,
        'manage_categories' => true,
        'moderate_comments' => true,
    ));
}

/**
 * Remove custom LMS roles on theme deactivation
 */
function eia_remove_lms_roles() {
    remove_role('student');
    remove_role('instructor');
    remove_role('lms_manager');
}

/**
 * Initialize LMS roles
 */
function eia_init_lms_roles() {
    // Check if roles are already added
    $student_role = get_role('student');

    if (!$student_role) {
        eia_add_lms_roles();
    }
}
add_action('after_switch_theme', 'eia_init_lms_roles');

/**
 * Add LMS capabilities to administrator
 */
function eia_add_lms_caps_to_admin() {
    $admin = get_role('administrator');

    if ($admin) {
        // Add all LMS capabilities to admin
        $lms_caps = array(
            'create_courses',
            'edit_courses',
            'delete_courses',
            'publish_courses',
            'edit_published_courses',
            'edit_others_courses',
            'delete_others_courses',
            'manage_lms_settings',
            'view_lms_analytics',
            'manage_instructors',
            'manage_students',
            'export_data',
            'manage_certificates',
            'manage_badges',
        );

        foreach ($lms_caps as $cap) {
            $admin->add_cap($cap);
        }
    }
}
add_action('after_switch_theme', 'eia_add_lms_caps_to_admin');

/**
 * Get user role display name
 */
function eia_get_user_role_name($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);

    if (!$user) {
        return '';
    }

    $roles = array(
        'student' => __('Étudiant', 'eia-theme'),
        'instructor' => __('Formateur', 'eia-theme'),
        'lms_manager' => __('Gestionnaire LMS', 'eia-theme'),
        'administrator' => __('Administrateur', 'eia-theme'),
    );

    foreach ($user->roles as $role) {
        if (isset($roles[$role])) {
            return $roles[$role];
        }
    }

    return __('Utilisateur', 'eia-theme');
}

/**
 * Check if user is student
 */
function eia_is_student($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);
    return $user && in_array('student', $user->roles);
}

/**
 * Check if user is instructor
 */
function eia_is_instructor($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);
    return $user && in_array('instructor', $user->roles);
}

/**
 * Check if user is LMS manager
 */
function eia_is_lms_manager($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $user = get_userdata($user_id);
    return $user && in_array('lms_manager', $user->roles);
}

/**
 * Get student count
 */
function eia_get_student_count() {
    $users = count_users();
    return isset($users['avail_roles']['student']) ? $users['avail_roles']['student'] : 0;
}

/**
 * Get instructor count
 */
function eia_get_instructor_count() {
    $users = count_users();
    return isset($users['avail_roles']['instructor']) ? $users['avail_roles']['instructor'] : 0;
}

/**
 * Restrict access based on role
 */
function eia_restrict_access_by_role() {
    // Don't restrict admin pages
    if (is_admin()) {
        return;
    }

    // Get current user
    $user = wp_get_current_user();

    // Students can't access instructor pages
    if (eia_is_student() && is_page(array('instructor-dashboard', 'create-course'))) {
        wp_redirect(home_url('/student-dashboard/'));
        exit;
    }

    // Instructors can't access student-only pages
    if (eia_is_instructor() && is_page('student-dashboard')) {
        wp_redirect(home_url('/instructor-dashboard/'));
        exit;
    }
}
add_action('template_redirect', 'eia_restrict_access_by_role');

/**
 * Customize user profile fields
 */
function eia_add_custom_user_profile_fields($user) {
    if (!current_user_can('edit_user', $user->ID)) {
        return;
    }
    ?>
    <h3><?php _e('Informations LMS', 'eia-theme'); ?></h3>
    <table class="form-table">
        <tr>
            <th><label for="student_id"><?php _e('ID Étudiant', 'eia-theme'); ?></label></th>
            <td>
                <input type="text" name="student_id" id="student_id"
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'student_id', true)); ?>"
                       class="regular-text" />
                <p class="description"><?php _e('Numéro d\'identification unique de l\'étudiant', 'eia-theme'); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="enrollment_date"><?php _e('Date d\'inscription', 'eia-theme'); ?></label></th>
            <td>
                <input type="date" name="enrollment_date" id="enrollment_date"
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'enrollment_date', true)); ?>"
                       class="regular-text" />
            </td>
        </tr>
        <tr>
            <th><label for="specialization"><?php _e('Spécialisation', 'eia-theme'); ?></label></th>
            <td>
                <input type="text" name="specialization" id="specialization"
                       value="<?php echo esc_attr(get_user_meta($user->ID, 'specialization', true)); ?>"
                       class="regular-text" />
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'eia_add_custom_user_profile_fields');
add_action('edit_user_profile', 'eia_add_custom_user_profile_fields');

/**
 * Save custom user profile fields
 */
function eia_save_custom_user_profile_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }

    if (isset($_POST['student_id'])) {
        update_user_meta($user_id, 'student_id', sanitize_text_field($_POST['student_id']));
    }

    if (isset($_POST['enrollment_date'])) {
        update_user_meta($user_id, 'enrollment_date', sanitize_text_field($_POST['enrollment_date']));
    }

    if (isset($_POST['specialization'])) {
        update_user_meta($user_id, 'specialization', sanitize_text_field($_POST['specialization']));
    }
}
add_action('personal_options_update', 'eia_save_custom_user_profile_fields');
add_action('edit_user_profile_update', 'eia_save_custom_user_profile_fields');
?>