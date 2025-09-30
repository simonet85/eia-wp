<?php
/**
 * Template Name: Instructor Dashboard
 *
 * @package EIA_Theme
 */

// Restrict access to instructors only
if (!is_user_logged_in() || (!eia_is_instructor() && !eia_is_lms_manager())) {
    wp_redirect(home_url());
    exit;
}

get_header();

$user_id = get_current_user_id();
$user = wp_get_current_user();
$courses_count = eia_get_instructor_courses_count($user_id);
$students_count = eia_get_instructor_students_count($user_id);
?>

<div class="instructor-dashboard bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4">

        <!-- Welcome Header -->
        <div class="bg-gradient-to-r from-eia-blue to-blue-600 rounded-lg shadow-lg p-8 mb-8 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-6">
                    <div class="avatar-container">
                        <?php echo eia_get_user_avatar($user_id, 96); ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold mb-2">
                            <?php printf(__('Bonjour, %s !', 'eia-theme'), $user->display_name); ?>
                        </h1>
                        <p class="text-blue-100"><?php _e('Tableau de bord formateur', 'eia-theme'); ?></p>
                    </div>
                </div>
                <div>
                    <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" class="btn-secondary inline-flex items-center gap-2">
                        <i class="fas fa-plus"></i>
                        <?php _e('Créer un cours', 'eia-theme'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-stats grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="fas fa-book"></i>
                </div>
                <div class="stat-card-value"><?php echo $courses_count; ?></div>
                <div class="stat-card-label"><?php _e('Cours créés', 'eia-theme'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon orange">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-card-value"><?php echo $students_count; ?></div>
                <div class="stat-card-label"><?php _e('Étudiants actifs', 'eia-theme'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon blue">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-card-value">4.8</div>
                <div class="stat-card-label"><?php _e('Note moyenne', 'eia-theme'); ?></div>
            </div>

            <div class="stat-card">
                <div class="stat-card-icon orange">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-card-value">85%</div>
                <div class="stat-card-label"><?php _e('Taux de réussite', 'eia-theme'); ?></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Main Content -->
            <div class="lg:col-span-2">

                <!-- My Courses -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-eia-blue">
                            <?php _e('Mes cours', 'eia-theme'); ?>
                        </h2>
                        <a href="<?php echo admin_url('edit.php?post_type=lp_course'); ?>" class="text-eia-orange hover:underline">
                            <?php _e('Gérer tous les cours', 'eia-theme'); ?> →
                        </a>
                    </div>

                    <?php
                    $args = array(
                        'post_type' => 'lp_course',
                        'author' => $user_id,
                        'posts_per_page' => 5,
                        'post_status' => array('publish', 'draft', 'pending'),
                    );

                    $courses_query = new WP_Query($args);

                    if ($courses_query->have_posts()) :
                        while ($courses_query->have_posts()) : $courses_query->the_post();
                            $course_id = get_the_ID();
                            $students = eia_get_course_students($course_id);
                            $student_count = count($students);
                            $status = get_post_status();
                            ?>
                            <div class="course-item border-b border-gray-200 py-4 last:border-0">
                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <?php if (has_post_thumbnail()) : ?>
                                            <?php the_post_thumbnail('thumbnail', array('class' => 'w-24 h-24 rounded-lg object-cover')); ?>
                                        <?php else : ?>
                                            <div class="w-24 h-24 bg-gray-200 rounded-lg flex items-center justify-center">
                                                <i class="fas fa-book text-gray-400 text-2xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="font-semibold text-gray-900">
                                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                                            </h3>
                                            <?php if ($status === 'draft') : ?>
                                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-700 rounded"><?php _e('Brouillon', 'eia-theme'); ?></span>
                                            <?php elseif ($status === 'pending') : ?>
                                                <span class="px-2 py-1 text-xs bg-yellow-200 text-yellow-800 rounded"><?php _e('En attente', 'eia-theme'); ?></span>
                                            <?php else : ?>
                                                <span class="px-2 py-1 text-xs bg-green-200 text-green-800 rounded"><?php _e('Publié', 'eia-theme'); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex items-center gap-4 text-sm text-gray-600">
                                            <span>
                                                <i class="fas fa-users mr-1"></i>
                                                <?php echo $student_count; ?> <?php _e('étudiants', 'eia-theme'); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-calendar mr-1"></i>
                                                <?php echo get_the_date(); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="<?php echo get_edit_post_link(); ?>" class="px-4 py-2 bg-eia-blue text-white rounded hover:bg-blue-700 text-sm">
                                            <i class="fas fa-edit mr-1"></i>
                                            <?php _e('Modifier', 'eia-theme'); ?>
                                        </a>
                                        <a href="<?php the_permalink(); ?>" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm">
                                            <i class="fas fa-eye mr-1"></i>
                                            <?php _e('Voir', 'eia-theme'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        ?>
                        <div class="text-center py-12">
                            <div class="text-gray-400 mb-4">
                                <i class="fas fa-book text-6xl"></i>
                            </div>
                            <p class="text-gray-600 mb-4">
                                <?php _e('Vous n\'avez pas encore créé de cours.', 'eia-theme'); ?>
                            </p>
                            <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" class="btn-primary">
                                <?php _e('Créer mon premier cours', 'eia-theme'); ?>
                            </a>
                        </div>
                        <?php
                    endif;
                    ?>
                </div>

                <!-- Recent Students -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-bold text-eia-blue mb-6">
                        <?php _e('Étudiants récents', 'eia-theme'); ?>
                    </h2>

                    <div class="space-y-4">
                        <?php
                        // Get recent enrolled students
                        $recent_args = array(
                            'post_type' => 'lp_course',
                            'author' => $user_id,
                            'posts_per_page' => 1,
                            'post_status' => 'publish',
                        );

                        $recent_courses = get_posts($recent_args);

                        if ($recent_courses) :
                            $course_id = $recent_courses[0]->ID;
                            $students = eia_get_course_students($course_id, 5);

                            if ($students) :
                                foreach ($students as $student_id) :
                                    $student = get_userdata($student_id);
                                    if ($student) :
                                        ?>
                                        <div class="flex items-center gap-4 pb-4 border-b border-gray-200 last:border-0">
                                            <div>
                                                <?php echo eia_get_user_avatar($student_id, 48); ?>
                                            </div>
                                            <div class="flex-1">
                                                <h4 class="font-semibold text-gray-900"><?php echo $student->display_name; ?></h4>
                                                <p class="text-sm text-gray-600"><?php echo get_the_title($course_id); ?></p>
                                            </div>
                                        </div>
                                        <?php
                                    endif;
                                endforeach;
                            else :
                                ?>
                                <p class="text-gray-600 text-center py-8">
                                    <?php _e('Aucun étudiant inscrit pour le moment.', 'eia-theme'); ?>
                                </p>
                                <?php
                            endif;
                        else :
                            ?>
                            <p class="text-gray-600 text-center py-8">
                                <?php _e('Créez votre premier cours pour accueillir des étudiants.', 'eia-theme'); ?>
                            </p>
                            <?php
                        endif;
                        ?>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                    <h3 class="text-lg font-bold text-eia-blue mb-4">
                        <?php _e('Actions rapides', 'eia-theme'); ?>
                    </h3>
                    <ul class="space-y-3">
                        <li>
                            <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-plus-circle w-5"></i>
                                <?php _e('Nouveau cours', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('edit.php?post_type=lp_course'); ?>" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-book w-5"></i>
                                <?php _e('Mes cours', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('edit.php?post_type=lp_lesson'); ?>" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-file-alt w-5"></i>
                                <?php _e('Mes leçons', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('edit.php?post_type=lp_quiz'); ?>" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-question-circle w-5"></i>
                                <?php _e('Mes quiz', 'eia-theme'); ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" class="flex items-center gap-3 text-gray-700 hover:text-eia-blue">
                                <i class="fas fa-chart-bar w-5"></i>
                                <?php _e('Statistiques', 'eia-theme'); ?>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="bg-gradient-to-br from-eia-orange to-yellow-500 rounded-lg shadow-sm p-6 text-white">
                    <h3 class="text-lg font-bold mb-4">
                        <?php _e('Ressources', 'eia-theme'); ?>
                    </h3>
                    <p class="text-sm mb-4 text-white/90">
                        <?php _e('Besoin d\'aide pour créer vos cours ?', 'eia-theme'); ?>
                    </p>
                    <a href="#" class="inline-block px-4 py-2 bg-white text-eia-orange rounded hover:bg-gray-100 text-sm font-semibold">
                        <?php _e('Guide du formateur', 'eia-theme'); ?>
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>

<?php get_footer(); ?>