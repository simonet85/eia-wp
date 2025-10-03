/**
 * EIA LMS Core - Admin JavaScript
 *
 * @package EIA_LMS_Core
 */

(function($) {
    'use strict';

    /**
     * Course Builder Module
     */
    const CourseBuilder = {
        init: function() {
            this.initSortable();
            this.initTabs();
            this.initAddLesson();
            this.initRemoveLesson();
            this.initSearchLesson();
            this.initAddSection();
        },

        initSortable: function() {
            // Initialize sortable for sections
            $('.course-sections').sortable({
                handle: '.section-handle',
                placeholder: 'section-placeholder',
                update: function(event, ui) {
                    CourseBuilder.saveOrder();
                }
            });

            // Initialize sortable for section items
            $('.section-items').sortable({
                handle: '.item-handle',
                connectWith: '.section-items',
                placeholder: 'item-placeholder',
                update: function(event, ui) {
                    CourseBuilder.saveOrder();
                }
            });
        },

        initTabs: function() {
            $('.builder-tab').on('click', function() {
                const tab = $(this).data('tab');

                $('.builder-tab').removeClass('active');
                $(this).addClass('active');

                $('.builder-tab-content').removeClass('active');
                $('[data-tab-content="' + tab + '"]').addClass('active');
            });
        },

        initAddLesson: function() {
            $(document).on('click', '.add-item-btn', function(e) {
                e.preventDefault();

                const $item = $(this).closest('.available-item');
                const itemId = $item.data('item-id');
                const itemType = $item.data('item-type');
                const courseId = $('#post_ID').val();

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_add_lesson_to_course',
                    nonce: eiaLMSCore.nonce,
                    course_id: courseId,
                    item_id: itemId,
                    item_type: itemType
                }, function(response) {
                    if (response.success) {
                        CourseBuilder.showNotice('success', response.data.message);
                        location.reload();
                    } else {
                        CourseBuilder.showNotice('error', response.data.message);
                    }
                });
            });
        },

        initRemoveLesson: function() {
            $(document).on('click', '.remove-item-btn', function(e) {
                e.preventDefault();

                if (!confirm(eiaLMSCore.strings.confirm_delete)) {
                    return;
                }

                const $item = $(this).closest('.section-item');
                const itemId = $item.data('item-id');
                const courseId = $('#post_ID').val();

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_remove_lesson_from_course',
                    nonce: eiaLMSCore.nonce,
                    course_id: courseId,
                    item_id: itemId
                }, function(response) {
                    if (response.success) {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                        });
                        CourseBuilder.showNotice('success', response.data.message);
                    } else {
                        CourseBuilder.showNotice('error', response.data.message);
                    }
                });
            });
        },

        initSearchLesson: function() {
            $('.search-lessons').on('keyup', function() {
                const searchTerm = $(this).val().toLowerCase();

                $('.lessons-list .available-item').each(function() {
                    const title = $(this).find('.item-title').text().toLowerCase();

                    if (title.indexOf(searchTerm) === -1) {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            });
        },

        initAddSection: function() {
            $('.add-section-btn').on('click', function() {
                const sectionTitle = prompt('Titre de la section:');

                if (!sectionTitle) {
                    return;
                }

                // TODO: Implement add section functionality
                CourseBuilder.showNotice('info', 'Fonctionnalité en cours de développement');
            });
        },

        saveOrder: function() {
            const courseId = $('#post_ID').val();
            const order = [];

            $('.course-sections .course-section').each(function(sectionIndex) {
                const sectionId = $(this).data('section-id');
                const items = [];

                $(this).find('.section-items .section-item').each(function(itemIndex) {
                    items.push({
                        id: $(this).data('item-id'),
                        type: $(this).data('item-type'),
                        order: itemIndex
                    });
                });

                order.push({
                    section_id: sectionId,
                    order: sectionIndex,
                    items: items
                });
            });

            $.post(eiaLMSCore.ajaxurl, {
                action: 'eia_reorder_course_items',
                nonce: eiaLMSCore.nonce,
                course_id: courseId,
                order: order
            }, function(response) {
                if (response.success) {
                    CourseBuilder.showNotice('success', eiaLMSCore.strings.saved);
                }
            });
        },

        showNotice: function(type, message) {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    /**
     * Gradebook Module
     */
    const Gradebook = {
        init: function() {
            this.initManualGrade();
            this.initExport();
            this.initViewDetails();
        },

        initManualGrade: function() {
            $(document).on('change', '.manual-grade-input', function() {
                const $input = $(this);
                const userId = $input.data('user-id');
                const courseId = $input.data('course-id');
                const grade = $input.val();

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_save_manual_grade',
                    nonce: eiaLMSCore.nonce,
                    user_id: userId,
                    course_id: courseId,
                    grade: grade
                }, function(response) {
                    if (response.success) {
                        const $row = $input.closest('tr');
                        $row.find('.final-grade').text(response.data.final_grade + '%');
                        $row.find('.status-badge')
                            .removeClass('status-completed status-in-progress status-failed status-pending')
                            .addClass('status-' + response.data.status);

                        Gradebook.showNotice('success', response.data.message);
                    } else {
                        Gradebook.showNotice('error', response.data.message);
                    }
                });
            });
        },

        initExport: function() {
            $(document).on('click', '.export-gradebook', function() {
                const courseId = $(this).data('course-id');

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_export_gradebook',
                    nonce: eiaLMSCore.nonce,
                    course_id: courseId
                }, function(response) {
                    if (response.success) {
                        Gradebook.downloadCSV(response.data.csv, response.data.filename);
                    }
                });
            });
        },

        initViewDetails: function() {
            $(document).on('click', '.view-details', function() {
                const userId = $(this).data('user-id');
                const courseId = $(this).data('course-id');

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_get_student_grades',
                    nonce: eiaLMSCore.nonce,
                    user_id: userId,
                    course_id: courseId
                }, function(response) {
                    if (response.success) {
                        Gradebook.showDetailsModal(response.data);
                    }
                });
            });
        },

        downloadCSV: function(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        },

        showDetailsModal: function(data) {
            // TODO: Implement modal for detailed grades
            console.log('Student grades:', data);
        },

        showNotice: function(type, message) {
            const $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    /**
     * Quiz Extended Module
     */
    const QuizExtended = {
        init: function() {
            this.initEssayGrading();
        },

        initEssayGrading: function() {
            $(document).on('click', '.grade-essay-btn', function() {
                const questionId = $(this).data('question-id');
                const userId = $(this).data('user-id');

                // Show grading modal
                QuizExtended.showGradingModal(questionId, userId);
            });

            $(document).on('click', '.save-essay-grade', function() {
                const questionId = $('#essay-question-id').val();
                const userId = $('#essay-user-id').val();
                const grade = $('#essay-grade').val();
                const feedback = $('#essay-feedback').val();

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_save_essay_grade',
                    nonce: eiaLMSCore.nonce,
                    question_id: questionId,
                    user_id: userId,
                    grade: grade,
                    feedback: feedback
                }, function(response) {
                    if (response.success) {
                        QuizExtended.closeGradingModal();
                        location.reload();
                    }
                });
            });
        },

        showGradingModal: function(questionId, userId) {
            // TODO: Implement grading modal
            console.log('Show grading modal for question:', questionId, 'user:', userId);
        },

        closeGradingModal: function() {
            // TODO: Implement modal close
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        // Initialize modules based on page
        if ($('.eia-course-builder').length) {
            CourseBuilder.init();
        }

        if ($('.eia-gradebook-wrapper').length) {
            Gradebook.init();
        }

        if ($('.eia-quiz-extended-options').length) {
            QuizExtended.init();
        }

        // Initialize WordPress color picker
        if ($.fn.wpColorPicker) {
            $('.color-picker').wpColorPicker();
        }
    });

})(jQuery);