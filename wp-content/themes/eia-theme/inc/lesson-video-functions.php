<?php
/**
 * Lesson Video Player Functions
 * Shortcodes and AJAX handlers for video lessons
 *
 * @package EIA_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Course Sidebar Shortcode
 * Shows course progress and lessons list
 */
function eia_course_sidebar_shortcode($atts) {
    $atts = shortcode_atts(array(
        'course_id' => 0,
        'current_lesson' => 0
    ), $atts);

    $course_id = intval($atts['course_id']);
    $current_lesson_id = intval($atts['current_lesson']);

    if (!$course_id) {
        return '<p>Cours non trouvé</p>';
    }

    $course = learn_press_get_course($course_id);
    if (!$course) {
        return '<p>Cours non trouvé</p>';
    }

    $user = learn_press_get_current_user();
    $user_course = $user->get_course_data($course_id);

    // Get course curriculum
    global $wpdb;
    $sections = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}learnpress_sections WHERE section_course_id = %d ORDER BY section_order ASC",
        $course_id
    ));

    // Calculate progress
    $total_items = 0;
    $completed_items = 0;

    foreach ($sections as $section) {
        $items = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = %d ORDER BY item_order ASC",
            $section->section_id
        ));

        $total_items += count($items);

        foreach ($items as $item) {
            if (learn_press_user_has_completed_item($item->item_id, $user->get_id(), $course_id)) {
                $completed_items++;
            }
        }
    }

    $progress_percent = $total_items > 0 ? round(($completed_items / $total_items) * 100) : 0;

    ob_start();
    ?>
    <div class="eia-course-sidebar-content">
        <!-- Progress Header -->
        <div class="eia-progress-header">
            <h3>
                Course content
                <span class="eia-completion-info"><?php echo $completed_items; ?>/<?php echo $total_items; ?> complété</span>
            </h3>

            <div class="eia-progress-bar">
                <div class="eia-progress-fill" style="width: <?php echo $progress_percent; ?>%"></div>
            </div>
            <span class="eia-progress-percent"><?php echo $progress_percent; ?>% terminé</span>
        </div>

        <!-- Course Sections -->
        <?php foreach ($sections as $section): ?>
            <?php
            $section_items = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = %d ORDER BY item_order ASC",
                $section->section_id
            ));
            ?>

            <div class="eia-course-section">
                <div class="eia-section-header">
                    <h4 class="eia-section-title"><?php echo esc_html($section->section_name); ?></h4>
                    <span class="eia-section-count"><?php echo count($section_items); ?> éléments</span>
                </div>

                <div class="eia-section-items">
                    <?php foreach ($section_items as $item): ?>
                        <?php
                        $item_post = get_post($item->item_id);
                        $is_current = ($item->item_id == $current_lesson_id);
                        $is_completed = learn_press_user_has_completed_item($item->item_id, $user->get_id(), $course_id);
                        $item_type = $item_post->post_type;

                        // Get item duration
                        $duration = get_post_meta($item->item_id, '_lp_duration', true) ?: get_post_meta($item->item_id, '_lp_lesson_video_duration', true);
                        if ($duration && is_numeric($duration)) {
                            $duration = intval($duration) . ' min';
                        }

                        // Icon based on type
                        $icon = 'fa-file-text';
                        if ($item_type == 'lp_lesson') {
                            $icon = 'fa-play-circle';
                        } elseif ($item_type == 'lp_quiz') {
                            $icon = 'fa-question-circle';
                        } elseif ($item_type == 'lp_assignment') {
                            $icon = 'fa-clipboard';
                        }

                        $item_url = get_permalink($item->item_id);
                        ?>

                        <div class="eia-lesson-item <?php echo $is_current ? 'current' : ''; ?> <?php echo $is_completed ? 'completed' : ''; ?>"
                             data-lesson-id="<?php echo $item->item_id; ?>"
                             data-lesson-url="<?php echo esc_url($item_url); ?>">

                            <div class="eia-lesson-radio"></div>

                            <div class="eia-lesson-icon">
                                <i class="fas <?php echo $icon; ?>"></i>
                            </div>

                            <div class="eia-lesson-info">
                                <p class="eia-lesson-name"><?php echo esc_html($item_post->post_title); ?></p>
                                <?php if ($duration): ?>
                                    <span class="eia-lesson-duration"><?php echo esc_html($duration); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($is_completed): ?>
                                <span class="eia-badge-complete">
                                    <i class="fas fa-check"></i> Terminé
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('eia_course_sidebar', 'eia_course_sidebar_shortcode');

