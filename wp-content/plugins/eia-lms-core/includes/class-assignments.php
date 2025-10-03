<?php
/**
 * EIA Assignments Management
 *
 * Gestion complète des devoirs et soumissions pour les étudiants
 * - Upload de fichiers
 * - Édition en ligne
 * - Notes et feedback
 * - Resoumission possible
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Assignments {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->init_hooks();
    }

    private function init_hooks() {
        // Register custom post type
        add_action('init', array($this, 'register_post_type'));

        // Fix query var parsing for single assignments
        add_action('pre_get_posts', array($this, 'fix_assignment_query'));

        // Add meta boxes
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post_lp_assignment', array($this, 'save_assignment_meta'));

        // Handle submissions
        add_action('wp_ajax_eia_submit_assignment', array($this, 'handle_submission'));
        add_action('wp_ajax_eia_grade_submission', array($this, 'handle_grading'));
        add_action('wp_ajax_eia_get_submission_details', array($this, 'get_submission_details'));

        // Shortcodes
        add_shortcode('eia_assignment_submit', array($this, 'render_submission_form'));
        add_shortcode('eia_my_assignments', array($this, 'render_student_assignments'));
        add_shortcode('eia_assignment_submissions', array($this, 'render_instructor_submissions'));

        // Admin columns
        add_filter('manage_lp_assignment_posts_columns', array($this, 'assignment_columns'));
        add_action('manage_lp_assignment_posts_custom_column', array($this, 'assignment_column_content'), 10, 2);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register Assignment custom post type
     */
    public function register_post_type() {
        $labels = array(
            'name'               => 'Devoirs',
            'singular_name'      => 'Devoir',
            'menu_name'          => 'Devoirs',
            'add_new'            => 'Ajouter un devoir',
            'add_new_item'       => 'Ajouter un nouveau devoir',
            'edit_item'          => 'Modifier le devoir',
            'new_item'           => 'Nouveau devoir',
            'view_item'          => 'Voir le devoir',
            'search_items'       => 'Rechercher des devoirs',
            'not_found'          => 'Aucun devoir trouvé',
            'not_found_in_trash' => 'Aucun devoir dans la corbeille',
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => 'learn_press',
            'query_var'           => 'lp_assignment',
            'rewrite'             => array('slug' => 'assignment'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array('title', 'editor', 'author', 'thumbnail'),
            'show_in_rest'        => true,
            'menu_icon'           => 'dashicons-media-document',
        );

        register_post_type('lp_assignment', $args);
    }

    /**
     * Fix assignment query to use correct post type
     *
     * WordPress doesn't automatically set post_type when using custom query vars
     * This function ensures that when lp_assignment query var is present,
     * the query searches for lp_assignment post type instead of regular posts
     */
    public function fix_assignment_query($query) {
        // Only run on main query and frontend
        if (is_admin() || !$query->is_main_query()) {
            return;
        }

        // Check if this is an assignment query
        if (isset($query->query_vars['lp_assignment']) && !empty($query->query_vars['lp_assignment'])) {
            // Force post type to lp_assignment
            $query->set('post_type', 'lp_assignment');

            // Set the name parameter for the query
            $query->set('name', $query->query_vars['lp_assignment']);

            // Make sure it's treated as a single post query
            $query->is_single = true;
            $query->is_singular = true;
        }
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'eia_assignment_settings',
            'Paramètres du devoir',
            array($this, 'render_assignment_settings_meta_box'),
            'lp_assignment',
            'normal',
            'high'
        );

        add_meta_box(
            'eia_assignment_course',
            'Cours associé',
            array($this, 'render_course_meta_box'),
            'lp_assignment',
            'side',
            'default'
        );
    }

    /**
     * Render assignment settings meta box
     */
    public function render_assignment_settings_meta_box($post) {
        wp_nonce_field('eia_assignment_settings', 'eia_assignment_settings_nonce');

        $due_date = get_post_meta($post->ID, '_assignment_due_date', true);
        $max_grade = get_post_meta($post->ID, '_assignment_max_grade', true) ?: 100;
        $allow_resubmission = get_post_meta($post->ID, '_assignment_allow_resubmission', true) ?: 'yes';
        $submission_type = get_post_meta($post->ID, '_assignment_submission_type', true) ?: 'file';
        $max_file_size = get_post_meta($post->ID, '_assignment_max_file_size', true) ?: 10;
        $allow_late_submission = get_post_meta($post->ID, '_assignment_allow_late_submission', true) ?: 'no';
        ?>
        <table class="form-table">
            <tr>
                <th><label for="assignment_due_date">Date limite</label></th>
                <td>
                    <input type="datetime-local"
                           id="assignment_due_date"
                           name="assignment_due_date"
                           value="<?php echo esc_attr($due_date); ?>"
                           class="regular-text">
                    <p class="description">Date et heure limite pour soumettre le devoir</p>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_max_grade">Note maximale</label></th>
                <td>
                    <input type="number"
                           id="assignment_max_grade"
                           name="assignment_max_grade"
                           value="<?php echo esc_attr($max_grade); ?>"
                           min="0"
                           step="0.01"
                           class="small-text">
                    <p class="description">Note maximale pour ce devoir</p>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_submission_type">Type de soumission</label></th>
                <td>
                    <select id="assignment_submission_type" name="assignment_submission_type">
                        <option value="file" <?php selected($submission_type, 'file'); ?>>Upload de fichier</option>
                        <option value="text" <?php selected($submission_type, 'text'); ?>>Texte en ligne</option>
                        <option value="both" <?php selected($submission_type, 'both'); ?>>Les deux</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_max_file_size">Taille max fichier (MB)</label></th>
                <td>
                    <input type="number"
                           id="assignment_max_file_size"
                           name="assignment_max_file_size"
                           value="<?php echo esc_attr($max_file_size); ?>"
                           min="1"
                           max="100"
                           class="small-text">
                    <p class="description">Taille maximale des fichiers en Mo</p>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_allow_resubmission">Permettre la resoumission</label></th>
                <td>
                    <input type="checkbox"
                           id="assignment_allow_resubmission"
                           name="assignment_allow_resubmission"
                           value="yes"
                           <?php checked($allow_resubmission, 'yes'); ?>>
                    <label for="assignment_allow_resubmission">Permettre aux étudiants de soumettre à nouveau</label>
                </td>
            </tr>
            <tr>
                <th><label for="assignment_allow_late_submission">Autoriser soumission tardive</label></th>
                <td>
                    <input type="checkbox"
                           id="assignment_allow_late_submission"
                           name="assignment_allow_late_submission"
                           value="yes"
                           <?php checked($allow_late_submission, 'yes'); ?>>
                    <label for="assignment_allow_late_submission">Permettre la soumission après la date limite</label>
                    <p class="description" style="color: #F59E0B; font-weight: 600;"><i class="fas fa-exclamation-triangle"></i> Si activé, les étudiants pourront soumettre même après expiration du délai</p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Render course selection meta box
     */
    public function render_course_meta_box($post) {
        wp_nonce_field('eia_assignment_course', 'eia_assignment_course_nonce');

        $course_id = get_post_meta($post->ID, '_assignment_course_id', true);

        $courses = get_posts(array(
            'post_type'      => 'lp_course',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
        ?>
        <p>
            <label for="assignment_course_id"><strong>Sélectionner un cours</strong></label>
            <select id="assignment_course_id" name="assignment_course_id" class="widefat">
                <option value="">-- Sélectionner --</option>
                <?php foreach ($courses as $course) : ?>
                    <option value="<?php echo $course->ID; ?>" <?php selected($course_id, $course->ID); ?>>
                        <?php echo esc_html($course->post_title); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
    }

    /**
     * Save assignment meta
     */
    public function save_assignment_meta($post_id) {
        // Check nonces
        if (!isset($_POST['eia_assignment_settings_nonce']) ||
            !wp_verify_nonce($_POST['eia_assignment_settings_nonce'], 'eia_assignment_settings')) {
            return;
        }

        if (!isset($_POST['eia_assignment_course_nonce']) ||
            !wp_verify_nonce($_POST['eia_assignment_course_nonce'], 'eia_assignment_course')) {
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

        // Save settings
        if (isset($_POST['assignment_due_date'])) {
            update_post_meta($post_id, '_assignment_due_date', sanitize_text_field($_POST['assignment_due_date']));
        }

        if (isset($_POST['assignment_max_grade'])) {
            update_post_meta($post_id, '_assignment_max_grade', floatval($_POST['assignment_max_grade']));
        }

        if (isset($_POST['assignment_submission_type'])) {
            update_post_meta($post_id, '_assignment_submission_type', sanitize_text_field($_POST['assignment_submission_type']));
        }

        if (isset($_POST['assignment_max_file_size'])) {
            update_post_meta($post_id, '_assignment_max_file_size', intval($_POST['assignment_max_file_size']));
        }

        $allow_resubmission = isset($_POST['assignment_allow_resubmission']) ? 'yes' : 'no';
        update_post_meta($post_id, '_assignment_allow_resubmission', $allow_resubmission);

        $allow_late_submission = isset($_POST['assignment_allow_late_submission']) ? 'yes' : 'no';
        update_post_meta($post_id, '_assignment_allow_late_submission', $allow_late_submission);

        if (isset($_POST['assignment_course_id'])) {
            update_post_meta($post_id, '_assignment_course_id', intval($_POST['assignment_course_id']));
        }
    }

    /**
     * Handle assignment submission
     */
    public function handle_submission() {
        check_ajax_referer('eia-assignments-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Vous devez être connecté'));
        }

        $assignment_id = intval($_POST['assignment_id']);
        $user_id = get_current_user_id();

        // Check deadline (only if late submissions are not allowed)
        $due_date = get_post_meta($assignment_id, '_assignment_due_date', true);
        $allow_late_submission = get_post_meta($assignment_id, '_assignment_allow_late_submission', true);

        if ($due_date && strtotime($due_date) < current_time('timestamp') && $allow_late_submission !== 'yes') {
            wp_send_json_error(array('message' => 'La date limite de soumission est dépassée'));
        }

        // Check if resubmission is allowed
        $previous_submission = $this->get_user_submission($assignment_id, $user_id);
        $allow_resubmission = get_post_meta($assignment_id, '_assignment_allow_resubmission', true);

        // Check if already graded
        if ($previous_submission && $previous_submission->status === 'graded') {
            wp_send_json_error(array('message' => 'Ce devoir a déjà été noté. Vous ne pouvez plus le modifier.'));
        }

        if ($previous_submission && $allow_resubmission !== 'yes') {
            wp_send_json_error(array('message' => 'Resoumission non autorisée'));
        }

        // Create submission
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';

        $submission_data = array(
            'assignment_id' => $assignment_id,
            'user_id'       => $user_id,
            'submission_text' => isset($_POST['submission_text']) ? wp_kses_post($_POST['submission_text']) : '',
            'submitted_date' => current_time('mysql'),
            'status'        => 'submitted',
        );

        // Handle file upload
        if (!empty($_FILES['submission_file'])) {
            $uploaded_file = $this->handle_file_upload($_FILES['submission_file'], $assignment_id);
            if (is_wp_error($uploaded_file)) {
                wp_send_json_error(array('message' => $uploaded_file->get_error_message()));
            }
            $submission_data['file_url'] = $uploaded_file;
        }

        $wpdb->insert($table_name, $submission_data);
        $submission_id = $wpdb->insert_id;

        // Send notification to instructor
        $notifications = EIA_Notifications::get_instance();
        $notifications->notify_assignment_submitted($submission_id);

        wp_send_json_success(array(
            'message' => 'Devoir soumis avec succès',
            'submission_id' => $submission_id
        ));
    }

    /**
     * Handle file upload
     */
    private function handle_file_upload($file, $assignment_id) {
        $max_size = get_post_meta($assignment_id, '_assignment_max_file_size', true) * 1048576; // Convert MB to bytes

        if ($file['size'] > $max_size) {
            return new WP_Error('file_too_large', 'Le fichier est trop volumineux');
        }

        $allowed_types = array('pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'zip');
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            return new WP_Error('invalid_file_type', 'Type de fichier non autorisé');
        }

        require_once(ABSPATH . 'wp-admin/includes/file.php');

        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) {
            return new WP_Error('upload_error', $upload['error']);
        }

        return $upload['url'];
    }

    /**
     * Handle grading
     */
    public function handle_grading() {
        // Accept both nonces for compatibility
        $nonce_valid = false;
        if (isset($_POST['nonce'])) {
            $nonce_valid = wp_verify_nonce($_POST['nonce'], 'eia-assignments-nonce') ||
                          wp_verify_nonce($_POST['nonce'], 'eia_grading_nonce');
        }

        if (!$nonce_valid) {
            wp_send_json_error(array('message' => 'Nonce invalide'));
        }

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
        }

        $submission_id = intval($_POST['submission_id']);
        $grade = floatval($_POST['grade']);
        $feedback = isset($_POST['feedback']) ? wp_kses_post($_POST['feedback']) : '';

        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';

        $updated = $wpdb->update(
            $table_name,
            array(
                'grade'      => $grade,
                'feedback'   => $feedback,
                'graded_by'  => get_current_user_id(),
                'graded_date' => current_time('mysql'),
                'status'     => 'graded',
            ),
            array('id' => $submission_id),
            array('%f', '%s', '%d', '%s', '%s'),
            array('%d')
        );

        if ($updated === false) {
            wp_send_json_error(array('message' => 'Erreur lors de la notation'));
        }

        // Send notification to student
        $notifications = EIA_Notifications::get_instance();
        $notifications->notify_assignment_graded($submission_id, $grade, $feedback);

        wp_send_json_success(array('message' => 'Notation enregistrée'));
    }

    /**
     * Get submission details for grading modal
     */
    public function get_submission_details() {
        check_ajax_referer('eia_grading_nonce', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(array('message' => 'Permission refusée'));
        }

        $submission_id = intval($_POST['submission_id']);

        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';

        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            wp_send_json_error(array('message' => 'Soumission introuvable'));
        }

        wp_send_json_success(array(
            'submission_text' => $submission->submission_text,
            'file_url' => $submission->file_url,
            'grade' => $submission->grade,
            'feedback' => $submission->feedback,
            'submitted_date' => $submission->submitted_date,
            'status' => $submission->status
        ));
    }

    /**
     * Get user submission
     */
    private function get_user_submission($assignment_id, $user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';

        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE assignment_id = %d AND user_id = %d ORDER BY submitted_date DESC LIMIT 1",
            $assignment_id,
            $user_id
        ));
    }

    /**
     * Render submission form shortcode
     */
    public function render_submission_form($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $assignment_id = intval($atts['id']);

        if (!$assignment_id || !is_user_logged_in()) {
            return '<p>Vous devez être connecté pour soumettre un devoir.</p>';
        }

        $assignment = get_post($assignment_id);
        if (!$assignment || $assignment->post_type !== 'lp_assignment') {
            return '<p>Devoir non trouvé.</p>';
        }

        $user_id = get_current_user_id();
        $submission = $this->get_user_submission($assignment_id, $user_id);
        $submission_type = get_post_meta($assignment_id, '_assignment_submission_type', true);
        $due_date = get_post_meta($assignment_id, '_assignment_due_date', true);
        $allow_resubmission = get_post_meta($assignment_id, '_assignment_allow_resubmission', true);

        ob_start();
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/assignment-submission-form.php';
        return ob_get_clean();
    }

    /**
     * Render student assignments list
     */
    public function render_student_assignments() {
        if (!is_user_logged_in()) {
            return '<p>Connectez-vous pour voir vos devoirs.</p>';
        }

        $user_id = get_current_user_id();

        // Get enrolled courses
        global $wpdb;
        $enrolled_courses = $wpdb->get_col($wpdb->prepare(
            "SELECT item_id FROM {$wpdb->prefix}learnpress_user_items
             WHERE user_id = %d AND item_type = 'lp_course'",
            $user_id
        ));

        if (empty($enrolled_courses)) {
            return '<p>Vous n\'êtes inscrit à aucun cours.</p>';
        }

        // Get assignments for enrolled courses
        $assignments = get_posts(array(
            'post_type'      => 'lp_assignment',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_assignment_course_id',
                    'value'   => $enrolled_courses,
                    'compare' => 'IN',
                ),
            ),
        ));

        ob_start();
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/student-assignments-list.php';
        return ob_get_clean();
    }

    /**
     * Render instructor submissions view
     */
    public function render_instructor_submissions($atts) {
        if (!current_user_can('edit_posts')) {
            return '<p>Accès refusé.</p>';
        }

        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $assignment_id = intval($atts['id']);

        // Set assignment_id for the template
        $_GET['assignment_id'] = $assignment_id;

        ob_start();
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/instructor-grading.php';
        return ob_get_clean();
    }

    /**
     * Admin columns
     */
    public function assignment_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['course'] = 'Cours';
        $new_columns['due_date'] = 'Date limite';
        $new_columns['submissions'] = 'Soumissions';
        $new_columns['author'] = $columns['author'];
        $new_columns['date'] = $columns['date'];

        return $new_columns;
    }

    public function assignment_column_content($column, $post_id) {
        switch ($column) {
            case 'course':
                $course_id = get_post_meta($post_id, '_assignment_course_id', true);
                if ($course_id) {
                    echo get_the_title($course_id);
                } else {
                    echo '—';
                }
                break;

            case 'due_date':
                $due_date = get_post_meta($post_id, '_assignment_due_date', true);
                if ($due_date) {
                    echo date('d/m/Y H:i', strtotime($due_date));
                } else {
                    echo '—';
                }
                break;

            case 'submissions':
                global $wpdb;
                $count = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}eia_assignment_submissions WHERE assignment_id = %d",
                    $post_id
                ));
                echo $count ?: '0';
                break;
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        if (is_singular('lp_assignment') || is_page()) {
            wp_enqueue_style('eia-assignments', EIA_LMS_CORE_PLUGIN_URL . 'assets/css/assignments.css', array(), EIA_LMS_CORE_VERSION);
            wp_enqueue_script('eia-assignments', EIA_LMS_CORE_PLUGIN_URL . 'assets/js/assignments.js', array('jquery'), EIA_LMS_CORE_VERSION, true);

            wp_localize_script('eia-assignments', 'eiaAssignments', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('eia-assignments-nonce'),
            ));
        }
    }

    /**
     * Create database table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            assignment_id bigint(20) NOT NULL,
            user_id bigint(20) NOT NULL,
            submission_text longtext,
            file_url varchar(500),
            submitted_date datetime NOT NULL,
            grade float DEFAULT NULL,
            feedback text,
            graded_by bigint(20) DEFAULT NULL,
            graded_date datetime DEFAULT NULL,
            status varchar(20) DEFAULT 'submitted',
            PRIMARY KEY (id),
            KEY assignment_id (assignment_id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
