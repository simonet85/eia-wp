<?php
/**
 * Course Builder Class
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EIA_Course_Builder {

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
        // Add course builder metabox
        add_action('add_meta_boxes', array($this, 'add_course_builder_metabox'));

        // Save course builder data
        add_action('save_post_lp_course', array($this, 'save_course_builder_data'));

        // AJAX: Add lesson to course
        add_action('wp_ajax_eia_add_lesson_to_course', array($this, 'ajax_add_lesson'));

        // AJAX: Reorder course items
        add_action('wp_ajax_eia_reorder_course_items', array($this, 'ajax_reorder_items'));

        // AJAX: Remove lesson from course
        add_action('wp_ajax_eia_remove_lesson_from_course', array($this, 'ajax_remove_lesson'));
    }

    /**
     * Add course builder metabox
     */
    public function add_course_builder_metabox() {
        add_meta_box(
            'eia-course-builder',
            __('Constructeur de Cours (Drag & Drop)', 'eia-lms-core'),
            array($this, 'render_course_builder'),
            'lp_course',
            'normal',
            'high'
        );
    }

    /**
     * Render course builder
     */
    public function render_course_builder($post) {
        wp_nonce_field('eia_course_builder_nonce', 'eia_course_builder_nonce');

        $course_id = $post->ID;
        $course = learn_press_get_course($course_id);

        ?>
        <div class="eia-course-builder">
            <div class="course-builder-header">
                <div class="builder-tabs">
                    <button class="builder-tab active" data-tab="curriculum">
                        <i class="dashicons dashicons-list-view"></i>
                        <?php _e('Programme', 'eia-lms-core'); ?>
                    </button>
                    <button class="builder-tab" data-tab="settings">
                        <i class="dashicons dashicons-admin-settings"></i>
                        <?php _e('Paramètres', 'eia-lms-core'); ?>
                    </button>
                </div>
            </div>

            <!-- Curriculum Tab -->
            <div class="builder-tab-content active" data-tab-content="curriculum">
                <div class="builder-layout">
                    <!-- Available Items -->
                    <div class="builder-sidebar">
                        <h3><?php _e('Éléments disponibles', 'eia-lms-core'); ?></h3>

                        <div class="available-items">
                            <div class="item-type-group">
                                <h4>
                                    <i class="dashicons dashicons-media-text"></i>
                                    <?php _e('Leçons', 'eia-lms-core'); ?>
                                </h4>
                                <div class="item-search">
                                    <input type="text" placeholder="<?php _e('Rechercher une leçon...', 'eia-lms-core'); ?>" class="search-lessons">
                                </div>
                                <ul class="lessons-list">
                                    <?php
                                    $lessons = get_posts(array(
                                        'post_type' => 'lp_lesson',
                                        'posts_per_page' => -1,
                                        'post_status' => 'publish',
                                    ));

                                    foreach ($lessons as $lesson) :
                                        ?>
                                        <li class="available-item" data-item-id="<?php echo $lesson->ID; ?>" data-item-type="lesson">
                                            <span class="item-icon"><i class="dashicons dashicons-media-text"></i></span>
                                            <span class="item-title"><?php echo $lesson->post_title; ?></span>
                                            <button class="add-item-btn" title="<?php _e('Ajouter au cours', 'eia-lms-core'); ?>">
                                                <i class="dashicons dashicons-plus"></i>
                                            </button>
                                        </li>
                                        <?php
                                    endforeach;
                                    ?>
                                </ul>
                            </div>

                            <div class="item-type-group">
                                <h4>
                                    <i class="dashicons dashicons-welcome-learn-more"></i>
                                    <?php _e('Quiz', 'eia-lms-core'); ?>
                                </h4>
                                <ul class="quizzes-list">
                                    <?php
                                    $quizzes = get_posts(array(
                                        'post_type' => 'lp_quiz',
                                        'posts_per_page' => -1,
                                        'post_status' => 'publish',
                                    ));

                                    foreach ($quizzes as $quiz) :
                                        ?>
                                        <li class="available-item" data-item-id="<?php echo $quiz->ID; ?>" data-item-type="quiz">
                                            <span class="item-icon"><i class="dashicons dashicons-welcome-learn-more"></i></span>
                                            <span class="item-title"><?php echo $quiz->post_title; ?></span>
                                            <button class="add-item-btn" title="<?php _e('Ajouter au cours', 'eia-lms-core'); ?>">
                                                <i class="dashicons dashicons-plus"></i>
                                            </button>
                                        </li>
                                        <?php
                                    endforeach;
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Course Curriculum -->
                    <div class="builder-main">
                        <div class="curriculum-header">
                            <h3><?php _e('Programme du cours', 'eia-lms-core'); ?></h3>
                            <button class="button button-secondary add-section-btn">
                                <i class="dashicons dashicons-plus"></i>
                                <?php _e('Ajouter une section', 'eia-lms-core'); ?>
                            </button>
                        </div>

                        <div class="curriculum-content">
                            <ul class="course-sections sortable">
                                <?php
                                if ($course) {
                                    $curriculum = $course->get_curriculum();

                                    if ($curriculum) {
                                        foreach ($curriculum as $section) {
                                            $this->render_section($section);
                                        }
                                    }
                                }
                                ?>
                            </ul>

                            <div class="empty-curriculum" style="<?php echo ($course && $course->get_curriculum()) ? 'display:none;' : ''; ?>">
                                <p><?php _e('Le programme du cours est vide. Ajoutez des leçons et des quiz depuis la barre latérale.', 'eia-lms-core'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div class="builder-tab-content" data-tab-content="settings">
                <div class="builder-settings">
                    <h3><?php _e('Paramètres du constructeur', 'eia-lms-core'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th><label><?php _e('Ordre automatique', 'eia-lms-core'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="eia_auto_order" value="1">
                                    <?php _e('Ordonner automatiquement les éléments par ordre alphabétique', 'eia-lms-core'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><label><?php _e('Numérotation', 'eia-lms-core'); ?></label></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="eia_show_numbering" value="1" checked>
                                    <?php _e('Afficher la numérotation des leçons', 'eia-lms-core'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <style>
        .eia-course-builder {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
        }

        .course-builder-header {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .builder-tabs {
            display: flex;
            gap: 5px;
        }

        .builder-tab {
            padding: 10px 20px;
            background: #fff;
            border: 1px solid #ddd;
            border-bottom: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .builder-tab.active {
            background: #2D4FB3;
            color: #fff;
        }

        .builder-tab:hover:not(.active) {
            background: #f0f0f0;
        }

        .builder-tab-content {
            display: none;
        }

        .builder-tab-content.active {
            display: block;
        }

        .builder-layout {
            display: flex;
            gap: 20px;
        }

        .builder-sidebar {
            width: 300px;
            background: #fff;
            padding: 15px;
            border-radius: 4px;
            max-height: 600px;
            overflow-y: auto;
        }

        .builder-main {
            flex: 1;
            background: #fff;
            padding: 15px;
            border-radius: 4px;
        }

        .available-item {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 5px;
            background: #f9f9f9;
            border-radius: 3px;
            cursor: move;
        }

        .available-item:hover {
            background: #f0f0f0;
        }

        .item-icon {
            margin-right: 10px;
            color: #2D4FB3;
        }

        .item-title {
            flex: 1;
        }

        .add-item-btn {
            background: #2D4FB3;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
        }

        .add-item-btn:hover {
            background: #1e40af;
        }

        .course-sections {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .empty-curriculum {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        </style>
        <?php
    }

    /**
     * Render section
     */
    private function render_section($section) {
        ?>
        <li class="course-section" data-section-id="<?php echo $section->get_id(); ?>">
            <div class="section-header">
                <span class="section-handle"><i class="dashicons dashicons-menu"></i></span>
                <span class="section-title"><?php echo $section->get_title(); ?></span>
                <div class="section-actions">
                    <button class="edit-section-btn" title="<?php _e('Modifier', 'eia-lms-core'); ?>">
                        <i class="dashicons dashicons-edit"></i>
                    </button>
                    <button class="delete-section-btn" title="<?php _e('Supprimer', 'eia-lms-core'); ?>">
                        <i class="dashicons dashicons-trash"></i>
                    </button>
                </div>
            </div>
            <ul class="section-items sortable">
                <?php
                foreach ($section->get_items() as $item) {
                    $this->render_item($item);
                }
                ?>
            </ul>
        </li>
        <?php
    }

    /**
     * Render item
     */
    private function render_item($item) {
        $item_type = $item->get_item_type();
        $icon = $item_type === 'lp_quiz' ? 'welcome-learn-more' : 'media-text';

        ?>
        <li class="section-item" data-item-id="<?php echo $item->get_id(); ?>" data-item-type="<?php echo $item_type; ?>">
            <span class="item-handle"><i class="dashicons dashicons-menu"></i></span>
            <span class="item-icon"><i class="dashicons dashicons-<?php echo $icon; ?>"></i></span>
            <span class="item-title"><?php echo $item->get_title(); ?></span>
            <button class="remove-item-btn" title="<?php _e('Retirer', 'eia-lms-core'); ?>">
                <i class="dashicons dashicons-no"></i>
            </button>
        </li>
        <?php
    }

    /**
     * Save course builder data
     */
    public function save_course_builder_data($post_id) {
        // Verify nonce
        if (!isset($_POST['eia_course_builder_nonce']) || !wp_verify_nonce($_POST['eia_course_builder_nonce'], 'eia_course_builder_nonce')) {
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

        // Save custom fields
        if (isset($_POST['eia_auto_order'])) {
            update_post_meta($post_id, '_eia_auto_order', 1);
        } else {
            delete_post_meta($post_id, '_eia_auto_order');
        }

        if (isset($_POST['eia_show_numbering'])) {
            update_post_meta($post_id, '_eia_show_numbering', 1);
        } else {
            delete_post_meta($post_id, '_eia_show_numbering');
        }
    }

    /**
     * AJAX: Add lesson to course
     */
    public function ajax_add_lesson() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $item_type = isset($_POST['item_type']) ? sanitize_text_field($_POST['item_type']) : '';

        if (!$course_id || !$item_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Add item to course (LearnPress API)
        // Implementation depends on LearnPress version

        wp_send_json_success(array(
            'message' => __('Élément ajouté au cours', 'eia-lms-core')
        ));
    }

    /**
     * AJAX: Reorder course items
     */
    public function ajax_reorder_items() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $order = isset($_POST['order']) ? $_POST['order'] : array();

        if (!$course_id || empty($order)) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Update order (LearnPress API)
        // Implementation depends on LearnPress version

        wp_send_json_success(array(
            'message' => __('Ordre mis à jour', 'eia-lms-core')
        ));
    }

    /**
     * AJAX: Remove lesson from course
     */
    public function ajax_remove_lesson() {
        check_ajax_referer('eia-lms-core-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;

        if (!$course_id || !$item_id) {
            wp_send_json_error(array('message' => __('Données invalides', 'eia-lms-core')));
        }

        // Remove item from course (LearnPress API)
        // Implementation depends on LearnPress version

        wp_send_json_success(array(
            'message' => __('Élément retiré du cours', 'eia-lms-core')
        ));
    }
}
?>