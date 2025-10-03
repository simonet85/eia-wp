<?php
/**
 * Roles and Capabilities Manager
 * Gère les permissions pour chaque rôle utilisateur
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EIA_Roles_Capabilities {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'setup_roles_capabilities'));
        add_action('admin_init', array($this, 'maybe_update_capabilities'));
    }

    /**
     * Setup all roles and capabilities
     */
    public function setup_roles_capabilities() {
        $this->setup_student_role();
        $this->setup_instructor_role();
        $this->setup_lms_manager_role();
    }

    /**
     * Update capabilities if needed (version check)
     */
    public function maybe_update_capabilities() {
        $version = get_option('eia_lms_capabilities_version', '0');

        if (version_compare($version, '1.0', '<')) {
            $this->setup_roles_capabilities();
            update_option('eia_lms_capabilities_version', '1.0');
        }
    }

    /**
     * Setup Student role capabilities
     */
    private function setup_student_role() {
        $role = get_role('student');

        if (!$role) {
            // Create student role if it doesn't exist
            $role = add_role('student', __('Étudiant', 'eia-lms-core'), array(
                'read' => true,
            ));
        }

        if ($role) {
            // Basic WordPress capabilities
            $role->add_cap('read');
            $role->add_cap('upload_files'); // Pour soumettre des devoirs

            // LearnPress Student capabilities
            $role->add_cap('lp_student'); // Capability de base LearnPress
            $role->add_cap('enroll_course'); // S'inscrire aux cours
            $role->add_cap('view_course_content'); // Voir le contenu des cours
            $role->add_cap('complete_lesson'); // Compléter les leçons
            $role->add_cap('take_quiz'); // Passer les quiz
            $role->add_cap('submit_assignment'); // Soumettre des devoirs
            $role->add_cap('view_own_progress'); // Voir sa propre progression
            $role->add_cap('download_certificate'); // Télécharger ses certificats

            // BuddyPress/Social capabilities
            $role->add_cap('bp_moderate'); // Participer aux forums/groupes

            // Remove admin access
            $role->remove_cap('edit_posts');
            $role->remove_cap('delete_posts');
            $role->remove_cap('publish_posts');
            $role->remove_cap('edit_published_posts');
        }
    }

    /**
     * Setup Instructor role capabilities
     */
    private function setup_instructor_role() {
        $role = get_role('instructor');

        if (!$role) {
            // Create instructor role if it doesn't exist
            $role = add_role('instructor', __('Formateur', 'eia-lms-core'), array(
                'read' => true,
            ));
        }

        if ($role) {
            // Basic WordPress capabilities
            $role->add_cap('read');
            $role->add_cap('upload_files');
            $role->add_cap('edit_posts');
            $role->add_cap('delete_posts');

            // LearnPress Instructor capabilities - Courses
            $role->add_cap('edit_lp_courses');
            $role->add_cap('edit_published_lp_courses');
            $role->add_cap('publish_lp_courses');
            $role->add_cap('delete_lp_courses');
            $role->add_cap('delete_published_lp_courses');
            $role->add_cap('read_private_lp_courses');

            // Can only edit their own courses (not others')
            $role->remove_cap('edit_others_lp_courses');
            $role->remove_cap('delete_others_lp_courses');

            // LearnPress Instructor capabilities - Lessons
            $role->add_cap('edit_lp_lessons');
            $role->add_cap('edit_published_lp_lessons');
            $role->add_cap('publish_lp_lessons');
            $role->add_cap('delete_lp_lessons');
            $role->add_cap('delete_published_lp_lessons');
            $role->add_cap('read_private_lp_lessons');

            // LearnPress Instructor capabilities - Quizzes
            $role->add_cap('edit_lp_quizzes');
            $role->add_cap('edit_published_lp_quizzes');
            $role->add_cap('publish_lp_quizzes');
            $role->add_cap('delete_lp_quizzes');
            $role->add_cap('delete_published_lp_quizzes');
            $role->add_cap('read_private_lp_quizzes');

            // LearnPress Instructor capabilities - Questions
            $role->add_cap('edit_lp_questions');
            $role->add_cap('edit_published_lp_questions');
            $role->add_cap('publish_lp_questions');
            $role->add_cap('delete_lp_questions');
            $role->add_cap('delete_published_lp_questions');

            // Student management (only their own students)
            $role->add_cap('view_students');
            $role->add_cap('view_student_progress');
            $role->add_cap('grade_assignments');
            $role->add_cap('send_messages_to_students');

            // Reports and analytics
            $role->add_cap('view_own_course_reports');
            $role->add_cap('export_own_course_data');

            // Remove capabilities they shouldn't have
            $role->remove_cap('edit_users');
            $role->remove_cap('delete_users');
            $role->remove_cap('install_plugins');
            $role->remove_cap('activate_plugins');
            $role->remove_cap('edit_theme_options');
            $role->remove_cap('manage_options');
        }
    }

    /**
     * Setup LMS Manager role capabilities
     */
    private function setup_lms_manager_role() {
        $role = get_role('lms_manager');

        if (!$role) {
            // Create LMS manager role if it doesn't exist
            $role = add_role('lms_manager', __('Gestionnaire LMS', 'eia-lms-core'), array(
                'read' => true,
            ));
        }

        if ($role) {
            // All WordPress editing capabilities
            $role->add_cap('read');
            $role->add_cap('upload_files');
            $role->add_cap('edit_posts');
            $role->add_cap('edit_others_posts');
            $role->add_cap('delete_posts');
            $role->add_cap('delete_others_posts');
            $role->add_cap('publish_posts');
            $role->add_cap('edit_published_posts');

            // LearnPress Manager - Full course management
            $role->add_cap('edit_lp_courses');
            $role->add_cap('edit_others_lp_courses');
            $role->add_cap('edit_published_lp_courses');
            $role->add_cap('publish_lp_courses');
            $role->add_cap('delete_lp_courses');
            $role->add_cap('delete_others_lp_courses');
            $role->add_cap('delete_published_lp_courses');
            $role->add_cap('read_private_lp_courses');

            // LearnPress Manager - Full lesson management
            $role->add_cap('edit_lp_lessons');
            $role->add_cap('edit_others_lp_lessons');
            $role->add_cap('edit_published_lp_lessons');
            $role->add_cap('publish_lp_lessons');
            $role->add_cap('delete_lp_lessons');
            $role->add_cap('delete_others_lp_lessons');
            $role->add_cap('delete_published_lp_lessons');
            $role->add_cap('read_private_lp_lessons');

            // LearnPress Manager - Full quiz management
            $role->add_cap('edit_lp_quizzes');
            $role->add_cap('edit_others_lp_quizzes');
            $role->add_cap('edit_published_lp_quizzes');
            $role->add_cap('publish_lp_quizzes');
            $role->add_cap('delete_lp_quizzes');
            $role->add_cap('delete_others_lp_quizzes');
            $role->add_cap('delete_published_lp_quizzes');
            $role->add_cap('read_private_lp_quizzes');

            // LearnPress Manager - Full question management
            $role->add_cap('edit_lp_questions');
            $role->add_cap('edit_others_lp_questions');
            $role->add_cap('edit_published_lp_questions');
            $role->add_cap('publish_lp_questions');
            $role->add_cap('delete_lp_questions');
            $role->add_cap('delete_others_lp_questions');
            $role->add_cap('delete_published_lp_questions');

            // User management capabilities
            $role->add_cap('list_users');
            $role->add_cap('edit_users');
            $role->add_cap('create_users');
            $role->add_cap('delete_users');
            $role->add_cap('promote_users'); // Change user roles

            // Reports and analytics
            $role->add_cap('view_all_reports');
            $role->add_cap('export_all_data');
            $role->add_cap('view_statistics');

            // Settings management
            $role->add_cap('manage_lp_settings'); // LearnPress settings
            $role->add_cap('manage_categories');

            // Remove super admin capabilities
            $role->remove_cap('install_plugins');
            $role->remove_cap('activate_plugins');
            $role->remove_cap('edit_plugins');
            $role->remove_cap('delete_plugins');
            $role->remove_cap('install_themes');
            $role->remove_cap('edit_themes');
            $role->remove_cap('delete_themes');
            $role->remove_cap('manage_options'); // Core WP settings
        }
    }

    /**
     * Check if user can manage course
     */
    public static function user_can_manage_course($user_id, $course_id) {
        $user = get_userdata($user_id);

        if (!$user) {
            return false;
        }

        // Admin and LMS Manager can manage all courses
        if (in_array('administrator', $user->roles) || in_array('lms_manager', $user->roles)) {
            return true;
        }

        // Instructor can only manage their own courses
        if (in_array('instructor', $user->roles)) {
            $course = get_post($course_id);
            return $course && $course->post_author == $user_id;
        }

        return false;
    }

    /**
     * Check if user can view student progress
     */
    public static function user_can_view_student_progress($user_id, $student_id) {
        $user = get_userdata($user_id);

        if (!$user) {
            return false;
        }

        // Admin and LMS Manager can view all
        if (in_array('administrator', $user->roles) || in_array('lms_manager', $user->roles)) {
            return true;
        }

        // Instructor can view their own students
        if (in_array('instructor', $user->roles)) {
            // Check if student is enrolled in instructor's course
            global $wpdb;
            $courses = $wpdb->get_col($wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'lp_course' AND post_author = %d",
                $user_id
            ));

            if (empty($courses)) {
                return false;
            }

            // Check if student is enrolled in any of these courses
            $enrolled = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}learnpress_user_items
                WHERE user_id = %d AND item_id IN (" . implode(',', array_map('intval', $courses)) . ")
                AND item_type = 'lp_course'",
                $student_id
            ));

            return $enrolled > 0;
        }

        // Users can view their own progress
        return $user_id == $student_id;
    }

    /**
     * Remove a role (for cleanup)
     */
    public static function remove_role($role_name) {
        remove_role($role_name);
    }

    /**
     * Reset all roles to default
     */
    public static function reset_all_roles() {
        self::remove_role('student');
        self::remove_role('instructor');
        self::remove_role('lms_manager');

        delete_option('eia_lms_capabilities_version');

        $instance = self::get_instance();
        $instance->setup_roles_capabilities();
    }
}

// Initialize
EIA_Roles_Capabilities::get_instance();
?>
