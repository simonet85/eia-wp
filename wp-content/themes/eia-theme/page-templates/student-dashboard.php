<?php
/**
 * Template Name: Student Dashboard
 *
 * @package EIA_Theme
 */

// Restrict access to students only
if (!is_user_logged_in() || !eia_is_student()) {
    wp_redirect(home_url());
    exit;
}

get_header();

$user_id = get_current_user_id();
$user = wp_get_current_user();
$enrolled_count = eia_get_user_enrolled_courses_count($user_id);
$completed_count = eia_get_user_completed_courses_count($user_id);
$badges_count = eia_get_user_badges_count($user_id);
$points = eia_get_user_points($user_id);
$progress = eia_calculate_overall_progress($user_id);
?>

<div class="student-dashboard bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4">

        <!-- Welcome Header -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="avatar-container">
                        <?php echo eia_get_user_avatar($user_id, 80); ?>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-eia-blue">
                            <?php printf(__('Bonjour, %s !', 'eia-theme'), $user->display_name); ?>
                        </h1>
                        <p class="text-gray-600"><?php _e('Bienvenue sur votre tableau de bord', 'eia-theme'); ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-eia-orange"><?php echo $points; ?></div>
                    <div class="text-sm text-gray-600"><?php _e('Points', 'eia-theme'); ?></div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-stats grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="fas fa-book-reader"></i>
                </div>
                <div class="stat-card-value"><?php echo $enrolled_count; ?></div>
                <div class="stat-card-label"><?php _e('Cours inscrits', 'eia-theme'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon orange">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-value"><?php echo $completed_count; ?></div>
                <div class="stat-card-label"><?php _e('Cours terminés', 'eia-theme'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="fas fa-trophy"></i>
                </div>
                <div class="stat-card-value"><?php echo $badges_count; ?></div>
                <div class="stat-card-label"><?php _e('Badges obtenus', 'eia-theme'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon orange">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-card-value"><?php echo $progress; ?>%</div>
                <div class="stat-card-label"><?php _e('Progression globale', 'eia-theme'); ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Main Content -->
            <div class="lg:col-span-2">

                <!-- My Courses -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-eia-blue">
                            <?php _e('Mes cours en cours', 'eia-theme'); ?>
                        </h2>
                        <a href="<?php echo learn_press_get_page_link('profile'); ?>" class="text-eia-orange hover:underline">
                            <?php _e('Voir tout', 'eia-theme'); ?> →
                        </a>
                    </div>

                    <?php
                    if (function_exists('learn_press_get_user')) {
                        $user_lp = learn_press_get_user($user_id);
                        $courses = $user_lp ? $user_lp->get_enrolled_courses(array(
                            'limit' => 3,
                            'status' => 'enrolled'
                        )) : array();

                        if ($courses) :
                            foreach ($courses as $course_id) :
                                $course = learn_press_get_course($course_id);
                                $progress = eia_get_course_progress($course_id, $user_id);
                                ?>
                                <div class="course-item border-b border-gray-200 py-4 last:border-0">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-shrink-0">
                                            <?php if (has_post_thumbnail($course_id)) : ?>
                                                <?php echo get_the_post_thumbnail($course_id, 'thumbnail', array('class' => 'w-20 h-20 rounded-lg object-cover')); ?>
                                            <?php else : ?>
                                                <div class="w-20 h-20 bg-gray-200 rounded-lg"></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-900 mb-2">
                                                <a href="<?php echo get_permalink($course_id); ?>">
                                                    <?php echo get_the_title($course_id); ?>
                                                </a>
                                            </h3>
                                            <div class="course-progress mb-2">
                                                <div class="course-progress-bar">
                                                    <div class="course-progress-fill" data-progress="<?php echo $progress; ?>" style="width: <?php echo $progress; ?>%;"></div>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600"><?php echo $progress; ?>% <?php _e('complété', 'eia-theme'); ?></div>
                                        </div>
                                        <div>
                                            <a href="<?php echo get_permalink($course_id); ?>" class="btn-primary text-sm">
                                                <?php _e('Continuer', 'eia-theme'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        else :
                            ?>
                            <p class="text-gray-600 text-center py-8">
                                <?php _e('Vous n\'êtes inscrit à aucun cours pour le moment.', 'eia-theme'); ?>
                            </p>
                            <div class="text-center">
                                <a href="<?php echo learn_press_get_page_link('courses'); ?>" class="btn-primary">
                                    <?php _e('Explorer les cours', 'eia-theme'); ?>
                                </a>
                            </div>
                            <?php
                        endif;
                    }
                    ?>
                </div>

                <!-- Recent Achievements -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-eia-blue mb-6">
                        <?php _e('Derniers succès', 'eia-theme'); ?>
                    </h2>

                    <?php
                    if (function_exists('gamipress_get_user_achievements')) {
                        $achievements = gamipress_get_user_achievements(array(
                            'user_id' => $user_id,
                            'limit' => 5,
                        ));

                        if ($achievements) :
                            foreach ($achievements as $achievement) :
                                ?>
                                <div class="achievement-item flex items-center gap-4 py-3 border-b border-gray-200 last:border-0">
                                    <div class="achievement-icon w-12 h-12 bg-eia-orange rounded-full flex items-center justify-center">
                                        <i class="fas fa-trophy text-white"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-gray-900"><?php echo $achievement->post_title; ?></h4>
                                        <p class="text-sm text-gray-600"><?php echo get_the_date('', $achievement->ID); ?></p>
                                    </div>
                                </div>
                                <?php
                            endforeach;
                        else :
                            ?>
                            <p class="text-gray-600 text-center py-8">
                                <?php _e('Aucun succès débloqué pour le moment.', 'eia-theme'); ?>
                            </p>
                            <?php
                        endif;
                    } else {
                        ?>
                        <p class="text-gray-600 text-center py-8">
                            <?php _e('Le système de badges sera bientôt disponible.', 'eia-theme'); ?>
                        </p>
                        <?php
                    }
                    ?>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">

                <!-- Quick Links -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-bold text-eia-blue mb-4">
                        <?php _e('Liens rapides', 'eia-theme'); ?>
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="<?php echo learn_press_get_page_link('courses'); ?>" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-book w-5"></i>
                                <?php _e('Catalogue de cours', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo learn_press_get_page_link('profile'); ?>" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-user w-5"></i>
                                <?php _e('Mon profil', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-certificate w-5"></i>
                                <?php _e('Mes certificats', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-heart w-5"></i>
                                <?php _e('Liste de souhaits', 'eia-theme'); ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Upcoming Events -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-eia-blue mb-4">
                        <?php _e('Événements à venir', 'eia-theme'); ?>
                    </h3>
                    <p class="text-gray-600 text-sm">
                        <?php _e('Aucun événement prévu pour le moment.', 'eia-theme'); ?>
                    </p>
                </div>

            </div>
        </div>

    </div>
</div>

<?php get_footer(); ?>