<?php
/**
 * Admin Dashboard Template
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$stats = EIA_Reports::get_instance()->get_dashboard_stats();
?>

<div class="wrap eia-admin-dashboard">
    <h1 class="wp-heading-inline">
        <i class="dashicons dashicons-graduation-cap"></i>
        <?php _e('Tableau de Bord EIA LMS', 'eia-lms-core'); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Statistics Cards -->
    <div class="eia-stats-grid">
        <div class="eia-stat-card">
            <div class="stat-icon" style="background: #2D4FB3;">
                <i class="dashicons dashicons-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_courses']); ?></h3>
                <p><?php _e('Cours au total', 'eia-lms-core'); ?></p>
            </div>
        </div>

        <div class="eia-stat-card">
            <div class="stat-icon" style="background: #F59E0B;">
                <i class="dashicons dashicons-groups"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_students']); ?></h3>
                <p><?php _e('Étudiants', 'eia-lms-core'); ?></p>
            </div>
        </div>

        <div class="eia-stat-card">
            <div class="stat-icon" style="background: #8B5CF6;">
                <i class="dashicons dashicons-welcome-learn-more"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['total_instructors']); ?></h3>
                <p><?php _e('Formateurs', 'eia-lms-core'); ?></p>
            </div>
        </div>

        <div class="eia-stat-card">
            <div class="stat-icon" style="background: #10B981;">
                <i class="dashicons dashicons-yes-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo number_format($stats['active_enrollments']); ?></h3>
                <p><?php _e('Inscriptions actives', 'eia-lms-core'); ?></p>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="eia-secondary-stats">
        <div class="stat-box">
            <span class="stat-label"><?php _e('Cours complétés', 'eia-lms-core'); ?></span>
            <span class="stat-value"><?php echo number_format($stats['completed_courses']); ?></span>
        </div>

        <div class="stat-box">
            <span class="stat-label"><?php _e('Taux de complétion moyen', 'eia-lms-core'); ?></span>
            <span class="stat-value"><?php echo $stats['avg_completion_rate']; ?>%</span>
        </div>
    </div>

    <!-- Popular Courses -->
    <div class="eia-dashboard-section">
        <h2><?php _e('Cours Populaires', 'eia-lms-core'); ?></h2>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Cours', 'eia-lms-core'); ?></th>
                    <th><?php _e('Inscriptions', 'eia-lms-core'); ?></th>
                    <th><?php _e('Actions', 'eia-lms-core'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($stats['popular_courses'])) : ?>
                    <?php foreach ($stats['popular_courses'] as $course) : ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo get_edit_post_link($course->ID); ?>">
                                        <?php echo esc_html($course->post_title); ?>
                                    </a>
                                </strong>
                            </td>
                            <td><?php echo number_format($course->enrollment_count); ?></td>
                            <td>
                                <a href="<?php echo get_permalink($course->ID); ?>" class="button button-small" target="_blank">
                                    <?php _e('Voir', 'eia-lms-core'); ?>
                                </a>
                                <a href="<?php echo get_edit_post_link($course->ID); ?>" class="button button-small">
                                    <?php _e('Modifier', 'eia-lms-core'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 40px;">
                            <?php _e('Aucun cours disponible', 'eia-lms-core'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div class="eia-quick-actions">
        <h2><?php _e('Actions Rapides', 'eia-lms-core'); ?></h2>

        <div class="quick-actions-grid">
            <a href="<?php echo admin_url('post-new.php?post_type=lp_course'); ?>" class="quick-action-btn">
                <i class="dashicons dashicons-plus-alt"></i>
                <?php _e('Créer un Cours', 'eia-lms-core'); ?>
            </a>

            <a href="<?php echo admin_url('admin.php?page=eia-lms-reports'); ?>" class="quick-action-btn">
                <i class="dashicons dashicons-chart-bar"></i>
                <?php _e('Voir Rapports', 'eia-lms-core'); ?>
            </a>

            <a href="<?php echo admin_url('users.php?role=student'); ?>" class="quick-action-btn">
                <i class="dashicons dashicons-groups"></i>
                <?php _e('Gérer Étudiants', 'eia-lms-core'); ?>
            </a>

            <a href="<?php echo admin_url('admin.php?page=eia-lms-settings'); ?>" class="quick-action-btn">
                <i class="dashicons dashicons-admin-settings"></i>
                <?php _e('Paramètres', 'eia-lms-core'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.eia-admin-dashboard {
    margin: 20px 20px 0 0;
}

.eia-admin-dashboard h1 {
    font-size: 28px;
    margin-bottom: 20px;
}

.eia-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.eia-stat-card {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.stat-icon .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
}

.stat-content h3 {
    margin: 0;
    font-size: 32px;
    font-weight: bold;
    color: #1e293b;
}

.stat-content p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 14px;
}

.eia-secondary-stats {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.stat-box {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.stat-label {
    color: #64748b;
    font-size: 14px;
}

.stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #1e293b;
}

.eia-dashboard-section {
    background: #fff;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 30px 0;
}

.eia-dashboard-section h2 {
    margin-top: 0;
    font-size: 20px;
    color: #1e293b;
}

.eia-quick-actions {
    background: #fff;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 30px 0;
}

.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    color: #1e293b;
    font-weight: 600;
    transition: all 0.2s;
}

.quick-action-btn:hover {
    background: #2D4FB3;
    color: #fff;
    border-color: #2D4FB3;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(45, 79, 179, 0.2);
}

.quick-action-btn .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}
</style>