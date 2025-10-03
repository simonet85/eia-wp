<?php
/**
 * Template for instructor to view and grade submissions
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$assignment = get_post($assignment_id);
$submissions = $wpdb->get_results($wpdb->prepare(
    "SELECT s.*, u.display_name, u.user_email
     FROM {$wpdb->prefix}eia_assignment_submissions s
     INNER JOIN {$wpdb->users} u ON s.user_id = u.ID
     WHERE s.assignment_id = %d
     ORDER BY s.submitted_date DESC",
    $assignment_id
));

$max_grade = get_post_meta($assignment_id, '_assignment_max_grade', true);
?>

<div class="eia-instructor-submissions">
    <div class="submissions-header">
        <h2><?php echo esc_html($assignment->post_title); ?></h2>
        <div class="header-meta">
            <span class="total-submissions">
                <span class="dashicons dashicons-groups"></span>
                <?php echo count($submissions); ?> soumission(s)
            </span>
        </div>
    </div>

    <?php if (empty($submissions)) : ?>
        <div class="no-submissions">
            <span class="dashicons dashicons-info"></span>
            <p>Aucune soumission pour ce devoir.</p>
        </div>
    <?php else : ?>
        <div class="submissions-list">
            <?php foreach ($submissions as $submission) :
                $grader = $submission->graded_by ? get_userdata($submission->graded_by) : null;
            ?>
                <div class="submission-card" data-submission-id="<?php echo $submission->id; ?>">
                    <div class="submission-header">
                        <div class="student-info">
                            <?php echo get_avatar($submission->user_id, 48); ?>
                            <div>
                                <strong><?php echo esc_html($submission->display_name); ?></strong>
                                <div class="student-email"><?php echo esc_html($submission->user_email); ?></div>
                            </div>
                        </div>

                        <div class="submission-status">
                            <?php if ($submission->status === 'graded') : ?>
                                <div class="graded-badge">
                                    <span class="grade"><?php echo $submission->grade; ?>/<?php echo $max_grade; ?></span>
                                    <span class="status-label">Noté</span>
                                </div>
                            <?php else : ?>
                                <div class="pending-badge">
                                    <span class="status-label">En attente</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="submission-details">
                        <div class="detail-item">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <strong>Soumis le :</strong>
                            <?php echo date('d/m/Y à H:i', strtotime($submission->submitted_date)); ?>
                        </div>

                        <?php if ($submission->status === 'graded') : ?>
                            <div class="detail-item">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <strong>Noté le :</strong>
                                <?php echo date('d/m/Y à H:i', strtotime($submission->graded_date)); ?>
                                <?php if ($grader) : ?>
                                    par <?php echo $grader->display_name; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($submission->submission_text) : ?>
                        <div class="submission-text">
                            <h4>Réponse de l'étudiant</h4>
                            <div class="text-content">
                                <?php echo wpautop($submission->submission_text); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($submission->file_url) : ?>
                        <div class="submission-file">
                            <span class="dashicons dashicons-paperclip"></span>
                            <a href="<?php echo esc_url($submission->file_url); ?>" target="_blank" class="file-link">
                                Télécharger le fichier soumis
                            </a>
                        </div>
                    <?php endif; ?>

                    <div class="grading-form">
                        <h4>Notation</h4>

                        <div class="grade-input-group">
                            <label for="grade_<?php echo $submission->id; ?>">Note</label>
                            <div class="grade-field">
                                <input
                                    type="number"
                                    id="grade_<?php echo $submission->id; ?>"
                                    name="grade"
                                    value="<?php echo esc_attr($submission->grade); ?>"
                                    min="0"
                                    max="<?php echo $max_grade; ?>"
                                    step="0.5"
                                    class="grade-input">
                                <span class="max-grade">/ <?php echo $max_grade; ?></span>
                            </div>
                        </div>

                        <div class="feedback-group">
                            <label for="feedback_<?php echo $submission->id; ?>">Commentaire / Feedback</label>
                            <textarea
                                id="feedback_<?php echo $submission->id; ?>"
                                name="feedback"
                                rows="5"
                                class="feedback-textarea"
                                placeholder="Ajoutez vos commentaires pour l'étudiant..."><?php echo esc_textarea($submission->feedback); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button
                                type="button"
                                class="button button-primary save-grade-btn"
                                data-submission-id="<?php echo $submission->id; ?>">
                                <span class="dashicons dashicons-yes"></span>
                                Enregistrer la note
                            </button>
                        </div>

                        <div class="grade-message" style="display: none;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.eia-instructor-submissions {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.submissions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 3px solid #2D4FB3;
}

.submissions-header h2 {
    margin: 0;
    color: #1f2937;
}

.header-meta {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.total-submissions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: #e0e7ff;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    color: #3730a3;
    font-weight: 600;
}

.no-submissions {
    text-align: center;
    padding: 3rem;
    background: #f9fafb;
    border-radius: 8px;
}

.no-submissions .dashicons {
    font-size: 3rem;
    width: 3rem;
    height: 3rem;
    color: #9ca3af;
}

.submissions-list {
    display: grid;
    gap: 2rem;
}

.submission-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 2rem;
    border-left: 4px solid #2D4FB3;
}

.submission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f3f4f6;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.student-info img {
    border-radius: 50%;
}

.student-email {
    color: #6b7280;
    font-size: 0.875rem;
}

.graded-badge {
    text-align: center;
}

.graded-badge .grade {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: #10b981;
}

.graded-badge .status-label {
    display: block;
    font-size: 0.75rem;
    color: #059669;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.pending-badge {
    background: #fef3c7;
    padding: 0.5rem 1rem;
    border-radius: 20px;
}

.pending-badge .status-label {
    color: #92400e;
    font-weight: 600;
    font-size: 0.875rem;
}

.submission-details {
    display: flex;
    gap: 2rem;
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #4b5563;
}

.detail-item .dashicons {
    color: #2D4FB3;
}

.submission-text {
    margin-bottom: 1.5rem;
}

.submission-text h4 {
    margin-bottom: 0.75rem;
    color: #1f2937;
}

.text-content {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 6px;
    border-left: 3px solid #3b82f6;
}

.submission-file {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: #fef3c7;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.submission-file .dashicons {
    color: #d97706;
}

.file-link {
    color: #92400e;
    font-weight: 600;
    text-decoration: none;
}

.file-link:hover {
    text-decoration: underline;
}

.grading-form {
    background: #f8fafc;
    padding: 1.5rem;
    border-radius: 6px;
    border: 2px solid #e2e8f0;
}

.grading-form h4 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    color: #1e293b;
}

.grade-input-group {
    margin-bottom: 1.5rem;
}

.grade-input-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.grade-field {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.grade-input {
    width: 120px;
    padding: 0.75rem;
    font-size: 1.25rem;
    font-weight: 600;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    text-align: center;
}

.grade-input:focus {
    outline: none;
    border-color: #2D4FB3;
    box-shadow: 0 0 0 3px rgba(45, 79, 179, 0.1);
}

.max-grade {
    font-size: 1.25rem;
    color: #6b7280;
    font-weight: 600;
}

.feedback-group {
    margin-bottom: 1.5rem;
}

.feedback-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.feedback-textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #d1d5db;
    border-radius: 6px;
    font-family: inherit;
    resize: vertical;
}

.feedback-textarea:focus {
    outline: none;
    border-color: #2D4FB3;
    box-shadow: 0 0 0 3px rgba(45, 79, 179, 0.1);
}

.form-actions {
    margin-top: 1rem;
}

.save-grade-btn {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: white !important;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.save-grade-btn:hover {
    background: #059669 !important;
    border-color: #059669 !important;
}

.save-grade-btn .dashicons {
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.grade-message {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 4px;
}

.grade-message.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.grade-message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}
</style>
