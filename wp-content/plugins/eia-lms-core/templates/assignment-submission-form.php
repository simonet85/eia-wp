<?php
/**
 * Template for assignment submission form
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="eia-assignment-submission-form">
    <h2><?php echo esc_html($assignment->post_title); ?></h2>

    <div class="assignment-details">
        <div class="assignment-content">
            <?php echo wpautop($assignment->post_content); ?>
        </div>

        <div class="assignment-meta">
            <?php if ($due_date) :
                $is_overdue = strtotime($due_date) < current_time('timestamp');
                $due_timestamp = strtotime($due_date);
            ?>
                <div class="meta-item">
                    <span class="dashicons dashicons-calendar-alt"></span>
                    <strong>Date limite :</strong>
                    <?php echo date('d/m/Y à H:i', strtotime($due_date)); ?>
                    <?php if ($is_overdue) : ?>
                        <?php if ($allow_late_submission === 'yes') : ?>
                            <span class="overdue-badge" style="background: #F59E0B;">⏰ Dépassé (soumission autorisée)</span>
                        <?php else : ?>
                            <span class="overdue-badge">⏰ Dépassé</span>
                        <?php endif; ?>
                    <?php else : ?>
                        <span class="countdown-badge" id="countdown-timer" data-deadline="<?php echo $due_timestamp; ?>">
                            ⏱️ Calcul...
                        </span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="meta-item">
                <span class="dashicons dashicons-awards"></span>
                <strong>Note maximale :</strong>
                <?php echo get_post_meta($assignment_id, '_assignment_max_grade', true); ?> points
            </div>

            <?php if ($submission) : ?>
                <div class="meta-item submission-status">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <strong>Statut :</strong>
                    <?php
                    if ($submission->status === 'graded') {
                        echo '<span class="status-badge graded">Noté</span>';
                        echo '<div class="grade-display">';
                        echo '<strong>Note :</strong> ' . $submission->grade . '/' . get_post_meta($assignment_id, '_assignment_max_grade', true);
                        echo '</div>';
                    } else {
                        echo '<span class="status-badge submitted">Soumis le ' . date('d/m/Y à H:i', strtotime($submission->submitted_date)) . '</span>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($submission && $submission->status === 'graded' && !empty($submission->feedback)) : ?>
        <div class="assignment-feedback">
            <h3>Feedback de l'instructeur</h3>
            <div class="feedback-content">
                <?php echo wpautop($submission->feedback); ?>
            </div>
        </div>
    <?php endif; ?>

    <?php
    // Check if submission should be disabled
    $allow_late_submission = get_post_meta($assignment_id, '_assignment_allow_late_submission', true);
    $is_overdue = isset($due_date) && strtotime($due_date) < current_time('timestamp');
    $is_graded = $submission && $submission->status === 'graded';

    // Disable only if overdue AND late submissions not allowed, OR if already graded
    $submission_disabled = ($is_overdue && $allow_late_submission !== 'yes') || $is_graded;

    if ($submission && $allow_resubmission !== 'yes') : ?>
        <div class="submission-info">
            <p><strong>Vous avez déjà soumis ce devoir.</strong> La resoumission n'est pas autorisée.</p>
            <?php if ($submission->file_url) : ?>
                <p>
                    <a href="<?php echo esc_url($submission->file_url); ?>" target="_blank" class="download-link">
                        <span class="dashicons dashicons-download"></span>
                        Télécharger votre soumission
                    </a>
                </p>
            <?php endif; ?>
        </div>
    <?php elseif ($submission_disabled) : ?>
        <div class="submission-disabled-notice">
            <?php if ($is_graded) : ?>
                <div class="disabled-icon"><i class="fas fa-lock"></i></div>
                <h3>Soumission fermée</h3>
                <p>Ce devoir a déjà été noté par votre instructeur. Vous ne pouvez plus modifier votre soumission.</p>
                <div class="graded-info">
                    <div class="grade-box">
                        <div class="grade-label">Votre note</div>
                        <div class="grade-value"><?php echo number_format($submission->grade, 1); ?>/<?php echo get_post_meta($assignment_id, '_assignment_max_grade', true); ?></div>
                    </div>
                </div>
            <?php elseif ($is_overdue && $allow_late_submission !== 'yes') : ?>
                <div class="disabled-icon">⏰</div>
                <h3>Délai dépassé</h3>
                <p>La date limite de soumission est dépassée. Vous ne pouvez plus soumettre ce devoir.</p>
                <p class="deadline-info">Date limite : <strong><?php echo date('d/m/Y à H:i', strtotime($due_date)); ?></strong></p>
            <?php endif; ?>

            <?php if ($submission && $submission->file_url) : ?>
                <div class="previous-submission">
                    <p><strong>Votre soumission :</strong></p>
                    <a href="<?php echo esc_url($submission->file_url); ?>" target="_blank" class="download-link">
                        <span class="dashicons dashicons-download"></span>
                        Télécharger votre fichier
                    </a>
                </div>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <form id="eia-assignment-submit-form" class="assignment-submit-form" enctype="multipart/form-data">
            <?php wp_nonce_field('eia_submit_assignment', 'assignment_nonce'); ?>
            <input type="hidden" name="assignment_id" value="<?php echo $assignment_id; ?>">

            <?php if ($submission) : ?>
                <div class="resubmission-notice">
                    <span class="dashicons dashicons-info"></span>
                    <p>Vous pouvez resoumettresingle ce devoir. Votre précédente soumission sera remplacée.</p>
                </div>
            <?php endif; ?>

            <?php if (in_array($submission_type, array('text', 'both'))) : ?>
                <div class="form-group">
                    <label for="submission_text">Votre réponse</label>
                    <textarea
                        id="submission_text"
                        name="submission_text"
                        rows="10"
                        class="widefat"
                        placeholder="Rédigez votre réponse ici..."><?php echo $submission ? esc_textarea($submission->submission_text) : ''; ?></textarea>
                </div>
            <?php endif; ?>

            <?php if (in_array($submission_type, array('file', 'both'))) : ?>
                <div class="form-group">
                    <label for="submission_file">
                        Fichier à soumettre
                        <span class="file-info">(Max: <?php echo get_post_meta($assignment_id, '_assignment_max_file_size', true); ?> MB)</span>
                    </label>
                    <input
                        type="file"
                        id="submission_file"
                        name="submission_file"
                        accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png,.zip">
                    <p class="description">Formats acceptés : PDF, DOC, DOCX, TXT, JPG, PNG, ZIP</p>

                    <?php if ($submission && $submission->file_url) : ?>
                        <div class="current-file">
                            <span class="dashicons dashicons-paperclip"></span>
                            <a href="<?php echo esc_url($submission->file_url); ?>" target="_blank">
                                Fichier actuel
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="button button-primary button-large">
                    <span class="dashicons dashicons-upload"></span>
                    <?php echo $submission ? 'Resoummettre' : 'Soumettre le devoir'; ?>
                </button>
            </div>

            <div class="submission-message" style="display: none;"></div>
        </form>
    <?php endif; ?>
</div>

<style>
.eia-assignment-submission-form {
    max-width: 800px;
    margin: 2rem auto;
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.assignment-details {
    margin: 2rem 0;
}

.assignment-content {
    background: #f9fafb;
    padding: 1.5rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.assignment-meta {
    display: grid;
    gap: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 4px;
}

.meta-item .dashicons {
    color: #2D4FB3;
}

.overdue-badge {
    background: #ef4444;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    margin-left: 0.5rem;
    font-weight: 600;
}

.countdown-badge {
    background: #10b981;
    color: white;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    margin-left: 0.5rem;
    font-weight: 600;
    font-family: monospace;
}

.countdown-badge.warning {
    background: #f59e0b;
}

.countdown-badge.danger {
    background: #ef4444;
    animation: pulse 1s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
}

.status-badge.submitted {
    background: #3b82f6;
    color: white;
}

.status-badge.graded {
    background: #10b981;
    color: white;
}

.grade-display {
    margin-top: 0.5rem;
    font-size: 1.125rem;
    color: #10b981;
}

.assignment-feedback {
    background: #eff6ff;
    border-left: 4px solid #3b82f6;
    padding: 1.5rem;
    margin: 1.5rem 0;
    border-radius: 4px;
}

.assignment-feedback h3 {
    margin-top: 0;
    color: #1e40af;
}

.feedback-content {
    color: #1f2937;
}

.submission-info {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 1.5rem;
    margin: 1.5rem 0;
    border-radius: 4px;
}

.resubmission-notice {
    background: #dbeafe;
    border-left: 4px solid #3b82f6;
    padding: 1rem;
    margin-bottom: 1.5rem;
    border-radius: 4px;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.resubmission-notice .dashicons {
    color: #3b82f6;
    flex-shrink: 0;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #1f2937;
}

.file-info {
    font-weight: normal;
    color: #6b7280;
    font-size: 0.875rem;
}

.form-group textarea,
.form-group input[type="file"] {
    width: 100%;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    padding: 0.75rem;
}

.form-group textarea:focus {
    outline: none;
    border-color: #2D4FB3;
    box-shadow: 0 0 0 3px rgba(45, 79, 179, 0.1);
}

.current-file {
    margin-top: 0.75rem;
    padding: 0.75rem;
    background: #f3f4f6;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.current-file .dashicons {
    color: #6b7280;
}

.form-actions {
    margin-top: 2rem;
}

.button-primary {
    background: #2D4FB3 !important;
    border-color: #2D4FB3 !important;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.button-primary:hover {
    background: #1e3a8a !important;
    border-color: #1e3a8a !important;
}

.submission-message {
    margin-top: 1rem;
    padding: 1rem;
    border-radius: 4px;
}

.submission-message.success {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #10b981;
}

.submission-message.error {
    background: #fee2e2;
    color: #991b1b;
    border: 1px solid #ef4444;
}

.download-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #2D4FB3;
    text-decoration: none;
    font-weight: 600;
}

.download-link:hover {
    text-decoration: underline;
}

.submission-disabled-notice {
    background: #fef3c7;
    border: 2px solid #f59e0b;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    margin: 2rem 0;
}

.submission-disabled-notice.graded {
    background: #d1fae5;
    border-color: #10b981;
}

.disabled-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.submission-disabled-notice h3 {
    color: #92400e;
    margin: 0 0 1rem 0;
    font-size: 1.5rem;
}

.submission-disabled-notice p {
    color: #78350f;
    margin: 0.5rem 0;
}

.deadline-info {
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 6px;
}

.graded-info {
    margin-top: 1.5rem;
}

.grade-box {
    display: inline-block;
    background: white;
    border: 3px solid #10b981;
    border-radius: 12px;
    padding: 1.5rem 2rem;
    text-align: center;
}

.grade-label {
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.grade-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #10b981;
}

.previous-submission {
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 1px solid #d1d5db;
}
</style>

<script>
(function() {
    const countdownTimer = document.getElementById('countdown-timer');

    if (!countdownTimer) return;

    const deadline = parseInt(countdownTimer.dataset.deadline) * 1000; // Convert to milliseconds

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = deadline - now;

        if (distance < 0) {
            countdownTimer.textContent = '⏰ Temps écoulé';
            countdownTimer.className = 'overdue-badge';
            clearInterval(countdownInterval);

            // Reload page to show disabled form
            setTimeout(() => {
                location.reload();
            }, 2000);
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        let timeString = '';

        if (days > 0) {
            timeString = `${days}j ${hours}h ${minutes}m`;
        } else if (hours > 0) {
            timeString = `${hours}h ${minutes}m ${seconds}s`;
        } else {
            timeString = `${minutes}m ${seconds}s`;
        }

        countdownTimer.textContent = `⏱️ ${timeString}`;

        // Change color based on remaining time
        const hoursRemaining = distance / (1000 * 60 * 60);

        if (hoursRemaining <= 1) {
            countdownTimer.className = 'countdown-badge danger';
        } else if (hoursRemaining <= 24) {
            countdownTimer.className = 'countdown-badge warning';
        } else {
            countdownTimer.className = 'countdown-badge';
        }
    }

    // Update immediately
    updateCountdown();

    // Update every second
    const countdownInterval = setInterval(updateCountdown, 1000);
})();
</script>
