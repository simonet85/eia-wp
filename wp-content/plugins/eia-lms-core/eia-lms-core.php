<?php
/**
 * Plugin Name: EIA LMS Core
 * Plugin URI: https://eia.sn
 * Description: Module LMS avancé pour l'École Internationale des Affaires - Course Builder, Quiz étendu, Gradebook, Rapports
 * Version: 1.0.0
 * Author: Créativ'In
 * Author URI: https://creativin.sn
 * Text Domain: eia-lms-core
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EIA_LMS_CORE_VERSION', '1.0.0');
define('EIA_LMS_CORE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EIA_LMS_CORE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EIA_LMS_CORE_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main EIA LMS Core Class
 */
class EIA_LMS_Core {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Get instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation/Deactivation
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Initialize plugin
        add_action('plugins_loaded', array($this, 'init'));

        // Load textdomain
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Enqueue scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));

        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    /**
     * Include required files
     */
    private function includes() {
        // Core classes
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-roles-capabilities.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-course-builder.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-quiz-extended.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-gradebook.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-reports.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-seeder.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-assignments.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-notifications.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-gamification.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-certificates.php';
        require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-forum.php';
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Check if LearnPress is active
        if (!class_exists('LearnPress')) {
            add_action('admin_notices', array($this, 'learnpress_missing_notice'));
            return;
        }

        // Initialize classes
        EIA_Course_Builder::get_instance();
        EIA_Quiz_Extended::get_instance();
        EIA_Gradebook::get_instance();
        EIA_Reports::get_instance();
        EIA_Seeder::get_instance();
        EIA_Assignments::get_instance();
        EIA_Notifications::get_instance();
        EIA_Gamification::get_instance();
        EIA_Certificates::get_instance();

        do_action('eia_lms_core_loaded');
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'eia-lms-core',
            false,
            dirname(EIA_LMS_CORE_PLUGIN_BASENAME) . '/languages/'
        );
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        // Course builder assets
        if (strpos($hook, 'lp_course') !== false || strpos($hook, 'eia-lms-core') !== false) {
            wp_enqueue_style(
                'eia-lms-core-admin',
                EIA_LMS_CORE_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                EIA_LMS_CORE_VERSION
            );

            wp_enqueue_script(
                'eia-lms-core-admin',
                EIA_LMS_CORE_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery', 'jquery-ui-sortable'),
                EIA_LMS_CORE_VERSION,
                true
            );

            wp_localize_script('eia-lms-core-admin', 'eiaLMSCore', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eia-lms-core-nonce'),
                'strings' => array(
                    'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer cet élément ?', 'eia-lms-core'),
                    'saving' => __('Enregistrement...', 'eia-lms-core'),
                    'saved' => __('Enregistré !', 'eia-lms-core'),
                    'error' => __('Erreur lors de l\'enregistrement', 'eia-lms-core'),
                ),
            ));
        }
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function frontend_enqueue_scripts() {
        // Only load on course pages
        if (is_singular('lp_course') || is_singular('lp_quiz') || is_singular('lp_lesson')) {
            wp_enqueue_style(
                'eia-lms-core-frontend',
                EIA_LMS_CORE_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                EIA_LMS_CORE_VERSION
            );

            wp_enqueue_script(
                'eia-lms-core-frontend',
                EIA_LMS_CORE_PLUGIN_URL . 'assets/js/frontend.js',
                array('jquery'),
                EIA_LMS_CORE_VERSION,
                true
            );

            wp_localize_script('eia-lms-core-frontend', 'eiaLMSCore', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('eia-lms-core-nonce'),
            ));
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('EIA LMS Core', 'eia-lms-core'),
            __('EIA LMS', 'eia-lms-core'),
            'manage_options',
            'eia-lms-core',
            array($this, 'render_admin_page'),
            'dashicons-graduation-cap',
            30
        );

        // Submenu: Dashboard
        add_submenu_page(
            'eia-lms-core',
            __('Tableau de bord', 'eia-lms-core'),
            __('Tableau de bord', 'eia-lms-core'),
            'manage_options',
            'eia-lms-core',
            array($this, 'render_admin_page')
        );

        // Submenu: Roles & Permissions
        add_submenu_page(
            'eia-lms-core',
            __('Rôles et Permissions', 'eia-lms-core'),
            __('Rôles et Permissions', 'eia-lms-core'),
            'manage_options',
            'eia-lms-roles',
            array($this, 'render_roles_page')
        );

        // Submenu: Reports
        add_submenu_page(
            'eia-lms-core',
            __('Rapports', 'eia-lms-core'),
            __('Rapports', 'eia-lms-core'),
            'manage_options',
            'eia-lms-reports',
            array($this, 'render_reports_page')
        );

        // Submenu: Settings
        add_submenu_page(
            'eia-lms-core',
            __('Paramètres', 'eia-lms-core'),
            __('Paramètres', 'eia-lms-core'),
            'manage_options',
            'eia-lms-settings',
            array($this, 'render_settings_page')
        );
    }

    /**
     * Render admin page
     */
    public function render_admin_page() {
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/admin-dashboard.php';
    }

    /**
     * Render roles and permissions page
     */
    public function render_roles_page() {
        include EIA_LMS_CORE_PLUGIN_DIR . 'includes/admin/page-roles-permissions.php';
    }

    /**
     * Render reports page
     */
    public function render_reports_page() {
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/admin-reports.php';
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        include EIA_LMS_CORE_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * LearnPress missing notice
     */
    public function learnpress_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php _e('EIA LMS Core', 'eia-lms-core'); ?>:</strong>
                <?php _e('LearnPress doit être installé et activé pour utiliser ce plugin.', 'eia-lms-core'); ?>
                <a href="<?php echo admin_url('plugin-install.php?s=learnpress&tab=search&type=term'); ?>">
                    <?php _e('Installer LearnPress', 'eia-lms-core'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Create necessary database tables
        $this->create_tables();

        // Set default options
        $this->set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log activation
        error_log('EIA LMS Core Plugin Activated');
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();

        // Log deactivation
        error_log('EIA LMS Core Plugin Deactivated');
    }

    /**
     * Create database tables
     */
    private function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Table for gradebook entries
        $table_name = $wpdb->prefix . 'eia_gradebook';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            item_id bigint(20) NOT NULL,
            item_type varchar(50) NOT NULL,
            grade float NOT NULL,
            max_grade float NOT NULL,
            graded_by bigint(20) NOT NULL,
            graded_date datetime NOT NULL,
            notes text,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY item_id (item_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Table for course analytics
        $table_name = $wpdb->prefix . 'eia_course_analytics';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            course_id bigint(20) NOT NULL,
            lesson_id bigint(20) DEFAULT NULL,
            event_type varchar(50) NOT NULL,
            event_data text,
            event_date datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY course_id (course_id),
            KEY event_type (event_type)
        ) $charset_collate;";

        dbDelta($sql);

        // Create assignments table
        EIA_Assignments::create_table();
    }

    /**
     * Set default options
     */
    private function set_default_options() {
        $defaults = array(
            'eia_lms_core_version' => EIA_LMS_CORE_VERSION,
            'eia_lms_enable_course_builder' => 'yes',
            'eia_lms_enable_gradebook' => 'yes',
            'eia_lms_enable_reports' => 'yes',
            'eia_lms_passing_grade' => 70,
        );

        foreach ($defaults as $key => $value) {
            if (get_option($key) === false) {
                add_option($key, $value);
            }
        }
    }
}

/**
 * Initialize plugin
 */
function eia_lms_core() {
    return EIA_LMS_Core::get_instance();
}

// Start the plugin
eia_lms_core();
?>