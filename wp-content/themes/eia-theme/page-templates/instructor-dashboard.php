<?php
/**
 * Template Name: Instructor Dashboard
 *
 * @package EIA_Theme
 */

// Restrict access to instructors only
if (!is_user_logged_in() || (!current_user_can('lp_teacher') && !current_user_can('administrator'))) {
    wp_redirect(home_url());
    exit;
}

// Force admin bar visibility
show_admin_bar(true);

get_header();

$user_id = get_current_user_id();
$user = wp_get_current_user();

// Get instructor statistics
global $wpdb;

// Count instructor's courses
$courses_count = $wpdb->get_var($wpdb->prepare(
    "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lp_course' AND post_author = %d AND post_status = 'publish'",
    $user_id
));

// Get instructor's course IDs
$course_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'lp_course' AND post_author = %d AND post_status = 'publish'",
    $user_id
));

// Count total students (unique)
$students_count = 0;
if (!empty($course_ids)) {
    $course_ids_placeholder = implode(',', array_fill(0, count($course_ids), '%d'));
    $students_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}learnpress_user_items
        WHERE item_type = 'lp_course' AND item_id IN ($course_ids_placeholder) AND status IN ('enrolled', 'finished')",
        ...$course_ids
    ));
}

// Get pending assignments for grading
$pending_assignments = $wpdb->get_results($wpdb->prepare(
    "SELECT s.*, a.post_title as assignment_title, u.display_name as student_name
    FROM {$wpdb->prefix}eia_assignment_submissions s
    INNER JOIN {$wpdb->posts} a ON s.assignment_id = a.ID
    INNER JOIN {$wpdb->users} u ON s.student_id = u.ID
    WHERE a.post_author = %d AND s.status IN ('submitted', 'pending')
    ORDER BY s.submitted_date DESC
    LIMIT 10",
    $user_id
));
?>

<style>
body.admin-bar {
    margin-top: 32px !important;
}
#wpadminbar {
    display: block !important;
    position: fixed !important;
    top: 0 !important;
    z-index: 99999 !important;
}
</style>