/**
 * Lesson Q&A Shortcode (bbPress integration)
 */
function eia_lesson_qa_shortcode($atts) {
    $atts = shortcode_atts(array(
        'lesson_id' => 0
    ), $atts);

    $lesson_id = intval($atts['lesson_id']);

    ob_start();
    ?>
    <div class="eia-qa-container">
        <div class="eia-qa-form">
            <h4>Poser une question</h4>
            <textarea id="eia-qa-question" rows="4" placeholder="Écrivez votre question..."></textarea>
            <button class="eia-btn-primary" onclick="EIA_QA.submitQuestion(<?php echo $lesson_id; ?>)">
                <i class="fas fa-paper-plane"></i> Publier
            </button>
        </div>

        <div class="eia-qa-list" id="eia-qa-list-<?php echo $lesson_id; ?>">
            <p class="eia-loading">Chargement des questions...</p>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Load Q&A on page load
        $.ajax({
            url: eiaLesson.ajaxurl,
            data: {
                action: 'eia_get_lesson_qa',
                lesson_id: <?php echo $lesson_id; ?>,
                nonce: eiaLesson.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#eia-qa-list-<?php echo $lesson_id; ?>').html(response.data.html);
                }
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('eia_lesson_qa', 'eia_lesson_qa_shortcode');

/**
 * Lesson Notes Shortcode
 */
function eia_lesson_notes_shortcode($atts) {
    $atts = shortcode_atts(array(
        'lesson_id' => 0
    ), $atts);

    $lesson_id = intval($atts['lesson_id']);
    $user_id = get_current_user_id();

    global $wpdb;
    $notes_table = $wpdb->prefix . 'eia_lesson_notes';

    // Get existing note
    $existing_note = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$notes_table} WHERE lesson_id = %d AND user_id = %d",
        $lesson_id, $user_id
    ));

    ob_start();
    ?>
    <div class="eia-notes-container">
        <div class="eia-notes-editor">
            <textarea id="eia-lesson-notes-<?php echo $lesson_id; ?>"
                      rows="10"
                      placeholder="Prenez vos notes ici..."><?php echo $existing_note ? esc_textarea($existing_note->note_content) : ''; ?></textarea>

            <div class="eia-notes-actions">
                <button class="eia-btn-primary" onclick="EIA_Notes.saveNote(<?php echo $lesson_id; ?>)">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
                <span class="eia-save-status" id="eia-note-status-<?php echo $lesson_id; ?>"></span>
            </div>
        </div>

        <?php if ($existing_note): ?>
            <p class="eia-note-meta">
                Dernière modification: <?php echo date_i18n('d/m/Y à H:i', strtotime($existing_note->updated_at)); ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('eia_lesson_notes', 'eia_lesson_notes_shortcode');

/**
 * Lesson Reviews Shortcode
 */
function eia_lesson_reviews_shortcode($atts) {
    $atts = shortcode_atts(array(
        'lesson_id' => 0
    ), $atts);

    $lesson_id = intval($atts['lesson_id']);

    ob_start();
    ?>
    <div class="eia-reviews-container">
        <div class="eia-review-form">
            <h4>Évaluer cette leçon</h4>
            <div class="eia-star-rating" id="eia-stars-<?php echo $lesson_id; ?>">
                <i class="far fa-star" data-rating="1"></i>
                <i class="far fa-star" data-rating="2"></i>
                <i class="far fa-star" data-rating="3"></i>
                <i class="far fa-star" data-rating="4"></i>
                <i class="far fa-star" data-rating="5"></i>
            </div>
            <textarea id="eia-review-text-<?php echo $lesson_id; ?>"
                      rows="4"
                      placeholder="Partagez votre avis..."></textarea>
            <button class="eia-btn-primary" onclick="EIA_Reviews.submitReview(<?php echo $lesson_id; ?>)">
                <i class="fas fa-star"></i> Publier mon avis
            </button>
        </div>

        <div class="eia-reviews-list" id="eia-reviews-<?php echo $lesson_id; ?>">
            <p class="eia-loading">Chargement des avis...</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('eia_lesson_reviews', 'eia_lesson_reviews_shortcode');

/**
 * AJAX: Update Lesson Progress
 */
function eia_ajax_update_lesson_progress() {
    check_ajax_referer('eia-lesson-nonce', 'nonce');

    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    $watched_duration = isset($_POST['watched_duration']) ? floatval($_POST['watched_duration']) : 0;

    if (!$lesson_id || !$course_id) {
        wp_send_json_error(array('message' => 'Données invalides'));
    }

    $user_id = get_current_user_id();

    // Update user meta with watched duration
    $current_progress = get_user_meta($user_id, "_lesson_{$lesson_id}_progress", true) ?: 0;
    update_user_meta($user_id, "_lesson_{$lesson_id}_progress", $current_progress + $watched_duration);

    wp_send_json_success(array(
        'message' => 'Progression enregistrée',
        'total_watched' => $current_progress + $watched_duration
    ));
}
add_action('wp_ajax_eia_update_lesson_progress', 'eia_ajax_update_lesson_progress');

/**
 * AJAX: Complete Lesson
 */
function eia_ajax_complete_lesson() {
    check_ajax_referer('eia-lesson-nonce', 'nonce');

    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

    if (!$lesson_id || !$course_id) {
        wp_send_json_error(array('message' => 'Données invalides'));
    }

    $user_id = get_current_user_id();
    $user = learn_press_get_user($user_id);

    // Complete lesson
    $result = $user->complete_lesson($lesson_id, $course_id);

    // Calculate new course progress
    $course = learn_press_get_course($course_id);
    $course_data = $user->get_course_data($course_id);
    $progress = $course_data ? $course_data->get_results('result') : 0;

    wp_send_json_success(array(
        'message' => 'Leçon complétée',
        'lesson_id' => $lesson_id,
        'course_progress' => round($progress)
    ));
}
add_action('wp_ajax_eia_complete_lesson', 'eia_ajax_complete_lesson');

/**
 * AJAX: Save Lesson Note
 */
function eia_ajax_save_lesson_note() {
    check_ajax_referer('eia-lesson-nonce', 'nonce');

    $lesson_id = isset($_POST['lesson_id']) ? intval($_POST['lesson_id']) : 0;
    $note_content = isset($_POST['note_content']) ? wp_kses_post($_POST['note_content']) : '';
    $user_id = get_current_user_id();

    if (!$lesson_id || !$user_id) {
        wp_send_json_error(array('message' => 'Données invalides'));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'eia_lesson_notes';

    // Check if note exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE lesson_id = %d AND user_id = %d",
        $lesson_id, $user_id
    ));

    if ($existing) {
        // Update
        $wpdb->update(
            $table,
            array('note_content' => $note_content, 'updated_at' => current_time('mysql')),
            array('id' => $existing),
            array('%s', '%s'),
            array('%d')
        );
    } else {
        // Insert
        $course_id = get_post_meta($lesson_id, '_lp_course', true);
        $wpdb->insert(
            $table,
            array(
                'user_id' => $user_id,
                'lesson_id' => $lesson_id,
                'course_id' => $course_id,
                'note_content' => $note_content,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array('%d', '%d', '%d', '%s', '%s', '%s')
        );
    }

    wp_send_json_success(array('message' => 'Note enregistrée'));
}
add_action('wp_ajax_eia_save_lesson_note', 'eia_ajax_save_lesson_note');
