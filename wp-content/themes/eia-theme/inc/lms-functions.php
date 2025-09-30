<?php
/**
 * LMS Functions for EIA Theme
 *
 * @package EIA_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get user dashboard URL based on role
 */
function eia_get_user_dashboard_url($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (eia_is_student($user_id)) {
        return home_url('/student-dashboard/');
    } elseif (eia_is_instructor($user_id)) {
        return home_url('/instructor-dashboard/');
    } elseif (eia_is_lms_manager($user_id)) {
        return admin_url();
    }

    return home_url();
}

/**
 * Get enrolled courses count for user
 */
function eia_get_user_enrolled_courses_count($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // If LearnPress is active
    if (function_exists('learn_press_get_user')) {
        $user = learn_press_get_user($user_id);
        return $user ? $user->get_enrolled_courses_count() : 0;
    }

    return 0;
}

/**
 * Get completed courses count for user
 */
function eia_get_user_completed_courses_count($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // If LearnPress is active
    if (function_exists('learn_press_get_user')) {
        $user = learn_press_get_user($user_id);
        return $user ? $user->get_completed_courses_count() : 0;
    }

    return 0;
}

/**
 * Get user's course progress
 */
function eia_get_course_progress($course_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // If LearnPress is active
    if (function_exists('learn_press_get_user')) {
        $user = learn_press_get_user($user_id);
        if ($user) {
            $progress = $user->get_course_data($course_id);
            return $progress ? $progress->get_percent_result() : 0;
        }
    }

    return 0;
}

/**
 * Check if user is enrolled in course
 */
function eia_is_user_enrolled($course_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // If LearnPress is active
    if (function_exists('learn_press_get_user')) {
        $user = learn_press_get_user($user_id);
        return $user ? $user->has_enrolled_course($course_id) : false;
    }

    return false;
}

/**
 * Get instructor courses count
 */
function eia_get_instructor_courses_count($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $args = array(
        'post_type' => 'lp_course',
        'author' => $user_id,
        'post_status' => array('publish', 'pending', 'draft'),
        'posts_per_page' => -1,
    );

    $courses = new WP_Query($args);
    return $courses->found_posts;
}

/**
 * Get instructor students count
 */
function eia_get_instructor_students_count($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // Get all courses by instructor
    $args = array(
        'post_type' => 'lp_course',
        'author' => $user_id,
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );

    $courses = get_posts($args);
    $students = array();

    foreach ($courses as $course) {
        if (function_exists('learn_press_get_course_students')) {
            $course_students = learn_press_get_course_students($course->ID);
            if ($course_students) {
                $students = array_merge($students, $course_students);
            }
        }
    }

    // Remove duplicates
    $students = array_unique($students);
    return count($students);
}

/**
 * Format course duration
 */
function eia_format_course_duration($duration) {
    if (empty($duration)) {
        return __('Non spécifié', 'eia-theme');
    }

    // Parse duration (format: "10 weeks" or "2 months")
    if (strpos($duration, 'week') !== false) {
        $weeks = intval($duration);
        return sprintf(_n('%s semaine', '%s semaines', $weeks, 'eia-theme'), $weeks);
    } elseif (strpos($duration, 'month') !== false) {
        $months = intval($duration);
        return sprintf(_n('%s mois', '%s mois', $months, 'eia-theme'), $months);
    } elseif (strpos($duration, 'day') !== false) {
        $days = intval($duration);
        return sprintf(_n('%s jour', '%s jours', $days, 'eia-theme'), $days);
    }

    return $duration;
}

/**
 * Get course difficulty level
 */
function eia_get_course_difficulty($course_id) {
    $difficulty = get_post_meta($course_id, '_lp_level', true);

    $levels = array(
        'beginner' => __('Débutant', 'eia-theme'),
        'intermediate' => __('Intermédiaire', 'eia-theme'),
        'advanced' => __('Avancé', 'eia-theme'),
        'expert' => __('Expert', 'eia-theme'),
    );

    return isset($levels[$difficulty]) ? $levels[$difficulty] : __('Non spécifié', 'eia-theme');
}

/**
 * Get course rating
 */
function eia_get_course_rating($course_id) {
    if (function_exists('learn_press_get_course_rate')) {
        return learn_press_get_course_rate($course_id);
    }

    return 0;
}

/**
 * Display course badge
 */
function eia_display_course_badge($course_id) {
    $is_featured = get_post_meta($course_id, '_lp_featured', true);
    $is_new = (time() - get_post_time('U', false, $course_id)) < (30 * DAY_IN_SECONDS);

    if ($is_featured) {
        echo '<span class="course-badge featured">' . __('Populaire', 'eia-theme') . '</span>';
    } elseif ($is_new) {
        echo '<span class="course-badge new">' . __('Nouveau', 'eia-theme') . '</span>';
    }
}

