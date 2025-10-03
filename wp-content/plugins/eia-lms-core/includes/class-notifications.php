<?php
/**
 * EIA Notifications System
 *
 * Handles email notifications for various events:
 * - Assignment submissions
 * - Assignment grading
 * - Course enrollment
 * - Course completion
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
        // Set HTML email content type
        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
    }

    /**
     * Set email content type to HTML
     */
    public function set_html_content_type() {
        return 'text/html';
    }

    /**
     * Send notification when assignment is graded
     *
     * @param int $submission_id The submission ID
     * @param float $grade The grade given
     * @param string $feedback Instructor feedback
     */
    public function notify_assignment_graded($submission_id, $grade, $feedback) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';

        // Get submission details
        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            return false;
        }

        // Get student info
        $student = get_userdata($submission->user_id);
        if (!$student) {
            return false;
        }

        // Get assignment info
        $assignment = get_post($submission->assignment_id);
        if (!$assignment) {
            return false;
        }

        $max_grade = get_post_meta($assignment->ID, '_assignment_max_grade', true);

        // Get instructor info
        $grader = get_userdata($submission->graded_by);
        $grader_name = $grader ? $grader->display_name : 'Votre instructeur';

        // Prepare email
        $to = $student->user_email;
        $subject = 'Votre devoir a été noté - ' . $assignment->post_title;

        $message = $this->get_email_template('assignment_graded', array(
            'student_name' => $student->display_name,
            'assignment_title' => $assignment->post_title,
            'grade' => $grade,
            'max_grade' => $max_grade,
            'feedback' => $feedback,
            'grader_name' => $grader_name,
            'assignment_url' => get_permalink($assignment->ID),
            'dashboard_url' => site_url('/mes-cours/')
        ));

        // Send email
        return wp_mail($to, $subject, $message);
    }

    /**
     * Send notification when assignment is submitted
     *
     * @param int $submission_id The submission ID
     */
    public function notify_assignment_submitted($submission_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'eia_assignment_submissions';

        // Get submission details
        $submission = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $submission_id
        ));

        if (!$submission) {
            return false;
        }

        // Get student info
        $student = get_userdata($submission->user_id);
        if (!$student) {
            return false;
        }

        // Get assignment info
        $assignment = get_post($submission->assignment_id);
        if (!$assignment) {
            return false;
        }

        // Get course info to find instructor
        $course_id = get_post_meta($assignment->ID, '_assignment_course', true);
        if (!$course_id) {
            return false;
        }

        $course = get_post($course_id);
        $instructor = get_userdata($course->post_author);

        if (!$instructor) {
            return false;
        }

        // Prepare email
        $to = $instructor->user_email;
        $subject = 'Nouveau devoir soumis - ' . $assignment->post_title;

        $message = $this->get_email_template('assignment_submitted', array(
            'instructor_name' => $instructor->display_name,
            'student_name' => $student->display_name,
            'assignment_title' => $assignment->post_title,
            'course_title' => $course->post_title,
            'submitted_date' => date('d/m/Y à H:i', strtotime($submission->submitted_date)),
            'grading_url' => site_url('/notation-devoirs/?assignment_id=' . $assignment->ID),
            'has_file' => !empty($submission->file_url),
            'has_text' => !empty($submission->submission_text)
        ));

        // Send email
        return wp_mail($to, $subject, $message);
    }

    /**
     * Get email template
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string HTML email content
     */
    private function get_email_template($template, $data) {
        $templates = array(
            'assignment_graded' => $this->template_assignment_graded($data),
            'assignment_submitted' => $this->template_assignment_submitted($data),
        );

        return isset($templates[$template]) ? $templates[$template] : '';
    }

    /**
     * Email template: Assignment graded
     */
    private function template_assignment_graded($data) {
        $percentage = ($data['grade'] / $data['max_grade']) * 100;
        $grade_color = $percentage >= 70 ? '#10B981' : ($percentage >= 50 ? '#F59E0B' : '#EF4444');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Devoir noté</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f7;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f7; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background-color: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #2D4FB3 0%, #1e3a8a 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;"><i class="fas fa-check-circle" style="margin-right: 10px;"></i>Devoir Noté</h1>
                                    <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">École Internationale des Affaires</p>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <p style="margin: 0 0 20px 0; font-size: 16px; color: #1f2937;">Bonjour <strong><?php echo esc_html($data['student_name']); ?></strong>,</p>

                                    <p style="margin: 0 0 30px 0; font-size: 16px; color: #1f2937; line-height: 1.6;">
                                        Votre devoir <strong>"<?php echo esc_html($data['assignment_title']); ?>"</strong> a été noté par <?php echo esc_html($data['grader_name']); ?>.
                                    </p>

                                    <!-- Grade Card -->
                                    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border: 3px solid <?php echo $grade_color; ?>; border-radius: 12px; margin-bottom: 30px;">
                                        <tr>
                                            <td style="padding: 30px; text-align: center;">
                                                <div style="font-size: 14px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">Votre note</div>
                                                <div style="font-size: 48px; font-weight: bold; color: <?php echo $grade_color; ?>; margin-bottom: 5px;">
                                                    <?php echo number_format($data['grade'], 1); ?><span style="font-size: 24px; color: #9ca3af;">/<?php echo $data['max_grade']; ?></span>
                                                </div>
                                                <div style="font-size: 18px; color: #6b7280;">
                                                    (<?php echo round($percentage); ?>%)
                                                </div>
                                            </td>
                                        </tr>
                                    </table>

                                    <?php if (!empty($data['feedback'])) : ?>
                                        <!-- Feedback -->
                                        <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 20px; border-radius: 6px; margin-bottom: 30px;">
                                            <h3 style="margin: 0 0 10px 0; color: #1e40af; font-size: 16px; font-weight: 600;">Feedback de l'instructeur</h3>
                                            <p style="margin: 0; color: #1f2937; font-size: 15px; line-height: 1.6;">
                                                <?php echo nl2br(esc_html($data['feedback'])); ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                    <!-- CTA Button -->
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center" style="padding: 20px 0;">
                                                <a href="<?php echo esc_url($data['assignment_url']); ?>" style="display: inline-block; padding: 16px 40px; background-color: #2D4FB3; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(45, 79, 179, 0.3);">
                                                    Voir les détails
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin: 30px 0 0 0; font-size: 14px; color: #6b7280; text-align: center;">
                                        Vous pouvez également consulter votre <a href="<?php echo esc_url($data['dashboard_url']); ?>" style="color: #2D4FB3; text-decoration: none;">tableau de bord</a>
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                                    <p style="margin: 0 0 10px 0; font-size: 14px; color: #6b7280;">
                                        École Internationale des Affaires (EIA)
                                    </p>
                                    <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                        Cet email a été envoyé automatiquement, merci de ne pas y répondre.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * Email template: Assignment submitted
     */
    private function template_assignment_submitted($data) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Nouvelle soumission</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f7;">
            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f7; padding: 40px 20px;">
                <tr>
                    <td align="center">
                        <table width="600" cellpadding="0" cellspacing="0" style="background-color: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">

                            <!-- Header -->
                            <tr>
                                <td style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); padding: 40px 30px; text-align: center;">
                                    <h1 style="margin: 0; color: white; font-size: 28px; font-weight: 700;"><i class="fas fa-file-alt" style="margin-right: 10px;"></i>Nouveau Devoir Soumis</h1>
                                    <p style="margin: 10px 0 0 0; color: rgba(255,255,255,0.9); font-size: 16px;">École Internationale des Affaires</p>
                                </td>
                            </tr>

                            <!-- Content -->
                            <tr>
                                <td style="padding: 40px 30px;">
                                    <p style="margin: 0 0 20px 0; font-size: 16px; color: #1f2937;">Bonjour <strong><?php echo esc_html($data['instructor_name']); ?></strong>,</p>

                                    <p style="margin: 0 0 30px 0; font-size: 16px; color: #1f2937; line-height: 1.6;">
                                        <strong><?php echo esc_html($data['student_name']); ?></strong> vient de soumettre le devoir <strong>"<?php echo esc_html($data['assignment_title']); ?>"</strong> pour le cours <strong><?php echo esc_html($data['course_title']); ?></strong>.
                                    </p>

                                    <!-- Submission Info -->
                                    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f9fafb; border-radius: 8px; margin-bottom: 30px;">
                                        <tr>
                                            <td style="padding: 20px;">
                                                <table width="100%" cellpadding="8" cellspacing="0">
                                                    <tr>
                                                        <td style="color: #6b7280; font-size: 14px; width: 140px;">Date de soumission:</td>
                                                        <td style="color: #1f2937; font-size: 14px; font-weight: 600;"><?php echo esc_html($data['submitted_date']); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td style="color: #6b7280; font-size: 14px;">Type de soumission:</td>
                                                        <td style="color: #1f2937; font-size: 14px;">
                                                            <?php if ($data['has_file'] && $data['has_text']) : ?>
                                                                <i class="fas fa-paperclip"></i> Fichier + <i class="fas fa-align-left"></i> Texte
                                                            <?php elseif ($data['has_file']) : ?>
                                                                <i class="fas fa-paperclip"></i> Fichier uniquement
                                                            <?php else : ?>
                                                                <i class="fas fa-align-left"></i> Texte uniquement
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>

                                    <!-- CTA Button -->
                                    <table width="100%" cellpadding="0" cellspacing="0">
                                        <tr>
                                            <td align="center" style="padding: 20px 0;">
                                                <a href="<?php echo esc_url($data['grading_url']); ?>" style="display: inline-block; padding: 16px 40px; background-color: #F59E0B; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);">
                                                    Noter le devoir
                                                </a>
                                            </td>
                                        </tr>
                                    </table>

                                    <p style="margin: 30px 0 0 0; font-size: 14px; color: #6b7280; text-align: center;">
                                        Pensez à noter rapidement pour maintenir l'engagement de vos étudiants!
                                    </p>
                                </td>
                            </tr>

                            <!-- Footer -->
                            <tr>
                                <td style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                                    <p style="margin: 0 0 10px 0; font-size: 14px; color: #6b7280;">
                                        École Internationale des Affaires (EIA)
                                    </p>
                                    <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                        Cet email a été envoyé automatiquement, merci de ne pas y répondre.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}
