<?php
/**
 * Reports Class
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EIA_Reports {

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
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // AJAX: Get dashboard stats
        add_action('wp_ajax_eia_get_dashboard_stats', array($this, 'ajax_get_dashboard_stats'));

        // AJAX: Get course analytics
        add_action('wp_ajax_eia_get_course_analytics', array($this, 'ajax_get_course_analytics'));

        // AJAX: Get student report
        add_action('wp_ajax_eia_get_student_report', array($this, 'ajax_get_student_report'));

        // AJAX: Export report
        add_action('wp_ajax_eia_export_report', array($this, 'ajax_export_report'));

        // Track course view
        add_action('wp', array($this, 'track_course_view'));

        // Track lesson completion
        add_action('learn-press/user/lesson-completed', array($this, 'track_lesson_completion'), 10, 3);
    }

    /**
     * Get dashboard statistics
     */
    public function get_dashboard_stats() {
        $stats = array(
            'total_courses' => $this->get_total_courses(),
            'total_students' => $this->get_total_students(),
            'total_instructors' => $this->get_total_instructors(),
            'active_enrollments' => $this->get_active_enrollments(),
            'completed_courses' => $this->get_completed_courses(),
            'avg_completion_rate' => $this->get_avg_completion_rate(),
            'revenue' => $this->get_total_revenue(),
            'popular_courses' => $this->get_popular_courses(5),
        );

        return $stats;
    }

    /**
     * Get total courses
     */
    private function get_total_courses() {
        $courses = wp_count_posts('lp_course');
        return $courses->publish;
    }

    /**
     * Get total students
     */
    private function get_total_students() {
        $users = count_users();
        $students = isset($users['avail_roles']['student']) ? $users['avail_roles']['student'] : 0;
        return $students;
    }

    /**
     * Get total instructors
     */
    private function get_total_instructors() {
        $users = count_users();
        $instructors = isset($users['avail_roles']['instructor']) ? $users['avail_roles']['instructor'] : 0;
        return $instructors;
    }

    /**
     * Get active enrollments
     */
    private function get_active_enrollments() {
        global $wpdb;

        $query = "
            SELECT COUNT(*)
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_type = 'lp_course'
            AND status = 'enrolled'
        ";

        return (int) $wpdb->get_var($query);
    }

    /**
     * Get completed courses
     */
    private function get_completed_courses() {
        global $wpdb;

        $query = "
            SELECT COUNT(*)
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_type = 'lp_course'
            AND status = 'finished'
        ";

        return (int) $wpdb->get_var($query);
    }

    /**
     * Get average completion rate
     */
    private function get_avg_completion_rate() {
        global $wpdb;

        $query = "
            SELECT AVG(graduation) as avg_rate
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_type = 'lp_course'
            AND graduation IS NOT NULL
        ";

        $result = $wpdb->get_var($query);
        return $result ? round($result, 2) : 0;
    }

    /**
     * Get total revenue
     */
    private function get_total_revenue() {
        global $wpdb;

        // This requires WooCommerce or LearnPress Paid Membership
        // Placeholder for now
        return 0;
    }

    /**
     * Get popular courses
     */
    private function get_popular_courses($limit = 5) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT p.ID, p.post_title, COUNT(ui.user_id) as enrollment_count
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->prefix}learnpress_user_items ui ON p.ID = ui.item_id AND ui.item_type = 'lp_course'
            WHERE p.post_type = 'lp_course'
            AND p.post_status = 'publish'
            GROUP BY p.ID
            ORDER BY enrollment_count DESC
            LIMIT %d
        ", $limit);

        return $wpdb->get_results($query);
    }

    /**
     * Get course analytics
     */
    public function get_course_analytics($course_id, $date_from = null, $date_to = null) {
        global $wpdb;

        if (!$date_from) {
            $date_from = date('Y-m-d', strtotime('-30 days'));
        }

        if (!$date_to) {
            $date_to = date('Y-m-d');
        }

        // Enrollment stats
        $enrollment_query = $wpdb->prepare("
            SELECT DATE(start_time) as date, COUNT(*) as count
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d
            AND item_type = 'lp_course'
            AND DATE(start_time) BETWEEN %s AND %s
            GROUP BY DATE(start_time)
            ORDER BY date ASC
        ", $course_id, $date_from, $date_to);

        $enrollment_data = $wpdb->get_results($enrollment_query);

        // Completion stats
        $completion_query = $wpdb->prepare("
            SELECT DATE(end_time) as date, COUNT(*) as count
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d
            AND item_type = 'lp_course'
            AND status = 'finished'
            AND DATE(end_time) BETWEEN %s AND %s
            GROUP BY DATE(end_time)
            ORDER BY date ASC
        ", $course_id, $date_from, $date_to);

        $completion_data = $wpdb->get_results($completion_query);

        // Engagement stats (from analytics table)
        $engagement_query = $wpdb->prepare("
            SELECT event_type, COUNT(*) as count
            FROM {$wpdb->prefix}eia_course_analytics
            WHERE course_id = %d
            AND DATE(event_date) BETWEEN %s AND %s
            GROUP BY event_type
        ", $course_id, $date_from, $date_to);

        $engagement_data = $wpdb->get_results($engagement_query);

        return array(
            'enrollments' => $enrollment_data,
            'completions' => $completion_data,
            'engagement' => $engagement_data,
            'total_students' => $this->get_course_student_count($course_id),
            'completion_rate' => $this->get_course_completion_rate($course_id),
            'avg_duration' => $this->get_avg_course_duration($course_id),
        );
    }

    /**
     * Get course student count
     */
    private function get_course_student_count($course_id) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT COUNT(DISTINCT user_id)
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d
            AND item_type = 'lp_course'
        ", $course_id);

        return (int) $wpdb->get_var($query);
    }

    /**
     * Get course completion rate
     */
    private function get_course_completion_rate($course_id) {
        global $wpdb;

        $total_query = $wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d
            AND item_type = 'lp_course'
        ", $course_id);

        $completed_query = $wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d
            AND item_type = 'lp_course'
            AND status = 'finished'
        ", $course_id);

        $total = (int) $wpdb->get_var($total_query);
        $completed = (int) $wpdb->get_var($completed_query);

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Get average course duration
     */
    private function get_avg_course_duration($course_id) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT AVG(TIMESTAMPDIFF(DAY, start_time, end_time)) as avg_days
            FROM {$wpdb->prefix}learnpress_user_items
            WHERE item_id = %d
            AND item_type = 'lp_course'
            AND status = 'finished'
            AND end_time IS NOT NULL
        ", $course_id);

        $result = $wpdb->get_var($query);
        return $result ? round($result, 1) : 0;
    }

    /**
     * Get student report
     */
    public function get_student_report($user_id) {
        global $wpdb;

        // Get enrolled courses
        $courses_query = $wpdb->prepare("
            SELECT ui.*, p.post_title
            FROM {$wpdb->prefix}learnpress_user_items ui
            INNER JOIN {$wpdb->posts} p ON ui.item_id = p.ID
            WHERE ui.user_id = %d
            AND ui.item_type = 'lp_course'
            ORDER BY ui.start_time DESC
        ", $user_id);

        $courses = $wpdb->get_results($courses_query);

        // Get quiz results
        $quizzes_query = $wpdb->prepare("
            SELECT ui.*, p.post_title, c.post_title as course_title
            FROM {$wpdb->prefix}learnpress_user_items ui
            INNER JOIN {$wpdb->posts} p ON ui.item_id = p.ID
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_lp_course'
            LEFT JOIN {$wpdb->posts} c ON pm.meta_value = c.ID
            WHERE ui.user_id = %d
            AND ui.item_type = 'lp_quiz'
            ORDER BY ui.end_time DESC
        ", $user_id);

        $quizzes = $wpdb->get_results($quizzes_query);

        // Get certificates
        $certificates = $this->get_user_certificates($user_id);

        // Calculate stats
        $total_courses = count($courses);
        $completed_courses = 0;
        $total_time = 0;

        foreach ($courses as $course) {
            if ($course->status === 'finished') {
                $completed_courses++;
            }

            if ($course->end_time) {
                $start = strtotime($course->start_time);
                $end = strtotime($course->end_time);
                $total_time += ($end - $start);
            }
        }

        return array(
            'courses' => $courses,
            'quizzes' => $quizzes,
            'certificates' => $certificates,
            'stats' => array(
                'total_courses' => $total_courses,
                'completed_courses' => $completed_courses,
                'completion_rate' => $total_courses > 0 ? round(($completed_courses / $total_courses) * 100, 2) : 0,
                'total_time_hours' => round($total_time / 3600, 1),
            ),
        );
    }

    /**
     * Get user certificates
     */
    private function get_user_certificates($user_id) {
        // Placeholder - requires LearnPress Certificates add-on
        return array();
    }

    /**
     * Track course view
     */
    public function track_course_view() {
        if (!is_singular('lp_course') || !is_user_logged_in()) {
            return;
        }

        global $post, $wpdb;

        $user_id = get_current_user_id();
        $course_id = $post->ID;

        // Insert analytics event
        $wpdb->insert(
            $wpdb->prefix . 'eia_course_analytics',
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'event_type' => 'course_view',
                'event_date' => current_time('mysql'),
            ),
            array('%d', '%d', '%s', '%s')
        );
    }

    /**
     * Track lesson completion
     */
    public function track_lesson_completion($lesson_id, $result, $user_id) {
        global $wpdb;

        $course_id = get_post_meta($lesson_id, '_lp_course', true);

        if (!$course_id) {
            return;
        }

        // Insert analytics event
        $wpdb->insert(
            $wpdb->prefix . 'eia_course_analytics',
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'lesson_id' => $lesson_id,
                'event_type' => 'lesson_completed',
                'event_data' => json_encode($result),
                'event_date' => current_time('mysql'),
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s')
        );
    }

    /**
     * AJAX: Get dashboard stats
     */
    public function ajax_get_dashboard_stats() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        $stats = $this->get_dashboard_stats();

        wp_send_json_success($stats);
    }

    /**
     * AJAX: Get course analytics
     */
    public function ajax_get_course_analytics() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $date_from = isset($_POST['date_from']) ? sanitize_text_field($_POST['date_from']) : null;
        $date_to = isset($_POST['date_to']) ? sanitize_text_field($_POST['date_to']) : null;

        if (!$course_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        $analytics = $this->get_course_analytics($course_id, $date_from, $date_to);

        wp_send_json_success($analytics);
    }

    /**
     * AJAX: Get student report
     */
    public function ajax_get_student_report() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : get_current_user_id();

        if (!$user_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Check permissions
        if ($user_id !== get_current_user_id() && !current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        $report = $this->get_student_report($user_id);

        wp_send_json_success($report);
    }

    /**
     * AJAX: Export report
     */
    public function ajax_export_report() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        $report_type = isset($_POST['report_type']) ? sanitize_text_field($_POST['report_type']) : 'dashboard';

        $csv = $this->generate_report_csv($report_type);

        wp_send_json_success(array(
            'csv' => $csv,
            'filename' => 'eia-report-' . $report_type . '-' . date('Y-m-d') . '.csv',
        ));
    }

    /**
     * Generate report CSV
     */
    private function generate_report_csv($report_type) {
        switch ($report_type) {
            case 'dashboard':
                return $this->generate_dashboard_csv();
            case 'courses':
                return $this->generate_courses_csv();
            case 'students':
                return $this->generate_students_csv();
            default:
                return '';
        }
    }

    /**
     * Generate dashboard CSV
     */
    private function generate_dashboard_csv() {
        $stats = $this->get_dashboard_stats();

        $csv = "Statistique,Valeur\n";
        $csv .= "Total Cours," . $stats['total_courses'] . "\n";
        $csv .= "Total Étudiants," . $stats['total_students'] . "\n";
        $csv .= "Total Formateurs," . $stats['total_instructors'] . "\n";
        $csv .= "Inscriptions Actives," . $stats['active_enrollments'] . "\n";
        $csv .= "Cours Complétés," . $stats['completed_courses'] . "\n";
        $csv .= "Taux Complétion Moyen," . $stats['avg_completion_rate'] . "%\n";

        return $csv;
    }

    /**
     * Generate courses CSV
     */
    private function generate_courses_csv() {
        $courses = get_posts(array(
            'post_type' => 'lp_course',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ));

        $csv = "Cours,Étudiants,Taux Complétion\n";

        foreach ($courses as $course) {
            $student_count = $this->get_course_student_count($course->ID);
            $completion_rate = $this->get_course_completion_rate($course->ID);

            $csv .= sprintf(
                '"%s",%d,%s%%' . "\n",
                $course->post_title,
                $student_count,
                $completion_rate
            );
        }

        return $csv;
    }

    /**
     * Generate students CSV
     */
    private function generate_students_csv() {
        $students = get_users(array('role' => 'student'));

        $csv = "Étudiant,Email,Cours Inscrits,Cours Complétés\n";

        foreach ($students as $student) {
            global $wpdb;

            $enrolled = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM {$wpdb->prefix}learnpress_user_items
                WHERE user_id = %d
                AND item_type = 'lp_course'
            ", $student->ID));

            $completed = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(*)
                FROM {$wpdb->prefix}learnpress_user_items
                WHERE user_id = %d
                AND item_type = 'lp_course'
                AND status = 'finished'
            ", $student->ID));

            $csv .= sprintf(
                '"%s","%s",%d,%d' . "\n",
                $student->display_name,
                $student->user_email,
                $enrolled,
                $completed
            );
        }

        return $csv;
    }
}
?>