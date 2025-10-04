/**
 * EIA Lesson Video Player JavaScript
 * Handles tabs, video tracking, and sidebar interactions
 */

(function($) {
    'use strict';

    const EIA_LessonVideo = {
        video: null,
        lessonId: null,
        courseId: null,
        startTime: null,
        watchedDuration: 0,

        init: function() {
            this.lessonId = $('.eia-lesson-wrapper').data('lesson-id');
            this.courseId = $('.eia-lesson-wrapper').data('course-id');
            this.video = document.getElementById('eia-lesson-video');

            this.bindTabs();
            this.bindVideoEvents();
            this.bindSidebarNavigation();
        },

        /**
         * Tabs Navigation
         */
        bindTabs: function() {
            $('.eia-tab-btn').on('click', function() {
                const tabId = $(this).data('tab');

                // Update active tab button
                $('.eia-tab-btn').removeClass('active');
                $(this).addClass('active');

                // Show corresponding content
                $('.eia-tab-pane').removeClass('active');
                $('#tab-' + tabId).addClass('active');
            });
        },

        /**
         * Video Events Tracking
         */
        bindVideoEvents: function() {
            if (!this.video) return;

            const self = this;

            // Track play
            this.video.addEventListener('play', function() {
                self.startTime = Date.now();
                console.log('Video started');
            });

            // Track pause
            this.video.addEventListener('pause', function() {
                self.updateWatchedTime();
            });

            // Track progress every 10 seconds
            this.video.addEventListener('timeupdate', function() {
                const progress = (this.currentTime / this.duration) * 100;

                // Update progress if >= 90% watched
                if (progress >= 90 && !self.video.dataset.completed) {
                    self.markLessonComplete();
                    self.video.dataset.completed = 'true';
                }
            });

            // Track video end
            this.video.addEventListener('ended', function() {
                self.updateWatchedTime();
                self.markLessonComplete();
            });

            // Overlay toggle
            $('.eia-video-overlay').on('click', function() {
                self.video.play();
            });
        },

        /**
         * Update watched time
         */
        updateWatchedTime: function() {
            if (this.startTime) {
                const duration = (Date.now() - this.startTime) / 1000;
                this.watchedDuration += duration;
                this.startTime = null;

                // Send to server every 30 seconds of watch time
                if (this.watchedDuration >= 30) {
                    this.saveProgress();
                    this.watchedDuration = 0;
                }
            }
        },

        /**
         * Save video progress
         */
        saveProgress: function() {
            if (!this.lessonId) return;

            $.ajax({
                url: eiaLesson.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_update_lesson_progress',
                    nonce: eiaLesson.nonce,
                    lesson_id: this.lessonId,
                    course_id: this.courseId,
                    watched_duration: this.watchedDuration,
                    current_time: this.video ? this.video.currentTime : 0
                },
                success: function(response) {
                    console.log('Progress saved', response);
                }
            });
        },

        /**
         * Mark lesson as complete
         */
        markLessonComplete: function() {
            if (!this.lessonId) return;

            $.ajax({
                url: eiaLesson.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_complete_lesson',
                    nonce: eiaLesson.nonce,
                    lesson_id: this.lessonId,
                    course_id: this.courseId
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI
                        $('.eia-lesson-item[data-lesson-id="' + response.data.lesson_id + '"]')
                            .addClass('completed')
                            .append('<span class="eia-badge-complete"><i class="fas fa-check"></i> Terminé</span>');

                        // Update progress bar
                        if (response.data.course_progress) {
                            $('.eia-progress-fill').css('width', response.data.course_progress + '%');
                            $('.eia-progress-percent').text(response.data.course_progress + '% terminé');
                        }

                        // Show notification
                        EIA_LessonVideo.showNotification('Leçon complétée!', 'success');
                    }
                }
            });
        },

        /**
         * Sidebar Navigation
         */
        bindSidebarNavigation: function() {
            // Toggle section
            $('.eia-section-header').on('click', function() {
                $(this).closest('.eia-course-section').toggleClass('collapsed');
                $(this).find('.eia-section-items').slideToggle(200);
            });

            // Navigate to lesson
            $('.eia-lesson-item').on('click', function(e) {
                if ($(e.target).hasClass('eia-badge-complete')) return;

                const lessonUrl = $(this).data('lesson-url');
                if (lessonUrl) {
                    window.location.href = lessonUrl;
                }
            });
        },

        /**
         * Show notification
         */
        showNotification: function(message, type) {
            const notification = $('<div class="eia-notification ' + type + '">' + message + '</div>');
            $('body').append(notification);

            setTimeout(function() {
                notification.addClass('show');
            }, 100);

            setTimeout(function() {
                notification.removeClass('show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.eia-lesson-wrapper').length) {
            EIA_LessonVideo.init();
        }
    });

    // Save progress before leaving page
    $(window).on('beforeunload', function() {
        if (EIA_LessonVideo.video && !EIA_LessonVideo.video.paused) {
            EIA_LessonVideo.updateWatchedTime();
            EIA_LessonVideo.saveProgress();
        }
    });

})(jQuery);
