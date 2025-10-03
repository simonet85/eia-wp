<?php
/**
 * Admin Settings Template
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Save settings
if (isset($_POST['eia_lms_save_settings']) && check_admin_referer('eia_lms_settings_nonce', 'eia_lms_settings_nonce')) {
    update_option('eia_lms_enable_course_builder', isset($_POST['enable_course_builder']) ? 'yes' : 'no');
    update_option('eia_lms_enable_gradebook', isset($_POST['enable_gradebook']) ? 'yes' : 'no');
    update_option('eia_lms_enable_reports', isset($_POST['enable_reports']) ? 'yes' : 'no');
    update_option('eia_lms_passing_grade', intval($_POST['passing_grade']));
    update_option('eia_lms_quiz_attempts', intval($_POST['quiz_attempts']));
    update_option('eia_lms_certificate_enabled', isset($_POST['certificate_enabled']) ? 'yes' : 'no');
    update_option('eia_lms_email_notifications', isset($_POST['email_notifications']) ? 'yes' : 'no');

    echo '<div class="notice notice-success"><p>' . __('Paramètres enregistrés avec succès.', 'eia-lms-core') . '</p></div>';
}

// Get current settings
$enable_course_builder = get_option('eia_lms_enable_course_builder', 'yes');
$enable_gradebook = get_option('eia_lms_enable_gradebook', 'yes');
$enable_reports = get_option('eia_lms_enable_reports', 'yes');
$passing_grade = get_option('eia_lms_passing_grade', 70);
$quiz_attempts = get_option('eia_lms_quiz_attempts', 3);
$certificate_enabled = get_option('eia_lms_certificate_enabled', 'yes');
$email_notifications = get_option('eia_lms_email_notifications', 'yes');
?>

<div class="wrap eia-admin-settings">
    <h1 class="wp-heading-inline">
        <i class="dashicons dashicons-admin-settings"></i>
        <?php _e('Paramètres EIA LMS', 'eia-lms-core'); ?>
    </h1>

    <hr class="wp-header-end">

    <form method="post" action="">
        <?php wp_nonce_field('eia_lms_settings_nonce', 'eia_lms_settings_nonce'); ?>

        <!-- General Settings -->
        <div class="settings-section">
            <h2><?php _e('Paramètres Généraux', 'eia-lms-core'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php _e('Modules Activés', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="enable_course_builder" value="1" <?php checked($enable_course_builder, 'yes'); ?>>
                                <?php _e('Constructeur de cours', 'eia-lms-core'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="enable_gradebook" value="1" <?php checked($enable_gradebook, 'yes'); ?>>
                                <?php _e('Carnet de notes', 'eia-lms-core'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="enable_reports" value="1" <?php checked($enable_reports, 'yes'); ?>>
                                <?php _e('Rapports et analyses', 'eia-lms-core'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="passing_grade"><?php _e('Note de passage (%)', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="passing_grade" name="passing_grade" value="<?php echo esc_attr($passing_grade); ?>" min="0" max="100" class="regular-text">
                        <p class="description"><?php _e('Note minimale requise pour réussir un cours.', 'eia-lms-core'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="quiz_attempts"><?php _e('Tentatives de quiz', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="quiz_attempts" name="quiz_attempts" value="<?php echo esc_attr($quiz_attempts); ?>" min="1" max="10" class="regular-text">
                        <p class="description"><?php _e('Nombre maximum de tentatives autorisées par quiz. 0 = illimité.', 'eia-lms-core'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Certificates Settings -->
        <div class="settings-section">
            <h2><?php _e('Certificats', 'eia-lms-core'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php _e('Certificats', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="certificate_enabled" value="1" <?php checked($certificate_enabled, 'yes'); ?>>
                            <?php _e('Activer les certificats de complétion', 'eia-lms-core'); ?>
                        </label>
                        <p class="description"><?php _e('Les étudiants recevront un certificat après avoir complété un cours.', 'eia-lms-core'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Notification Settings -->
        <div class="settings-section">
            <h2><?php _e('Notifications', 'eia-lms-core'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php _e('Notifications Email', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="email_notifications" value="1" <?php checked($email_notifications, 'yes'); ?>>
                            <?php _e('Activer les notifications par email', 'eia-lms-core'); ?>
                        </label>
                        <p class="description"><?php _e('Envoyer des emails pour les inscriptions, complétions, etc.', 'eia-lms-core'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Advanced Settings -->
        <div class="settings-section">
            <h2><?php _e('Paramètres Avancés', 'eia-lms-core'); ?></h2>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php _e('Version du plugin', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <strong><?php echo EIA_LMS_CORE_VERSION; ?></strong>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label><?php _e('Base de données', 'eia-lms-core'); ?></label>
                    </th>
                    <td>
                        <button type="button" class="button" id="reset-db-tables">
                            <?php _e('Réinitialiser les tables', 'eia-lms-core'); ?>
                        </button>
                        <p class="description"><?php _e('⚠️ Attention: Cette action supprimera toutes les données du plugin.', 'eia-lms-core'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(__('Enregistrer les paramètres', 'eia-lms-core'), 'primary', 'eia_lms_save_settings'); ?>
    </form>

    <!-- System Information -->
    <div class="settings-section">
        <h2><?php _e('Informations Système', 'eia-lms-core'); ?></h2>

        <table class="widefat striped">
            <tbody>
                <tr>
                    <td><strong><?php _e('WordPress Version', 'eia-lms-core'); ?></strong></td>
                    <td><?php echo get_bloginfo('version'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('PHP Version', 'eia-lms-core'); ?></strong></td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('LearnPress Version', 'eia-lms-core'); ?></strong></td>
                    <td><?php echo defined('LEARNPRESS_VERSION') ? LEARNPRESS_VERSION : __('Non installé', 'eia-lms-core'); ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Theme', 'eia-lms-core'); ?></strong></td>
                    <td><?php echo wp_get_theme()->get('Name') . ' ' . wp_get_theme()->get('Version'); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
.eia-admin-settings {
    margin: 20px 20px 0 0;
}

.settings-section {
    background: #fff;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.settings-section h2 {
    margin-top: 0;
    font-size: 20px;
    color: #1e293b;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 10px;
}

.form-table th {
    width: 250px;
    font-weight: 600;
    color: #1e293b;
}

.form-table td label {
    display: inline-block;
    margin-bottom: 8px;
}

.form-table .description {
    color: #64748b;
    font-size: 13px;
    margin-top: 5px;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#reset-db-tables').on('click', function() {
        if (!confirm('<?php _e('Êtes-vous sûr de vouloir réinitialiser toutes les tables ? Cette action est irréversible.', 'eia-lms-core'); ?>')) {
            return;
        }

        $.post(ajaxurl, {
            action: 'eia_reset_db_tables',
            nonce: '<?php echo wp_create_nonce('eia-reset-db'); ?>'
        }, function(response) {
            if (response.success) {
                alert('<?php _e('Tables réinitialisées avec succès.', 'eia-lms-core'); ?>');
                location.reload();
            } else {
                alert('<?php _e('Erreur lors de la réinitialisation.', 'eia-lms-core'); ?>');
            }
        });
    });
});
</script>