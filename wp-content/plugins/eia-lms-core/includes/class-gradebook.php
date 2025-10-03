<?php
/**
 * Gradebook Class
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EIA_Gradebook {

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
        // Add gradebook metabox to course
        add_action('add_meta_boxes', array($this, 'add_gradebook_metabox'));

        // AJAX: Get student grades
        add_action('wp_ajax_eia_get_student_grades', array($this, 'ajax_get_student_grades'));

        // AJAX: Save manual grade
        add_action('wp_ajax_eia_save_manual_grade', array($this, 'ajax_save_manual_grade'));

        // AJAX: Export gradebook
        add_action('wp_ajax_eia_export_gradebook', array($this, 'ajax_export_gradebook'));

        // Add gradebook page for instructors
        add_action('admin_menu', array($this, 'add_gradebook_page'));

        // Auto-record grades when quiz completed
        add_action('learn-press/user/quiz-finished', array($this, 'record_quiz_grade'), 10, 3);
    }

    /**
     * Add gradebook metabox
     */
    public function add_gradebook_metabox() {
        add_meta_box(
            'eia-course-gradebook',
            __('Carnet de Notes', 'eia-lms-core'),
            array($this, 'render_gradebook_metabox'),
            'lp_course',
            'normal',
            'high'
        );
    }

    /**
     * Render gradebook metabox
     */
    public function render_gradebook_metabox($post) {
        $course_id = $post->ID;
        $course = learn_press_get_course($course_id);

        if (!$course) {
            echo '<p>' . __('Erreur de chargement du cours', 'eia-lms-core') . '</p>';
            return;
        }

        // Get enrolled students
        $students = $this->get_course_students($course_id);

        ?>
        <div class="eia-gradebook-wrapper">
            <div class="gradebook-header">
                <div class="gradebook-actions">
                    <button class="button button-primary export-gradebook" data-course-id="<?php echo $course_id; ?>">
                        <i class="dashicons dashicons-download"></i>
                        <?php _e('Exporter (CSV)', 'eia-lms-core'); ?>
                    </button>
                    <button class="button calculate-final-grades" data-course-id="<?php echo $course_id; ?>">
                        <i class="dashicons dashicons-calculator"></i>
                        <?php _e('Calculer notes finales', 'eia-lms-core'); ?>
                    </button>
                </div>
            </div>

            <?php if (empty($students)) : ?>
                <div class="gradebook-empty">
                    <p><?php _e('Aucun étudiant inscrit à ce cours.', 'eia-lms-core'); ?></p>
                </div>
            <?php else : ?>
                <div class="gradebook-table-wrapper">
                    <table class="gradebook-table widefat striped">
                        <thead>
                            <tr>
                                <th><?php _e('Étudiant', 'eia-lms-core'); ?></th>
                                <th><?php _e('Progression', 'eia-lms-core'); ?></th>
                                <th><?php _e('Quiz', 'eia-lms-core'); ?></th>
                                <th><?php _e('Travaux', 'eia-lms-core'); ?></th>
                                <th><?php _e('Note finale', 'eia-lms-core'); ?></th>
                                <th><?php _e('Statut', 'eia-lms-core'); ?></th>
                                <th><?php _e('Actions', 'eia-lms-core'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student) :
                                $user_id = $student->ID;
                                $grades = $this->get_student_grades($course_id, $user_id);
                            ?>
                                <tr data-user-id="<?php echo $user_id; ?>">
                                    <td>
                                        <strong><?php echo esc_html($student->display_name); ?></strong><br>
                                        <small><?php echo esc_html($student->user_email); ?></small>
                                    </td>
                                    <td>
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $grades['progress']; ?>%"></div>
                                            <span class="progress-text"><?php echo $grades['progress']; ?>%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="grade-value"><?php echo $grades['quiz_avg']; ?>%</span>
                                    </td>
                                    <td>
                                        <input type="number"
                                               class="manual-grade-input"
                                               data-user-id="<?php echo $user_id; ?>"
                                               data-course-id="<?php echo $course_id; ?>"
                                               value="<?php echo $grades['manual_grade']; ?>"
                                               min="0"
                                               max="100"
                                               step="0.1">
                                    </td>
                                    <td>
                                        <strong class="final-grade" style="color: <?php echo $this->get_grade_color($grades['final_grade']); ?>">
                                            <?php echo $grades['final_grade']; ?>%
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $grades['status']; ?>">
                                            <?php echo $this->get_status_label($grades['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="button button-small view-details"
                                                data-user-id="<?php echo $user_id; ?>"
                                                data-course-id="<?php echo $course_id; ?>">
                                            <?php _e('Détails', 'eia-lms-core'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <style>
        .eia-gradebook-wrapper {
            margin: 20px 0;
        }

        .gradebook-header {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 4px;
        }

        .gradebook-actions {
            display: flex;
            gap: 10px;
        }

        .gradebook-table {
            margin-top: 20px;
        }

        .gradebook-table th {
            background: #2D4FB3;
            color: #fff;
            padding: 12px 8px;
        }

        .gradebook-table td {
            padding: 12px 8px;
            vertical-align: middle;
        }

        .progress-bar {
            width: 100%;
            height: 24px;
            background: #e0e0e0;
            border-radius: 12px;
            position: relative;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #2D4FB3;
            transition: width 0.3s;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }

        .grade-value {
            font-weight: bold;
            font-size: 14px;
        }

        .manual-grade-input {
            width: 70px;
            padding: 4px 8px;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background: #4CAF50;
            color: #fff;
        }

        .status-in-progress {
            background: #F59E0B;
            color: #fff;
        }

        .status-failed {
            background: #f44336;
            color: #fff;
        }

        .status-pending {
            background: #9E9E9E;
            color: #fff;
        }

        .gradebook-empty {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        </style>
        <?php
    }

    /**
     * Add gradebook admin page
     */
    public function add_gradebook_page() {
        add_submenu_page(
            'learn_press',
            __('Carnet de Notes', 'eia-lms-core'),
            __('Carnet de Notes', 'eia-lms-core'),
            'edit_posts',
            'eia-gradebook',
            array($this, 'render_gradebook_page')
        );
    }

    /**
     * Render gradebook page
     */
    public function render_gradebook_page() {
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/admin-gradebook.php';
    }

    /**
     * Get course students
     */
    private function get_course_students($course_id) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT DISTINCT u.ID, u.display_name, u.user_email
            FROM {$wpdb->users} u
            INNER JOIN {$wpdb->prefix}learnpress_user_items ui ON u.ID = ui.user_id
            WHERE ui.item_id = %d
            AND ui.item_type = 'lp_course'
            ORDER BY u.display_name ASC
        ", $course_id);

        return $wpdb->get_results($query);
    }

    /**
     * Get student grades
     */
    public function get_student_grades($course_id, $user_id) {
        $course = learn_press_get_course($course_id);

        // Get progress
        $progress = $this->get_course_progress($course_id, $user_id);

        // Get quiz average
        $quiz_avg = $this->get_quiz_average($course_id, $user_id);

        // Get manual grade (for assignments, participation, etc.)
        $manual_grade = $this->get_manual_grade($course_id, $user_id);

        // Calculate final grade (weighted)
        $quiz_weight = 70; // 70% quiz
        $manual_weight = 30; // 30% manual (assignments)
        $final_grade = ($quiz_avg * $quiz_weight / 100) + ($manual_grade * $manual_weight / 100);

        // Determine status
        $status = $this->determine_status($progress, $final_grade);

        return array(
            'progress' => $progress,
            'quiz_avg' => $quiz_avg,
            'manual_grade' => $manual_grade,
            'final_grade' => round($final_grade, 2),
            'status' => $status,
        );
    }

    /**
     * Get course progress
     */
    private function get_course_progress($course_id, $user_id) {
        $course = learn_press_get_course($course_id);
        $user = learn_press_get_user($user_id);

        if (!$course || !$user) {
            return 0;
        }

        $course_data = $user->get_course_data($course_id);
        return $course_data ? $course_data->get_percent_result() : 0;
    }

    /**
     * Get quiz average
     */
    private function get_quiz_average($course_id, $user_id) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT AVG(g.grade / g.max_grade * 100) as avg_grade
            FROM {$wpdb->prefix}eia_gradebook g
            WHERE g.course_id = %d
            AND g.user_id = %d
            AND g.item_type = 'lp_quiz'
        ", $course_id, $user_id);

        $result = $wpdb->get_var($query);
        return $result ? round($result, 2) : 0;
    }

    /**
     * Get manual grade
     */
    private function get_manual_grade($course_id, $user_id) {
        return (float) get_user_meta($user_id, '_eia_manual_grade_' . $course_id, true);
    }

    /**
     * Determine status
     */
    private function determine_status($progress, $final_grade) {
        $passing_grade = get_option('eia_lms_passing_grade', 70);

        if ($progress >= 100) {
            return $final_grade >= $passing_grade ? 'completed' : 'failed';
        } elseif ($progress > 0) {
            return 'in-progress';
        } else {
            return 'pending';
        }
    }

    /**
     * Get status label
     */
    private function get_status_label($status) {
        $labels = array(
            'completed' => __('Réussi', 'eia-lms-core'),
            'in-progress' => __('En cours', 'eia-lms-core'),
            'failed' => __('Échoué', 'eia-lms-core'),
            'pending' => __('En attente', 'eia-lms-core'),
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }

    /**
     * Get grade color
     */
    private function get_grade_color($grade) {
        if ($grade >= 90) return '#4CAF50';
        if ($grade >= 80) return '#8BC34A';
        if ($grade >= 70) return '#F59E0B';
        if ($grade >= 60) return '#FF9800';
        return '#f44336';
    }

    /**
     * AJAX: Get student grades
     */
    public function ajax_get_student_grades() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

        if (!$course_id || !$user_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        $grades = $this->get_detailed_grades($course_id, $user_id);

        wp_send_json_success($grades);
    }

    /**
     * Get detailed grades
     */
    private function get_detailed_grades($course_id, $user_id) {
        global $wpdb;

        $query = $wpdb->prepare("
            SELECT *
            FROM {$wpdb->prefix}eia_gradebook
            WHERE course_id = %d
            AND user_id = %d
            ORDER BY graded_date DESC
        ", $course_id, $user_id);

        return $wpdb->get_results($query);
    }

    /**
     * AJAX: Save manual grade
     */
    public function ajax_save_manual_grade() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $grade = isset($_POST['grade']) ? floatval($_POST['grade']) : 0;

        if (!$course_id || !$user_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Save manual grade
        update_user_meta($user_id, '_eia_manual_grade_' . $course_id, $grade);

        // Recalculate final grade
        $grades = $this->get_student_grades($course_id, $user_id);

        wp_send_json_success(array(
            'message' => __('Note enregistrée', 'eia-lms-core'),
            'final_grade' => $grades['final_grade'],
            'status' => $grades['status'],
        ));
    }

    /**
     * AJAX: Export gradebook
     */
    public function ajax_export_gradebook() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

        if (!$course_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        $csv = $this->generate_gradebook_csv($course_id);

        wp_send_json_success(array(
            'csv' => $csv,
            'filename' => 'gradebook-' . $course_id . '-' . date('Y-m-d') . '.csv',
        ));
    }

    /**
     * Generate gradebook CSV
     */
    private function generate_gradebook_csv($course_id) {
        $students = $this->get_course_students($course_id);

        $csv = "Étudiant,Email,Progression,Quiz,Travaux,Note Finale,Statut\n";

        foreach ($students as $student) {
            $grades = $this->get_student_grades($course_id, $student->ID);

            $csv .= sprintf(
                '"%s","%s",%s%%,%s%%,%s%%,%s%%,%s' . "\n",
                $student->display_name,
                $student->user_email,
                $grades['progress'],
                $grades['quiz_avg'],
                $grades['manual_grade'],
                $grades['final_grade'],
                $this->get_status_label($grades['status'])
            );
        }

        return $csv;
    }

    /**
     * Record quiz grade automatically
     */
    public function record_quiz_grade($quiz_id, $user_id, $result) {
        global $wpdb;

        $course_id = get_post_meta($quiz_id, '_lp_course', true);

        if (!$course_id) {
            return;
        }

        // Insert grade into gradebook
        $wpdb->insert(
            $wpdb->prefix . 'eia_gradebook',
            array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'item_id' => $quiz_id,
                'item_type' => 'lp_quiz',
                'grade' => $result['mark'],
                'max_grade' => $result['mark_max'],
                'graded_by' => 0, // Auto-graded
                'graded_date' => current_time('mysql'),
                'notes' => 'Auto-graded by system',
            ),
            array('%d', '%d', '%d', '%s', '%f', '%f', '%d', '%s', '%s')
        );
    }
}
?>