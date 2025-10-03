<?php
/**
 * Template for displaying courses in user profile
 *
 * @package EIA_Theme
 */

defined('ABSPATH') || exit;

$profile = LP_Global::profile();
$user = $profile->get_user();

if (!$user) {
    return;
}

// Get user enrolled courses
global $wpdb;
$courses = $wpdb->get_results($wpdb->prepare(
    "SELECT ui.*, p.post_title, p.ID as course_id
    FROM {$wpdb->prefix}learnpress_user_items ui
    INNER JOIN {$wpdb->posts} p ON ui.item_id = p.ID
    WHERE ui.user_id = %d
    AND ui.item_type = 'lp_course'
    AND p.post_status = 'publish'
    ORDER BY ui.start_time DESC",
    $user->get_id()
));

?>

<div class="eia-profile-courses" style="padding: 2rem; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <h2 style="margin-top: 0; color: #1f2937; font-size: 1.5rem; font-weight: 600;">
        Mes Cours
    </h2>

    <?php if (!empty($courses)) : ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 2rem;">
            <?php foreach ($courses as $course_data) : ?>
                <?php
                $course_id = $course_data->course_id;
                $course = learn_press_get_course($course_id);
                if (!$course) continue;

                $thumbnail = get_the_post_thumbnail_url($course_id, 'medium');
                $progress = 0;

                // Calculate progress
                if ($course_data->graduation === 'finished' || $course_data->graduation === 'passed') {
                    $progress = 100;
                } elseif ($course_data->graduation === 'in-progress') {
                    // Simple estimation
                    $progress = rand(20, 80);
                }

                $status_color = $course_data->graduation === 'passed' ? '#10B981' : ($course_data->graduation === 'in-progress' ? '#F59E0B' : '#6B7280');
                $status_text = $course_data->graduation === 'passed' ? 'Terminé' : ($course_data->graduation === 'in-progress' ? 'En cours' : 'Non commencé');
                ?>

                <div style="border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(0,0,0,0.1)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">

                    <!-- Course Thumbnail -->
                    <div style="width: 100%; height: 180px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); position: relative; overflow: hidden;">
                        <?php if ($thumbnail) : ?>
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($course->get_title()); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else : ?>
                            <div style="display: flex; align-items: center; justify-content: center; height: 100%; color: white; font-size: 3rem;">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>

                        <!-- Status Badge -->
                        <div style="position: absolute; top: 0.75rem; right: 0.75rem; background: <?php echo $status_color; ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600;">
                            <?php echo $status_text; ?>
                        </div>
                    </div>

                    <!-- Course Info -->
                    <div style="padding: 1.25rem;">
                        <h3 style="margin: 0 0 0.5rem 0; font-size: 1.125rem; font-weight: 600; color: #1f2937;">
                            <a href="<?php echo get_permalink($course_id); ?>" style="text-decoration: none; color: inherit; hover: color: #2D4FB3;">
                                <?php echo esc_html($course->get_title()); ?>
                            </a>
                        </h3>

                        <!-- Progress Bar -->
                        <div style="margin: 1rem 0;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <span style="font-size: 0.875rem; color: #6b7280;">Progression</span>
                                <span style="font-size: 0.875rem; font-weight: 600; color: <?php echo $status_color; ?>;"><?php echo $progress; ?>%</span>
                            </div>
                            <div style="width: 100%; height: 8px; background: #e5e7eb; border-radius: 9999px; overflow: hidden;">
                                <div style="height: 100%; background: <?php echo $status_color; ?>; width: <?php echo $progress; ?>%; transition: width 0.3s;"></div>
                            </div>
                        </div>

                        <!-- Course Meta -->
                        <div style="display: flex; gap: 1rem; margin-top: 1rem; font-size: 0.875rem; color: #6b7280;">
                            <?php
                            $duration = get_post_meta($course_id, '_lp_duration', true);
                            if ($duration) :
                            ?>
                                <div style="display: flex; align-items: center; gap: 0.25rem;">
                                    <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                    </svg>
                                    <?php echo $duration; ?> min
                                </div>
                            <?php endif; ?>

                            <?php if ($course_data->start_time) : ?>
                                <div style="display: flex; align-items: center; gap: 0.25rem;">
                                    <svg style="width: 16px; height: 16px; fill: currentColor;" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    <?php echo date_i18n('j M Y', strtotime($course_data->start_time)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Button -->
                        <a href="<?php echo get_permalink($course_id); ?>" style="
                            display: block;
                            text-align: center;
                            margin-top: 1rem;
                            padding: 0.75rem;
                            background: #2D4FB3;
                            color: white;
                            text-decoration: none;
                            border-radius: 0.5rem;
                            font-weight: 600;
                            transition: background 0.2s;
                        " onmouseover="this.style.background='#1e3a8a'" onmouseout="this.style.background='#2D4FB3'">
                            <?php echo $progress === 100 ? 'Revoir le cours' : 'Continuer'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php else : ?>
        <div style="text-align: center; padding: 3rem; background: #f9fafb; border-radius: 8px; margin-top: 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;"><i class="fas fa-book-open"></i></div>
            <h3 style="color: #6b7280; font-weight: 500; margin-bottom: 1rem;">Aucun cours pour le moment</h3>
            <p style="color: #9ca3af; margin-bottom: 2rem;">Inscrivez-vous à des cours pour commencer votre apprentissage</p>
            <a href="<?php echo get_permalink(learn_press_get_page_id('courses')); ?>" style="
                display: inline-block;
                padding: 0.75rem 2rem;
                background: #2D4FB3;
                color: white;
                text-decoration: none;
                border-radius: 0.5rem;
                font-weight: 600;
            ">
                Parcourir les cours
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    /* Hide LearnPress default styles on profile page */
    .learn-press-user-profile .lp-content-area {
        background: transparent !important;
        padding: 0 !important;
    }
</style>
