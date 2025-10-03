<?php
/**
 * EIA Notifications System
 *
 * Système de notifications en temps réel pour les étudiants et instructeurs
 * - Notifications in-app avec badge de compteur
 * - Notifications email (instantanées ou digest)
 * - Préférences par type de notification
 * - Marquer comme lu/non-lu
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Notifications {

    private static $instance = null;

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
        // AJAX handlers
        add_action('wp_ajax_eia_get_notifications', array($this, 'ajax_get_notifications'));
        add_action('wp_ajax_eia_mark_notification_read', array($this, 'ajax_mark_read'));
        add_action('wp_ajax_eia_mark_all_read', array($this, 'ajax_mark_all_read'));
        add_action('wp_ajax_eia_delete_notification', array($this, 'ajax_delete_notification'));
        add_action('wp_ajax_eia_get_unread_count', array($this, 'ajax_get_unread_count'));

        // Admin bar notification badge
        add_action('admin_bar_menu', array($this, 'add_notification_badge'), 999);

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Hooks pour générer des notifications automatiques
        $this->register_notification_triggers();

        // Cron pour les emails digest
        add_action('eia_send_notification_digest', array($this, 'send_daily_digest'));
        if (!wp_next_scheduled('eia_send_notification_digest')) {
            wp_schedule_event(strtotime('tomorrow 8:00'), 'daily', 'eia_send_notification_digest');
        }
    }

    /**
     * Create notifications table
     */
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_notifications';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            message text NOT NULL,
            action_url varchar(500) DEFAULT NULL,
            icon varchar(50) DEFAULT 'bell',
            is_read tinyint(1) DEFAULT 0,
            email_sent tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            read_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY is_read (is_read),
            KEY created_at (created_at),
            KEY type (type)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Table des préférences
        $prefs_table = $wpdb->prefix . 'eia_notification_preferences';
        $sql_prefs = "CREATE TABLE IF NOT EXISTS $prefs_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            notification_type varchar(50) NOT NULL,
            in_app tinyint(1) DEFAULT 1,
            email_instant tinyint(1) DEFAULT 0,
            email_digest tinyint(1) DEFAULT 1,
            PRIMARY KEY (id),
            UNIQUE KEY user_type (user_id, notification_type)
        ) $charset_collate;";

        dbDelta($sql_prefs);
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
     * Create a notification
     */
    public function create_notification($user_id, $type, $title, $message, $action_url = null, $icon = 'bell') {
        global $wpdb;

        // Vérifier les préférences de l'utilisateur
        $prefs = $this->get_user_preferences($user_id, $type);

        // Créer notification in-app si activée
        if ($prefs['in_app']) {
            $wpdb->insert(
                $wpdb->prefix . 'eia_notifications',
                array(
                    'user_id' => $user_id,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'action_url' => $action_url,
                    'icon' => $icon,
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s')
            );

            $notification_id = $wpdb->insert_id;
        }

        // Envoyer email instantané si activé
        if ($prefs['email_instant']) {
            $this->send_instant_email($user_id, $title, $message, $action_url);
        }

        return $notification_id ?? null;
    }

    /**
     * Get user notification preferences
     */
    private function get_user_preferences($user_id, $type) {
        global $wpdb;

        $prefs = $wpdb->get_row($wpdb->prepare(
            "SELECT in_app, email_instant, email_digest
            FROM {$wpdb->prefix}eia_notification_preferences
            WHERE user_id = %d AND notification_type = %s",
            $user_id, $type
        ), ARRAY_A);

        // Valeurs par défaut si pas de préférences
        if (!$prefs) {
            return array(
                'in_app' => true,
                'email_instant' => false,
                'email_digest' => true
            );
        }

        return $prefs;
    }

    /**
     * Get notifications for a user
     */
    public function get_notifications($user_id, $limit = 20, $offset = 0, $unread_only = false) {
        global $wpdb;

        $where = $wpdb->prepare("user_id = %d", $user_id);
        if ($unread_only) {
            $where .= " AND is_read = 0";
        }

        $notifications = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_notifications
            WHERE $where
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d",
            $limit, $offset
        ));

        return $notifications;
    }

    /**
     * Get unread count
     */
    public function get_unread_count($user_id) {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}eia_notifications
            WHERE user_id = %d AND is_read = 0",
            $user_id
        ));
    }

    /**
     * Mark notification as read
     */
    public function mark_as_read($notification_id, $user_id = null) {
        global $wpdb;

        $where = array('id' => $notification_id);
        if ($user_id) {
            $where['user_id'] = $user_id;
        }

        return $wpdb->update(
            $wpdb->prefix . 'eia_notifications',
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            $where,
            array('%d', '%s'),
            array('%d')
        );
    }

    /**
     * Mark all notifications as read
     */
    public function mark_all_read($user_id) {
        global $wpdb;

        return $wpdb->update(
            $wpdb->prefix . 'eia_notifications',
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            array('user_id' => $user_id, 'is_read' => 0),
            array('%d', '%s'),
            array('%d', '%d')
        );
    }

    /**
     * Delete notification
     */
    public function delete_notification($notification_id, $user_id = null) {
        global $wpdb;

        $where = array('id' => $notification_id);
        if ($user_id) {
            $where['user_id'] = $user_id;
        }

        return $wpdb->delete(
            $wpdb->prefix . 'eia_notifications',
            $where,
            array('%d')
        );
    }

    /**
     * AJAX: Get notifications
     */
    public function ajax_get_notifications() {
        check_ajax_referer('eia-notifications-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $unread_only = isset($_POST['unread_only']) ? (bool) $_POST['unread_only'] : false;

        $notifications = $this->get_notifications($user_id, $limit, $offset, $unread_only);
        $unread_count = $this->get_unread_count($user_id);

        wp_send_json_success(array(
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ));
    }

    /**
     * AJAX: Mark notification as read
     */
    public function ajax_mark_read() {
        check_ajax_referer('eia-notifications-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
        if (!$notification_id) {
            wp_send_json_error(array('message' => 'ID de notification invalide'));
        }

        $result = $this->mark_as_read($notification_id, $user_id);

        if ($result !== false) {
            $unread_count = $this->get_unread_count($user_id);
            wp_send_json_success(array('unread_count' => $unread_count));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la mise à jour'));
        }
    }

    /**
     * AJAX: Mark all as read
     */
    public function ajax_mark_all_read() {
        check_ajax_referer('eia-notifications-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $this->mark_all_read($user_id);
        wp_send_json_success(array('unread_count' => 0));
    }

    /**
     * AJAX: Delete notification
     */
    public function ajax_delete_notification() {
        check_ajax_referer('eia-notifications-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;
        if (!$notification_id) {
            wp_send_json_error(array('message' => 'ID de notification invalide'));
        }

        $result = $this->delete_notification($notification_id, $user_id);

        if ($result !== false) {
            $unread_count = $this->get_unread_count($user_id);
            wp_send_json_success(array('unread_count' => $unread_count));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
        }
    }

    /**
     * AJAX: Get unread count
     */
    public function ajax_get_unread_count() {
        check_ajax_referer('eia-notifications-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $count = $this->get_unread_count($user_id);
        wp_send_json_success(array('count' => $count));
    }

    /**
     * Add notification badge to admin bar
     */
    public function add_notification_badge($wp_admin_bar) {
        if (!is_user_logged_in()) {
            return;
        }

        $user_id = get_current_user_id();
        $unread_count = $this->get_unread_count($user_id);

        $badge_html = $unread_count > 0 ? ' <span class="eia-notification-badge">' . $unread_count . '</span>' : '';

        $wp_admin_bar->add_node(array(
            'id'    => 'eia-notifications',
            'title' => '<i class="fas fa-bell"></i>' . $badge_html,
            'href'  => '#',
            'meta'  => array(
                'class' => 'eia-notifications-menu',
                'onclick' => 'return false;'
            ),
        ));
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_user_logged_in()) {
            return;
        }

        wp_enqueue_style(
            'eia-notifications',
            EIA_LMS_CORE_PLUGIN_URL . 'assets/css/notifications.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'eia-notifications',
            EIA_LMS_CORE_PLUGIN_URL . 'assets/js/notifications.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('eia-notifications', 'eiaNotifications', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eia-notifications-nonce'),
            'user_id' => get_current_user_id(),
        ));
    }

    /**
     * Send instant email notification
     */
    private function send_instant_email($user_id, $title, $message, $action_url = null) {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        $subject = '[EIA] ' . $title;

        $body = "Bonjour {$user->display_name},\n\n";
        $body .= strip_tags($message) . "\n\n";

        if ($action_url) {
            $body .= "Voir les détails: {$action_url}\n\n";
        }

        $body .= "---\n";
        $body .= "École Internationale des Affaires\n";
        $body .= site_url();

        return wp_mail($user->user_email, $subject, $body);
    }

    /**
     * Send daily digest email
     */
    public function send_daily_digest() {
        global $wpdb;

        // Récupérer tous les utilisateurs avec notifications non lues
        $users_with_notifications = $wpdb->get_col(
            "SELECT DISTINCT user_id
            FROM {$wpdb->prefix}eia_notifications
            WHERE is_read = 0
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)"
        );

        foreach ($users_with_notifications as $user_id) {
            // Vérifier si l'utilisateur veut recevoir le digest
            $prefs = $wpdb->get_results($wpdb->prepare(
                "SELECT notification_type FROM {$wpdb->prefix}eia_notification_preferences
                WHERE user_id = %d AND email_digest = 1",
                $user_id
            ));

            if (empty($prefs)) {
                continue; // Pas de préférences ou digest désactivé
            }

            // Récupérer les notifications non lues des dernières 24h
            $notifications = $this->get_notifications($user_id, 50, 0, true);

            if (empty($notifications)) {
                continue;
            }

            $this->send_digest_email($user_id, $notifications);
        }
    }

    /**
     * Send digest email to user
     */
    private function send_digest_email($user_id, $notifications) {
        $user = get_userdata($user_id);
        if (!$user) {
            return false;
        }

        $count = count($notifications);
        $subject = "[EIA] Vous avez {$count} notification" . ($count > 1 ? 's' : '') . " non lue" . ($count > 1 ? 's' : '');

        $body = "Bonjour {$user->display_name},\n\n";
        $body .= "Voici un résumé de vos notifications des dernières 24 heures:\n\n";

        foreach ($notifications as $notif) {
            $body .= "• " . $notif->title . "\n";
            $body .= "  " . strip_tags($notif->message) . "\n";
            if ($notif->action_url) {
                $body .= "  → " . $notif->action_url . "\n";
            }
            $body .= "\n";
        }

        $body .= "---\n";
        $body .= "Connectez-vous pour voir toutes vos notifications: " . site_url('/mes-cours/') . "\n\n";
        $body .= "École Internationale des Affaires\n";
        $body .= site_url();

        return wp_mail($user->user_email, $subject, $body);
    }

    // ==================== NOTIFICATION TRIGGERS ====================

    /**
     * Notify: Assignment submitted
     */
    public function notify_assignment_submitted($assignment_id, $user_id, $submission_id) {
        $assignment = get_post($assignment_id);
        $student = get_userdata($user_id);
        $instructor_id = $assignment->post_author;

        $this->create_notification(
            $instructor_id,
            'assignment_submitted',
            'Nouveau devoir soumis',
            "<strong>{$student->display_name}</strong> a soumis le devoir <strong>{$assignment->post_title}</strong>.",
            admin_url("post.php?post={$assignment_id}&action=edit"),
            'file-alt'
        );
    }

    /**
     * Notify: Assignment graded
     */
    public function notify_assignment_graded($assignment_id, $user_id, $grade) {
        $assignment = get_post($assignment_id);

        $this->create_notification(
            $user_id,
            'assignment_graded',
            'Devoir noté',
            "Votre devoir <strong>{$assignment->post_title}</strong> a été noté: <strong>{$grade}/100</strong>",
            get_permalink($assignment_id),
            'check-circle'
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

        $author = get_userdata($author_id);

        $this->create_notification(
            $topic->user_id,
            'forum_reply',
            'Nouvelle réponse à votre question',
            "<strong>{$author->display_name}</strong> a répondu à votre question: <strong>{$topic->title}</strong>",
            site_url("/forum-cours/?course_id={$topic->course_id}&topic_id={$topic_id}"),
            'comments'
        );
    }

    /**
     * Notify: Best answer marked
     */
    public function notify_best_answer($topic_id, $reply_id, $author_id) {
        $author = get_userdata($author_id);

        $this->create_notification(
            $author_id,
            'forum_best_answer',
            'Meilleure réponse sélectionnée! 🎉',
            "Votre réponse a été marquée comme la meilleure! Vous avez gagné <strong>+15 XP</strong>",
            site_url("/forum-cours/?topic_id={$topic_id}"),
            'star'
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
                'new_course',
                'Nouveau cours disponible!',
                "Un nouveau cours est maintenant disponible: <strong>{$post->post_title}</strong>",
                get_permalink($post_id),
                'graduation-cap'
            );
        }
    }

    /**
     * Notify: Certificate earned
     */
    public function notify_certificate($user_id, $course_id) {
        $course = get_post($course_id);

        $this->create_notification(
            $user_id,
            'certificate_earned',
            'Certificat obtenu! 🎓',
            "Félicitations! Vous avez obtenu votre certificat pour le cours <strong>{$course->post_title}</strong>",
            site_url("/certificat/?course_id={$course_id}"),
            'certificate'
        );
    }

    /**
     * Notify: XP earned
     */
    public function notify_xp_earned($user_id, $xp_amount, $reason) {
        $this->create_notification(
            $user_id,
            'xp_earned',
            "Points d'expérience gagnés!",
            "Vous avez gagné <strong>+{$xp_amount} XP</strong> pour: {$reason}",
            site_url('/mes-cours/'),
            'trophy'
        );
    }

    /**
     * Notify: Badge earned
     */
    public function notify_badge_earned($user_id, $badge_name) {
        $this->create_notification(
            $user_id,
            'badge_earned',
            'Nouveau badge débloqué! 🏆',
            "Félicitations! Vous avez débloqué le badge <strong>{$badge_name}</strong>",
            site_url('/mes-cours/'),
            'medal'
        );
    }

    /**
     * Notify: Assignment reminder (24h before due)
     */
    public function notify_assignment_reminder($assignment_id, $user_id) {
        $assignment = get_post($assignment_id);
        $due_date = get_post_meta($assignment_id, '_assignment_due_date', true);

        $this->create_notification(
            $user_id,
            'assignment_reminder',
            'Rappel: Devoir à rendre bientôt',
            "Le devoir <strong>{$assignment->post_title}</strong> est à rendre le <strong>" . date('d/m/Y à H:i', strtotime($due_date)) . "</strong>",
            get_permalink($assignment_id),
            'clock'
        );
    }
}
