<?php
/**
 * Template Name: Analytics Instructeur
 *
 * @package EIA_Theme
 */

// Restrict to instructors and admins
if (!current_user_can('edit_posts') && !current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}

show_admin_bar(true);
get_header();

$current_user = wp_get_current_user();
$is_instructor = in_array('instructor', $current_user->roles);
$is_admin = in_array('administrator', $current_user->roles);

// Get instructor courses
global $wpdb;
$instructor_courses = get_posts(array(
    'post_type' => 'lp_course',
    'posts_per_page' => -1,
    'author' => $current_user->ID,
    'post_status' => 'publish'
));

// Default selected course
$selected_course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : ($instructor_courses ? $instructor_courses[0]->ID : 0);

// Get analytics class
if (class_exists('EIA_Reports')) {
    $reports = EIA_Reports::get_instance();
} else {
    echo '<p>Module Analytics non disponible</p>';
    get_footer();
    exit;
}

// Get course analytics
$date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : date('Y-m-d');

$analytics = null;
$course_title = '';
if ($selected_course_id) {
    $analytics = $reports->get_course_analytics($selected_course_id, $date_from, $date_to);
    $course = get_post($selected_course_id);
    $course_title = $course ? $course->post_title : '';
}

// Get student list for selected course
$students = array();
if ($selected_course_id) {
    $student_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT user_id FROM {$wpdb->prefix}learnpress_user_items WHERE item_id = %d AND item_type = 'lp_course'",
        $selected_course_id
    ));

    if ($student_ids) {
        $students = get_users(array('include' => $student_ids));

        // Get progress for each student
        foreach ($students as &$student) {
            $enrollment = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}learnpress_user_items WHERE user_id = %d AND item_id = %d AND item_type = 'lp_course'",
                $student->ID,
                $selected_course_id
            ));

            $student->status = $enrollment ? $enrollment->status : 'unknown';
            $student->progress = $enrollment && $enrollment->graduation ? floatval($enrollment->graduation) : 0;
            $student->start_date = $enrollment && $enrollment->start_time ? date('d/m/Y', strtotime($enrollment->start_time)) : '-';
        }
    }
}
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: #f9fafb;
    margin: 0;
    padding: 0;
}
body.admin-bar { margin-top: 32px !important; }
#wpadminbar { display: block !important; position: fixed !important; top: 0 !important; z-index: 99999 !important; }

.analytics-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
}

.analytics-header {
    background: linear-gradient(135deg, #2D4FB3 0%, #1e3a8a 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(45, 79, 179, 0.2);
}

.analytics-header h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 700;
}

.analytics-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 1.125rem;
}

.filters-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.filters-grid {
    display: grid;
    grid-template-columns: 1fr 200px 200px auto;
    gap: 1rem;
    align-items: end;
}

.filter-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
}

