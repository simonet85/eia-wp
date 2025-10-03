<?php
/**
 * Quiz Extended Class
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EIA_Quiz_Extended {

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
        // Add custom question types metabox
        add_action('add_meta_boxes', array($this, 'add_question_types_metabox'));

        // Save quiz extended data
        add_action('save_post_lp_quiz', array($this, 'save_quiz_extended_data'));

        // Add custom question types
        add_filter('learn-press/quiz/question-types', array($this, 'add_custom_question_types'));

        // AJAX: Validate essay answer
        add_action('wp_ajax_eia_validate_essay', array($this, 'ajax_validate_essay'));

        // AJAX: Save essay grade
        add_action('wp_ajax_eia_save_essay_grade', array($this, 'ajax_save_essay_grade'));

        // Custom quiz result display
        add_filter('learn-press/quiz/result', array($this, 'custom_quiz_result'), 10, 2);
    }

    /**
     * Add question types metabox
     */
    public function add_question_types_metabox() {
        add_meta_box(
            'eia-quiz-extended',
            __('Types de Questions Étendus', 'eia-lms-core'),
            array($this, 'render_question_types_metabox'),
            'lp_quiz',
            'side',
            'default'
        );
    }

    /**
     * Render question types metabox
     */
    public function render_question_types_metabox($post) {
        wp_nonce_field('eia_quiz_extended_nonce', 'eia_quiz_extended_nonce');

        $quiz_id = $post->ID;
        $enable_essay = get_post_meta($quiz_id, '_eia_enable_essay', true);
        $enable_matching = get_post_meta($quiz_id, '_eia_enable_matching', true);
        $enable_ordering = get_post_meta($quiz_id, '_eia_enable_ordering', true);
        $time_limit = get_post_meta($quiz_id, '_eia_time_limit', true);
        $show_hints = get_post_meta($quiz_id, '_eia_show_hints', true);
        $randomize = get_post_meta($quiz_id, '_eia_randomize_questions', true);

        ?>
        <div class="eia-quiz-extended-options">
            <p>
                <label>
                    <input type="checkbox" name="eia_enable_essay" value="1" <?php checked($enable_essay, '1'); ?>>
                    <?php _e('Activer questions à développement', 'eia-lms-core'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="eia_enable_matching" value="1" <?php checked($enable_matching, '1'); ?>>
                    <?php _e('Activer questions d\'appariement', 'eia-lms-core'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="eia_enable_ordering" value="1" <?php checked($enable_ordering, '1'); ?>>
                    <?php _e('Activer questions d\'ordre', 'eia-lms-core'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="eia_randomize_questions" value="1" <?php checked($randomize, '1'); ?>>
                    <?php _e('Mélanger les questions', 'eia-lms-core'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="eia_show_hints" value="1" <?php checked($show_hints, '1'); ?>>
                    <?php _e('Afficher les indices', 'eia-lms-core'); ?>
                </label>
            </p>

            <p>
                <label>
                    <?php _e('Limite de temps (minutes)', 'eia-lms-core'); ?>
                    <input type="number" name="eia_time_limit" value="<?php echo esc_attr($time_limit); ?>" min="0" style="width: 100%;">
                </label>
            </p>

            <p class="description">
                <?php _e('Options avancées pour ce quiz.', 'eia-lms-core'); ?>
            </p>
        </div>

        <style>
        .eia-quiz-extended-options p {
            margin-bottom: 10px;
        }
        .eia-quiz-extended-options label {
            display: block;
        }
        .eia-quiz-extended-options input[type="number"] {
            margin-top: 5px;
        }
        </style>
        <?php
    }

    /**
     * Save quiz extended data
     */
    public function save_quiz_extended_data($post_id) {
        // Verify nonce
        if (!isset($_POST['eia_quiz_extended_nonce']) || !wp_verify_nonce($_POST['eia_quiz_extended_nonce'], 'eia_quiz_extended_nonce')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save options
        $options = array('eia_enable_essay', 'eia_enable_matching', 'eia_enable_ordering', 'eia_randomize_questions', 'eia_show_hints');

        foreach ($options as $option) {
            if (isset($_POST[$option])) {
                update_post_meta($post_id, '_' . $option, '1');
            } else {
                delete_post_meta($post_id, '_' . $option);
            }
        }

        // Save time limit
        if (isset($_POST['eia_time_limit'])) {
            update_post_meta($post_id, '_eia_time_limit', intval($_POST['eia_time_limit']));
        }
    }

    /**
     * Add custom question types
     */
    public function add_custom_question_types($types) {
        $types['essay'] = array(
            'title' => __('Question à développement', 'eia-lms-core'),
            'icon' => 'dashicons-edit',
        );

        $types['matching'] = array(
            'title' => __('Appariement', 'eia-lms-core'),
            'icon' => 'dashicons-networking',
        );

        $types['ordering'] = array(
            'title' => __('Mise en ordre', 'eia-lms-core'),
            'icon' => 'dashicons-sort',
        );

        return $types;
    }

    /**
     * AJAX: Validate essay answer
     */
    public function ajax_validate_essay() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $answer = isset($_POST['answer']) ? sanitize_textarea_field($_POST['answer']) : '';
        $user_id = get_current_user_id();

        if (!$question_id || !$user_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Save essay answer for manual grading
        update_user_meta($user_id, '_eia_essay_answer_' . $question_id, $answer);
        update_user_meta($user_id, '_eia_essay_submitted_' . $question_id, current_time('mysql'));

        wp_send_json_success(array(
            'message' => __('Réponse enregistrée. En attente de correction manuelle.', 'eia-lms-core')
        ));
    }

    /**
     * AJAX: Save essay grade
     */
    public function ajax_save_essay_grade() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        $question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $grade = isset($_POST['grade']) ? floatval($_POST['grade']) : 0;
        $feedback = isset($_POST['feedback']) ? sanitize_textarea_field($_POST['feedback']) : '';

        if (!$question_id || !$user_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Save grade
        update_user_meta($user_id, '_eia_essay_grade_' . $question_id, $grade);
        update_user_meta($user_id, '_eia_essay_feedback_' . $question_id, $feedback);
        update_user_meta($user_id, '_eia_essay_graded_by_' . $question_id, get_current_user_id());
        update_user_meta($user_id, '_eia_essay_graded_date_' . $question_id, current_time('mysql'));

        wp_send_json_success(array(
            'message' => __('Note enregistrée avec succès', 'eia-lms-core')
        ));
    }

    /**
     * Custom quiz result display
     */
    public function custom_quiz_result($result, $quiz_id) {
        $user_id = get_current_user_id();

        // Check for pending essay questions
        $pending_essays = $this->get_pending_essays($quiz_id, $user_id);

        if ($pending_essays > 0) {
            $result['message'] = sprintf(
                __('Quiz terminé. %d question(s) à développement en attente de correction.', 'eia-lms-core'),
                $pending_essays
            );
            $result['status'] = 'pending';
        }

        return $result;
    }

    /**
     * Get pending essays count
     */
    private function get_pending_essays($quiz_id, $user_id) {
        // Get all questions for this quiz
        $quiz = learn_press_get_quiz($quiz_id);
        if (!$quiz) {
            return 0;
        }

        $questions = $quiz->get_questions();
        $pending = 0;

        foreach ($questions as $question) {
            $question_id = $question->get_id();
            $submitted = get_user_meta($user_id, '_eia_essay_submitted_' . $question_id, true);
            $graded = get_user_meta($user_id, '_eia_essay_grade_' . $question_id, true);

            if ($submitted && !$graded) {
                $pending++;
            }
        }

        return $pending;
    }

    /**
     * Get essay answer for grading
     */
    public function get_essay_answer($question_id, $user_id) {
        return get_user_meta($user_id, '_eia_essay_answer_' . $question_id, true);
    }

    /**
     * Get essay grade
     */
    public function get_essay_grade($question_id, $user_id) {
        return array(
            'grade' => get_user_meta($user_id, '_eia_essay_grade_' . $question_id, true),
            'feedback' => get_user_meta($user_id, '_eia_essay_feedback_' . $question_id, true),
            'graded_by' => get_user_meta($user_id, '_eia_essay_graded_by_' . $question_id, true),
            'graded_date' => get_user_meta($user_id, '_eia_essay_graded_date_' . $question_id, true),
        );
    }
}
?>