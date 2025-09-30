/**
 * LMS Scripts for EIA Theme
 *
 * @package EIA_Theme
 */

(function($) {
    'use strict';

    /**
     * Document ready
     */
    $(document).ready(function() {
        // Initialize LMS features
        initEnrollButton();
        initWishlistButton();
        initLoadMoreCourses();
        initLessonCompletion();
        initQuizSubmission();
        initNotifications();
        initInstructorMessage();
        initCourseRating();
    });

    /**
     * Enroll in course
     */
    function initEnrollButton() {
        $(document).on('click', '.enroll-course-btn', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var courseId = $btn.data('course-id');

            if ($btn.hasClass('loading')) {
                return;
            }

            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_enroll_course',
                    course_id: courseId,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        if (response.data.redirect) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 1000);
                        }
                    } else {
                        showNotification(response.data.message, 'error');
                        $btn.removeClass('loading').prop('disabled', false);
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
    }

    /**
     * Add/Remove from wishlist
     */
    function initWishlistButton() {
        $(document).on('click', '.wishlist-btn', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var courseId = $btn.data('course-id');

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_add_to_wishlist',
                    course_id: courseId,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.toggleClass('in-wishlist', response.data.in_wishlist);
                        $btn.find('.wishlist-count').text(response.data.count);
                        showNotification(response.data.message, 'success');
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                }
            });
        });
    }

    /**
     * Load more courses (infinite scroll)
     */
    function initLoadMoreCourses() {
        var $loadMoreBtn = $('.load-more-courses');
        var page = 1;

        $loadMoreBtn.on('click', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var $container = $('.courses-container');
            var category = $btn.data('category') || '';
            var level = $btn.data('level') || '';

            page++;

            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_load_more_courses',
                    page: page,
                    category: category,
                    level: level,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $container.append(response.data.html);

                        if (!response.data.has_more) {
                            $btn.hide();
                        } else {
                            $btn.removeClass('loading').prop('disabled', false);
                        }
                    } else {
                        showNotification(response.data.message, 'error');
                        $btn.removeClass('loading').prop('disabled', false);
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
    }

    /**
     * Mark lesson as completed
     */
    function initLessonCompletion() {
        $(document).on('click', '.complete-lesson-btn', function(e) {
            e.preventDefault();

            var $btn = $(this);
            var lessonId = $btn.data('lesson-id');
            var courseId = $btn.data('course-id');

            if ($btn.hasClass('completed')) {
                return;
            }

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_complete_lesson',
                    lesson_id: lessonId,
                    course_id: courseId,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.addClass('completed');
                        $('.lesson-item[data-lesson-id="' + lessonId + '"]').addClass('completed');

                        // Update progress bar
                        if (response.data.progress) {
                            $('.course-progress-fill').css('width', response.data.progress + '%');
                        }

                        showNotification(response.data.message, 'success');
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                }
            });
        });
    }

    /**
     * Submit quiz
     */
    function initQuizSubmission() {
        $(document).on('submit', '.quiz-form', function(e) {
            e.preventDefault();

            var $form = $(this);
            var quizId = $form.data('quiz-id');
            var answers = {};

            // Collect answers
            $form.find('input[type="radio"]:checked, input[type="checkbox"]:checked').each(function() {
                var questionId = $(this).attr('name');
                if (!answers[questionId]) {
                    answers[questionId] = [];
                }
                answers[questionId].push($(this).val());
            });

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_submit_quiz',
                    quiz_id: quizId,
                    answers: answers,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        if (response.data.redirect) {
                            setTimeout(function() {
                                window.location.href = response.data.redirect;
                            }, 1500);
                        }
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                }
            });
        });

        // Quiz answer selection
        $(document).on('click', '.quiz-answer', function() {
            var $answer = $(this);
            var $question = $answer.closest('.quiz-question');
            var isMultiple = $question.data('type') === 'multiple';

            if (!isMultiple) {
                $question.find('.quiz-answer').removeClass('selected');
            }

            $answer.toggleClass('selected');
            $answer.find('input').prop('checked', $answer.hasClass('selected'));
        });
    }

    /**
     * Notifications
     */
    function initNotifications() {
        var $notifBtn = $('.notifications-toggle');
        var $dropdown = $('.notifications-dropdown');

        $notifBtn.on('click', function(e) {
            e.preventDefault();
            e.stopPropagation();

            $dropdown.toggleClass('hidden');

            if (!$dropdown.hasClass('hidden')) {
                loadNotifications();
            }
        });

        // Close dropdown when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.notifications-toggle, .notifications-dropdown').length) {
                $dropdown.addClass('hidden');
            }
        });

        // Mark notification as read
        $(document).on('click', '.notification-item', function() {
            var $item = $(this);
            var notificationId = $item.data('notification-id');

            if ($item.hasClass('unread')) {
                $.ajax({
                    url: eiaLMS.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'eia_mark_notification_read',
                        notification_id: notificationId,
                        nonce: eiaLMS.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $item.removeClass('unread');
                            updateNotificationCount();
                        }
                    }
                });
            }
        });
    }

    /**
     * Load notifications
     */
    function loadNotifications() {
        $.ajax({
            url: eiaLMS.ajaxurl,
            type: 'POST',
            data: {
                action: 'eia_get_notifications',
                nonce: eiaLMS.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update notifications list
                    updateNotificationCount(response.data.count);
                }
            }
        });
    }

    /**
     * Update notification count
     */
    function updateNotificationCount(count) {
        var $badge = $('.notification-badge');
        if (count > 0) {
            $badge.text(count).show();
        } else {
            $badge.hide();
        }
    }

    /**
     * Send message to instructor
     */
    function initInstructorMessage() {
        $(document).on('submit', '.instructor-message-form', function(e) {
            e.preventDefault();

            var $form = $(this);
            var instructorId = $form.data('instructor-id');
            var subject = $form.find('[name="subject"]').val();
            var message = $form.find('[name="message"]').val();

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_send_instructor_message',
                    instructor_id: instructorId,
                    subject: subject,
                    message: message,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        $form[0].reset();
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                }
            });
        });
    }

    /**
     * Rate course
     */
    function initCourseRating() {
        // Star rating interaction
        $(document).on('click', '.rating-star', function() {
            var $star = $(this);
            var rating = $star.data('rating');
            var $container = $star.closest('.rating-stars');

            $container.find('.rating-star').removeClass('active');
            $container.find('.rating-star').each(function() {
                if ($(this).data('rating') <= rating) {
                    $(this).addClass('active');
                }
            });

            $container.data('selected-rating', rating);
        });

        // Submit rating
        $(document).on('submit', '.course-rating-form', function(e) {
            e.preventDefault();

            var $form = $(this);
            var courseId = $form.data('course-id');
            var rating = $form.find('.rating-stars').data('selected-rating');
            var review = $form.find('[name="review"]').val();

            if (!rating) {
                showNotification('Veuillez sélectionner une note', 'error');
                return;
            }

            $.ajax({
                url: eiaLMS.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_rate_course',
                    course_id: courseId,
                    rating: rating,
                    review: review,
                    nonce: eiaLMS.nonce
                },
                success: function(response) {
                    if (response.success) {
                        showNotification(response.data.message, 'success');
                        $form[0].reset();
                        $form.find('.rating-star').removeClass('active');

                        // Update course rating display
                        if (response.data.rating) {
                            $('.course-rating-value').text(response.data.rating);
                        }
                    } else {
                        showNotification(response.data.message, 'error');
                    }
                },
                error: function() {
                    showNotification(eiaLMS.strings.error, 'error');
                }
            });
        });
    }

    /**
     * Show notification
     */
    function showNotification(message, type) {
        var $notification = $('<div class="eia-notification ' + type + '">' + message + '</div>');

        $('body').append($notification);

        setTimeout(function() {
            $notification.addClass('show');
        }, 100);

        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }

    /**
     * Progress bar animation
     */
    $('.course-progress-fill').each(function() {
        var $bar = $(this);
        var targetWidth = $bar.data('progress') + '%';

        setTimeout(function() {
            $bar.css('width', targetWidth);
        }, 500);
    });

})(jQuery);