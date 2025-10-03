<?php
/**
 * Template: Instructor Assignment Grading Interface
 *
 * Shows all submissions for an assignment with grading capabilities
 */

if (!defined('ABSPATH')) {
    exit;
}

$assignment_id = isset($_GET['assignment_id']) ? intval($_GET['assignment_id']) : 0;

if (!$assignment_id) {
    echo '<p style="color: red;">Assignment ID manquant.</p>';
    return;
}

$assignment = get_post($assignment_id);
if (!$assignment || $assignment->post_type !== 'lp_assignment') {
    echo '<p style="color: red;">Assignment introuvable.</p>';
    return;
}

// Get submissions
global $wpdb;
$table_name = $wpdb->prefix . 'eia_assignment_submissions';

$submissions = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM $table_name WHERE assignment_id = %d ORDER BY submitted_date DESC",
    $assignment_id
));

$total_submissions = count($submissions);
$graded_count = 0;
$pending_count = 0;

foreach ($submissions as $sub) {
    if ($sub->status === 'graded') {
        $graded_count++;
    } else {
        $pending_count++;
    }
}
?>

<div class="eia-instructor-grading" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0 0 0.5rem 0; color: #2D4FB3;">
            <i class="fas fa-clipboard-check"></i> <?php echo esc_html($assignment->post_title); ?>
        </h1>
        <p style="color: #666; margin: 0;">
            Interface de notation pour les instructeurs
        </p>
    </div>

    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Total soumissions</div>
            <div style="font-size: 2rem; font-weight: bold; color: #2D4FB3;"><?php echo $total_submissions; ?></div>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">En attente</div>
            <div style="font-size: 2rem; font-weight: bold; color: #F59E0B;"><?php echo $pending_count; ?></div>
        </div>
        <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; padding: 1.5rem;">
            <div style="color: #6b7280; font-size: 0.875rem; margin-bottom: 0.5rem;">Notées</div>
            <div style="font-size: 2rem; font-weight: bold; color: #10B981;"><?php echo $graded_count; ?></div>
        </div>
    </div>

    <!-- Submissions Table -->
    <div style="background: white; border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden;">
        <?php if (empty($submissions)): ?>
            <div style="padding: 3rem; text-align: center; color: #9ca3af;">
                <p style="font-size: 1.125rem; margin: 0;">Aucune soumission pour le moment</p>
            </div>
        <?php else: ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead style="background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                    <tr>
                        <th style="text-align: left; padding: 1rem; font-weight: 600; color: #374151;">Étudiant</th>
                        <th style="text-align: left; padding: 1rem; font-weight: 600; color: #374151;">Date soumission</th>
                        <th style="text-align: left; padding: 1rem; font-weight: 600; color: #374151;">Statut</th>
                        <th style="text-align: left; padding: 1rem; font-weight: 600; color: #374151;">Note</th>
                        <th style="text-align: left; padding: 1rem; font-weight: 600; color: #374151;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission):
                        $student = get_userdata($submission->user_id);
                        $student_name = $student ? $student->display_name : 'Utilisateur inconnu';

                        $status_colors = array(
                            'submitted' => array('bg' => '#DBEAFE', 'text' => '#1E40AF', 'label' => 'Soumis'),
                            'graded' => array('bg' => '#D1FAE5', 'text' => '#065F46', 'label' => 'Noté'),
                        );

                        $status = $status_colors[$submission->status] ?? $status_colors['submitted'];
                    ?>
                        <tr style="border-bottom: 1px solid #e5e7eb;" data-submission-id="<?php echo $submission->id; ?>">
                            <td style="padding: 1rem;">
                                <strong><?php echo esc_html($student_name); ?></strong><br>
                                <small style="color: #6b7280;"><?php echo esc_html($student->user_email ?? ''); ?></small>
                            </td>
                            <td style="padding: 1rem; color: #6b7280;">
                                <?php echo date('d/m/Y à H:i', strtotime($submission->submitted_date)); ?>
                            </td>
                            <td style="padding: 1rem;">
                                <span class="submission-status" style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; background: <?php echo $status['bg']; ?>; color: <?php echo $status['text']; ?>;">
                                    <?php echo $status['label']; ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <span class="submission-grade" style="font-weight: 600; color: #2D4FB3;">
                                    <?php echo $submission->grade !== null ? number_format($submission->grade, 1) . '/100' : '—'; ?>
                                </span>
                            </td>
                            <td style="padding: 1rem;">
                                <button onclick="openGradingModal(<?php echo $submission->id; ?>)"
                                        style="background: #2D4FB3; color: white; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem;">
                                    <?php echo $submission->status === 'graded' ? 'Modifier la note' : 'Noter'; ?>
                                </button>
                                <button onclick="viewSubmissionDetails(<?php echo $submission->id; ?>)"
                                        style="background: white; color: #374151; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.875rem; margin-left: 0.5rem;">
                                    Voir détails
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Grading Modal -->
<div id="grading-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; color: #2D4FB3;">Noter la soumission</h2>
            <button onclick="closeGradingModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">×</button>
        </div>

        <div id="modal-content" style="padding: 1.5rem;">
            <!-- Content will be loaded dynamically -->
        </div>

        <div style="padding: 1.5rem; border-top: 1px solid #e5e7eb; display: flex; gap: 1rem; justify-content: flex-end;">
            <button onclick="closeGradingModal()" style="background: white; color: #374151; border: 1px solid #d1d5db; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer;">
                Annuler
            </button>
            <button onclick="submitGrade()" style="background: #10B981; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer; font-weight: 600;">
                Enregistrer la note
            </button>
        </div>
    </div>
