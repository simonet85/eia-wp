<?php
/**
 * EIA Messaging System - BuddyPress Messages Integration
 *
 * Intégration et amélioration du système de messagerie BuddyPress
 * - Interface chat moderne
 * - Partage de fichiers dans les messages
 * - Notifications de nouveaux messages
 * - Conversations de groupe pour projets
 * - Raccourcis rapides pour contacter instructeurs
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Messaging {

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
        // Vérifier que BuddyPress Messages est actif
        if (!function_exists('bp_is_active') || !bp_is_active('messages')) {
            return;
        }

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Add messaging interface to student dashboard
        add_shortcode('eia_messages', array($this, 'render_messages_interface'));

        // AJAX handlers
        add_action('wp_ajax_eia_send_message', array($this, 'ajax_send_message'));
        add_action('wp_ajax_eia_get_conversations', array($this, 'ajax_get_conversations'));
        add_action('wp_ajax_eia_get_messages', array($this, 'ajax_get_messages'));
        add_action('wp_ajax_eia_upload_message_attachment', array($this, 'ajax_upload_attachment'));

        // Add quick message buttons
        add_filter('eia_course_instructor_actions', array($this, 'add_message_instructor_button'), 10, 2);
        add_filter('eia_student_profile_actions', array($this, 'add_message_student_button'), 10, 2);

        // Notifications for new messages (integrate with BuddyPress Notifications)
        add_action('messages_message_sent', array($this, 'notify_new_message'), 10, 1);
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (!is_user_logged_in()) {
            return;
        }

        wp_enqueue_style(
            'eia-messaging',
            EIA_LMS_CORE_PLUGIN_URL . 'assets/css/messaging.css',
            array(),
            '1.0.0'
        );

        wp_enqueue_script(
            'eia-messaging',
            EIA_LMS_CORE_PLUGIN_URL . 'assets/js/messaging.js',
            array('jquery'),
            '1.0.0',
            true
        );

        wp_localize_script('eia-messaging', 'eiaMessaging', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eia-messaging-nonce'),
            'user_id' => get_current_user_id(),
            'messages_url' => bp_loggedin_user_domain() . bp_get_messages_slug() . '/',
        ));
    }

    /**
     * Render messages interface
     */
    public function render_messages_interface($atts) {
        if (!is_user_logged_in()) {
            return '<p>Vous devez être connecté pour accéder à la messagerie.</p>';
        }

        ob_start();
        ?>
        <div class="eia-messaging-container">
            <div class="eia-messaging-header">
                <h2><i class="fas fa-comments"></i> Messagerie</h2>
                <button class="eia-new-message-btn" id="eia-new-message-btn">
                    <i class="fas fa-plus-circle"></i> Nouveau message
                </button>
            </div>

            <div class="eia-messaging-layout">
                <!-- Conversations list -->
                <div class="eia-conversations-sidebar">
                    <div class="eia-conversations-search">
                        <input type="text" placeholder="Rechercher une conversation..." id="eia-search-conversations">
                        <i class="fas fa-search"></i>
                    </div>
                    <div class="eia-conversations-list" id="eia-conversations-list">
                        <div class="eia-loading">
                            <i class="fas fa-circle-notch fa-spin"></i> Chargement...
                        </div>
                    </div>
                </div>

                <!-- Messages area -->
                <div class="eia-messages-area">
                    <div class="eia-messages-placeholder">
                        <i class="fas fa-comments"></i>
                        <p>Sélectionnez une conversation pour voir les messages</p>
                    </div>
                    <div class="eia-messages-content" id="eia-messages-content" style="display: none;">
                        <!-- Header -->
                        <div class="eia-messages-header" id="eia-messages-header"></div>

                        <!-- Messages -->
                        <div class="eia-messages-list" id="eia-messages-list"></div>

                        <!-- Compose -->
                        <div class="eia-message-compose">
                            <div class="eia-compose-attachments" id="eia-compose-attachments"></div>
                            <div class="eia-compose-input-area">
                                <button class="eia-attach-file-btn" id="eia-attach-file-btn" title="Joindre un fichier">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <textarea id="eia-message-input" placeholder="Écrivez votre message..." rows="2"></textarea>
                                <button class="eia-send-message-btn" id="eia-send-message-btn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <input type="file" id="eia-file-input" style="display: none;" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New Message Modal -->
        <div class="eia-modal" id="eia-new-message-modal">
            <div class="eia-modal-content">
                <div class="eia-modal-header">
                    <h3>Nouveau message</h3>
                    <button class="eia-modal-close">&times;</button>
                </div>
                <div class="eia-modal-body">
                    <div class="eia-form-group">
                        <label>Destinataire</label>
                        <select id="eia-recipient-select" class="eia-select">
                            <option value="">Sélectionner un utilisateur...</option>
                            <?php $this->render_users_options(); ?>
                        </select>
                    </div>
                    <div class="eia-form-group">
                        <label>Message</label>
                        <textarea id="eia-new-message-text" rows="6" placeholder="Écrivez votre message..."></textarea>
                    </div>
                </div>
                <div class="eia-modal-footer">
                    <button class="eia-btn-secondary eia-modal-close">Annuler</button>
                    <button class="eia-btn-primary" id="eia-send-new-message-btn">
                        <i class="fas fa-paper-plane"></i> Envoyer
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render users options for recipient select
     */
    private function render_users_options() {
        $current_user_id = get_current_user_id();
        $current_user = wp_get_current_user();

        // Get instructors if user is student
        if (in_array('student', $current_user->roles)) {
            echo '<optgroup label="Instructeurs">';
            $instructors = get_users(array('role' => 'instructor'));
            foreach ($instructors as $instructor) {
                echo '<option value="' . $instructor->ID . '">' . esc_html($instructor->display_name) . '</option>';
            }
            echo '</optgroup>';

            // Get other students
            echo '<optgroup label="Étudiants">';
            $students = get_users(array('role' => 'student', 'exclude' => array($current_user_id)));
            foreach ($students as $student) {
                echo '<option value="' . $student->ID . '">' . esc_html($student->display_name) . '</option>';
            }
            echo '</optgroup>';
        }

        // Get students if user is instructor
        if (in_array('instructor', $current_user->roles) || in_array('administrator', $current_user->roles)) {
            echo '<optgroup label="Étudiants">';
            $students = get_users(array('role' => 'student'));
            foreach ($students as $student) {
                echo '<option value="' . $student->ID . '">' . esc_html($student->display_name) . '</option>';
            }
            echo '</optgroup>';

            echo '<optgroup label="Instructeurs">';
            $instructors = get_users(array('role' => 'instructor', 'exclude' => array($current_user_id)));
            foreach ($instructors as $instructor) {
                echo '<option value="' . $instructor->ID . '">' . esc_html($instructor->display_name) . '</option>';
            }
            echo '</optgroup>';
        }
    }

    /**
     * AJAX: Send message
     */
    public function ajax_send_message() {
        check_ajax_referer('eia-messaging-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $recipient_id = isset($_POST['recipient_id']) ? intval($_POST['recipient_id']) : 0;
        $thread_id = isset($_POST['thread_id']) ? intval($_POST['thread_id']) : 0;
        $message_content = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';

        if (empty($message_content)) {
            wp_send_json_error(array('message' => 'Le message ne peut pas être vide'));
        }

        // Send via BuddyPress
        if ($thread_id) {
            // Reply to existing thread
            $sent = messages_new_message(array(
                'thread_id' => $thread_id,
                'sender_id' => $user_id,
                'content' => $message_content,
            ));
        } else {
            // New thread
            if (!$recipient_id) {
                wp_send_json_error(array('message' => 'Destinataire manquant'));
            }

            $sent = messages_new_message(array(
                'sender_id' => $user_id,
                'recipients' => array($recipient_id),
                'subject' => 'Message de ' . wp_get_current_user()->display_name,
                'content' => $message_content,
            ));
        }

        if ($sent) {
            wp_send_json_success(array(
                'message' => 'Message envoyé',
                'thread_id' => $sent
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de l\'envoi'));
        }
    }

    /**
     * AJAX: Get conversations
     */
    public function ajax_get_conversations() {
        check_ajax_referer('eia-messaging-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $threads = BP_Messages_Thread::get_current_threads_for_user(array(
            'user_id' => $user_id,
            'box' => 'inbox',
            'per_page' => 50,
        ));

        $conversations = array();
        if (!empty($threads['threads'])) {
            foreach ($threads['threads'] as $thread) {
                $last_message = BP_Messages_Thread::get_last_message($thread->thread_id);
                $other_user_id = $this->get_other_user_id($thread->thread_id, $user_id);
                $other_user = get_userdata($other_user_id);

                $conversations[] = array(
                    'thread_id' => $thread->thread_id,
                    'subject' => $thread->subject,
                    'excerpt' => wp_trim_words(strip_tags($last_message->message), 10),
                    'date' => bp_core_time_since($last_message->date_sent),
                    'unread' => $thread->unread_count,
                    'recipient' => array(
                        'id' => $other_user_id,
                        'name' => $other_user ? $other_user->display_name : 'Utilisateur',
                        'avatar' => get_avatar_url($other_user_id, array('size' => 40)),
                    ),
                );
            }
        }

        wp_send_json_success(array('conversations' => $conversations));
    }

    /**
     * AJAX: Get messages from thread
     */
    public function ajax_get_messages() {
        check_ajax_referer('eia-messaging-nonce', 'nonce');

        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error(array('message' => 'Non autorisé'));
        }

        $thread_id = isset($_POST['thread_id']) ? intval($_POST['thread_id']) : 0;
        if (!$thread_id) {
            wp_send_json_error(array('message' => 'Thread ID manquant'));
        }

        // Mark as read
        messages_mark_thread_read($thread_id);

        // Get messages
        $thread = new BP_Messages_Thread($thread_id);
        $messages_data = array();

        if (!empty($thread->messages)) {
            foreach ($thread->messages as $message) {
                $sender = get_userdata($message->sender_id);
                $messages_data[] = array(
                    'id' => $message->id,
                    'content' => wpautop($message->message),
                    'date' => bp_core_time_since($message->date_sent),
                    'sender' => array(
                        'id' => $message->sender_id,
                        'name' => $sender ? $sender->display_name : 'Utilisateur',
                        'avatar' => get_avatar_url($message->sender_id, array('size' => 32)),
                        'is_current' => $message->sender_id == $user_id,
                    ),
                );
            }
        }

        // Get other user info
        $other_user_id = $this->get_other_user_id($thread_id, $user_id);
        $other_user = get_userdata($other_user_id);

        wp_send_json_success(array(
            'messages' => array_reverse($messages_data),
            'recipient' => array(
                'id' => $other_user_id,
                'name' => $other_user ? $other_user->display_name : 'Utilisateur',
                'avatar' => get_avatar_url($other_user_id, array('size' => 40)),
            ),
        ));
    }

    /**
     * Get the other user ID in a thread
     */
    private function get_other_user_id($thread_id, $current_user_id) {
        global $wpdb;
        $bp = buddypress();

        $recipient_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM {$bp->messages->table_name_recipients}
            WHERE thread_id = %d AND user_id != %d
            LIMIT 1",
            $thread_id, $current_user_id
        ));

        return $recipient_id ? $recipient_id : $current_user_id;
    }

    /**
     * AJAX: Upload attachment
     */
    public function ajax_upload_attachment() {
        check_ajax_referer('eia-messaging-nonce', 'nonce');

        if (!isset($_FILES['file'])) {
            wp_send_json_error(array('message' => 'Aucun fichier uploadé'));
        }

        $file = $_FILES['file'];
        $upload = wp_handle_upload($file, array('test_form' => false));

        if (isset($upload['error'])) {
            wp_send_json_error(array('message' => $upload['error']));
        }

        wp_send_json_success(array(
            'url' => $upload['url'],
            'filename' => basename($upload['file']),
        ));
    }

    /**
     * Add message instructor button to course page
     */
    public function add_message_instructor_button($actions, $course_id) {
        $instructor_id = get_post_field('post_author', $course_id);
        $instructor = get_userdata($instructor_id);

        if ($instructor) {
            $actions[] = sprintf(
                '<a href="#" class="eia-message-instructor-btn" data-instructor-id="%d" data-instructor-name="%s">
                    <i class="fas fa-envelope"></i> Contacter l\'instructeur
                </a>',
                $instructor_id,
                esc_attr($instructor->display_name)
            );
        }

        return $actions;
    }

    /**
     * Notify new message via BuddyPress Notifications
     */
    public function notify_new_message($message) {
        if (!function_exists('bp_notifications_add_notification')) {
            return;
        }

        // Get recipients
        $thread = new BP_Messages_Thread($message->thread_id);
        foreach ($thread->recipients as $recipient) {
            if ($recipient->user_id != $message->sender_id) {
                bp_notifications_add_notification(array(
                    'user_id' => $recipient->user_id,
                    'item_id' => $message->thread_id,
                    'secondary_item_id' => $message->sender_id,
                    'component_name' => 'messages',
                    'component_action' => 'new_message',
                    'date_notified' => bp_core_current_time(),
                    'is_new' => 1,
                ));
            }
        }
    }
}
