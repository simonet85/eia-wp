/**
 * EIA LMS Core - Frontend JavaScript
 *
 * @package EIA_LMS_Core
 */

(function($) {
    'use strict';

    /**
     * Course Interaction Module
     */
    const CourseInteraction = {
        init: function() {
            this.initProgressTracking();
            this.initLessonNavigation();
            this.initQuizTimer();
        },

        initProgressTracking: function() {
            // Track when user views a lesson
            if ($('.lp-lesson-content').length) {
                const lessonId = $('.lp-lesson-content').data('lesson-id');
                const courseId = $('.lp-lesson-content').data('course-id');

                this.trackLessonView(lessonId, courseId);
            }
        },

        trackLessonView: function(lessonId, courseId) {
            $.post(eiaLMSCore.ajaxurl, {
                action: 'eia_track_lesson_view',
                nonce: eiaLMSCore.nonce,
                lesson_id: lessonId,
                course_id: courseId
            });
        },

        initLessonNavigation: function() {
            $('.eia-lesson-nav-btn').on('click', function(e) {
                e.preventDefault();

                const nextUrl = $(this).data('next-url');
                if (nextUrl) {
                    window.location.href = nextUrl;
                }
            });
        },

        initQuizTimer: function() {
            const $timer = $('.eia-quiz-timer');

            if ($timer.length) {
                const timeLimit = $timer.data('time-limit'); // in seconds
                let timeRemaining = timeLimit;

                const interval = setInterval(function() {
                    timeRemaining--;

                    const minutes = Math.floor(timeRemaining / 60);
                    const seconds = timeRemaining % 60;

                    $timer.text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);

                    if (timeRemaining <= 0) {
                        clearInterval(interval);
                        CourseInteraction.submitQuizAutomatically();
                    }
                }, 1000);
            }
        },

        submitQuizAutomatically: function() {
            alert('Le temps est écoulé. Le quiz sera soumis automatiquement.');
            $('.lp-quiz-form').submit();
        }
    };

    /**
     * Quiz Extended Module
     */
    const QuizExtended = {
        init: function() {
            this.initEssaySubmit();
            this.initMatchingQuestions();
            this.initOrderingQuestions();
            this.initHints();
        },

        initEssaySubmit: function() {
            $(document).on('submit', '.eia-essay-form', function(e) {
                e.preventDefault();

                const $form = $(this);
                const questionId = $form.data('question-id');
                const answer = $form.find('textarea').val();

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_validate_essay',
                    nonce: eiaLMSCore.nonce,
                    question_id: questionId,
                    answer: answer
                }, function(response) {
                    if (response.success) {
                        QuizExtended.showNotice('success', response.data.message);
                        $form.find('textarea').prop('disabled', true);
                        $form.find('button[type="submit"]').prop('disabled', true);
                    } else {
                        QuizExtended.showNotice('error', response.data.message);
                    }
                });
            });
        },

        initMatchingQuestions: function() {
            // TODO: Implement drag & drop for matching questions
        },

        initOrderingQuestions: function() {
            $('.eia-ordering-list').sortable({
                placeholder: 'ordering-placeholder',
                update: function(event, ui) {
                    QuizExtended.updateOrderingInput($(this));
                }
            });
        },

        updateOrderingInput: function($list) {
            const order = [];
            $list.find('li').each(function() {
                order.push($(this).data('item-id'));
            });
            $list.siblings('input[type="hidden"]').val(order.join(','));
        },

        initHints: function() {
            $(document).on('click', '.eia-hint-toggle', function() {
                $(this).next('.eia-hint-content').slideToggle();
            });
        },

        showNotice: function(type, message) {
            const $notice = $('<div class="eia-notice eia-notice-' + type + '">' + message + '</div>');
            $('.lp-quiz-content').prepend($notice);

            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    /**
     * Certificate Module
     */
    const Certificate = {
        init: function() {
            this.initDownload();
            this.initShare();
        },

        initDownload: function() {
            $('.eia-download-certificate').on('click', function(e) {
                e.preventDefault();

                const certificateId = $(this).data('certificate-id');
                window.open(eiaLMSCore.ajaxurl + '?action=eia_download_certificate&id=' + certificateId, '_blank');
            });
        },

        initShare: function() {
            $('.eia-share-certificate').on('click', function(e) {
                e.preventDefault();

                const certificateUrl = $(this).data('certificate-url');
                const shareText = $(this).data('share-text');

                if (navigator.share) {
                    navigator.share({
                        title: shareText,
                        url: certificateUrl
                    });
                } else {
                    // Fallback: copy to clipboard
                    Certificate.copyToClipboard(certificateUrl);
                    alert('Lien copié dans le presse-papiers!');
                }
            });
        },

        copyToClipboard: function(text) {
            const $temp = $('<input>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();
        }
    };

    /**
     * Notification Module
     */
    const Notification = {
        init: function() {
            this.initMarkAsRead();
            this.initLoadMore();
        },

        initMarkAsRead: function() {
            $(document).on('click', '.eia-notification-item', function() {
                const notificationId = $(this).data('notification-id');

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_mark_notification_read',
                    nonce: eiaLMSCore.nonce,
                    notification_id: notificationId
                }, function(response) {
                    if (response.success) {
                        $('.eia-notification-item[data-notification-id="' + notificationId + '"]')
                            .removeClass('unread');

                        // Update notification count
                        Notification.updateCount();
                    }
                });
            });
        },

        initLoadMore: function() {
            $('.eia-load-more-notifications').on('click', function() {
                const page = $(this).data('page') || 1;
                const nextPage = page + 1;

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_load_notifications',
                    nonce: eiaLMSCore.nonce,
                    page: nextPage
                }, function(response) {
                    if (response.success) {
                        $('.eia-notifications-list').append(response.data.html);
                        $('.eia-load-more-notifications').data('page', nextPage);

                        if (!response.data.has_more) {
                            $('.eia-load-more-notifications').hide();
                        }
                    }
                });
            });
        },

        updateCount: function() {
            $.post(eiaLMSCore.ajaxurl, {
                action: 'eia_get_notification_count',
                nonce: eiaLMSCore.nonce
            }, function(response) {
                if (response.success) {
                    $('.eia-notification-count').text(response.data.count);

                    if (response.data.count === 0) {
                        $('.eia-notification-count').hide();
                    }
                }
            });
        }
    };

    /**
     * Wishlist Module
     */
    const Wishlist = {
        init: function() {
            this.initToggle();
        },

        initToggle: function() {
            $(document).on('click', '.eia-wishlist-toggle', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const courseId = $btn.data('course-id');

                $.post(eiaLMSCore.ajaxurl, {
                    action: 'eia_toggle_wishlist',
                    nonce: eiaLMSCore.nonce,
                    course_id: courseId
                }, function(response) {
                    if (response.success) {
                        if (response.data.added) {
                            $btn.addClass('is-favorited');
                            $btn.find('i').removeClass('dashicons-heart').addClass('dashicons-heart');
                        } else {
                            $btn.removeClass('is-favorited');
                            $btn.find('i').removeClass('dashicons-heart').addClass('dashicons-heart-o');
                        }
                    }
                });
            });
        }
    };

    /**
     * Course Filter Module
     */
    const CourseFilter = {
        init: function() {
            this.initFilters();
            this.initSearch();
        },

        initFilters: function() {
            $(document).on('change', '.eia-course-filter', function() {
                CourseFilter.applyFilters();
            });
        },

        initSearch: function() {
            let searchTimeout;

            $('.eia-course-search').on('keyup', function() {
                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(function() {
                    CourseFilter.applyFilters();
                }, 500);
            });
        },

        applyFilters: function() {
            const filters = {
                search: $('.eia-course-search').val(),
                category: $('.eia-filter-category').val(),
                level: $('.eia-filter-level').val(),
                price: $('.eia-filter-price').val()
            };

            $.post(eiaLMSCore.ajaxurl, {
                action: 'eia_filter_courses',
                nonce: eiaLMSCore.nonce,
                filters: filters
            }, function(response) {
                if (response.success) {
                    $('.eia-courses-grid').html(response.data.html);
                }
            });
        }
    };

    /**
     * Initialize on document ready
     */
    $(document).ready(function() {
        CourseInteraction.init();
        QuizExtended.init();
        Certificate.init();
        Notification.init();
        Wishlist.init();
        CourseFilter.init();
    });

})(jQuery);