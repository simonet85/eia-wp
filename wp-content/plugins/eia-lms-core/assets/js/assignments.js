/**
 * EIA Assignments JavaScript
 *
 * @package EIA_LMS_Core
 */

jQuery(document).ready(function($) {

    // Handle assignment submission
    $('#eia-assignment-submit-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var $message = $form.find('.submission-message');
        var formData = new FormData(this);

        formData.append('action', 'eia_submit_assignment');
        formData.append('nonce', eiaAssignments.nonce);

        // Disable submit button
        $submitBtn.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin"></span> Envoi en cours...');
        $message.hide();

        $.ajax({
            url: eiaAssignments.ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message)
                        .fadeIn();

                    // Reload page after 2 seconds
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html('<span class="dashicons dashicons-warning"></span> ' + response.data.message)
                        .fadeIn();

                    $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Soumettre le devoir');
                }
            },
            error: function(xhr, status, error) {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html('<span class="dashicons dashicons-warning"></span> Erreur lors de l\'envoi. Veuillez réessayer.')
                    .fadeIn();

                $submitBtn.prop('disabled', false).html('<span class="dashicons dashicons-upload"></span> Soumettre le devoir');

                console.error('AJAX Error:', error);
            }
        });
    });

    // Handle grading
    $('.save-grade-btn').on('click', function() {
        var $btn = $(this);
        var submissionId = $btn.data('submission-id');
        var $card = $btn.closest('.submission-card');
        var grade = $card.find('input[name="grade"]').val();
        var feedback = $card.find('textarea[name="feedback"]').val();
        var $message = $card.find('.grade-message');

        if (!grade) {
            $message
                .removeClass('success')
                .addClass('error')
                .html('<span class="dashicons dashicons-warning"></span> Veuillez entrer une note.')
                .fadeIn();
            return;
        }

        // Disable button
        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update dashicons-spin"></span> Enregistrement...');
        $message.hide();

        $.ajax({
            url: eiaAssignments.ajaxurl,
            type: 'POST',
            data: {
                action: 'eia_grade_submission',
                nonce: eiaAssignments.nonce,
                submission_id: submissionId,
                grade: grade,
                feedback: feedback
            },
            success: function(response) {
                if (response.success) {
                    $message
                        .removeClass('error')
                        .addClass('success')
                        .html('<span class="dashicons dashicons-yes-alt"></span> ' + response.data.message)
                        .fadeIn();

                    // Update status badge
                    var $statusBadge = $card.find('.submission-status');
                    var maxGrade = $btn.closest('.grading-form').find('.max-grade').text().replace('/ ', '');
                    $statusBadge.html(
                        '<div class="graded-badge">' +
                        '<span class="grade">' + grade + '/' + maxGrade + '</span>' +
                        '<span class="status-label">Noté</span>' +
                        '</div>'
                    );

                    // Re-enable button after 2 seconds
                    setTimeout(function() {
                        $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Enregistrer la note');
                    }, 2000);
                } else {
                    $message
                        .removeClass('success')
                        .addClass('error')
                        .html('<span class="dashicons dashicons-warning"></span> ' + response.data.message)
                        .fadeIn();

                    $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Enregistrer la note');
                }
            },
            error: function(xhr, status, error) {
                $message
                    .removeClass('success')
                    .addClass('error')
                    .html('<span class="dashicons dashicons-warning"></span> Erreur lors de l\'enregistrement.')
                    .fadeIn();

                $btn.prop('disabled', false).html('<span class="dashicons dashicons-yes"></span> Enregistrer la note');

                console.error('AJAX Error:', error);
            }
        });
    });

    // File input validation
    $('input[type="file"][name="submission_file"]').on('change', function() {
        var file = this.files[0];
        if (!file) return;

        var maxSize = $(this).closest('form').find('.file-info').text();
        var maxSizeMB = parseInt(maxSize.match(/\d+/)[0]);
        var maxSizeBytes = maxSizeMB * 1024 * 1024;

        if (file.size > maxSizeBytes) {
            alert('Le fichier est trop volumineux. Taille maximale: ' + maxSizeMB + ' MB');
            $(this).val('');
            return;
        }

        var allowedExtensions = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'zip'];
        var fileExtension = file.name.split('.').pop().toLowerCase();

        if (allowedExtensions.indexOf(fileExtension) === -1) {
            alert('Type de fichier non autorisé. Formats acceptés: ' + allowedExtensions.join(', ').toUpperCase());
            $(this).val('');
            return;
        }
    });

    // Grade input validation
    $('.grade-input').on('input', function() {
        var value = parseFloat($(this).val());
        var max = parseFloat($(this).attr('max'));
        var min = parseFloat($(this).attr('min'));

        if (value > max) {
            $(this).val(max);
        } else if (value < min) {
            $(this).val(min);
        }
    });

});
