<?php
/**
 * Forum & Q&A System
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Forum {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Create tables on init
        add_action('init', array($this, 'maybe_create_tables'));

        // AJAX handlers
        add_action('wp_ajax_eia_create_topic', array($this, 'ajax_create_topic'));
        add_action('wp_ajax_eia_create_reply', array($this, 'ajax_create_reply'));
        add_action('wp_ajax_eia_vote', array($this, 'ajax_vote'));
        add_action('wp_ajax_eia_mark_best_answer', array($this, 'ajax_mark_best_answer'));
        add_action('wp_ajax_eia_resolve_topic', array($this, 'ajax_resolve_topic'));
        add_action('wp_ajax_eia_search_forum', array($this, 'ajax_search_forum'));

        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Create forum tables
     */
    public function maybe_create_tables() {
        global $wpdb;

        $topics_table = $wpdb->prefix . 'eia_forum_topics';
        $replies_table = $wpdb->prefix . 'eia_forum_replies';
        $votes_table = $wpdb->prefix . 'eia_forum_votes';

        // Check if tables exist
        if ($wpdb->get_var("SHOW TABLES LIKE '$topics_table'") != $topics_table) {
            $charset_collate = $wpdb->get_charset_collate();

            // Topics table
            $sql_topics = "CREATE TABLE $topics_table (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                course_id bigint(20) UNSIGNED NOT NULL,
                user_id bigint(20) UNSIGNED NOT NULL,
                title varchar(255) NOT NULL,
                content longtext NOT NULL,
                views int(11) DEFAULT 0,
                is_resolved tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY course_id (course_id),
                KEY user_id (user_id),
                KEY is_resolved (is_resolved),
                KEY created_at (created_at)
            ) $charset_collate;";

            // Replies table
            $sql_replies = "CREATE TABLE $replies_table (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                topic_id bigint(20) UNSIGNED NOT NULL,
                user_id bigint(20) UNSIGNED NOT NULL,
                content longtext NOT NULL,
                is_best_answer tinyint(1) DEFAULT 0,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY topic_id (topic_id),
                KEY user_id (user_id),
                KEY is_best_answer (is_best_answer),
                KEY created_at (created_at)
            ) $charset_collate;";

            // Votes table
            $sql_votes = "CREATE TABLE $votes_table (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                entity_type varchar(20) NOT NULL,
                entity_id bigint(20) UNSIGNED NOT NULL,
                user_id bigint(20) UNSIGNED NOT NULL,
                vote_type tinyint(2) NOT NULL,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY unique_vote (entity_type, entity_id, user_id),
                KEY entity (entity_type, entity_id),
                KEY user_id (user_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_topics);
            dbDelta($sql_replies);
            dbDelta($sql_votes);
        }
    }

    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        if (is_singular('lp_course') || is_page_template('page-templates/course-forum.php')) {
            wp_enqueue_style(
                'eia-forum-css',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/forum.css',
                array(),
                '1.0.0'
            );

            wp_enqueue_script(
                'eia-forum-js',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/forum.js',
                array('jquery'),
                '1.0.0',
                true
            );

            wp_localize_script('eia-forum-js', 'eiaForum', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eia-forum-nonce'),
                'currentUserId' => get_current_user_id()
            ));
        }
    }

    /**
     * Create new topic
     */
    public function create_topic($course_id, $user_id, $title, $content) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'eia_forum_topics',
            array(
                'course_id' => $course_id,
                'user_id' => $user_id,
                'title' => $title,
                'content' => $content
            ),
            array('%d', '%d', '%s', '%s')
        );

        if ($result) {
            $topic_id = $wpdb->insert_id;

            // Award gamification points
            if (class_exists('EIA_Gamification')) {
                $gamification = EIA_Gamification::get_instance();
                $gamification->award_points($user_id, 5, 'forum_topic', "Question posée dans le forum", $topic_id, 'forum');
            }

            return $topic_id;
        }

        return false;
    }

    /**
     * Create reply to topic
     */
    public function create_reply($topic_id, $user_id, $content) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'eia_forum_replies',
            array(
                'topic_id' => $topic_id,
                'user_id' => $user_id,
                'content' => $content
            ),
            array('%d', '%d', '%s')
        );

        if ($result) {
            $reply_id = $wpdb->insert_id;

            // Award gamification points
            if (class_exists('EIA_Gamification')) {
                $gamification = EIA_Gamification::get_instance();
                $gamification->award_points($user_id, 3, 'forum_reply', "Réponse dans le forum", $reply_id, 'forum');
            }

            // Get topic info for notification
            $topic = $this->get_topic($topic_id);
            if ($topic && $topic->user_id != $user_id) {
                // Notify topic author
                if (class_exists('EIA_Notifications')) {
                    $notifications = EIA_Notifications::get_instance();
                    $replier = get_userdata($user_id);

                    $notifications->send_notification(
                        $topic->user_id,
                        'Nouvelle réponse à votre question',
                        "{$replier->display_name} a répondu à votre question : {$topic->title}",
                        site_url("/forum-cours/?course_id={$topic->course_id}&topic_id={$topic_id}")
                    );
                }
            }

            return $reply_id;
        }

        return false;
    }

    /**
     * Vote on topic or reply
     */
    public function vote($entity_type, $entity_id, $user_id, $vote_type) {
        global $wpdb;

        // Check if user already voted
        $existing = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_forum_votes
            WHERE entity_type = %s AND entity_id = %d AND user_id = %d",
            $entity_type,
            $entity_id,
            $user_id
        ));

        if ($existing) {
            // Update vote
            if ($existing->vote_type == $vote_type) {
                // Remove vote (toggle)
                $wpdb->delete(
                    $wpdb->prefix . 'eia_forum_votes',
                    array('id' => $existing->id),
                    array('%d')
                );
                return 'removed';
            } else {
                // Change vote
                $wpdb->update(
                    $wpdb->prefix . 'eia_forum_votes',
                    array('vote_type' => $vote_type),
                    array('id' => $existing->id),
                    array('%d'),
                    array('%d')
                );
                return 'updated';
            }
        } else {
            // New vote
            $wpdb->insert(
                $wpdb->prefix . 'eia_forum_votes',
                array(
                    'entity_type' => $entity_type,
                    'entity_id' => $entity_id,
                    'user_id' => $user_id,
                    'vote_type' => $vote_type
                ),
                array('%s', '%d', '%d', '%d')
            );

            // Award points for helpful reply (upvote)
            if ($vote_type == 1 && $entity_type == 'reply') {
                $reply = $this->get_reply($entity_id);
                if ($reply && class_exists('EIA_Gamification')) {
                    $gamification = EIA_Gamification::get_instance();
                    $gamification->award_points($reply->user_id, 2, 'helpful_reply', "Réponse utile (+1)", $entity_id, 'forum');
                }
            }

            return 'added';
        }
    }

    /**
     * Mark reply as best answer
     */
    public function mark_best_answer($reply_id, $user_id) {
        global $wpdb;

        $reply = $this->get_reply($reply_id);
        if (!$reply) {
            return false;
        }

        $topic = $this->get_topic($reply->topic_id);
        if (!$topic) {
            return false;
        }

        // Check if user is topic author or instructor
        $course = get_post($topic->course_id);
        $is_author = ($topic->user_id == $user_id);
        $is_instructor = ($course && $course->post_author == $user_id);

        if (!$is_author && !$is_instructor) {
            return false;
        }

        // Remove previous best answer
        $wpdb->update(
            $wpdb->prefix . 'eia_forum_replies',
            array('is_best_answer' => 0),
            array('topic_id' => $reply->topic_id),
            array('%d'),
            array('%d')
        );

        // Set new best answer
        $result = $wpdb->update(
            $wpdb->prefix . 'eia_forum_replies',
            array('is_best_answer' => 1),
            array('id' => $reply_id),
            array('%d'),
            array('%d')
        );

        if ($result !== false) {
            // Mark topic as resolved
            $wpdb->update(
                $wpdb->prefix . 'eia_forum_topics',
                array('is_resolved' => 1),
                array('id' => $reply->topic_id),
                array('%d'),
                array('%d')
            );

            // Award points for best answer
            if (class_exists('EIA_Gamification')) {
                $gamification = EIA_Gamification::get_instance();
                $gamification->award_points($reply->user_id, 15, 'best_answer', "Meilleure réponse", $reply_id, 'forum');
            }

            return true;
        }

        return false;
    }

    /**
     * Toggle topic resolved status
     */
    public function toggle_resolved($topic_id, $user_id) {
        global $wpdb;

        $topic = $this->get_topic($topic_id);
        if (!$topic) {
            return false;
        }

        // Check permissions
        $course = get_post($topic->course_id);
        $is_author = ($topic->user_id == $user_id);
        $is_instructor = ($course && $course->post_author == $user_id);

        if (!$is_author && !$is_instructor) {
            return false;
        }

        $new_status = $topic->is_resolved ? 0 : 1;

        $result = $wpdb->update(
            $wpdb->prefix . 'eia_forum_topics',
            array('is_resolved' => $new_status),
            array('id' => $topic_id),
            array('%d'),
            array('%d')
        );

        return $result !== false;
    }

    /**
     * Get topic by ID
     */
    public function get_topic($topic_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT t.*, u.display_name as author_name, u.user_email as author_email
            FROM {$wpdb->prefix}eia_forum_topics t
            INNER JOIN {$wpdb->users} u ON t.user_id = u.ID
            WHERE t.id = %d",
            $topic_id
        ));
    }

    /**
     * Get reply by ID
     */
    public function get_reply($reply_id) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT r.*, u.display_name as author_name, u.user_email as author_email
            FROM {$wpdb->prefix}eia_forum_replies r
            INNER JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE r.id = %d",
            $reply_id
        ));
    }

    /**
     * Get topics for a course
     */
    public function get_topics($course_id, $limit = 20, $offset = 0, $search = '') {
        global $wpdb;

        $where = $wpdb->prepare("WHERE t.course_id = %d", $course_id);

        if ($search) {
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $where .= $wpdb->prepare(" AND (t.title LIKE %s OR t.content LIKE %s)", $search_term, $search_term);
        }

        $sql = "SELECT t.*,
                u.display_name as author_name,
                (SELECT COUNT(*) FROM {$wpdb->prefix}eia_forum_replies WHERE topic_id = t.id) as reply_count,
                (SELECT SUM(vote_type) FROM {$wpdb->prefix}eia_forum_votes WHERE entity_type = 'topic' AND entity_id = t.id) as vote_score
            FROM {$wpdb->prefix}eia_forum_topics t
            INNER JOIN {$wpdb->users} u ON t.user_id = u.ID
            $where
            ORDER BY t.is_resolved ASC, t.created_at DESC
            LIMIT %d OFFSET %d";

        return $wpdb->get_results($wpdb->prepare($sql, $limit, $offset));
    }

    /**
     * Get replies for a topic
     */
    public function get_replies($topic_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT r.*,
                u.display_name as author_name,
                (SELECT SUM(vote_type) FROM {$wpdb->prefix}eia_forum_votes WHERE entity_type = 'reply' AND entity_id = r.id) as vote_score
            FROM {$wpdb->prefix}eia_forum_replies r
            INNER JOIN {$wpdb->users} u ON r.user_id = u.ID
            WHERE r.topic_id = %d
            ORDER BY r.is_best_answer DESC, r.created_at ASC",
            $topic_id
        ));
    }

    /**
     * Get user's vote for entity
     */
    public function get_user_vote($entity_type, $entity_id, $user_id) {
        global $wpdb;

        return $wpdb->get_var($wpdb->prepare(
            "SELECT vote_type FROM {$wpdb->prefix}eia_forum_votes
            WHERE entity_type = %s AND entity_id = %d AND user_id = %d",
            $entity_type,
            $entity_id,
            $user_id
        ));
    }

    /**
     * Increment topic views
     */
    public function increment_views($topic_id) {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->prefix}eia_forum_topics SET views = views + 1 WHERE id = %d",
            $topic_id
        ));
    }

    /**
     * AJAX: Create topic
     */
    public function ajax_create_topic() {
        check_ajax_referer('eia-forum-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Vous devez être connecté'));
        }

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (!$course_id || !$title || !$content) {
            wp_send_json_error(array('message' => 'Données manquantes'));
        }

        $topic_id = $this->create_topic($course_id, get_current_user_id(), $title, $content);

        if ($topic_id) {
            wp_send_json_success(array(
                'topic_id' => $topic_id,
                'message' => 'Question publiée avec succès'
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la création'));
        }
    }

    /**
     * AJAX: Create reply
     */
    public function ajax_create_reply() {
        check_ajax_referer('eia-forum-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Vous devez être connecté'));
        }

        $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (!$topic_id || !$content) {
            wp_send_json_error(array('message' => 'Données manquantes'));
        }

        $reply_id = $this->create_reply($topic_id, get_current_user_id(), $content);

        if ($reply_id) {
            $reply = $this->get_reply($reply_id);
            wp_send_json_success(array(
                'reply' => $reply,
                'message' => 'Réponse publiée avec succès'
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la création'));
        }
    }

    /**
     * AJAX: Vote
     */
    public function ajax_vote() {
        check_ajax_referer('eia-forum-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Vous devez être connecté'));
        }

        $entity_type = isset($_POST['entity_type']) ? sanitize_text_field($_POST['entity_type']) : '';
        $entity_id = isset($_POST['entity_id']) ? intval($_POST['entity_id']) : 0;
        $vote_type = isset($_POST['vote_type']) ? intval($_POST['vote_type']) : 0;

        if (!in_array($entity_type, array('topic', 'reply')) || !$entity_id || !in_array($vote_type, array(-1, 1))) {
            wp_send_json_error(array('message' => 'Données invalides'));
        }

        $result = $this->vote($entity_type, $entity_id, get_current_user_id(), $vote_type);

        if ($result) {
            // Get new vote score
            global $wpdb;
            $score = $wpdb->get_var($wpdb->prepare(
                "SELECT SUM(vote_type) FROM {$wpdb->prefix}eia_forum_votes
                WHERE entity_type = %s AND entity_id = %d",
                $entity_type,
                $entity_id
            ));

            wp_send_json_success(array(
                'action' => $result,
                'score' => intval($score)
            ));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors du vote'));
        }
    }

    /**
     * AJAX: Mark best answer
     */
    public function ajax_mark_best_answer() {
        check_ajax_referer('eia-forum-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Vous devez être connecté'));
        }

        $reply_id = isset($_POST['reply_id']) ? intval($_POST['reply_id']) : 0;

        if (!$reply_id) {
            wp_send_json_error(array('message' => 'Données invalides'));
        }

        $result = $this->mark_best_answer($reply_id, get_current_user_id());

        if ($result) {
            wp_send_json_success(array('message' => 'Meilleure réponse marquée'));
        } else {
            wp_send_json_error(array('message' => 'Permission refusée'));
        }
    }

    /**
     * AJAX: Resolve topic
     */
    public function ajax_resolve_topic() {
        check_ajax_referer('eia-forum-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Vous devez être connecté'));
        }

        $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;

        if (!$topic_id) {
            wp_send_json_error(array('message' => 'Données invalides'));
        }

        $result = $this->toggle_resolved($topic_id, get_current_user_id());

        if ($result) {
            $topic = $this->get_topic($topic_id);
            wp_send_json_success(array(
                'is_resolved' => $topic->is_resolved,
                'message' => $topic->is_resolved ? 'Question marquée comme résolue' : 'Question rouverte'
            ));
        } else {
            wp_send_json_error(array('message' => 'Permission refusée'));
        }
    }

    /**
     * AJAX: Search forum
     */
    public function ajax_search_forum() {
        check_ajax_referer('eia-forum-nonce', 'nonce');

        $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
        $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';

        if (!$course_id) {
            wp_send_json_error(array('message' => 'Données invalides'));
        }

        $topics = $this->get_topics($course_id, 20, 0, $search);

        wp_send_json_success(array('topics' => $topics));
    }
}

// Initialize
EIA_Forum::get_instance();
