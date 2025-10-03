/**
 * EIA Forum JavaScript
 */

(function($) {
    'use strict';

    const EIAForum = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Show new topic form
            $(document).on('click', '.btn-new-topic', this.showNewTopicForm);

            // Submit new topic
            $(document).on('submit', '#new-topic-form', this.submitNewTopic);

            // Submit reply
            $(document).on('submit', '#new-reply-form', this.submitReply);

            // Vote
            $(document).on('click', '.vote-btn', this.handleVote);

            // Mark best answer
            $(document).on('click', '.btn-best-answer', this.markBestAnswer);

            // Resolve topic
            $(document).on('click', '.btn-resolve', this.resolveTopic);

            // Search
            $(document).on('input', '#forum-search', this.debounce(this.searchForum, 500));

            // Cancel forms
            $(document).on('click', '.btn-cancel', this.hideForm);

            // Show reply form
            $(document).on('click', '.btn-reply', this.showReplyForm);
        },

        showNewTopicForm: function(e) {
            e.preventDefault();
            $('#new-topic-modal').fadeIn(200);
        },

        hideForm: function(e) {
            e.preventDefault();
            $('.forum-modal').fadeOut(200);
            $('form').trigger('reset');
        },

        submitNewTopic: function(e) {
            e.preventDefault();

            const $form = $(this);
            const $button = $form.find('.btn-submit');
            const originalText = $button.text();

            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Publication...');

            $.ajax({
                url: eiaForum.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_create_topic',
                    nonce: eiaForum.nonce,
                    course_id: $('#topic-course-id').val(),
                    title: $('#topic-title').val(),
                    content: $('#topic-content').val()
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        EIAForum.showNotification('Question publiée avec succès!', 'success');

                        // Reload page to show new topic
                        setTimeout(function() {
                            window.location.reload();
                        }, 1000);
                    } else {
                        EIAForum.showNotification(response.data.message || 'Erreur lors de la publication', 'error');
                        $button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    EIAForum.showNotification('Erreur réseau', 'error');
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        submitReply: function(e) {
            e.preventDefault();

            const $form = $(this);
            const $button = $form.find('.btn-submit');
            const originalText = $button.text();

            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Publication...');

            $.ajax({
                url: eiaForum.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_create_reply',
                    nonce: eiaForum.nonce,
                    topic_id: $('#reply-topic-id').val(),
                    content: $('#reply-content').val()
                },
                success: function(response) {
                    if (response.success) {
                        EIAForum.showNotification('Réponse publiée avec succès!', 'success');

                        // Add reply to list
                        const reply = response.data.reply;
                        EIAForum.addReplyToList(reply);

                        // Reset form
                        $form.trigger('reset');
                        $button.prop('disabled', false).text(originalText);

                        // Scroll to new reply
                        $('html, body').animate({
                            scrollTop: $('.reply-item:last').offset().top - 100
                        }, 500);
                    } else {
                        EIAForum.showNotification(response.data.message || 'Erreur lors de la publication', 'error');
                        $button.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    EIAForum.showNotification('Erreur réseau', 'error');
                    $button.prop('disabled', false).text(originalText);
                }
            });
        },

        handleVote: function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const entityType = $btn.data('entity-type');
            const entityId = $btn.data('entity-id');
            const voteType = $btn.hasClass('upvote') ? 1 : -1;

            $.ajax({
                url: eiaForum.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_vote',
                    nonce: eiaForum.nonce,
                    entity_type: entityType,
                    entity_id: entityId,
                    vote_type: voteType
                },
                success: function(response) {
                    if (response.success) {
                        // Update score
                        const $container = $btn.closest('.topic-votes, .reply-votes');
                        const $score = $container.find('.vote-score');
                        $score.text(response.data.score || 0);

                        // Toggle active states
                        if (response.data.action === 'removed') {
                            $btn.removeClass('active');
                        } else if (response.data.action === 'added') {
                            $btn.addClass('active');
                        } else if (response.data.action === 'updated') {
                            $container.find('.vote-btn').removeClass('active');
                            $btn.addClass('active');
                        }
                    } else {
                        EIAForum.showNotification(response.data.message || 'Erreur lors du vote', 'error');
                    }
                },
                error: function() {
                    EIAForum.showNotification('Erreur réseau', 'error');
                }
            });
        },

        markBestAnswer: function(e) {
            e.preventDefault();

            const $btn = $(this);
            const replyId = $btn.data('reply-id');

            if (!confirm('Marquer cette réponse comme la meilleure?')) {
                return;
            }

            $.ajax({
                url: eiaForum.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_mark_best_answer',
                    nonce: eiaForum.nonce,
                    reply_id: replyId
                },
                success: function(response) {
                    if (response.success) {
                        EIAForum.showNotification('Meilleure réponse marquée!', 'success');

                        // Update UI
                        $('.reply-item').removeClass('best-answer');
                        $('.reply-item .badge.best-answer').remove();
                        $btn.closest('.reply-item').addClass('best-answer');
                        $btn.closest('.reply-item').find('.reply-header').append(
                            '<span class="badge best-answer"><i class="fas fa-check"></i> Meilleure réponse</span>'
                        );

                        // Remove all best answer buttons
                        $('.btn-best-answer').remove();
                    } else {
                        EIAForum.showNotification(response.data.message || 'Erreur', 'error');
                    }
                },
                error: function() {
                    EIAForum.showNotification('Erreur réseau', 'error');
                }
            });
        },

        resolveTopic: function(e) {
            e.preventDefault();

            const $btn = $(this);
            const topicId = $btn.data('topic-id');

            $.ajax({
                url: eiaForum.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_resolve_topic',
                    nonce: eiaForum.nonce,
                    topic_id: topicId
                },
                success: function(response) {
                    if (response.success) {
                        const isResolved = response.data.is_resolved;
                        EIAForum.showNotification(response.data.message, 'success');

                        // Update button and badge
                        if (isResolved) {
                            $btn.html('<i class="fas fa-redo"></i> Rouvrir');
                            if ($('.badge.resolved').length === 0) {
                                $('.topic-badges').prepend('<span class="badge resolved"><i class="fas fa-check"></i> Résolu</span>');
                            }
                        } else {
                            $btn.html('<i class="fas fa-check"></i> Marquer résolu');
                            $('.badge.resolved').remove();
                        }
                    } else {
                        EIAForum.showNotification(response.data.message || 'Erreur', 'error');
                    }
                },
                error: function() {
                    EIAForum.showNotification('Erreur réseau', 'error');
                }
            });
        },

        searchForum: function() {
            const search = $(this).val();
            const courseId = $('#forum-course-id').val();

            if (search.length < 2 && search.length > 0) {
                return;
            }

            $.ajax({
                url: eiaForum.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_search_forum',
                    nonce: eiaForum.nonce,
                    course_id: courseId,
                    search: search
                },
                success: function(response) {
                    if (response.success) {
                        EIAForum.updateTopicsList(response.data.topics);
                    }
                }
            });
        },

        updateTopicsList: function(topics) {
            const $list = $('.topics-list');

            if (topics.length === 0) {
                $list.html('<div class="empty-state"><i class="fas fa-search"></i><h3>Aucun résultat</h3><p>Essayez d\'autres mots-clés</p></div>');
                return;
            }

            let html = '';
            topics.forEach(function(topic) {
                html += EIAForum.renderTopicItem(topic);
            });

            $list.html(html);
        },

        renderTopicItem: function(topic) {
            const resolvedBadge = topic.is_resolved == 1 ? '<span class="badge resolved"><i class="fas fa-check"></i> Résolu</span>' : '';
            const excerpt = topic.content.substring(0, 150) + (topic.content.length > 150 ? '...' : '');
            const voteScore = topic.vote_score || 0;

            return `
                <div class="topic-item" onclick="window.location.href='?topic_id=${topic.id}'">
                    <div class="topic-header">
                        <div class="topic-votes">
                            <button class="vote-btn upvote" data-entity-type="topic" data-entity-id="${topic.id}">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                            <span class="vote-score">${voteScore}</span>
                            <button class="vote-btn downvote" data-entity-type="topic" data-entity-id="${topic.id}">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="topic-content">
                            <h3 class="topic-title">
                                ${topic.title}
                                <span class="topic-badges">${resolvedBadge}</span>
                            </h3>
                            <p class="topic-excerpt">${excerpt}</p>
                            <div class="topic-meta">
                                <span class="topic-meta-item">
                                    <i class="fas fa-user"></i> ${topic.author_name}
                                </span>
                                <span class="topic-meta-item">
                                    <i class="far fa-comment"></i> ${topic.reply_count} réponses
                                </span>
                                <span class="topic-meta-item">
                                    <i class="far fa-eye"></i> ${topic.views} vues
                                </span>
                                <span class="topic-meta-item">
                                    <i class="far fa-clock"></i> ${EIAForum.timeAgo(topic.created_at)}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },

        addReplyToList: function(reply) {
            const html = `
                <div class="reply-item">
                    <div class="reply-header">
                        <div class="reply-votes">
                            <button class="vote-btn upvote" data-entity-type="reply" data-entity-id="${reply.id}">
                                <i class="fas fa-chevron-up"></i>
                            </button>
                            <span class="vote-score">0</span>
                            <button class="vote-btn downvote" data-entity-type="reply" data-entity-id="${reply.id}">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div class="reply-content">
                            <div class="author-details">
                                <strong class="author-name">${reply.author_name}</strong> •
                                <span class="topic-date">À l'instant</span>
                            </div>
                            <div class="reply-text">${reply.content}</div>
                        </div>
                    </div>
                </div>
            `;

            $('.replies-list').append(html);

            // Update reply count
            const currentCount = parseInt($('.replies-header').text().match(/\d+/)[0] || 0);
            $('.replies-header').html(`<i class="fas fa-comments"></i> ${currentCount + 1} Réponses`);
        },

        showReplyForm: function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('#new-reply-form').offset().top - 100
            }, 500);
            $('#reply-content').focus();
        },

        showNotification: function(message, type) {
            const bgColor = type === 'success' ? '#10B981' : '#EF4444';
            const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';

            const $notification = $(`
                <div style="
                    position: fixed;
                    top: 100px;
                    right: 20px;
                    background: ${bgColor};
                    color: white;
                    padding: 1rem 1.5rem;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                    max-width: 400px;
                    animation: slideIn 0.3s ease-out;
                ">
                    <i class="fas fa-${icon}"></i>
                    <span>${message}</span>
                </div>
            `);

            $('body').append($notification);

            setTimeout(function() {
                $notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        },

        timeAgo: function(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return 'À l\'instant';
            if (seconds < 3600) return Math.floor(seconds / 60) + ' min';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h';
            if (seconds < 2592000) return Math.floor(seconds / 86400) + 'j';
            return Math.floor(seconds / 2592000) + ' mois';
        },

        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func.apply(this, args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        EIAForum.init();
    });

})(jQuery);
