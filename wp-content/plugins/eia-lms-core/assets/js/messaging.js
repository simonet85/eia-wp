/**
 * EIA Messaging JavaScript
 * Interface de chat interactive avec polling AJAX
 */

(function($) {
    'use strict';

    const EIA_Messaging = {
        currentThreadId: null,
        pollingInterval: null,
        attachments: [],

        init: function() {
            this.bindEvents();
            this.loadConversations();
            this.startPolling();
        },

        bindEvents: function() {
            const self = this;

            // New message button
            $(document).on('click', '#eia-new-message-btn', function(e) {
                e.preventDefault();
                self.openModal('#eia-new-message-modal');
            });

            // Close modal
            $(document).on('click', '.eia-modal-close', function() {
                self.closeModal($(this).closest('.eia-modal'));
            });

            // Close modal on background click
            $('.eia-modal').on('click', function(e) {
                if (e.target === this) {
                    self.closeModal($(this));
                }
            });

            // Send new message
            $(document).on('click', '#eia-send-new-message-btn', function(e) {
                e.preventDefault();
                self.sendNewMessage();
            });

            // Select conversation
            $(document).on('click', '.eia-conversation-item', function() {
                const threadId = $(this).data('thread-id');
                self.loadMessages(threadId);
            });

            // Send message
            $(document).on('click', '#eia-send-message-btn', function(e) {
                e.preventDefault();
                self.sendMessage();
            });

            // Enter to send
            $(document).on('keypress', '#eia-message-input', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            });

            // Attach file
            $(document).on('click', '#eia-attach-file-btn', function(e) {
                e.preventDefault();
                $('#eia-file-input').click();
            });

            $(document).on('change', '#eia-file-input', function() {
                self.handleFileUpload(this.files[0]);
            });

            // Remove attachment
            $(document).on('click', '.eia-attachment-remove', function() {
                const index = $(this).data('index');
                self.removeAttachment(index);
            });

            // Search conversations
            $(document).on('keyup', '#eia-search-conversations', function() {
                const query = $(this).val().toLowerCase();
                $('.eia-conversation-item').each(function() {
                    const name = $(this).find('.eia-conversation-name').text().toLowerCase();
                    const excerpt = $(this).find('.eia-conversation-excerpt').text().toLowerCase();
                    if (name.includes(query) || excerpt.includes(query)) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        },

        loadConversations: function(silent) {
            const self = this;
            silent = silent || false;

            $.ajax({
                url: eiaMessaging.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_get_conversations',
                    nonce: eiaMessaging.nonce
                },
                beforeSend: function() {
                    // Only show loading on initial load, not on refresh
                    if (!silent) {
                        $('#eia-conversations-list').html('<div class="eia-loading"><i class="fas fa-circle-notch fa-spin"></i> Chargement...</div>');
                    }
                },
                success: function(response) {
                    if (response.success) {
                        self.renderConversations(response.data.conversations);
                    }
                },
                error: function() {
                    if (!silent) {
                        $('#eia-conversations-list').html('<div class="eia-loading">Erreur de chargement</div>');
                    }
                }
            });
        },

        renderConversations: function(conversations) {
            const list = $('#eia-conversations-list');
            const activeThreadId = this.currentThreadId;

            if (!conversations || conversations.length === 0) {
                list.html('<div class="eia-loading">Aucune conversation</div>');
                return;
            }

            let html = '';
            conversations.forEach(function(conv) {
                const unreadBadge = conv.unread > 0 ?
                    `<span class="eia-conversation-unread">${conv.unread}</span>` : '';
                const activeClass = activeThreadId == conv.thread_id ? 'active' : '';

                html += `
                    <div class="eia-conversation-item ${activeClass}" data-thread-id="${conv.thread_id}">
                        <div class="eia-conversation-avatar">
                            <img src="${conv.recipient.avatar}" alt="${conv.recipient.name}">
                        </div>
                        <div class="eia-conversation-info">
                            <div class="eia-conversation-name">
                                <span>${conv.recipient.name}</span>
                                <span class="eia-conversation-time">${conv.date}</span>
                            </div>
                            <div class="eia-conversation-excerpt">${conv.excerpt}</div>
                        </div>
                        ${unreadBadge}
                    </div>
                `;
            });

            // Only update if content has changed (avoid visual flash)
            const currentHtml = list.html().replace(/\s+/g, ' ').trim();
            const newHtml = html.replace(/\s+/g, ' ').trim();

            if (currentHtml !== newHtml) {
                list.html(html);
            }
        },

        loadMessages: function(threadId, silent) {
            const self = this;
            silent = silent || false;

            $.ajax({
                url: eiaMessaging.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_get_messages',
                    nonce: eiaMessaging.nonce,
                    thread_id: threadId
                },
                beforeSend: function() {
                    // Only show loading on initial load, not on refresh
                    if (!silent) {
                        $('#eia-messages-list').html('<div class="eia-loading"><i class="fas fa-circle-notch fa-spin"></i> Chargement...</div>');
                        $('.eia-messages-placeholder').hide();
                        $('#eia-messages-content').show();
                    }
                },
                success: function(response) {
                    if (response.success) {
                        self.currentThreadId = threadId;
                        self.renderMessages(response.data.messages, response.data.recipient);

                        // Mark conversation as active (only on initial load)
                        if (!silent) {
                            $('.eia-conversation-item').removeClass('active');
                            $(`.eia-conversation-item[data-thread-id="${threadId}"]`).addClass('active');

                            // Remove unread badge
                            $(`.eia-conversation-item[data-thread-id="${threadId}"] .eia-conversation-unread`).remove();
                        }
                    }
                }
            });
        },

        renderMessages: function(messages, recipient) {
            // Render header (only if changed)
            const headerHtml = `
                <img src="${recipient.avatar}" alt="${recipient.name}">
                <div class="eia-messages-header-info">
                    <h3>${recipient.name}</h3>
                </div>
            `;
            const $header = $('#eia-messages-header');
            if ($header.html().replace(/\s+/g, ' ').trim() !== headerHtml.replace(/\s+/g, ' ').trim()) {
                $header.html(headerHtml);
            }

            // Render messages
            const list = $('#eia-messages-list');
            const currentScroll = list.scrollTop();
            const scrollHeight = list[0] ? list[0].scrollHeight : 0;
            const containerHeight = list.height();
            const isScrolledToBottom = currentScroll + containerHeight >= scrollHeight - 50;

            let html = '';

            messages.forEach(function(msg) {
                const ownClass = msg.sender.is_current ? 'own' : '';

                html += `
                    <div class="eia-message-item ${ownClass}">
                        <div class="eia-message-avatar">
                            <img src="${msg.sender.avatar}" alt="${msg.sender.name}">
                        </div>
                        <div class="eia-message-content">
                            <div class="eia-message-bubble">
                                ${msg.content}
                            </div>
                            <div class="eia-message-meta">${msg.date}</div>
                        </div>
                    </div>
                `;
            });

            // Only update if content has changed
            const currentHtml = list.html().replace(/\s+/g, ' ').trim();
            const newHtml = html.replace(/\s+/g, ' ').trim();

            if (currentHtml !== newHtml) {
                list.html(html);

                // Only auto-scroll if user was already at bottom
                if (isScrolledToBottom) {
                    list.scrollTop(list[0].scrollHeight);
                }
            }
        },

        sendMessage: function() {
            const self = this;
            const message = $('#eia-message-input').val().trim();

            if (!message) {
                return;
            }

            if (!this.currentThreadId) {
                alert('Sélectionnez une conversation');
                return;
            }

            // Add attachments to message if any
            let fullMessage = message;
            if (this.attachments.length > 0) {
                fullMessage += '\n\nPièces jointes:\n';
                this.attachments.forEach(function(att) {
                    fullMessage += `- ${att.filename}: ${att.url}\n`;
                });
            }

            $.ajax({
                url: eiaMessaging.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_send_message',
                    nonce: eiaMessaging.nonce,
                    thread_id: this.currentThreadId,
                    message: fullMessage
                },
                beforeSend: function() {
                    $('#eia-send-message-btn').prop('disabled', true);
                },
                success: function(response) {
                    if (response.success) {
                        $('#eia-message-input').val('');
                        self.attachments = [];
                        $('#eia-compose-attachments').removeClass('has-files').html('');
                        self.loadMessages(self.currentThreadId);
                        self.loadConversations();
                    } else {
                        alert(response.data.message);
                    }
                },
                complete: function() {
                    $('#eia-send-message-btn').prop('disabled', false);
                }
            });
        },

        sendNewMessage: function() {
            const self = this;
            const recipientId = $('#eia-recipient-select').val();
            const message = $('#eia-new-message-text').val().trim();

            console.log('sendNewMessage - recipientId:', recipientId);
            console.log('sendNewMessage - message:', message);

            if (!recipientId) {
                alert('Sélectionnez un destinataire');
                return;
            }

            if (!message) {
                alert('Écrivez un message');
                return;
            }

            $.ajax({
                url: eiaMessaging.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_send_message',
                    nonce: eiaMessaging.nonce,
                    recipient_id: recipientId,
                    message: message
                },
                beforeSend: function() {
                    $('#eia-send-new-message-btn').prop('disabled', true);
                },
                success: function(response) {
                    console.log('sendNewMessage response:', response);
                    if (response.success) {
                        // Close modal with animation
                        self.closeModal($('#eia-new-message-modal'));

                        // Clear form
                        setTimeout(function() {
                            $('#eia-recipient-select').val('');
                            $('#eia-new-message-text').val('');
                        }, 200);

                        self.loadConversations();

                        // Load the new thread
                        if (response.data.thread_id) {
                            setTimeout(function() {
                                self.loadMessages(response.data.thread_id);
                            }, 300);
                        }
                    } else {
                        alert(response.data.message || 'Erreur lors de l\'envoi');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('sendNewMessage error:', xhr, status, error);
                    alert('Erreur lors de l\'envoi: ' + error);
                },
                complete: function() {
                    $('#eia-send-new-message-btn').prop('disabled', false);
                }
            });
        },

        handleFileUpload: function(file) {
            const self = this;

            if (!file) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'eia_upload_message_attachment');
            formData.append('nonce', eiaMessaging.nonce);
            formData.append('file', file);

            $.ajax({
                url: eiaMessaging.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        self.attachments.push({
                            url: response.data.url,
                            filename: response.data.filename
                        });
                        self.renderAttachments();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        },

        renderAttachments: function() {
            const container = $('#eia-compose-attachments');
            let html = '';

            this.attachments.forEach(function(att, index) {
                html += `
                    <div class="eia-attachment-item">
                        <i class="fas fa-paperclip"></i>
                        <span>${att.filename}</span>
                        <button class="eia-attachment-remove" data-index="${index}">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });

            container.html(html);
            container.toggleClass('has-files', this.attachments.length > 0);
        },

        removeAttachment: function(index) {
            this.attachments.splice(index, 1);
            this.renderAttachments();
        },

        startPolling: function() {
            const self = this;

            // Poll for new messages every 10 seconds
            this.pollingInterval = setInterval(function() {
                if (self.currentThreadId) {
                    // Silent refresh - no loading indicator
                    self.loadMessages(self.currentThreadId, true);
                }
                // Silent refresh for conversations too
                self.loadConversations(true);
            }, 10000);
        },

        openModal: function(modalSelector) {
            $(modalSelector).addClass('active');
        },

        closeModal: function($modal) {
            $modal.addClass('closing');
            setTimeout(function() {
                $modal.removeClass('active closing');
            }, 200);
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.eia-messaging-container').length) {
            EIA_Messaging.init();
        }
    });

})(jQuery);
