<?php
/**
 * EIA Notifications System - BuddyPress Integration
 *
 * Utilise l'API BuddyPress Notifications pour les notifications LMS
 * - Notifications in-app via BuddyPress
 * - Email notifications optionnelles
 * - Intégration avec les événements LMS (devoirs, forum, XP, etc.)
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Notifications {

    private static $instance = null;

    // Component slugs for BuddyPress
    const COMPONENT_ASSIGNMENT = 'eia_assignment';
    const COMPONENT_FORUM = 'eia_forum';
    const COMPONENT_COURSE = 'eia_course';
    const COMPONENT_GAMIFICATION = 'eia_gamification';
    const COMPONENT_CERTIFICATE = 'eia_certificate';

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
        // Vérifier que BuddyPress est actif
        if (!function_exists('bp_is_active') || !bp_is_active('notifications')) {
            return;
        }

        // Register notification format callbacks for each component
        add_filter('bp_notifications_get_notifications_for_user', array($this, 'format_notifications_filter'), 10, 8);

        // Register callback for each component via the registered_components filter
        add_filter('bp_notifications_get_registered_components', array($this, 'register_components'));

        // Hooks pour générer des notifications automatiques
        $this->register_notification_triggers();
    }

    /**
     * Register our components with BuddyPress
     */
    public function register_components($component_names = array()) {
        $component_names[] = self::COMPONENT_ASSIGNMENT;
        $component_names[] = self::COMPONENT_FORUM;
        $component_names[] = self::COMPONENT_COURSE;
        $component_names[] = self::COMPONENT_GAMIFICATION;
        $component_names[] = self::COMPONENT_CERTIFICATE;

        return $component_names;
    }

    /**
     * Register notification triggers for various events
     */
    private function register_notification_triggers() {
        // Devoir soumis (notifier l'instructeur)
        add_action('eia_assignment_submitted', array($this, 'notify_assignment_submitted'), 10, 3);

        // Devoir noté (notifier l'étudiant)
        add_action('eia_assignment_graded', array($this, 'notify_assignment_graded'), 10, 3);

        // Nouvelle réponse au forum (notifier l'auteur du topic)
        add_action('eia_forum_reply_created', array($this, 'notify_forum_reply'), 10, 3);

        // Réponse marquée comme meilleure (notifier l'auteur)
        add_action('eia_forum_best_answer_marked', array($this, 'notify_best_answer'), 10, 3);

        // Nouveau cours disponible (notifier tous les étudiants)
        add_action('publish_lp_course', array($this, 'notify_new_course'), 10, 2);

        // Certificat obtenu (notifier l'étudiant)
        add_action('eia_certificate_earned', array($this, 'notify_certificate'), 10, 2);

        // Points XP gagnés (notifier l'étudiant)
        add_action('eia_xp_awarded', array($this, 'notify_xp_earned'), 10, 3);

        // Badge débloqué (notifier l'étudiant)
        add_action('eia_badge_earned', array($this, 'notify_badge_earned'), 10, 2);

        // Rappel d'échéance de devoir (24h avant)
        add_action('eia_assignment_reminder', array($this, 'notify_assignment_reminder'), 10, 2);
    }

    /**
     * Create a BuddyPress notification
     *
     * @param int $user_id User to notify
     * @param string $component Component slug
     * @param string $action Action type
     * @param int $item_id Primary item ID
     * @param int $secondary_item_id Secondary item ID
     * @param array $extra_data Additional data
     */
    private function create_notification($user_id, $component, $action, $item_id, $secondary_item_id = 0, $extra_data = array()) {
        if (!function_exists('bp_notifications_add_notification')) {
            return false;
        }

        return bp_notifications_add_notification(array(
            'user_id'           => $user_id,
            'item_id'           => $item_id,
            'secondary_item_id' => $secondary_item_id,
            'component_name'    => $component,
            'component_action'  => $action,
            'date_notified'     => bp_core_current_time(),
            'is_new'            => 1,
        ));
    }

    /**
     * Format notifications for display via BuddyPress filter
     */
    public function format_notifications_filter($action, $item_id, $secondary_item_id, $total_items, $format = 'string', $component_action = '', $component_name = '', $id = 0) {
        // Only process our custom components
        if (!in_array($component_name, array(
            self::COMPONENT_ASSIGNMENT,
            self::COMPONENT_FORUM,
            self::COMPONENT_COURSE,
            self::COMPONENT_GAMIFICATION,
            self::COMPONENT_CERTIFICATE
        ))) {
            return $action;
        }

        // Get formatted text based on component
        if ($component_name === self::COMPONENT_ASSIGNMENT) {
            return $this->format_assignment_notification($component_action, $item_id, $secondary_item_id, $total_items, $format, $id);
        } elseif ($component_name === self::COMPONENT_FORUM) {
            return $this->format_forum_notification($component_action, $item_id, $secondary_item_id, $total_items, $format, $id);
        } elseif ($component_name === self::COMPONENT_COURSE) {
            return $this->format_course_notification($component_action, $item_id, $secondary_item_id, $total_items, $format, $id);
        } elseif ($component_name === self::COMPONENT_GAMIFICATION) {
            return $this->format_gamification_notification($component_action, $item_id, $secondary_item_id, $total_items, $format, $id);
        } elseif ($component_name === self::COMPONENT_CERTIFICATE) {
            return $this->format_certificate_notification($component_action, $item_id, $secondary_item_id, $total_items, $format, $id);
        }

        return $action;
    }

    /**
     * Format assignment notifications
     */
    private function format_assignment_notification($action, $item_id, $secondary_item_id, $total_items, $format, $id) {
        $assignment = get_post($item_id);
        if (!$assignment) {
            return '';
        }

        if ($action === 'assignment_submitted') {
            $student = get_userdata($secondary_item_id);
            $text = sprintf('%s a soumis le devoir "%s"', $student->display_name, $assignment->post_title);
            $link = admin_url("post.php?post={$item_id}&action=edit");
        } elseif ($action === 'assignment_graded') {
            $text = sprintf('Votre devoir "%s" a été noté', $assignment->post_title);
            $link = get_permalink($item_id);
        } elseif ($action === 'assignment_reminder') {
            $text = sprintf('Rappel: le devoir "%s" est à rendre bientôt', $assignment->post_title);
            $link = get_permalink($item_id);
        }

        if ($format === 'string') {
            return '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>';
        } else {
            return array(
                'text' => $text,
                'link' => $link
            );
        }
    }

    /**
     * Format forum notifications
     */
    private function format_forum_notification($action, $item_id, $secondary_item_id, $total_items, $format, $id) {
        global $wpdb;

        if ($action === 'forum_reply') {
            $topic = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}eia_forum_topics WHERE id = %d",
                $item_id
            ));

            if ($topic) {
                $author = get_userdata($secondary_item_id);
                $text = sprintf('%s a répondu à votre question "%s"', $author->display_name, $topic->title);
                $link = site_url("/forum-cours/?course_id={$topic->course_id}&topic_id={$item_id}");
            }
        } elseif ($action === 'forum_best_answer') {
            $text = 'Votre réponse a été marquée comme la meilleure! +15 XP';
            $link = site_url("/forum-cours/?topic_id={$item_id}");
        }

        if ($format === 'string') {
            return '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>';
        } else {
            return array(
                'text' => $text,
                'link' => $link
            );
        }
    }

    /**
     * Format course notifications
     */
    private function format_course_notification($action, $item_id, $secondary_item_id, $total_items, $format, $id) {
        $course = get_post($item_id);
        if (!$course) {
            return '';
        }

        if ($action === 'new_course') {
            $text = sprintf('Nouveau cours disponible: "%s"', $course->post_title);
            $link = get_permalink($item_id);
        }

        if ($format === 'string') {
            return '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>';
        } else {
            return array(
                'text' => $text,
                'link' => $link
            );
        }
    }

    /**
     * Format gamification notifications
     */
    private function format_gamification_notification($action, $item_id, $secondary_item_id, $total_items, $format, $id) {
        if ($action === 'xp_earned') {
            $text = sprintf('Vous avez gagné +%d XP!', $item_id);
            $link = site_url('/mes-cours/');
        } elseif ($action === 'badge_earned') {
            $text = sprintf('Nouveau badge débloqué: %s', get_post_meta($item_id, '_badge_name', true));
            $link = site_url('/mes-cours/');
        }

        if ($format === 'string') {
            return '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>';
        } else {
            return array(
                'text' => $text,
                'link' => $link
            );
        }
    }

    /**
     * Format certificate notifications
     */
    private function format_certificate_notification($action, $item_id, $secondary_item_id, $total_items, $format, $id) {
        $course = get_post($item_id);
        if (!$course) {
            return '';
        }

        if ($action === 'certificate_earned') {
            $text = sprintf('Félicitations! Certificat obtenu pour "%s"', $course->post_title);
            $link = site_url("/certificat/?course_id={$item_id}");
        }

        if ($format === 'string') {
            return '<a href="' . esc_url($link) . '">' . esc_html($text) . '</a>';
        } else {
            return array(
                'text' => $text,
                'link' => $link
            );
        }
    }

    // ==================== NOTIFICATION TRIGGERS ====================

    /**
     * Notify: Assignment submitted
     */
    public function notify_assignment_submitted($assignment_id, $user_id, $submission_id) {
        $assignment = get_post($assignment_id);
        if (!$assignment) {
            return;
        }

        $instructor_id = $assignment->post_author;

        $this->create_notification(
            $instructor_id,
            self::COMPONENT_ASSIGNMENT,
            'assignment_submitted',
            $assignment_id,
            $user_id
        );
    }

    /**
     * Notify: Assignment graded
     */
    public function notify_assignment_graded($assignment_id, $user_id, $grade) {
        $this->create_notification(
            $user_id,
            self::COMPONENT_ASSIGNMENT,
            'assignment_graded',
            $assignment_id,
            0
        );
    }

    /**
     * Notify: Forum reply
     */
    public function notify_forum_reply($topic_id, $reply_id, $author_id) {
        global $wpdb;

        $topic = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_forum_topics WHERE id = %d",
            $topic_id
        ));

        if (!$topic || $topic->user_id == $author_id) {
            return; // Ne pas notifier l'auteur de sa propre réponse
        }

        $this->create_notification(
            $topic->user_id,
            self::COMPONENT_FORUM,
            'forum_reply',
            $topic_id,
            $author_id
        );
    }

    /**
     * Notify: Best answer marked
     */
    public function notify_best_answer($topic_id, $reply_id, $author_id) {
        $this->create_notification(
            $author_id,
            self::COMPONENT_FORUM,
            'forum_best_answer',
            $topic_id,
            $reply_id
        );
    }

    /**
     * Notify: New course published
     */
    public function notify_new_course($post_id, $post) {
        if ($post->post_status !== 'publish' || wp_is_post_revision($post_id)) {
            return;
        }

        // Notifier tous les étudiants
        $students = get_users(array('role' => 'student'));

        foreach ($students as $student) {
            $this->create_notification(
                $student->ID,
                self::COMPONENT_COURSE,
                'new_course',
                $post_id,
                0
            );
        }
    }

    /**
     * Notify: Certificate earned
     */
    public function notify_certificate($user_id, $course_id) {
        $this->create_notification(
            $user_id,
            self::COMPONENT_CERTIFICATE,
            'certificate_earned',
            $course_id,
            0
        );
    }

    /**
     * Notify: XP earned
     */
    public function notify_xp_earned($user_id, $xp_amount, $reason) {
        $this->create_notification(
            $user_id,
            self::COMPONENT_GAMIFICATION,
            'xp_earned',
            $xp_amount,
            0
        );
    }

    /**
     * Notify: Badge earned
     */
    public function notify_badge_earned($user_id, $badge_name) {
        $this->create_notification(
            $user_id,
            self::COMPONENT_GAMIFICATION,
            'badge_earned',
            0,
            0
        );
    }

    /**
     * Notify: Assignment reminder (24h before due)
     */
    public function notify_assignment_reminder($assignment_id, $user_id) {
        $this->create_notification(
            $user_id,
            self::COMPONENT_ASSIGNMENT,
            'assignment_reminder',
            $assignment_id,
            0
        );
    }
}
