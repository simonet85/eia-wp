<?php
/**
 * Gamification System - Badges & Points
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Gamification {

    private static $instance = null;

    /**
     * Badge definitions with conditions and rewards
     */
    private $badges = array(
        'first_enrollment' => array(
            'name' => 'Première Inscription',
            'description' => 'Inscrit à votre premier cours',
            'icon' => 'fas fa-star',
            'color' => '#3B82F6',
            'points' => 50,
            'condition' => 'enroll_course_count',
            'threshold' => 1
        ),
        'course_collector' => array(
            'name' => 'Collectionneur de Cours',
            'description' => 'Inscrit à 5 cours',
            'icon' => 'fas fa-book-reader',
            'color' => '#8B5CF6',
            'points' => 200,
            'condition' => 'enroll_course_count',
            'threshold' => 5
        ),
        'first_completion' => array(
            'name' => 'Premier Succès',
            'description' => 'Complété votre premier cours',
            'icon' => 'fas fa-trophy',
            'color' => '#F59E0B',
            'points' => 100,
            'condition' => 'completed_course_count',
            'threshold' => 1
        ),
        'course_master' => array(
            'name' => 'Maître des Cours',
            'description' => 'Complété 5 cours',
            'icon' => 'fas fa-graduation-cap',
            'color' => '#10B981',
            'points' => 500,
            'condition' => 'completed_course_count',
            'threshold' => 5
        ),
        'assignment_ace' => array(
            'name' => 'As du Devoir',
            'description' => 'Note parfaite (100%) sur un devoir',
            'icon' => 'fas fa-medal',
            'color' => '#EF4444',
            'points' => 150,
            'condition' => 'perfect_assignment',
            'threshold' => 1
        ),
        'streak_7' => array(
            'name' => 'Série de 7 jours',
            'description' => 'Connecté 7 jours consécutifs',
            'icon' => 'fas fa-fire',
            'color' => '#F97316',
            'points' => 300,
            'condition' => 'login_streak',
            'threshold' => 7
        ),
        'quiz_master' => array(
            'name' => 'Expert Quiz',
            'description' => 'Réussi 10 quiz avec 80%+',
            'icon' => 'fas fa-brain',
            'color' => '#06B6D4',
            'points' => 400,
            'condition' => 'quiz_pass_count',
            'threshold' => 10
        ),
        'speed_learner' => array(
            'name' => 'Apprenant Rapide',
            'description' => 'Complété un cours en moins de 7 jours',
            'icon' => 'fas fa-rocket',
            'color' => '#EC4899',
            'points' => 250,
            'condition' => 'fast_completion',
            'threshold' => 1
        )
    );

    /**
     * XP levels configuration
     */
    private $levels = array(
        1 => 0,
        2 => 100,
        3 => 250,
        4 => 500,
        5 => 1000,
        6 => 2000,
        7 => 3500,
        8 => 5500,
        9 => 8000,
        10 => 12000
    );

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Award badges on specific actions
        add_action('learn-press/user/course-enrolled', array($this, 'on_course_enrolled'), 10, 3);
        add_action('learn-press/user/course-finished', array($this, 'on_course_completed'), 10, 3);
        add_action('eia_assignment_graded', array($this, 'on_assignment_graded'), 10, 3);
        add_action('wp_login', array($this, 'on_user_login'), 10, 2);
    }

    /**
     * Award points to a user
     */
    public function award_points($user_id, $points, $action_type, $description = '', $reference_id = null, $reference_type = null) {
        global $wpdb;

        // Get or create user points record
        $user_points = $this->get_user_points($user_id);

        if (!$user_points) {
            // Create new record
            $wpdb->insert(
                $wpdb->prefix . 'eia_user_points',
                array(
                    'user_id' => $user_id,
                    'points' => $points,
                    'total_xp' => $points,
                    'level' => 1
                ),
                array('%d', '%d', '%d', '%d')
            );
        } else {
            // Update existing record
            $new_total = $user_points->total_xp + $points;
            $new_level = $this->calculate_level($new_total);

            $wpdb->update(
                $wpdb->prefix . 'eia_user_points',
                array(
                    'points' => $user_points->points + $points,
                    'total_xp' => $new_total,
                    'level' => $new_level
                ),
                array('user_id' => $user_id),
                array('%d', '%d', '%d'),
                array('%d')
            );
        }

        // Log in history
        $wpdb->insert(
            $wpdb->prefix . 'eia_points_history',
            array(
                'user_id' => $user_id,
                'action_type' => $action_type,
                'points' => $points,
                'description' => $description,
                'reference_id' => $reference_id,
                'reference_type' => $reference_type
            ),
            array('%d', '%s', '%d', '%s', '%d', '%s')
        );
    }

    /**
     * Award badge to user
     */
    public function award_badge($user_id, $badge_type) {
        global $wpdb;

        // Check if user already has this badge
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}eia_user_badges WHERE user_id = %d AND badge_type = %s",
            $user_id,
            $badge_type
        ));

        if ($exists) {
            return false; // Already has badge
        }

        $badge = $this->badges[$badge_type] ?? null;

        if (!$badge) {
            return false;
        }

        // Insert badge
        $wpdb->insert(
            $wpdb->prefix . 'eia_user_badges',
            array(
                'user_id' => $user_id,
                'badge_type' => $badge_type,
                'badge_name' => $badge['name'],
                'badge_description' => $badge['description'],
                'metadata' => json_encode(array(
                    'icon' => $badge['icon'],
                    'color' => $badge['color']
                ))
            ),
            array('%d', '%s', '%s', '%s', '%s')
        );

        // Award points for earning badge
        $this->award_points(
            $user_id,
            $badge['points'],
            'badge_earned',
            "Badge gagné: {$badge['name']}",
            $wpdb->insert_id,
            'badge'
        );

        return true;
    }

    /**
     * Get user points record
     */
    public function get_user_points($user_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_user_points WHERE user_id = %d",
            $user_id
        ));
    }

    /**
     * Get user badges
     */
    public function get_user_badges($user_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_user_badges WHERE user_id = %d ORDER BY earned_date DESC",
            $user_id
        ));
    }

    /**
     * Calculate level from total XP
     */
    private function calculate_level($total_xp) {
        $level = 1;
        foreach ($this->levels as $lvl => $required_xp) {
            if ($total_xp >= $required_xp) {
                $level = $lvl;
            } else {
                break;
            }
        }
        return $level;
    }

    /**
     * Get XP needed for next level
     */
    public function get_xp_for_next_level($current_level) {
        return $this->levels[$current_level + 1] ?? null;
    }

    /**
     * Check and award badges based on conditions
     */
    private function check_badge_conditions($user_id) {
        global $wpdb;

        foreach ($this->badges as $badge_type => $badge) {
            // Skip if already has badge
            $has_badge = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}eia_user_badges WHERE user_id = %d AND badge_type = %s",
                $user_id,
                $badge_type
            ));

            if ($has_badge) {
                continue;
            }

            $condition_met = false;

            switch ($badge['condition']) {
                case 'enroll_course_count':
                    $count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}learnpress_user_items
                        WHERE user_id = %d AND item_type = 'lp_course'",
                        $user_id
                    ));
                    $condition_met = ($count >= $badge['threshold']);
                    break;

                case 'completed_course_count':
                    $count = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}learnpress_user_items
                        WHERE user_id = %d AND item_type = 'lp_course' AND status = 'finished'",
                        $user_id
                    ));
                    $condition_met = ($count >= $badge['threshold']);
                    break;

                case 'perfect_assignment':
                    $perfect = $wpdb->get_var($wpdb->prepare(
                        "SELECT COUNT(*) FROM {$wpdb->prefix}eia_assignment_submissions s
                        INNER JOIN {$wpdb->posts} a ON s.assignment_id = a.ID
                        WHERE s.student_id = %d AND s.status = 'graded'
                        AND s.grade = (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = a.ID AND meta_key = '_assignment_max_grade')",
                        $user_id
                    ));
                    $condition_met = ($perfect >= $badge['threshold']);
                    break;
            }

            if ($condition_met) {
                $this->award_badge($user_id, $badge_type);
            }
        }
    }

    /**
     * Event: User enrolled in course
     */
    public function on_course_enrolled($course_id, $user_id, $result) {
        // Award points for enrollment
        $course = get_post($course_id);
        $this->award_points(
            $user_id,
            25,
            'course_enrolled',
            "Inscription au cours: {$course->post_title}",
            $course_id,
            'course'
        );

        // Check badge conditions
        $this->check_badge_conditions($user_id);
    }

    /**
     * Event: User completed course
     */
    public function on_course_completed($course_id, $user_id, $result) {
        // Award points for completion
        $course = get_post($course_id);
        $this->award_points(
            $user_id,
            100,
            'course_completed',
            "Cours terminé: {$course->post_title}",
            $course_id,
            'course'
        );

        // Check badge conditions
        $this->check_badge_conditions($user_id);
    }

    /**
     * Event: Assignment graded
     */
    public function on_assignment_graded($submission_id, $grade, $max_grade) {
        global $wpdb;

        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_assignment_submissions WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            return;
        }

        $assignment = get_post($submission->assignment_id);
        $percentage = ($grade / $max_grade) * 100;

        // Award points based on grade
        if ($percentage >= 90) {
            $points = 50;
        } elseif ($percentage >= 70) {
            $points = 30;
        } else {
            $points = 10;
        }

        $this->award_points(
            $submission->student_id,
            $points,
            'assignment_graded',
            "Devoir noté: {$assignment->post_title} ({$grade}/{$max_grade})",
            $submission->assignment_id,
            'assignment'
        );

        // Check badge conditions
        $this->check_badge_conditions($submission->student_id);
    }

    /**
     * Event: User login
     */
    public function on_user_login($user_login, $user) {
        // Award daily login points (once per day)
        $last_login = get_user_meta($user->ID, '_eia_last_login_date', true);
        $today = date('Y-m-d');

        if ($last_login !== $today) {
            $this->award_points(
                $user->ID,
                10,
                'daily_login',
                'Connexion quotidienne',
                null,
                null
            );

            update_user_meta($user->ID, '_eia_last_login_date', $today);
        }
    }

    /**
     * Get leaderboard
     */
    public function get_leaderboard($limit = 10) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, u.display_name, u.user_email
            FROM {$wpdb->prefix}eia_user_points p
            INNER JOIN {$wpdb->users} u ON p.user_id = u.ID
            ORDER BY p.total_xp DESC
            LIMIT %d",
            $limit
        ));
    }

    /**
     * Get user rank
     */
    public function get_user_rank($user_id) {
        global $wpdb;

        $rank = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) + 1 FROM {$wpdb->prefix}eia_user_points
            WHERE total_xp > (SELECT total_xp FROM {$wpdb->prefix}eia_user_points WHERE user_id = %d)",
            $user_id
        ));

        return $rank ?: 'N/A';
    }

    /**
     * Get available badges info
     */
    public function get_all_badges() {
        return $this->badges;
    }
}

// Initialize
EIA_Gamification::get_instance();
