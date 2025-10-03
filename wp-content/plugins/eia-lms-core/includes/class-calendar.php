<?php
/**
 * Calendar & Events System
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Calendar {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', array($this, 'maybe_create_table'));

        // AJAX handlers
        add_action('wp_ajax_eia_get_calendar_events', array($this, 'ajax_get_events'));
        add_action('wp_ajax_eia_create_event', array($this, 'ajax_create_event'));
        add_action('wp_ajax_eia_delete_event', array($this, 'ajax_delete_event'));

        // Export handler
        add_action('init', array($this, 'handle_ical_export'));

        // Cron for reminders
        add_action('eia_send_calendar_reminders', array($this, 'send_reminders'));

        if (!wp_next_scheduled('eia_send_calendar_reminders')) {
            wp_schedule_event(time(), 'hourly', 'eia_send_calendar_reminders');
        }
    }

    /**
     * Create calendar events table
     */
    public function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_calendar_events';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id bigint(20) UNSIGNED NOT NULL,
                event_type varchar(50) NOT NULL,
                title varchar(255) NOT NULL,
                description longtext,
                start_date datetime NOT NULL,
                end_date datetime,
                all_day tinyint(1) DEFAULT 0,
                course_id bigint(20) UNSIGNED DEFAULT NULL,
                related_id bigint(20) UNSIGNED DEFAULT NULL,
                color varchar(20) DEFAULT '#3B82F6',
                reminder_24h_sent tinyint(1) DEFAULT 0,
                reminder_1h_sent tinyint(1) DEFAULT 0,
                metadata longtext,
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY event_type (event_type),
                KEY start_date (start_date),
                KEY course_id (course_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Get all events for a user
     */
    public function get_user_events($user_id, $start_date = null, $end_date = null) {
        $events = array();

        // Get custom events
        $events = array_merge($events, $this->get_custom_events($user_id, $start_date, $end_date));

        // Get assignment deadlines
        $events = array_merge($events, $this->get_assignment_events($user_id, $start_date, $end_date));

        // Get quiz events
        $events = array_merge($events, $this->get_quiz_events($user_id, $start_date, $end_date));

        // Get course start/end dates
        $events = array_merge($events, $this->get_course_events($user_id, $start_date, $end_date));

        return $events;
    }

    /**
     * Get custom events from database
     */
    private function get_custom_events($user_id, $start_date, $end_date) {
        global $wpdb;

        $where = $wpdb->prepare("WHERE user_id = %d", $user_id);

        if ($start_date && $end_date) {
            $where .= $wpdb->prepare(
                " AND ((start_date BETWEEN %s AND %s) OR (end_date BETWEEN %s AND %s))",
                $start_date,
                $end_date,
                $start_date,
                $end_date
            );
        }

        $results = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}eia_calendar_events $where ORDER BY start_date ASC"
        );

        $events = array();
        foreach ($results as $row) {
            $events[] = array(
                'id' => 'custom_' . $row->id,
                'title' => $row->title,
                'start' => $row->start_date,
                'end' => $row->end_date,
                'allDay' => (bool) $row->all_day,
                'color' => $row->color,
                'extendedProps' => array(
                    'type' => $row->event_type,
                    'description' => $row->description,
                    'courseId' => $row->course_id,
                    'isCustom' => true
                )
            );
        }

        return $events;
    }

    /**
     * Get assignment deadline events
     */
    private function get_assignment_events($user_id, $start_date, $end_date) {
        global $wpdb;

        // Get user's enrolled courses
        $course_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT item_id FROM {$wpdb->prefix}learnpress_user_items
            WHERE user_id = %d AND item_type = 'lp_course'",
            $user_id
        ));

        if (empty($course_ids)) {
            return array();
        }

        $placeholders = implode(',', array_fill(0, count($course_ids), '%d'));

        // Get assignments for these courses
        $assignments = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, pm.meta_value as due_date, pm2.meta_value as course_id
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_eia_assignment_due_date'
            INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_eia_assignment_course'
            WHERE p.post_type = 'lp_assignment'
            AND p.post_status = 'publish'
            AND pm2.meta_value IN ($placeholders)",
            ...$course_ids
        ));

        $events = array();
        foreach ($assignments as $assignment) {
            if (empty($assignment->due_date)) continue;

            // Check if already submitted
            $submitted = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}eia_assignment_submissions
                WHERE assignment_id = %d AND student_id = %d",
                $assignment->ID,
                $user_id
            ));

            $color = '#EF4444'; // Red by default (not submitted)
            if ($submitted) {
                $color = '#10B981'; // Green (submitted)
            } elseif (strtotime($assignment->due_date) < current_time('timestamp')) {
                $color = '#DC2626'; // Dark red (overdue)
            }

            $events[] = array(
                'id' => 'assignment_' . $assignment->ID,
                'title' => '📝 ' . $assignment->post_title,
                'start' => date('Y-m-d\TH:i:s', strtotime($assignment->due_date)),
                'color' => $color,
                'extendedProps' => array(
                    'type' => 'assignment',
                    'assignmentId' => $assignment->ID,
                    'courseId' => $assignment->course_id,
                    'submitted' => (bool) $submitted,
                    'url' => get_permalink($assignment->ID)
                )
            );
        }

        return $events;
    }

    /**
     * Get quiz events
     */
    private function get_quiz_events($user_id, $start_date, $end_date) {
        global $wpdb;

        // Get user's enrolled courses
        $course_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT DISTINCT item_id FROM {$wpdb->prefix}learnpress_user_items
            WHERE user_id = %d AND item_type = 'lp_course'",
            $user_id
        ));

        if (empty($course_ids)) {
            return array();
        }

        $events = array();

        // Get quizzes from course sections
        foreach ($course_ids as $course_id) {
            $sections = $wpdb->get_results($wpdb->prepare(
                "SELECT section_id FROM {$wpdb->prefix}learnpress_sections
                WHERE section_course_id = %d",
                $course_id
            ));

            foreach ($sections as $section) {
                $items = $wpdb->get_results($wpdb->prepare(
                    "SELECT si.item_id, p.post_title
                    FROM {$wpdb->prefix}learnpress_section_items si
                    INNER JOIN {$wpdb->posts} p ON si.item_id = p.ID
                    WHERE si.section_id = %d AND si.item_type = 'lp_quiz'",
                    $section->section_id
                ));

                foreach ($items as $item) {
                    // Check if quiz has a due date meta
                    $due_date = get_post_meta($item->item_id, '_lp_quiz_due_date', true);

                    if (!$due_date) continue;

                    // Check if already taken
                    $taken = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM {$wpdb->prefix}learnpress_user_items
                        WHERE user_id = %d AND item_id = %d AND item_type = 'lp_quiz'",
                        $user_id,
                        $item->item_id
                    ));

                    $color = $taken ? '#10B981' : '#F59E0B'; // Green if taken, orange if not

                    $events[] = array(
                        'id' => 'quiz_' . $item->item_id,
                        'title' => '✏️ ' . $item->post_title,
                        'start' => date('Y-m-d\TH:i:s', strtotime($due_date)),
                        'color' => $color,
                        'extendedProps' => array(
                            'type' => 'quiz',
                            'quizId' => $item->item_id,
                            'courseId' => $course_id,
                            'taken' => (bool) $taken
                        )
                    );
                }
            }
        }

        return $events;
    }

    /**
     * Get course enrollment events
     */
    private function get_course_events($user_id, $start_date, $end_date) {
        global $wpdb;

        $enrollments = $wpdb->get_results($wpdb->prepare(
            "SELECT ui.*, p.post_title
            FROM {$wpdb->prefix}learnpress_user_items ui
            INNER JOIN {$wpdb->posts} p ON ui.item_id = p.ID
            WHERE ui.user_id = %d AND ui.item_type = 'lp_course'",
            $user_id
        ));

        $events = array();
        foreach ($enrollments as $enrollment) {
            if ($enrollment->start_time) {
                $events[] = array(
                    'id' => 'course_start_' . $enrollment->item_id,
                    'title' => '🎓 Début: ' . $enrollment->post_title,
                    'start' => date('Y-m-d', strtotime($enrollment->start_time)),
                    'allDay' => true,
                    'color' => '#3B82F6',
                    'extendedProps' => array(
                        'type' => 'course_start',
                        'courseId' => $enrollment->item_id
                    )
                );
            }

            if ($enrollment->end_time && $enrollment->status === 'finished') {
                $events[] = array(
                    'id' => 'course_end_' . $enrollment->item_id,
                    'title' => '🏆 Terminé: ' . $enrollment->post_title,
                    'start' => date('Y-m-d', strtotime($enrollment->end_time)),
                    'allDay' => true,
                    'color' => '#10B981',
                    'extendedProps' => array(
                        'type' => 'course_end',
                        'courseId' => $enrollment->item_id
                    )
                );
            }
        }

        return $events;
    }

    /**
     * Create custom event
     */
    public function create_event($user_id, $data) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'eia_calendar_events',
            array(
                'user_id' => $user_id,
                'event_type' => sanitize_text_field($data['event_type']),
                'title' => sanitize_text_field($data['title']),
                'description' => wp_kses_post($data['description']),
                'start_date' => sanitize_text_field($data['start_date']),
                'end_date' => sanitize_text_field($data['end_date']),
                'all_day' => isset($data['all_day']) ? 1 : 0,
                'course_id' => isset($data['course_id']) ? intval($data['course_id']) : null,
                'color' => sanitize_text_field($data['color'])
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s')
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Delete custom event
     */
    public function delete_event($event_id, $user_id) {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'eia_calendar_events',
            array('id' => $event_id, 'user_id' => $user_id),
            array('%d', '%d')
        );
    }

    /**
     * Generate iCal file
     */
    public function generate_ical($user_id) {
        $events = $this->get_user_events($user_id);
        $user = get_userdata($user_id);

        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//EIA LMS//Calendar//FR\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:EIA - " . $user->display_name . "\r\n";
        $ical .= "X-WR-TIMEZONE:Africa/Dakar\r\n";

        foreach ($events as $event) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . md5($event['id']) . "@eia-lms.com\r\n";
            $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";

            $start = strtotime($event['start']);
            if (isset($event['allDay']) && $event['allDay']) {
                $ical .= "DTSTART;VALUE=DATE:" . gmdate('Ymd', $start) . "\r\n";
            } else {
                $ical .= "DTSTART:" . gmdate('Ymd\THis\Z', $start) . "\r\n";
            }

            if (isset($event['end'])) {
                $end = strtotime($event['end']);
                if (isset($event['allDay']) && $event['allDay']) {
                    $ical .= "DTEND;VALUE=DATE:" . gmdate('Ymd', $end) . "\r\n";
                } else {
                    $ical .= "DTEND:" . gmdate('Ymd\THis\Z', $end) . "\r\n";
                }
            }

            $ical .= "SUMMARY:" . $this->escape_ical_string($event['title']) . "\r\n";

            if (isset($event['extendedProps']['description'])) {
                $ical .= "DESCRIPTION:" . $this->escape_ical_string($event['extendedProps']['description']) . "\r\n";
            }

            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        return $ical;
    }

    /**
     * Escape iCal string
     */
    private function escape_ical_string($string) {
        return str_replace(array("\r\n", "\n", "\r", ",", ";"), array("\\n", "\\n", "\\n", "\\,", "\\;"), $string);
    }

    /**
     * Handle iCal export
     */
    public function handle_ical_export() {
        if (!isset($_GET['eia_calendar_export']) || !isset($_GET['user_id'])) {
            return;
        }

        $user_id = intval($_GET['user_id']);

        // Verify user
        if (!is_user_logged_in() || get_current_user_id() != $user_id) {
            wp_die('Accès refusé');
        }

        $ical = $this->generate_ical($user_id);
        $user = get_userdata($user_id);

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="EIA-Calendar-' . sanitize_file_name($user->display_name) . '.ics"');
        echo $ical;
        exit;
    }

    /**
     * Send reminders for upcoming events
     */
    public function send_reminders() {
        global $wpdb;

        $now = current_time('mysql');
        $in_24h = date('Y-m-d H:i:s', strtotime('+24 hours', current_time('timestamp')));
        $in_1h = date('Y-m-d H:i:s', strtotime('+1 hour', current_time('timestamp')));

        // 24h reminders
        $events_24h = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_calendar_events
            WHERE start_date BETWEEN %s AND %s
            AND reminder_24h_sent = 0",
            $now,
            $in_24h
        ));

        foreach ($events_24h as $event) {
            $this->send_reminder_email($event, '24 heures');
            $wpdb->update(
                $wpdb->prefix . 'eia_calendar_events',
                array('reminder_24h_sent' => 1),
                array('id' => $event->id),
                array('%d'),
                array('%d')
            );
        }

        // 1h reminders
        $events_1h = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}eia_calendar_events
            WHERE start_date BETWEEN %s AND %s
            AND reminder_1h_sent = 0",
            $now,
            $in_1h
        ));

        foreach ($events_1h as $event) {
            $this->send_reminder_email($event, '1 heure');
            $wpdb->update(
                $wpdb->prefix . 'eia_calendar_events',
                array('reminder_1h_sent' => 1),
                array('id' => $event->id),
                array('%d'),
                array('%d')
            );
        }
    }

    /**
     * Send reminder email
     */
    private function send_reminder_email($event, $time_before) {
        if (!class_exists('EIA_Notifications')) {
            return;
        }

        $user = get_userdata($event->user_id);
        if (!$user) return;

        $notifications = EIA_Notifications::get_instance();
        $notifications->send_notification(
            $event->user_id,
            "Rappel: {$event->title}",
            "Votre événement \"{$event->title}\" commence dans {$time_before}.",
            site_url('/mon-calendrier/')
        );
    }

    /**
     * AJAX: Get events
     */
    public function ajax_get_events() {
        check_ajax_referer('eia-calendar-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Non connecté'));
        }

        $user_id = get_current_user_id();
        $start = isset($_POST['start']) ? sanitize_text_field($_POST['start']) : null;
        $end = isset($_POST['end']) ? sanitize_text_field($_POST['end']) : null;

        $events = $this->get_user_events($user_id, $start, $end);

        wp_send_json_success($events);
    }

    /**
     * AJAX: Create event
     */
    public function ajax_create_event() {
        check_ajax_referer('eia-calendar-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Non connecté'));
        }

        $event_id = $this->create_event(get_current_user_id(), $_POST);

        if ($event_id) {
            wp_send_json_success(array('event_id' => $event_id));
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la création'));
        }
    }

    /**
     * AJAX: Delete event
     */
    public function ajax_delete_event() {
        check_ajax_referer('eia-calendar-nonce', 'nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error(array('message' => 'Non connecté'));
        }

        $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

        if ($this->delete_event($event_id, get_current_user_id())) {
            wp_send_json_success();
        } else {
            wp_send_json_error(array('message' => 'Erreur lors de la suppression'));
        }
    }
}

// Initialize
EIA_Calendar::get_instance();
