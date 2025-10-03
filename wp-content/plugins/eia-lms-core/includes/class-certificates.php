<?php
/**
 * Certificate Generation System
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

class EIA_Certificates {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Auto-generate certificate on course completion (multiple hooks for compatibility)
        add_action('learn-press/user/course-finished', array($this, 'auto_generate_certificate'), 10, 3);
        add_action('learnpress/user/course-finished', array($this, 'auto_generate_certificate'), 10, 3);
        add_action('learn_press_user_finish_course', array($this, 'auto_generate_certificate_legacy'), 10, 2);

        // AJAX handlers
        add_action('wp_ajax_eia_download_certificate', array($this, 'ajax_download_certificate'));
        add_action('wp_ajax_eia_verify_certificate', array($this, 'ajax_verify_certificate'));

        // PDF download handler
        add_action('init', array($this, 'handle_pdf_download'));

        // Create certificates table on activation
        add_action('init', array($this, 'maybe_create_table'));

        // Load TCPDF
        $this->load_tcpdf();
    }

    /**
     * Load TCPDF library
     */
    private function load_tcpdf() {
        $autoload_file = plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
        if (file_exists($autoload_file)) {
            require_once $autoload_file;
        }
    }

    /**
     * Create certificates table
     */
    public function maybe_create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_certificates';

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                certificate_code varchar(50) NOT NULL UNIQUE,
                user_id bigint(20) UNSIGNED NOT NULL,
                course_id bigint(20) UNSIGNED NOT NULL,
                completion_date datetime NOT NULL,
                grade_percentage decimal(5,2) DEFAULT NULL,
                instructor_id bigint(20) UNSIGNED DEFAULT NULL,
                generated_date datetime DEFAULT CURRENT_TIMESTAMP,
                metadata longtext,
                PRIMARY KEY (id),
                KEY user_id (user_id),
                KEY course_id (course_id),
                KEY certificate_code (certificate_code)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    /**
     * Generate unique certificate code
     */
    private function generate_certificate_code() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_certificates';

        do {
            $code = 'EIA-' . strtoupper(wp_generate_password(12, false));
        } while ($wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE certificate_code = %s",
            $code
        )));

        return $code;
    }

    /**
     * Legacy hook compatibility
     */
    public function auto_generate_certificate_legacy($user_id, $course_id) {
        $this->auto_generate_certificate($course_id, $user_id, null);
    }

    /**
     * Auto-generate certificate on course completion
     */
    public function auto_generate_certificate($course_id, $user_id, $result) {
        global $wpdb;

        // Check if certificate already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}eia_certificates WHERE user_id = %d AND course_id = %d",
            $user_id,
            $course_id
        ));

        if ($existing) {
            return; // Already has certificate
        }

        // Get course info
        $course = get_post($course_id);
        if (!$course) {
            return;
        }

        // Get instructor
        $instructor_id = $course->post_author;

        // Calculate grade percentage (if available)
        $grade_percentage = $this->calculate_course_grade($user_id, $course_id);

        // Generate certificate code
        $certificate_code = $this->generate_certificate_code();

        // Insert certificate record
        $wpdb->insert(
            $wpdb->prefix . 'eia_certificates',
            array(
                'certificate_code' => $certificate_code,
                'user_id' => $user_id,
                'course_id' => $course_id,
                'completion_date' => current_time('mysql'),
                'grade_percentage' => $grade_percentage,
                'instructor_id' => $instructor_id,
                'metadata' => json_encode(array(
                    'course_title' => $course->post_title,
                    'course_duration' => get_post_meta($course_id, '_lp_duration', true)
                ))
            ),
            array('%s', '%d', '%d', '%s', '%f', '%d', '%s')
        );

        // Award gamification points
        if (class_exists('EIA_Gamification')) {
            $gamification = EIA_Gamification::get_instance();
            $gamification->award_points(
                $user_id,
                75,
                'certificate_earned',
                "Certificat obtenu: {$course->post_title}",
                $course_id,
                'course'
            );
        }
    }

    /**
     * Calculate course grade percentage
     */
    private function calculate_course_grade($user_id, $course_id) {
        global $wpdb;

        // Get all quiz results for this course
        $quiz_results = $wpdb->get_results($wpdb->prepare(
            "SELECT ui.graduation, ui.item_id
            FROM {$wpdb->prefix}learnpress_user_items ui
            INNER JOIN {$wpdb->prefix}learnpress_section_items si ON ui.item_id = si.item_id
            INNER JOIN {$wpdb->prefix}learnpress_sections s ON si.section_id = s.section_id
            WHERE ui.user_id = %d
            AND s.section_course_id = %d
            AND ui.item_type = 'lp_quiz'
            AND ui.graduation IN ('passed', 'failed')",
            $user_id,
            $course_id
        ));

        if (empty($quiz_results)) {
            return null;
        }

        $passed = 0;
        foreach ($quiz_results as $result) {
            if ($result->graduation === 'passed') {
                $passed++;
            }
        }

        return ($passed / count($quiz_results)) * 100;
    }

    /**
     * Get user certificates
     */
    public function get_user_certificates($user_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, p.post_title as course_title
            FROM {$wpdb->prefix}eia_certificates c
            INNER JOIN {$wpdb->posts} p ON c.course_id = p.ID
            WHERE c.user_id = %d
            ORDER BY c.completion_date DESC",
            $user_id
        ));
    }

    /**
     * Get certificate by code
     */
    public function get_certificate_by_code($code) {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare(
            "SELECT c.*,
                   p.post_title as course_title,
                   u.display_name as student_name,
                   i.display_name as instructor_name
            FROM {$wpdb->prefix}eia_certificates c
            INNER JOIN {$wpdb->posts} p ON c.course_id = p.ID
            INNER JOIN {$wpdb->users} u ON c.user_id = u.ID
            LEFT JOIN {$wpdb->users} i ON c.instructor_id = i.ID
            WHERE c.certificate_code = %s",
            $code
        ));
    }

    /**
     * Generate certificate HTML
     */
    public function generate_certificate_html($certificate_code) {
        $cert = $this->get_certificate_by_code($certificate_code);

        if (!$cert) {
            return '<p>Certificat introuvable.</p>';
        }

        $completion_date = date('d F Y', strtotime($cert->completion_date));
        $verification_url = site_url('/verification-certificat/?code=' . $cert->certificate_code);

        ob_start();
        ?>
        <div class="eia-certificate" style="
            width: 297mm;
            height: 210mm;
            margin: 0 auto;
            background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
            border: 20px solid #2D4FB3;
            padding: 60px;
            position: relative;
            font-family: 'Georgia', serif;
            box-sizing: border-box;
        ">
            <!-- Decorative corners -->
            <div style="position: absolute; top: 40px; left: 40px; width: 100px; height: 100px; border-top: 3px solid #F59E0B; border-left: 3px solid #F59E0B;"></div>
            <div style="position: absolute; top: 40px; right: 40px; width: 100px; height: 100px; border-top: 3px solid #F59E0B; border-right: 3px solid #F59E0B;"></div>
            <div style="position: absolute; bottom: 40px; left: 40px; width: 100px; height: 100px; border-bottom: 3px solid #F59E0B; border-left: 3px solid #F59E0B;"></div>
            <div style="position: absolute; bottom: 40px; right: 40px; width: 100px; height: 100px; border-bottom: 3px solid #F59E0B; border-right: 3px solid #F59E0B;"></div>

            <!-- Content -->
            <div style="text-align: center;">
                <!-- Logo/Header -->
                <div style="margin-bottom: 30px;">
                    <h1 style="font-size: 48px; color: #2D4FB3; margin: 0; font-weight: 700; letter-spacing: 2px;">
                        ÉCOLE INTERNATIONALE<br>DES AFFAIRES
                    </h1>
                    <div style="width: 200px; height: 3px; background: #F59E0B; margin: 20px auto;"></div>
                </div>

                <!-- Certificate Title -->
                <div style="margin-bottom: 40px;">
                    <h2 style="font-size: 36px; color: #1f2937; margin: 0; font-weight: 400; font-style: italic;">
                        Certificat de Réussite
                    </h2>
                </div>

                <!-- Text -->
                <div style="margin-bottom: 40px; font-size: 18px; color: #4b5563; line-height: 1.8;">
                    <p style="margin: 0 0 20px 0;">Décerné à</p>
                    <h3 style="font-size: 42px; color: #2D4FB3; margin: 20px 0; font-weight: 700;">
                        <?php echo esc_html($cert->student_name); ?>
                    </h3>
                    <p style="margin: 20px 0;">pour avoir complété avec succès le cours</p>
                    <h4 style="font-size: 28px; color: #1f2937; margin: 20px 0; font-weight: 600;">
                        <?php echo esc_html($cert->course_title); ?>
                    </h4>
                    <?php if ($cert->grade_percentage) : ?>
                        <p style="margin: 10px 0; font-weight: 600; color: #10B981;">
                            Note obtenue : <?php echo number_format($cert->grade_percentage, 1); ?>%
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Date and Signatures -->
                <div style="margin-top: 60px; display: flex; justify-content: space-around; align-items: end;">
                    <div style="text-align: center;">
                        <div style="width: 200px; border-top: 2px solid #6b7280; padding-top: 10px;">
                            <p style="margin: 0; font-size: 14px; color: #6b7280;">Date de complétion</p>
                            <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: 600; color: #1f2937;">
                                <?php echo $completion_date; ?>
                            </p>
                        </div>
                    </div>

                    <?php if ($cert->instructor_name) : ?>
                        <div style="text-align: center;">
                            <div style="width: 200px; border-top: 2px solid #6b7280; padding-top: 10px;">
                                <p style="margin: 0; font-size: 14px; color: #6b7280;">Instructeur</p>
                                <p style="margin: 5px 0 0 0; font-size: 16px; font-weight: 600; color: #1f2937;">
                                    <?php echo esc_html($cert->instructor_name); ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Certificate Code -->
                <div style="margin-top: 40px; padding: 15px; background: #f3f4f6; border-radius: 8px; display: inline-block;">
                    <p style="margin: 0; font-size: 12px; color: #6b7280;">Code de vérification</p>
                    <p style="margin: 5px 0 0 0; font-size: 18px; font-weight: 700; color: #2D4FB3; font-family: monospace;">
                        <?php echo esc_html($cert->certificate_code); ?>
                    </p>
                    <p style="margin: 5px 0 0 0; font-size: 11px; color: #9ca3af;">
                        Vérifier à : <?php echo site_url('/verification-certificat/'); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX: Download certificate as HTML
     */
    public function ajax_download_certificate() {
        check_ajax_referer('eia-certificates', 'nonce');

        $certificate_code = sanitize_text_field($_POST['certificate_code']);
        $cert = $this->get_certificate_by_code($certificate_code);

        if (!$cert || $cert->user_id != get_current_user_id()) {
            wp_send_json_error(array('message' => 'Certificat introuvable ou accès refusé'));
        }

        $html = $this->generate_certificate_html($certificate_code);

        wp_send_json_success(array('html' => $html));
    }

    /**
     * AJAX: Verify certificate
     */
    public function ajax_verify_certificate() {
        $code = sanitize_text_field($_POST['code']);
        $cert = $this->get_certificate_by_code($code);

        if (!$cert) {
            wp_send_json_error(array('message' => 'Certificat invalide ou introuvable'));
        }

        wp_send_json_success(array(
            'valid' => true,
            'student_name' => $cert->student_name,
            'course_title' => $cert->course_title,
            'completion_date' => date('d/m/Y', strtotime($cert->completion_date)),
            'grade_percentage' => $cert->grade_percentage
        ));
    }

    /**
     * Handle PDF download request
     */
    public function handle_pdf_download() {
        if (!isset($_GET['eia_certificate_pdf']) || !isset($_GET['code'])) {
            return;
        }

        $certificate_code = sanitize_text_field($_GET['code']);
        $cert = $this->get_certificate_by_code($certificate_code);

        if (!$cert) {
            wp_die('Certificat introuvable.');
        }

        // Generate PDF
        $this->generate_certificate_pdf($cert);
        exit;
    }

    /**
     * Generate certificate PDF using TCPDF
     */
    public function generate_certificate_pdf($cert) {
        if (!class_exists('TCPDF')) {
            wp_die('TCPDF library not found.');
        }

        // Create new PDF document (A4 Landscape)
        $pdf = new \TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('EIA - École Internationale des Affaires');
        $pdf->SetAuthor('EIA');
        $pdf->SetTitle('Certificat de Réussite - ' . $cert->student_name);
        $pdf->SetSubject('Certificat de Réussite');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false, 0);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Main border
        $pdf->SetLineWidth(5);
        $pdf->SetDrawColor(45, 79, 179); // #2D4FB3
        $pdf->Rect(10, 10, 277, 190);

        // Inner decorative corners
        $pdf->SetLineWidth(1);
        $pdf->SetDrawColor(245, 158, 11); // #F59E0B
        // Top left
        $pdf->Line(20, 20, 20, 45);
        $pdf->Line(20, 20, 45, 20);
        // Top right
        $pdf->Line(252, 20, 277, 20);
        $pdf->Line(277, 20, 277, 45);
        // Bottom left
        $pdf->Line(20, 155, 20, 180);
        $pdf->Line(20, 180, 45, 180);
        // Bottom right
        $pdf->Line(277, 155, 277, 180);
        $pdf->Line(252, 180, 277, 180);

        // Reset to black for text
        $pdf->SetTextColor(0, 0, 0);

        // Logo/Header
        $pdf->SetFont('helvetica', 'B', 24);
        $pdf->SetTextColor(45, 79, 179);
        $pdf->SetXY(20, 30);
        $pdf->Cell(257, 10, 'ÉCOLE INTERNATIONALE DES AFFAIRES', 0, 1, 'C');

        // Decorative line
        $pdf->SetLineWidth(1);
        $pdf->SetDrawColor(245, 158, 11);
        $pdf->Line(90, 45, 207, 45);

        // Certificate Title
        $pdf->SetFont('helvetica', 'I', 18);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->SetXY(20, 55);
        $pdf->Cell(257, 10, 'Certificat de Réussite', 0, 1, 'C');

        // "Décerné à" text
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetXY(20, 70);
        $pdf->Cell(257, 8, 'Décerné à', 0, 1, 'C');

        // Student name
        $pdf->SetFont('helvetica', 'B', 22);
        $pdf->SetTextColor(45, 79, 179);
        $pdf->SetXY(20, 80);
        $pdf->Cell(257, 12, $cert->student_name, 0, 1, 'C');

        // "pour avoir complété" text
        $pdf->SetFont('helvetica', '', 11);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetXY(20, 95);
        $pdf->Cell(257, 8, 'pour avoir complété avec succès le cours', 0, 1, 'C');

        // Course title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->SetXY(20, 105);
        $pdf->MultiCell(257, 8, $cert->course_title, 0, 'C');

        // Grade percentage (if available)
        if ($cert->grade_percentage) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(16, 185, 129); // #10B981
            $pdf->SetXY(20, 120);
            $pdf->Cell(257, 8, 'Note obtenue : ' . number_format($cert->grade_percentage, 1) . '%', 0, 1, 'C');
        }

        // Completion date
        $completion_date = date('d F Y', strtotime($cert->completion_date));
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->SetXY(40, 145);
        $pdf->Cell(70, 5, 'Date de complétion', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(31, 41, 55);
        $pdf->SetXY(40, 150);
        $pdf->Cell(70, 5, $completion_date, 0, 1, 'C');
        // Line above
        $pdf->SetLineWidth(0.5);
        $pdf->SetDrawColor(107, 114, 128);
        $pdf->Line(50, 144, 100, 144);

        // Instructor name (if available)
        if ($cert->instructor_name) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(107, 114, 128);
            $pdf->SetXY(187, 145);
            $pdf->Cell(70, 5, 'Instructeur', 0, 1, 'C');
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetTextColor(31, 41, 55);
            $pdf->SetXY(187, 150);
            $pdf->Cell(70, 5, $cert->instructor_name, 0, 1, 'C');
            // Line above
            $pdf->SetLineWidth(0.5);
            $pdf->SetDrawColor(107, 114, 128);
            $pdf->Line(197, 144, 247, 144);
        }

        // Certificate code box
        $pdf->SetFillColor(243, 244, 246); // #f3f4f6
        $pdf->RoundedRect(100, 165, 97, 20, 3, '1111', 'F');

        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->SetXY(100, 167);
        $pdf->Cell(97, 4, 'Code de vérification', 0, 1, 'C');

        $pdf->SetFont('courier', 'B', 12);
        $pdf->SetTextColor(45, 79, 179);
        $pdf->SetXY(100, 172);
        $pdf->Cell(97, 5, $cert->certificate_code, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 7);
        $pdf->SetTextColor(156, 163, 175);
        $pdf->SetXY(100, 179);
        $pdf->Cell(97, 4, 'Vérifier à : ' . site_url('/verification-certificat/'), 0, 1, 'C');

        // Output PDF
        $filename = 'Certificat-' . sanitize_file_name($cert->student_name) . '-' . $cert->certificate_code . '.pdf';
        $pdf->Output($filename, 'D'); // D = force download
    }
}

// Initialize
EIA_Certificates::get_instance();