/**
 * Get user badges count
 */
function eia_get_user_badges_count($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // If GamiPress is active
    if (function_exists('gamipress_get_user_achievements')) {
        $achievements = gamipress_get_user_achievements(array(
            'user_id' => $user_id,
            'achievement_type' => 'badge',
        ));
        return count($achievements);
    }

    return 0;
}

/**
 * Get user points
 */
function eia_get_user_points($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    // If GamiPress is active
    if (function_exists('gamipress_get_user_points')) {
        return gamipress_get_user_points($user_id);
    }

    return 0;
}

/**
 * Display user avatar with fallback
 */
function eia_get_user_avatar($user_id = null, $size = 96) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $avatar = get_avatar($user_id, $size, '', '', array('class' => 'rounded-full'));

    if (!$avatar) {
        // Fallback avatar
        $user = get_userdata($user_id);
        $initials = '';
        if ($user) {
            $name_parts = explode(' ', $user->display_name);
            foreach ($name_parts as $part) {
                $initials .= strtoupper(substr($part, 0, 1));
            }
        }
        $avatar = '<div class="avatar-fallback rounded-full" style="width:' . $size . 'px;height:' . $size . 'px;">' . $initials . '</div>';
    }

    return $avatar;
}

/**
 * Get recent course announcements
 */
function eia_get_recent_announcements($course_id = null, $limit = 5) {
    $args = array(
        'post_type' => 'lp_announcement',
        'posts_per_page' => $limit,
        'post_status' => 'publish',
        'orderby' => 'date',
        'order' => 'DESC',
    );

    if ($course_id) {
        $args['meta_query'] = array(
            array(
                'key' => '_lp_course_id',
                'value' => $course_id,
                'compare' => '=',
            ),
        );
    }

    return get_posts($args);
}

/**
 * Format lesson duration
 */
function eia_format_lesson_duration($minutes) {
    if ($minutes < 60) {
        return sprintf(_n('%s minute', '%s minutes', $minutes, 'eia-theme'), $minutes);
    }

    $hours = floor($minutes / 60);
    $remaining_minutes = $minutes % 60;

    if ($remaining_minutes > 0) {
        return sprintf(__('%sh %smin', 'eia-theme'), $hours, $remaining_minutes);
    }

    return sprintf(_n('%s heure', '%s heures', $hours, 'eia-theme'), $hours);
}

/**
 * Check if course has prerequisites
 */
function eia_course_has_prerequisites($course_id) {
    $prerequisites = get_post_meta($course_id, '_lp_course_prerequisite', true);
    return !empty($prerequisites);
}

/**
 * Get course students list
 */
function eia_get_course_students($course_id, $limit = -1) {
    if (!function_exists('learn_press_get_course_students')) {
        return array();
    }

    $students = learn_press_get_course_students($course_id, $limit);
    return $students ? $students : array();
}

/**
 * Calculate user's overall progress
 */
function eia_calculate_overall_progress($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    $enrolled = eia_get_user_enrolled_courses_count($user_id);
    $completed = eia_get_user_completed_courses_count($user_id);

    if ($enrolled == 0) {
        return 0;
    }

    return round(($completed / $enrolled) * 100);
}

/**
 * Get next lesson for user in course
 */
function eia_get_next_lesson($course_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }

    if (function_exists('learn_press_get_user')) {
        $user = learn_press_get_user($user_id);
        if ($user) {
            $course_data = $user->get_course_data($course_id);
            if ($course_data) {
                return $course_data->get_next_item();
            }
        }
    }

    return null;
}

/**
 * Display breadcrumb for LMS pages
 */
function eia_lms_breadcrumb() {
    if (!is_singular('lp_course') && !is_singular('lp_lesson') && !is_singular('lp_quiz')) {
        return;
    }

    echo '<nav class="eia-breadcrumb" aria-label="breadcrumb">';
    echo '<ol class="breadcrumb">';
    echo '<li class="breadcrumb-item"><a href="' . home_url() . '">' . __('Accueil', 'eia-theme') . '</a></li>';

    if (is_singular('lp_course')) {
        echo '<li class="breadcrumb-item"><a href="' . learn_press_get_page_link('courses') . '">' . __('Cours', 'eia-theme') . '</a></li>';
        echo '<li class="breadcrumb-item active">' . get_the_title() . '</li>';
    }

    echo '</ol>';
    echo '</nav>';
}
?>