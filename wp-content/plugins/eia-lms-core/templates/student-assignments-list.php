<?php
/**
 * Template for student assignments list
 *
 * @package EIA_LMS_Core
 */

if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
?>

<div class="eia-student-assignments">
    <h2>Mes devoirs</h2>

    <?php if (empty($assignments)) : ?>
        <div class="no-assignments">
            <span class="dashicons dashicons-info"></span>
            <p>Aucun devoir disponible pour le moment.</p>
        </div>
    <?php else : ?>
        <div class="assignments-grid">
            <?php foreach ($assignments as $assignment) :
                $assignment_id = $assignment->ID;
                $course_id = get_post_meta($assignment_id, '_assignment_course_id', true);
                $due_date = get_post_meta($assignment_id, '_assignment_due_date', true);
                $max_grade = get_post_meta($assignment_id, '_assignment_max_grade', true);

                // Get submission
                $submission = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}eia_assignment_submissions
                     WHERE assignment_id = %d AND user_id = %d
                     ORDER BY submitted_date DESC LIMIT 1",
                    $assignment_id,
                    $user_id
                ));

                $is_overdue = $due_date && strtotime($due_date) < current_time('timestamp');
                $is_submitted = !empty($submission);
                $is_graded = $is_submitted && $submission->status === 'graded';
            ?>
                <div class="assignment-card <?php echo $is_overdue ? 'overdue' : ''; ?>">
                    <div class="assignment-header">
                        <h3><?php echo esc_html($assignment->post_title); ?></h3>

                        <?php if ($is_graded) : ?>
                            <div class="grade-badge">
                                <span class="grade-value"><?php echo $submission->grade; ?></span>
                                <span class="grade-max">/<?php echo $max_grade; ?></span>
                            </div>
                        <?php elseif ($is_submitted) : ?>
                            <div class="status-badge submitted">Soumis</div>
                        <?php elseif ($is_overdue) : ?>
                            <div class="status-badge overdue">Dépassé</div>
                        <?php else : ?>
                            <div class="status-badge pending">À faire</div>
                        <?php endif; ?>
                    </div>

                    <div class="assignment-course">
                        <span class="dashicons dashicons-book"></span>
                        <?php echo get_the_title($course_id); ?>
                    </div>

                    <div class="assignment-excerpt">
                        <?php echo wp_trim_words($assignment->post_content, 20); ?>
                    </div>

                    <div class="assignment-meta">
                        <?php if ($due_date) : ?>
                            <div class="meta-item">
                                <span class="dashicons dashicons-calendar-alt"></span>
                                <?php echo date('d/m/Y H:i', strtotime($due_date)); ?>
                            </div>
                        <?php endif; ?>

                        <div class="meta-item">
                            <span class="dashicons dashicons-awards"></span>
                            <?php echo $max_grade; ?> points
                        </div>
                    </div>

                    <?php if ($is_submitted) : ?>
                        <div class="submission-info">
                            <span class="dashicons dashicons-yes-alt"></span>
                            Soumis le <?php echo date('d/m/Y à H:i', strtotime($submission->submitted_date)); ?>
                        </div>

                        <?php if ($is_graded && $submission->feedback) : ?>
                            <div class="feedback-preview">
                                <strong>Feedback :</strong>
                                <?php echo wp_trim_words($submission->feedback, 15); ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="assignment-actions">
                        <a href="<?php echo get_permalink($assignment_id); ?>" class="button button-primary">
                            <?php echo $is_submitted ? 'Voir les détails' : 'Soumettre'; ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.eia-student-assignments {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.eia-student-assignments h2 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 2rem;
    border-bottom: 3px solid #2D4FB3;
    padding-bottom: 0.75rem;
}

.no-assignments {
    text-align: center;
    padding: 3rem;
    background: #f9fafb;
    border-radius: 8px;
}

.no-assignments .dashicons {
    font-size: 3rem;
    width: 3rem;
    height: 3rem;
    color: #9ca3af;
}

.assignments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 1.5rem;
}

.assignment-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    padding: 1.5rem;
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid #2D4FB3;
}

.assignment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.assignment-card.overdue {
    border-left-color: #ef4444;
}

.assignment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.assignment-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #1f2937;
    flex: 1;
}

.grade-badge {
    background: #10b981;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 8px;
    font-weight: 600;
    text-align: center;
    min-width: 70px;
}

.grade-value {
    font-size: 1.5rem;
}

.grade-max {
    font-size: 0.875rem;
    opacity: 0.9;
}

.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-badge.submitted {
    background: #3b82f6;
    color: white;
}

.status-badge.overdue {
    background: #ef4444;
    color: white;
}

.status-badge.pending {
    background: #f59e0b;
    color: white;
}

.assignment-course {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6b7280;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.assignment-course .dashicons {
    color: #2D4FB3;
}

.assignment-excerpt {
    color: #4b5563;
    margin-bottom: 1rem;
    line-height: 1.6;
}

.assignment-meta {
    display: flex;
    gap: 1.5rem;
    padding: 1rem 0;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.meta-item .dashicons {
    color: #9ca3af;
    font-size: 18px;
    width: 18px;
    height: 18px;
}

.submission-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem;
    background: #d1fae5;
    color: #065f46;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: 0.875rem;
}

.submission-info .dashicons {
    color: #10b981;
}

.feedback-preview {
    background: #eff6ff;
    padding: 0.75rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    font-size: 0.875rem;
    color: #1e40af;
}

.assignment-actions {
    margin-top: 1rem;
}

.assignment-actions .button {
    width: 100%;
    text-align: center;
    justify-content: center;
    background: #2D4FB3;
    border-color: #2D4FB3;
    color: white;
    font-weight: 600;
}

.assignment-actions .button:hover {
    background: #1e3a8a;
    border-color: #1e3a8a;
}
</style>