</div>

<!-- Submission Details Modal -->
<div id="details-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; border-radius: 12px; max-width: 800px; width: 90%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center;">
            <h2 style="margin: 0; color: #2D4FB3;">Détails de la soumission</h2>
            <button onclick="closeDetailsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280;">×</button>
        </div>

        <div id="details-content" style="padding: 1.5rem;">
            <!-- Content will be loaded dynamically -->
        </div>

        <div style="padding: 1.5rem; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end;">
            <button onclick="closeDetailsModal()" style="background: #2D4FB3; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; cursor: pointer;">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
let currentSubmissionId = null;

function openGradingModal(submissionId) {
    currentSubmissionId = submissionId;

    // Load submission data
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'eia_get_submission_details',
            submission_id: submissionId,
            nonce: '<?php echo wp_create_nonce('eia_grading_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;

                let html = '<div style="margin-bottom: 1.5rem;">';
                html += '<label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Note (sur 100)</label>';
                html += '<input type="number" id="grade-input" min="0" max="100" step="0.5" value="' + (data.grade || '') + '" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem;">';
                html += '</div>';

                html += '<div>';
                html += '<label style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">Feedback</label>';
                html += '<textarea id="feedback-input" rows="5" style="width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical;">' + (data.feedback || '') + '</textarea>';
                html += '</div>';

                document.getElementById('modal-content').innerHTML = html;
                document.getElementById('grading-modal').style.display = 'flex';
            }
        }
    });
}

function closeGradingModal() {
    document.getElementById('grading-modal').style.display = 'none';
    currentSubmissionId = null;
}

function submitGrade() {
    const grade = document.getElementById('grade-input').value;
    const feedback = document.getElementById('feedback-input').value;

    if (!grade || grade < 0 || grade > 100) {
        alert('Veuillez entrer une note valide entre 0 et 100');
        return;
    }

    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'eia_grade_submission',
            submission_id: currentSubmissionId,
            grade: grade,
            feedback: feedback,
            nonce: '<?php echo wp_create_nonce('eia_grading_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                alert('Note enregistrée avec succès!');
                location.reload();
            } else {
                alert('Erreur: ' + response.data.message);
            }
        }
    });
}

function viewSubmissionDetails(submissionId) {
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'eia_get_submission_details',
            submission_id: submissionId,
            nonce: '<?php echo wp_create_nonce('eia_grading_nonce'); ?>'
        },
        success: function(response) {
            if (response.success) {
                const data = response.data;

                let html = '<div style="margin-bottom: 1.5rem;">';
                html += '<h3 style="margin: 0 0 0.5rem 0; color: #374151;">Texte soumis</h3>';
                html += '<div style="background: #f9fafb; padding: 1rem; border-radius: 6px; white-space: pre-wrap;">' + (data.submission_text || '<em>Aucun texte soumis</em>') + '</div>';
                html += '</div>';

                if (data.file_url) {
                    html += '<div style="margin-bottom: 1.5rem;">';
                    html += '<h3 style="margin: 0 0 0.5rem 0; color: #374151;">Fichier joint</h3>';
                    html += '<a href="' + data.file_url + '" target="_blank" style="color: #2D4FB3; text-decoration: underline;"><i class="fas fa-download"></i> Télécharger le fichier</a>';
                    html += '</div>';
                }

                if (data.grade !== null) {
                    html += '<div style="margin-bottom: 1.5rem;">';
                    html += '<h3 style="margin: 0 0 0.5rem 0; color: #374151;">Note attribuée</h3>';
                    html += '<div style="font-size: 1.5rem; font-weight: bold; color: #10B981;">' + data.grade + ' / 100</div>';
                    html += '</div>';

                    if (data.feedback) {
                        html += '<div>';
                        html += '<h3 style="margin: 0 0 0.5rem 0; color: #374151;">Feedback</h3>';
                        html += '<div style="background: #f9fafb; padding: 1rem; border-radius: 6px; white-space: pre-wrap;">' + data.feedback + '</div>';
                        html += '</div>';
                    }
                }

                document.getElementById('details-content').innerHTML = html;
                document.getElementById('details-modal').style.display = 'flex';
            }
        }
    });
}

function closeDetailsModal() {
    document.getElementById('details-modal').style.display = 'none';
}
</script>
