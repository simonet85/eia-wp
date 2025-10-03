<?php
/**
 * Template Name: Mon Calendrier
 *
 * @package EIA_Theme
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

show_admin_bar(true);
get_header();

$current_user = wp_get_current_user();

if (!class_exists('EIA_Calendar')) {
    echo '<p>Module Calendrier non disponible</p>';
    get_footer();
    exit;
}
?>

<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet">

<!-- FullCalendar JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>

<style>
body.admin-bar { margin-top: 32px !important; }
#wpadminbar { display: block !important; position: fixed !important; top: 0 !important; z-index: 99999 !important; }

.calendar-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.calendar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.calendar-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}

.calendar-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.125rem;
}

.calendar-actions {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.btn-action {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 2px solid #e5e7eb;
}

.btn-secondary:hover {
    border-color: #667eea;
    color: #667eea;
}

.calendar-wrapper {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 2rem;
}

#calendar {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.fc .fc-button-primary {
    background: #667eea !important;
    border-color: #667eea !important;
}

.fc .fc-button-primary:hover {
    background: #5568d3 !important;
}

.fc .fc-button-primary:disabled {
    background: #9ca3af !important;
    border-color: #9ca3af !important;
}

.fc-event {
    border-radius: 4px;
    padding: 2px 4px;
}

.legend {
    display: flex;
    gap: 2rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.legend-color {
    width: 20px;
    height: 20px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .calendar-container {
        padding: 1rem;
    }

    .calendar-actions {
        flex-direction: column;
    }

    .btn-action {
        width: 100%;
    }
}
</style>

<div class="calendar-container">
    <!-- Header -->
    <div class="calendar-header">
        <h1><i class="fas fa-calendar-alt"></i> Mon Calendrier</h1>
        <p>Tous vos devoirs, quiz et événements en un seul endroit</p>
    </div>

    <!-- Actions -->
    <div class="calendar-actions">
        <a href="?eia_calendar_export=1&user_id=<?php echo $current_user->ID; ?>" class="btn-action btn-secondary">
            <i class="fas fa-download"></i> Exporter (.ics)
        </a>
        <button onclick="window.print()" class="btn-action btn-secondary">
            <i class="fas fa-print"></i> Imprimer
        </button>
    </div>

    <!-- Calendar -->
    <div class="calendar-wrapper">
        <div id="calendar"></div>

        <!-- Legend -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color" style="background: #EF4444;"></div>
                <span>Devoir (non soumis)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #10B981;"></div>
                <span>Devoir (soumis) / Complété</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #F59E0B;"></div>
                <span>Quiz (non fait)</span>
            </div>
            <div class="legend-item">
                <div class="legend-color" style="background: #3B82F6;"></div>
                <span>Événement cours</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'fr',
        firstDay: 1,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Aujourd\'hui',
            month: 'Mois',
            week: 'Semaine',
            list: 'Liste'
        },
        height: 'auto',
        navLinks: true,
        editable: false,
        dayMaxEvents: true,
        events: function(info, successCallback, failureCallback) {
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'eia_get_calendar_events',
                    nonce: '<?php echo wp_create_nonce('eia-calendar-nonce'); ?>',
                    start: info.startStr,
                    end: info.endStr
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.data);
                    } else {
                        failureCallback();
                    }
                },
                error: function() {
                    failureCallback();
                }
            });
        },
        eventClick: function(info) {
            const event = info.event;
            const props = event.extendedProps;

            let message = event.title + '\n\n';

            if (props.description) {
                message += props.description + '\n\n';
            }

            message += 'Date: ' + event.start.toLocaleDateString('fr-FR', {
                day: 'numeric',
                month: 'long',
                year: 'numeric',
                hour: event.allDay ? undefined : '2-digit',
                minute: event.allDay ? undefined : '2-digit'
            });

            if (props.type === 'assignment') {
                if (props.submitted) {
                    message += '\n✅ Devoir soumis';
                } else {
                    message += '\n⚠️ Devoir non soumis';
                }

                if (props.url) {
                    if (confirm(message + '\n\nVoulez-vous accéder au devoir?')) {
                        window.location.href = props.url;
                    }
                } else {
                    alert(message);
                }
            } else if (props.type === 'quiz') {
                if (props.taken) {
                    message += '\n✅ Quiz complété';
                } else {
                    message += '\n⚠️ Quiz à faire';
                }
                alert(message);
            } else {
                alert(message);
            }
        },
        eventDidMount: function(info) {
            // Add tooltip
            info.el.title = info.event.title;
        }
    });

    calendar.render();

    // Add custom CSS for today's date
    setTimeout(function() {
        const today = document.querySelector('.fc-day-today');
        if (today) {
            today.style.backgroundColor = '#fef3c7';
        }
    }, 100);
});
</script>

<?php get_footer(); ?>
