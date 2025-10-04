<?php
/**
 * Template for displaying single lesson with video player
 *
 * @package EIA_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure admin bar is visible
show_admin_bar(true);

get_header();

while (have_posts()) : the_post();
    $lesson_id = get_the_ID();
    $course_id = get_post_meta($lesson_id, '_lp_course', true);
    $course = learn_press_get_course($course_id);

    // Get H5P content ID if exists
    $h5p_id = get_post_meta($lesson_id, '_lesson_h5p_id', true);

    // Get lesson video URL (fallback if no H5P)
    $video_url = get_post_meta($lesson_id, '_lp_lesson_video_url', true);
    $video_duration = get_post_meta($lesson_id, '_lp_lesson_video_duration', true);
?>

<div class="eia-lesson-wrapper">
    <div class="eia-lesson-container">
        <!-- Main Video Section -->
        <div class="eia-video-section">
            <!-- Video Player Area -->
            <div class="eia-video-player">
                <?php if ($h5p_id): ?>
                    <!-- H5P Interactive Video -->
                    <div class="eia-h5p-wrapper">
                        <?php echo do_shortcode('[h5p id="' . $h5p_id . '"]'); ?>
                    </div>
                <?php elseif ($video_url): ?>
                    <!-- Standard Video Player -->
                    <div class="eia-standard-video">
                        <video id="eia-lesson-video" controls>
                            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                            Votre navigateur ne supporte pas la lecture de vidéos.
                        </video>

                        <!-- Video Overlay Info -->
                        <div class="eia-video-overlay">
                            <div class="eia-video-info">
                                <div class="eia-play-icon">
                                    <i class="fas fa-play"></i>
                                </div>
                                <h2 class="eia-lesson-title"><?php the_title(); ?></h2>
                                <p class="eia-lesson-course"><?php echo $course ? esc_html($course->get_title()) : ''; ?></p>

                                <?php if (learn_press_user_has_completed_lesson($lesson_id, get_current_user_id())): ?>
                                    <div class="eia-enrollment-badge">
                                        <i class="fas fa-check-circle"></i> Vous avez complété cette leçon
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- No Video - Show Content -->
                    <div class="eia-lesson-content-only">
                        <h1><?php the_title(); ?></h1>
                        <?php the_content(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tabs Section -->
            <div class="eia-lesson-tabs">
                <div class="eia-tab-navigation">
                    <button class="eia-tab-btn active" data-tab="overview">
                        <i class="fas fa-info-circle"></i> Overview
                    </button>
                    <button class="eia-tab-btn" data-tab="qa">
                        <i class="fas fa-question-circle"></i> Q&A
                    </button>
                    <button class="eia-tab-btn" data-tab="notes">
                        <i class="fas fa-sticky-note"></i> Notes
                    </button>
                    <button class="eia-tab-btn" data-tab="reviews">
                        <i class="fas fa-star"></i> Reviews
                    </button>
                </div>

                <div class="eia-tab-content">
                    <!-- Overview Tab -->
                    <div class="eia-tab-pane active" id="tab-overview">
                        <h3>À propos de cette leçon</h3>
                        <div class="eia-lesson-description">
                            <?php the_content(); ?>
                        </div>

                        <?php if ($video_duration): ?>
                            <div class="eia-lesson-meta">
                                <span><i class="fas fa-clock"></i> Durée: <?php echo esc_html($video_duration); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Q&A Tab -->
                    <div class="eia-tab-pane" id="tab-qa">
                        <h3>Questions & Réponses</h3>
                        <div class="eia-qa-section">
                            <?php
                            // Integration bbPress forum for this lesson
                            echo do_shortcode('[eia_lesson_qa lesson_id="' . $lesson_id . '"]');
                            ?>
                        </div>
                    </div>

                    <!-- Notes Tab -->
                    <div class="eia-tab-pane" id="tab-notes">
                        <h3>Mes Notes</h3>
                        <div class="eia-notes-section">
                            <?php echo do_shortcode('[eia_lesson_notes lesson_id="' . $lesson_id . '"]'); ?>
                        </div>
                    </div>

                    <!-- Reviews Tab -->
                    <div class="eia-tab-pane" id="tab-reviews">
                        <h3>Avis sur cette leçon</h3>
                        <div class="eia-reviews-section">
                            <?php echo do_shortcode('[eia_lesson_reviews lesson_id="' . $lesson_id . '"]'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Content Sidebar -->
        <aside class="eia-course-sidebar">
            <?php
            if ($course_id) {
                echo do_shortcode('[eia_course_sidebar course_id="' . $course_id . '" current_lesson="' . $lesson_id . '"]');
            }
            ?>
        </aside>
    </div>
</div>

<?php
endwhile;

get_footer();