<div style="min-height: 100vh; background: #f9fafb; padding: 2rem 0;">
    <div style="max-width: 1400px; margin: 0 auto; padding: 0 1rem;">

        <!-- Welcome Header -->
        <div style="background: linear-gradient(135deg, #2D4FB3 0%, #1e3a8a 100%); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); padding: 2.5rem; margin-bottom: 2rem; color: white;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
                <div style="display: flex; align-items: center; gap: 1.5rem;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #2D4FB3; font-weight: bold;">
                        <?php echo strtoupper(substr($user->display_name, 0, 1)); ?>
                    </div>
                    <div>
                        <h1 style="font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0;">
                            <i class="fas fa-chalkboard-teacher" style="margin-right: 0.75rem;"></i>Bonjour, <?php echo esc_html($user->display_name); ?> !
                        </h1>
                        <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 1.125rem;">Tableau de bord formateur</p>
                    </div>
                </div>
                <div>
                    <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" style="
                        display: inline-flex;
                        align-items: center;
                        gap: 0.5rem;
                        padding: 0.875rem 1.75rem;
                        background: white;
                        color: #2D4FB3;
                        text-decoration: none;
                        border-radius: 8px;
                        font-weight: 600;
                        font-size: 1rem;
                        transition: all 0.3s;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(0,0,0,0.15)'" onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.1)'">
                        <i class="fas fa-plus-circle"></i> Créer un cours
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <!-- Courses Count -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, #2D4FB3 0%, #1e3a8a 100%); opacity: 0.1; border-radius: 0 12px 0 100%;"></div>
                <div style="display: flex; align-items: center; gap: 1rem; position: relative;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #2D4FB3 0%, #1e3a8a 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;"><?php echo $courses_count; ?></div>
                        <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">Cours créés</div>
                    </div>
                </div>
            </div>

            <!-- Students Count -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); opacity: 0.1; border-radius: 0 12px 0 100%;"></div>
                <div style="display: flex; align-items: center; gap: 1rem; position: relative;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;"><?php echo $students_count; ?></div>
                        <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">Étudiants actifs</div>
                    </div>
                </div>
            </div>

            <!-- Pending Grading -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); opacity: 0.1; border-radius: 0 12px 0 100%;"></div>
                <div style="display: flex; align-items: center; gap: 1rem; position: relative;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;"><?php echo count($pending_assignments); ?></div>
                        <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">Devoirs à noter</div>
                    </div>
                </div>
            </div>

            <!-- Lessons Count -->
            <div style="background: white; border-radius: 12px; padding: 1.75rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; overflow: hidden;">
                <div style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); opacity: 0.1; border-radius: 0 12px 0 100%;"></div>
                <div style="display: flex; align-items: center; gap: 1rem; position: relative;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div>
                        <?php
                        $lessons_count = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'lp_lesson' AND post_author = %d",
                            $user_id
                        ));
                        ?>
                        <div style="font-size: 2rem; font-weight: 700; color: #1f2937; margin-bottom: 0.25rem;"><?php echo $lessons_count; ?></div>
                        <div style="color: #6b7280; font-size: 0.875rem; font-weight: 500;">Leçons créées</div>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">

            <!-- Pending Assignments Section -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #2D4FB3; margin: 0;">
                        <i class="fas fa-clipboard-check" style="margin-right: 0.5rem; color: #F59E0B;"></i>Devoirs à noter
                    </h2>
                    <a href="<?php echo site_url('/notation-devoirs/'); ?>" style="color: #F59E0B; text-decoration: none; font-weight: 600; font-size: 0.875rem;">
                        Tout voir <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
                    </a>
                </div>

                <?php if (!empty($pending_assignments)) : ?>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach (array_slice($pending_assignments, 0, 5) as $submission) :
                            $assignment = get_post($submission->assignment_id);
                            $time_diff = human_time_diff(strtotime($submission->submitted_date), current_time('timestamp'));
                        ?>
                            <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.25rem; transition: all 0.2s;" onmouseover="this.style.borderColor='#2D4FB3'; this.style.boxShadow='0 4px 12px rgba(45, 79, 179, 0.1)'" onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow=''">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.75rem;">
                                    <div style="flex: 1;">
                                        <h3 style="font-weight: 600; color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1rem;">
                                            <?php echo esc_html($submission->assignment_title); ?>
                                        </h3>
                                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">
                                            <i class="fas fa-user" style="margin-right: 0.25rem;"></i>
                                            <?php echo esc_html($submission->student_name); ?>
                                        </p>
                                    </div>
                                    <span style="background: #FEF3C7; color: #D97706; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; white-space: nowrap;">
                                        <i class="far fa-clock"></i> <?php echo $time_diff; ?>
                                    </span>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="<?php echo site_url('/notation-devoirs/?assignment_id=' . $submission->assignment_id); ?>" style="
                                        flex: 1;
                                        padding: 0.5rem 1rem;
                                        background: #2D4FB3;
                                        color: white;
                                        text-decoration: none;
                                        border-radius: 6px;
                                        text-align: center;
                                        font-size: 0.875rem;
                                        font-weight: 600;
                                        transition: all 0.2s;
                                    " onmouseover="this.style.background='#1e3a8a'" onmouseout="this.style.background='#2D4FB3'">
                                        <i class="fas fa-pencil-alt"></i> Noter
                                    </a>
                                    <?php if ($submission->file_url) : ?>
                                        <a href="<?php echo esc_url($submission->file_url); ?>" target="_blank" style="
                                            padding: 0.5rem 1rem;
                                            background: #f3f4f6;
                                            color: #374151;
                                            text-decoration: none;
                                            border-radius: 6px;
                                            font-size: 0.875rem;
                                            font-weight: 600;
                                            transition: all 0.2s;
                                        " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <div style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                        <div style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <p style="margin: 0; font-size: 1.125rem; font-weight: 500;">Aucun devoir en attente</p>
                        <p style="margin: 0.5rem 0 0 0; font-size: 0.875rem;">Tous les devoirs ont été notés !</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- My Courses Section -->
            <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #2D4FB3; margin: 0;">
                        <i class="fas fa-book-reader" style="margin-right: 0.5rem;"></i>Mes cours
                    </h2>
                    <a href="<?php echo admin_url('edit.php?post_type=lp_course'); ?>" style="color: #F59E0B; text-decoration: none; font-weight: 600; font-size: 0.875rem;">
                        Gérer <i class="fas fa-arrow-right" style="margin-left: 0.25rem;"></i>
                    </a>
                </div>

                <?php
                $courses = $wpdb->get_results($wpdb->prepare(
                    "SELECT ID, post_title, post_date FROM {$wpdb->posts}
                    WHERE post_type = 'lp_course' AND post_author = %d AND post_status = 'publish'
                    ORDER BY post_date DESC LIMIT 5",
                    $user_id
                ));

                if (!empty($courses)) :
                    foreach ($courses as $course) :
                        $course_students = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->prefix}learnpress_user_items
                            WHERE item_type = 'lp_course' AND item_id = %d AND status IN ('enrolled', 'finished')",
                            $course->ID
                        ));
                        $thumbnail = get_the_post_thumbnail_url($course->ID, 'thumbnail');
                ?>
                    <div style="display: flex; gap: 1rem; padding: 1rem; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 1rem; transition: all 0.2s;" onmouseover="this.style.borderColor='#2D4FB3'; this.style.boxShadow='0 4px 12px rgba(45, 79, 179, 0.1)'" onmouseout="this.style.borderColor='#e5e7eb'; this.style.boxShadow=''">
                        <?php if ($thumbnail) : ?>
                            <img src="<?php echo esc_url($thumbnail); ?>" alt="" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover;">
                        <?php else : ?>
                            <div style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 2rem;">
                                <i class="fas fa-book"></i>
                            </div>
                        <?php endif; ?>
                        <div style="flex: 1;">
                            <h3 style="font-weight: 600; color: #1f2937; margin: 0 0 0.5rem 0; font-size: 1rem;">
                                <a href="<?php echo get_permalink($course->ID); ?>" style="color: inherit; text-decoration: none;">
                                    <?php echo esc_html($course->post_title); ?>
                                </a>
                            </h3>
                            <div style="display: flex; gap: 1.5rem; font-size: 0.875rem; color: #6b7280;">
                                <span><i class="fas fa-users" style="margin-right: 0.25rem;"></i><?php echo $course_students; ?> étudiants</span>
                                <span><i class="far fa-calendar" style="margin-right: 0.25rem;"></i><?php echo date('d/m/Y', strtotime($course->post_date)); ?></span>
                            </div>
                        </div>
                        <a href="<?php echo get_edit_post_link($course->ID); ?>" style="
                            align-self: center;
                            padding: 0.5rem 1rem;
                            background: #f3f4f6;
                            color: #374151;
                            text-decoration: none;
                            border-radius: 6px;
                            font-size: 0.875rem;
                            font-weight: 600;
                            transition: all 0.2s;
                        " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                <?php endforeach; ?>
                <?php else : ?>
                    <div style="text-align: center; padding: 3rem 1rem; color: #6b7280;">
                        <div style="font-size: 4rem; opacity: 0.3; margin-bottom: 1rem;">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <p style="margin: 0 0 1.5rem 0; font-size: 1.125rem; font-weight: 500;">Aucun cours créé</p>
                        <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" style="
                            display: inline-block;
                            padding: 0.75rem 1.5rem;
                            background: #2D4FB3;
                            color: white;
                            text-decoration: none;
                            border-radius: 8px;
                            font-weight: 600;
                        ">
                            <i class="fas fa-plus"></i> Créer mon premier cours
                        </a>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Quick Actions -->
        <div style="background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); padding: 2rem; margin-top: 2rem;">
            <h2 style="font-size: 1.5rem; font-weight: 700; color: #2D4FB3; margin: 0 0 1.5rem 0;">
                <i class="fas fa-bolt" style="margin-right: 0.5rem; color: #F59E0B;"></i>Actions rapides
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" style="
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1.25rem;
                    background: linear-gradient(135deg, #2D4FB3 0%, #1e3a8a 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s;
                " onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(45, 79, 179, 0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                    <i class="fas fa-plus-circle" style="font-size: 1.5rem;"></i>
                    <span>Nouveau cours</span>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=lp_lesson'); ?>" style="
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1.25rem;
                    background: linear-gradient(135deg, #10B981 0%, #059669 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s;
                " onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(16, 185, 129, 0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                    <i class="fas fa-file-alt" style="font-size: 1.5rem;"></i>
                    <span>Nouvelle leçon</span>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=lp_quiz'); ?>" style="
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1.25rem;
                    background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s;
                " onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(245, 158, 11, 0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                    <i class="fas fa-question-circle" style="font-size: 1.5rem;"></i>
                    <span>Nouveau quiz</span>
                </a>
                <a href="<?php echo admin_url('post-new.php?post_type=lp_assignment'); ?>" style="
                    display: flex;
                    align-items: center;
                    gap: 1rem;
                    padding: 1.25rem;
                    background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
                    color: white;
                    text-decoration: none;
                    border-radius: 8px;
                    font-weight: 600;
                    transition: all 0.3s;
                " onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 8px 16px rgba(239, 68, 68, 0.3)'" onmouseout="this.style.transform=''; this.style.boxShadow=''">
                    <i class="fas fa-clipboard-list" style="font-size: 1.5rem;"></i>
                    <span>Nouveau devoir</span>
                </a>
            </div>
        </div>

    </div>
</div>

<?php get_footer(); ?>
