<?php
/**
 * Admin Reports Template
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap eia-admin-reports">
    <h1 class="wp-heading-inline">
        <i class="dashicons dashicons-chart-bar"></i>
        <?php _e('Rapports et Analyses', 'eia-lms-core'); ?>
    </h1>

    <button class="page-title-action export-report-btn">
        <i class="dashicons dashicons-download"></i>
        <?php _e('Exporter', 'eia-lms-core'); ?>
    </button>

    <hr class="wp-header-end">

    <!-- Report Filters -->
    <div class="eia-report-filters">
        <div class="filter-group">
            <label><?php _e('Type de rapport', 'eia-lms-core'); ?></label>
            <select id="report-type" class="regular-text">
                <option value="overview"><?php _e('Vue d\'ensemble', 'eia-lms-core'); ?></option>
                <option value="courses"><?php _e('Cours', 'eia-lms-core'); ?></option>
                <option value="students"><?php _e('Étudiants', 'eia-lms-core'); ?></option>
                <option value="instructors"><?php _e('Formateurs', 'eia-lms-core'); ?></option>
                <option value="engagement"><?php _e('Engagement', 'eia-lms-core'); ?></option>
            </select>
        </div>

        <div class="filter-group">
            <label><?php _e('Période', 'eia-lms-core'); ?></label>
            <select id="date-range" class="regular-text">
                <option value="7"><?php _e('7 derniers jours', 'eia-lms-core'); ?></option>
                <option value="30" selected><?php _e('30 derniers jours', 'eia-lms-core'); ?></option>
                <option value="90"><?php _e('90 derniers jours', 'eia-lms-core'); ?></option>
                <option value="365"><?php _e('Cette année', 'eia-lms-core'); ?></option>
                <option value="custom"><?php _e('Personnalisé', 'eia-lms-core'); ?></option>
            </select>
        </div>

        <div class="filter-group date-range-custom" style="display: none;">
            <label><?php _e('Du', 'eia-lms-core'); ?></label>
            <input type="date" id="date-from" class="regular-text">
        </div>

        <div class="filter-group date-range-custom" style="display: none;">
            <label><?php _e('Au', 'eia-lms-core'); ?></label>
            <input type="date" id="date-to" class="regular-text">
        </div>

        <div class="filter-group">
            <button class="button button-primary" id="apply-filters">
                <?php _e('Appliquer', 'eia-lms-core'); ?>
            </button>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="eia-chart-section">
        <div class="chart-container">
            <h2><?php _e('Inscriptions par Jour', 'eia-lms-core'); ?></h2>
            <canvas id="enrollments-chart"></canvas>
        </div>

        <div class="chart-container">
            <h2><?php _e('Complétion par Jour', 'eia-lms-core'); ?></h2>
            <canvas id="completions-chart"></canvas>
        </div>
    </div>

    <!-- Detailed Reports -->
    <div class="eia-detailed-reports">
        <div class="report-card">
            <h3><?php _e('Performance par Cours', 'eia-lms-core'); ?></h3>
            <div id="course-performance-table"></div>
        </div>

        <div class="report-card">
            <h3><?php _e('Top Étudiants', 'eia-lms-core'); ?></h3>
            <div id="top-students-table"></div>
        </div>

        <div class="report-card">
            <h3><?php _e('Engagement Utilisateurs', 'eia-lms-core'); ?></h3>
            <div id="user-engagement-table"></div>
        </div>
    </div>
</div>

<style>
.eia-admin-reports {
    margin: 20px 20px 0 0;
}

.eia-report-filters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
    align-items: flex-end;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.filter-group label {
    font-weight: 600;
    color: #1e293b;
}

.eia-chart-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.chart-container {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.chart-container h2 {
    margin-top: 0;
    font-size: 18px;
    color: #1e293b;
}

.chart-container canvas {
    max-height: 300px;
}

.eia-detailed-reports {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.report-card {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.report-card h3 {
    margin-top: 0;
    font-size: 16px;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

.export-report-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.export-report-btn .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle custom date range
    $('#date-range').on('change', function() {
        if ($(this).val() === 'custom') {
            $('.date-range-custom').show();
        } else {
            $('.date-range-custom').hide();
        }
    });

    // Apply filters
    $('#apply-filters').on('click', function() {
        loadReportData();
    });

    // Export report
    $('.export-report-btn').on('click', function() {
        exportReport();
    });

    // Load initial data
    loadReportData();

    function loadReportData() {
        const reportType = $('#report-type').val();
        const dateRange = $('#date-range').val();

        // Show loading state
        showLoading();

        // AJAX call to load report data
        $.post(eiaLMSCore.ajaxurl, {
            action: 'eia_get_dashboard_stats',
            nonce: eiaLMSCore.nonce,
            report_type: reportType,
            date_range: dateRange
        }, function(response) {
            if (response.success) {
                renderCharts(response.data);
                renderTables(response.data);
            }
            hideLoading();
        });
    }

    function renderCharts(data) {
        // TODO: Integrate Chart.js for visualization
        console.log('Rendering charts with data:', data);
    }

    function renderTables(data) {
        // TODO: Render data tables
        console.log('Rendering tables with data:', data);
    }

    function exportReport() {
        const reportType = $('#report-type').val();

        $.post(eiaLMSCore.ajaxurl, {
            action: 'eia_export_report',
            nonce: eiaLMSCore.nonce,
            report_type: reportType
        }, function(response) {
            if (response.success) {
                downloadCSV(response.data.csv, response.data.filename);
            }
        });
    }

    function downloadCSV(csv, filename) {
        const blob = new Blob([csv], { type: 'text/csv' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.click();
    }

    function showLoading() {
        // TODO: Show loading spinner
    }

    function hideLoading() {
        // TODO: Hide loading spinner
    }
});
</script>