.filter-group select,
.filter-group input[type="date"] {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

.filter-group select:focus,
.filter-group input[type="date"]:focus {
    outline: none;
    border-color: #2D4FB3;
    box-shadow: 0 0 0 3px rgba(45, 79, 179, 0.1);
}

.btn-filter {
    padding: 0.75rem 1.5rem;
    background: #2D4FB3;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-filter:hover {
    background: #1e3a8a;
    transform: translateY(-1px);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-left: 4px solid;
    transition: transform 0.2s, box-shadow 0.2s;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}

.stat-card.blue { border-color: #3B82F6; }
.stat-card.green { border-color: #10B981; }
.stat-card.orange { border-color: #F59E0B; }
.stat-card.purple { border-color: #8B5CF6; }

.stat-icon {
    font-size: 2rem;
    margin-bottom: 0.75rem;
}

.stat-card.blue .stat-icon { color: #3B82F6; }
.stat-card.green .stat-icon { color: #10B981; }
.stat-card.orange .stat-icon { color: #F59E0B; }
.stat-card.purple .stat-icon { color: #8B5CF6; }

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1f2937;
    margin: 0;
}

.stat-label {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.25rem 0 0 0;
}

.charts-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.chart-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.chart-card h3 {
    margin: 0 0 1.5rem 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
}

.chart-container {
    position: relative;
    height: 300px;
}

.students-section {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.students-section h3 {
    margin: 0 0 1.5rem 0;
    color: #1f2937;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.students-table {
    width: 100%;
    border-collapse: collapse;
}

.students-table thead {
    background: #f9fafb;
}

.students-table th {
    text-align: left;
    padding: 1rem;
    font-weight: 600;
    color: #374151;
    font-size: 0.875rem;
    border-bottom: 2px solid #e5e7eb;
}

.students-table td {
    padding: 1rem;
    border-bottom: 1px solid #e5e7eb;
    color: #1f2937;
}

.students-table tbody tr:hover {
    background: #f9fafb;
}

.progress-bar-container {
    width: 100%;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #10B981 0%, #059669 100%);
    border-radius: 4px;
    transition: width 0.3s;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-badge.enrolled {
    background: #dbeafe;
    color: #1e40af;
}

.status-badge.finished {
    background: #d1fae5;
    color: #065f46;
}

.status-badge.unknown {
    background: #f3f4f6;
    color: #6b7280;
}

.export-section {
    margin-top: 2rem;
    display: flex;
    gap: 1rem;
}

.btn-export {
    padding: 0.75rem 1.5rem;
    background: white;
    color: #2D4FB3;
    border: 2px solid #2D4FB3;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-export:hover {
    background: #2D4FB3;
    color: white;
}

.btn-export i {
    margin-right: 0.5rem;
}

.no-data {
    text-align: center;
    padding: 3rem;
    color: #6b7280;
}

.no-data i {
    font-size: 3rem;
    color: #d1d5db;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .filters-grid {
        grid-template-columns: 1fr;
    }

    .charts-section {
        grid-template-columns: 1fr;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="analytics-container">
    <!-- Header -->
    <div class="analytics-header">
        <h1><i class="fas fa-chart-line"></i> Analytics & Rapports</h1>
        <p>Tableau de bord analytique pour vos cours</p>
    </div>

    <?php if (empty($instructor_courses)) : ?>
        <div class="no-data">
            <i class="fas fa-chart-bar"></i>
            <h3>Aucun cours disponible</h3>
            <p>Vous devez avoir au moins un cours publié pour voir les analytics</p>
        </div>
    <?php else : ?>

        <!-- Filters -->
        <div class="filters-section">
            <form method="get" class="filters-grid">
                <div class="filter-group">
                    <label><i class="fas fa-book"></i> Cours</label>
                    <select name="course_id" required>
                        <?php foreach ($instructor_courses as $course) : ?>
                            <option value="<?php echo $course->ID; ?>" <?php selected($selected_course_id, $course->ID); ?>>
                                <?php echo esc_html($course->post_title); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> Date début</label>
                    <input type="date" name="date_from" value="<?php echo esc_attr($date_from); ?>" required>
                </div>

                <div class="filter-group">
                    <label><i class="fas fa-calendar-alt"></i> Date fin</label>
                    <input type="date" name="date_to" value="<?php echo esc_attr($date_to); ?>" required>
                </div>

                <button type="submit" class="btn-filter">
                    <i class="fas fa-filter"></i> Filtrer
                </button>
            </form>
        </div>

        <?php if ($analytics) : ?>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon"><i class="fas fa-users"></i></div>
                    <div class="stat-value"><?php echo number_format($analytics['total_students']); ?></div>
                    <div class="stat-label">Étudiants inscrits</div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="stat-value"><?php echo number_format($analytics['completion_rate'], 1); ?>%</div>
                    <div class="stat-label">Taux de complétion</div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-value"><?php echo number_format($analytics['avg_duration'], 1); ?></div>
                    <div class="stat-label">Jours moyen pour finir</div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                    <div class="stat-value"><?php echo count($analytics['enrollments']); ?></div>
                    <div class="stat-label">Nouvelles inscriptions (période)</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-section">
                <!-- Enrollments Chart -->
                <div class="chart-card">
                    <h3><i class="fas fa-user-plus"></i> Inscriptions par jour</h3>
                    <div class="chart-container">
                        <canvas id="enrollmentsChart"></canvas>
                    </div>
                </div>

                <!-- Completions Chart -->
                <div class="chart-card">
                    <h3><i class="fas fa-trophy"></i> Complétions par jour</h3>
                    <div class="chart-container">
                        <canvas id="completionsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Students List -->
            <div class="students-section">
                <h3><i class="fas fa-users"></i> Liste des étudiants (<?php echo count($students); ?>)</h3>

                <?php if ($students) : ?>
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Email</th>
                                <th>Date d'inscription</th>
                                <th>Statut</th>
                                <th>Progression</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($student->display_name); ?></strong></td>
                                    <td><?php echo esc_html($student->user_email); ?></td>
                                    <td><?php echo $student->start_date; ?></td>
                                    <td>
                                        <?php
                                        $status_class = 'unknown';
                                        $status_label = 'Inconnu';
                                        if ($student->status === 'enrolled') {
                                            $status_class = 'enrolled';
                                            $status_label = 'En cours';
                                        } elseif ($student->status === 'finished') {
                                            $status_class = 'finished';
                                            $status_label = 'Terminé';
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $status_class; ?>">
                                            <?php echo $status_label; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <div class="progress-bar-container" style="flex: 1;">
                                                <div class="progress-bar" style="width: <?php echo $student->progress; ?>%"></div>
                                            </div>
                                            <span style="font-weight: 600; color: #10B981; min-width: 45px;">
                                                <?php echo number_format($student->progress, 0); ?>%
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="no-data">
                        <p>Aucun étudiant inscrit pour le moment</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Export Section -->
            <div class="export-section">
                <button class="btn-export" onclick="exportCSV()">
                    <i class="fas fa-file-csv"></i> Exporter CSV
                </button>
                <button class="btn-export" onclick="exportPDF()">
                    <i class="fas fa-file-pdf"></i> Exporter PDF
                </button>
            </div>

        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
// Chart.js configuration
const chartConfig = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0
            }
        }
    }
};

// Enrollments Chart
<?php if ($analytics && !empty($analytics['enrollments'])) : ?>
const enrollmentsData = {
    labels: <?php echo json_encode(array_map(function($item) {
        return date('d/m', strtotime($item->date));
    }, $analytics['enrollments'])); ?>,
    datasets: [{
        label: 'Inscriptions',
        data: <?php echo json_encode(array_map(function($item) {
            return $item->count;
        }, $analytics['enrollments'])); ?>,
        backgroundColor: 'rgba(59, 130, 246, 0.2)',
        borderColor: 'rgba(59, 130, 246, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
    }]
};

new Chart(document.getElementById('enrollmentsChart'), {
    type: 'line',
    data: enrollmentsData,
    options: chartConfig
});
<?php endif; ?>

// Completions Chart
<?php if ($analytics && !empty($analytics['completions'])) : ?>
const completionsData = {
    labels: <?php echo json_encode(array_map(function($item) {
        return date('d/m', strtotime($item->date));
    }, $analytics['completions'])); ?>,
    datasets: [{
        label: 'Complétions',
        data: <?php echo json_encode(array_map(function($item) {
            return $item->count;
        }, $analytics['completions'])); ?>,
        backgroundColor: 'rgba(16, 185, 129, 0.2)',
        borderColor: 'rgba(16, 185, 129, 1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4
    }]
};

new Chart(document.getElementById('completionsChart'), {
    type: 'line',
    data: completionsData,
    options: chartConfig
});
<?php endif; ?>

// Export CSV
function exportCSV() {
    const courseId = <?php echo $selected_course_id; ?>;
    const courseName = "<?php echo addslashes($course_title); ?>";
    const dateFrom = "<?php echo $date_from; ?>";
    const dateTo = "<?php echo $date_to; ?>";

    let csv = "Rapport Analytics - " + courseName + "\n";
    csv += "Période: " + dateFrom + " au " + dateTo + "\n\n";

    csv += "Statistiques Générales\n";
    csv += "Étudiants inscrits,<?php echo $analytics['total_students']; ?>\n";
    csv += "Taux de complétion,<?php echo number_format($analytics['completion_rate'], 2); ?>%\n";
    csv += "Durée moyenne,<?php echo number_format($analytics['avg_duration'], 1); ?> jours\n\n";

    csv += "Liste des Étudiants\n";
    csv += "Nom,Email,Date inscription,Statut,Progression\n";

    <?php foreach ($students as $student) : ?>
    csv += "<?php echo addslashes($student->display_name); ?>,<?php echo $student->user_email; ?>,<?php echo $student->start_date; ?>,<?php
        echo $student->status === 'enrolled' ? 'En cours' : ($student->status === 'finished' ? 'Terminé' : 'Inconnu');
    ?>,<?php echo number_format($student->progress, 2); ?>%\n";
    <?php endforeach; ?>

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = 'analytics-' + courseName.replace(/[^a-z0-9]/gi, '-').toLowerCase() + '-' + dateFrom + '.csv';
    link.click();
}

// Export PDF (using print)
function exportPDF() {
    window.print();
}
</script>

<?php get_footer(); ?>
