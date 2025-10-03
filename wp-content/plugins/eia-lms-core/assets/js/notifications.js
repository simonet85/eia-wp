/**
 * EIA Notifications JavaScript
 * Gestion du centre de notifications avec AJAX
 */

(function($) {
    'use strict';

    const EIA_Notifications = {
        dropdown: null,
        currentTab: 'all',
        isOpen: false,
        pollingInterval: null,

        init: function() {
            this.createDropdown();
            this.bindEvents();
            this.startPolling();
        },

        createDropdown: function() {
            const dropdownHTML = `
                <div class="eia-notifications-dropdown" id="eia-notifications-dropdown">
                    <div class="eia-notifications-header">
                        <h3><i class="fas fa-bell"></i> Notifications</h3>
                        <div class="eia-notifications-actions">
                            <button class="mark-all-read" title="Tout marquer comme lu">
                                <i class="fas fa-check-double"></i>
                            </button>
                            <button class="close-dropdown" title="Fermer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="eia-notifications-tabs">
                        <button class="eia-notifications-tab active" data-tab="all">
                            Toutes
                        </button>
                        <button class="eia-notifications-tab" data-tab="unread">
                            Non lues
                        </button>
                    </div>

                    <div class="eia-notifications-list" id="eia-notifications-list">
                        <div class="eia-notifications-loading">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(dropdownHTML);
            this.dropdown = $('#eia-notifications-dropdown');
        },

        bindEvents: function() {
            const self = this;

            // Toggle dropdown
            $(document).on('click', '#wp-admin-bar-eia-notifications', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.toggleDropdown();
            });

            // Close dropdown
            $(document).on('click', '.close-dropdown', function(e) {
                e.preventDefault();
                self.closeDropdown();
            });

            // Close on outside click
            $(document).on('click', function(e) {
                if (self.isOpen && !$(e.target).closest('.eia-notifications-dropdown, #wp-admin-bar-eia-notifications').length) {
                    self.closeDropdown();
                }
            });

            // Tabs
            $(document).on('click', '.eia-notifications-tab', function() {
                $('.eia-notifications-tab').removeClass('active');
                $(this).addClass('active');
                self.currentTab = $(this).data('tab');
                self.loadNotifications();
            });

            // Mark all as read
            $(document).on('click', '.mark-all-read', function(e) {
                e.preventDefault();
                self.markAllAsRead();
            });

            // Mark single as read
            $(document).on('click', '.eia-notification-item:not(.read)', function() {
                const notifId = $(this).data('id');
                const actionUrl = $(this).data('url');

                self.markAsRead(notifId, function() {
                    if (actionUrl) {
                        window.location.href = actionUrl;
                    }
                });
            });

            // Delete notification
            $(document).on('click', '.delete-notification', function(e) {
                e.stopPropagation();
                const notifId = $(this).closest('.eia-notification-item').data('id');
                self.deleteNotification(notifId);
            });

            // Keyboard shortcuts
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && self.isOpen) {
                    self.closeDropdown();
                }
            });
        },

        toggleDropdown: function() {
            if (this.isOpen) {
                this.closeDropdown();
            } else {
                this.openDropdown();
            }
        },

        openDropdown: function() {
            this.dropdown.addClass('active');
            this.isOpen = true;
            this.loadNotifications();
        },

        closeDropdown: function() {
            this.dropdown.removeClass('active');
            this.isOpen = false;
        },

        loadNotifications: function() {
            const self = this;

            $.ajax({
                url: eiaNotifications.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_get_notifications',
                    nonce: eiaNotifications.nonce,
                    limit: 20,
                    unread_only: this.currentTab === 'unread'
                },
                beforeSend: function() {
                    $('#eia-notifications-list').html(`
                        <div class="eia-notifications-loading">
                            <i class="fas fa-circle-notch fa-spin"></i>
                        </div>
                    `);
                },
                success: function(response) {
                    if (response.success) {
                        self.renderNotifications(response.data.notifications);
                        self.updateBadge(response.data.unread_count);
                    }
                },
                error: function() {
                    $('#eia-notifications-list').html(`
                        <div class="eia-notifications-empty">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Erreur lors du chargement des notifications</p>
                        </div>
                    `);
                }
            });
        },

        renderNotifications: function(notifications) {
            const list = $('#eia-notifications-list');

            if (!notifications || notifications.length === 0) {
                list.html(`
                    <div class="eia-notifications-empty">
                        <i class="fas fa-bell-slash"></i>
                        <p>${this.currentTab === 'unread' ? 'Aucune notification non lue' : 'Aucune notification'}</p>
                    </div>
                `);
                return;
            }

            let html = '';
            notifications.forEach(function(notif) {
                const isUnread = notif.is_read === '0';
                const timeAgo = EIA_Notifications.timeAgo(notif.created_at);

                html += `
                    <div class="eia-notification-item ${isUnread ? 'unread' : 'read'}"
                         data-id="${notif.id}"
                         data-url="${notif.action_url || ''}">
                        <div class="eia-notification-icon icon-${notif.icon}">
                            <i class="fas fa-${notif.icon}"></i>
                        </div>
                        <div class="eia-notification-content">
                            <h4 class="eia-notification-title">${notif.title}</h4>
                            <div class="eia-notification-message">${notif.message}</div>
                            <div class="eia-notification-time">
                                <i class="fas fa-clock"></i>
                                ${timeAgo}
                            </div>
                        </div>
                        <div class="eia-notification-item-actions">
                            <button class="delete-notification" title="Supprimer">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                `;
            });

            list.html(html);
        },

        markAsRead: function(notificationId, callback) {
            const self = this;

            $.ajax({
                url: eiaNotifications.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_mark_notification_read',
                    nonce: eiaNotifications.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        self.updateBadge(response.data.unread_count);
                        if (self.currentTab === 'unread') {
                            self.loadNotifications();
                        } else {
                            $(`.eia-notification-item[data-id="${notificationId}"]`)
                                .removeClass('unread')
                                .addClass('read');
                        }
                        if (callback) callback();
                    }
                }
            });
        },

        markAllAsRead: function() {
            const self = this;

            $.ajax({
                url: eiaNotifications.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_mark_all_read',
                    nonce: eiaNotifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateBadge(0);
                        self.loadNotifications();
                    }
                }
            });
        },

        deleteNotification: function(notificationId) {
            const self = this;

            if (!confirm('Supprimer cette notification ?')) {
                return;
            }

            $.ajax({
                url: eiaNotifications.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_delete_notification',
                    nonce: eiaNotifications.nonce,
                    notification_id: notificationId
                },
                success: function(response) {
                    if (response.success) {
                        self.updateBadge(response.data.unread_count);
                        $(`.eia-notification-item[data-id="${notificationId}"]`).fadeOut(200, function() {
                            $(this).remove();
                            if ($('.eia-notification-item').length === 0) {
                                self.loadNotifications();
                            }
                        });
                    }
                }
            });
        },

        updateBadge: function(count) {
            const badge = $('.eia-notification-badge');

            if (count > 0) {
                if (badge.length === 0) {
                    $('#wp-admin-bar-eia-notifications .ab-item').append(
                        `<span class="eia-notification-badge">${count}</span>`
                    );
                } else {
                    badge.text(count);
                }
            } else {
                badge.remove();
            }
        },

        startPolling: function() {
            const self = this;

            // Check for new notifications every 30 seconds
            this.pollingInterval = setInterval(function() {
                if (!self.isOpen) {
                    self.checkUnreadCount();
                }
            }, 30000);
        },

        checkUnreadCount: function() {
            const self = this;

            $.ajax({
                url: eiaNotifications.ajaxurl,
                type: 'POST',
                data: {
                    action: 'eia_get_unread_count',
                    nonce: eiaNotifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.updateBadge(response.data.count);
                    }
                }
            });
        },

        timeAgo: function(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            const intervals = {
                'an': 31536000,
                'mois': 2592000,
                'semaine': 604800,
                'jour': 86400,
                'heure': 3600,
                'minute': 60,
                'seconde': 1
            };

            for (let [name, secondsInInterval] of Object.entries(intervals)) {
                const interval = Math.floor(seconds / secondsInInterval);

                if (interval >= 1) {
                    if (name === 'mois' || name === 'an') {
                        return `Il y a ${interval} ${name}${interval > 1 ? (name === 'mois' ? '' : 's') : ''}`;
                    }
                    return `Il y a ${interval} ${name}${interval > 1 ? 's' : ''}`;
                }
            }

            return 'À l\'instant';
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        EIA_Notifications.init();
    });

})(jQuery);
