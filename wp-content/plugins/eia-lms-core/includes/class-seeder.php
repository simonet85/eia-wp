<?php
/**
 * Seeder Class - Generate demo data
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class EIA_Seeder {

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
        // Hook for admin page
        add_action('admin_menu', array($this, 'add_seeder_page'));

        // AJAX handlers
        add_action('wp_ajax_eia_run_seeder', array($this, 'ajax_run_seeder'));
        add_action('wp_ajax_eia_clear_demo_data', array($this, 'ajax_clear_demo_data'));
    }

    /**
     * Add seeder admin page
     */
    public function add_seeder_page() {
        add_submenu_page(
            'eia-lms-core',
            __('Installateur / Seeder', 'eia-lms-core'),
            __('Seeder', 'eia-lms-core'),
            'manage_options',
            'eia-lms-seeder',
            array($this, 'render_seeder_page')
        );
    }

    /**
     * Render seeder page
     */
    public function render_seeder_page() {
        ?>
        <div class="wrap eia-seeder-page">
            <h1 class="wp-heading-inline">
                <i class="dashicons dashicons-database-add"></i>
                <?php _e('Installateur / Seeder de Données', 'eia-lms-core'); ?>
            </h1>

            <hr class="wp-header-end">

            <div class="seeder-container">
                <div class="seeder-notice">
                    <p><strong>⚠️ Attention:</strong> <?php _e('Cette fonctionnalité crée des données de démonstration pour tester la plateforme.', 'eia-lms-core'); ?></p>
                </div>

                <!-- Seeder Options -->
                <div class="seeder-options">
                    <h2><?php _e('Options de génération', 'eia-lms-core'); ?></h2>

                    <form id="seeder-form">
                        <?php wp_nonce_field('eia_seeder_nonce', 'eia_seeder_nonce'); ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label><?php _e('Formateurs', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="seed_instructors" id="seed_instructors" value="1" checked>
                                    <label for="seed_instructors"><?php _e('Créer des formateurs', 'eia-lms-core'); ?></label>
                                    <br>
                                    <input type="number" name="instructors_count" value="5" min="1" max="20" class="small-text">
                                    <span class="description"><?php _e('Nombre de formateurs (1-20)', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label><?php _e('Étudiants', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="seed_students" id="seed_students" value="1" checked>
                                    <label for="seed_students"><?php _e('Créer des étudiants', 'eia-lms-core'); ?></label>
                                    <br>
                                    <input type="number" name="students_count" value="20" min="1" max="100" class="small-text">
                                    <span class="description"><?php _e('Nombre d\'étudiants (1-100)', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label><?php _e('Cours', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="seed_courses" id="seed_courses" value="1" checked>
                                    <label for="seed_courses"><?php _e('Créer des cours', 'eia-lms-core'); ?></label>
                                    <br>
                                    <input type="number" name="courses_count" value="10" min="1" max="50" class="small-text">
                                    <span class="description"><?php _e('Nombre de cours (1-50)', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label><?php _e('Leçons par cours', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="number" name="lessons_per_course" value="5" min="1" max="20" class="small-text">
                                    <span class="description"><?php _e('Nombre de leçons par cours (1-20)', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label><?php _e('Quiz par cours', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="number" name="quizzes_per_course" value="2" min="0" max="10" class="small-text">
                                    <span class="description"><?php _e('Nombre de quiz par cours (0-10)', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label><?php _e('Inscriptions', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="seed_enrollments" id="seed_enrollments" value="1" checked>
                                    <label for="seed_enrollments"><?php _e('Inscrire des étudiants aux cours', 'eia-lms-core'); ?></label>
                                    <br>
                                    <span class="description"><?php _e('Chaque étudiant sera inscrit à 2-5 cours aléatoires', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>

                            <tr>
                                <th scope="row">
                                    <label><?php _e('Progrès', 'eia-lms-core'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="seed_progress" id="seed_progress" value="1" checked>
                                    <label for="seed_progress"><?php _e('Générer des progrès aléatoires', 'eia-lms-core'); ?></label>
                                    <br>
                                    <span class="description"><?php _e('Les étudiants auront des progrès variés (0-100%)', 'eia-lms-core'); ?></span>
                                </td>
                            </tr>
                        </table>

                        <p class="submit">
                            <button type="submit" class="button button-primary button-hero" id="run-seeder">
                                <i class="dashicons dashicons-database-add"></i>
                                <?php _e('Lancer le Seeder', 'eia-lms-core'); ?>
                            </button>
                        </p>
                    </form>
                </div>

                <!-- Progress Display -->
                <div id="seeder-progress" style="display: none;">
                    <h2><?php _e('Génération en cours...', 'eia-lms-core'); ?></h2>
                    <div class="seeder-progress-bar">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <p class="progress-text">0%</p>
                    <div class="seeder-log"></div>
                </div>

                <!-- Clear Data -->
                <div class="seeder-danger-zone">
                    <h2><?php _e('Zone Dangereuse', 'eia-lms-core'); ?></h2>
                    <p><?php _e('Supprimer toutes les données de démonstration générées par le seeder.', 'eia-lms-core'); ?></p>
                    <button class="button button-secondary" id="clear-demo-data">
                        <i class="dashicons dashicons-trash"></i>
                        <?php _e('Supprimer les données démo', 'eia-lms-core'); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
        .eia-seeder-page {
            margin: 20px 20px 0 0;
        }

        .seeder-container {
            max-width: 900px;
        }

        .seeder-notice {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .seeder-options {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .seeder-options h2 {
            margin-top: 0;
        }

        #seeder-progress {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 20px 0;
        }

        .seeder-progress-bar {
            width: 100%;
            height: 30px;
            background: #e0e0e0;
            border-radius: 15px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-fill {
            height: 100%;
            background: #2D4FB3;
            transition: width 0.3s;
        }

        .progress-text {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #2D4FB3;
        }

        .seeder-log {
            max-height: 400px;
            overflow-y: auto;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            margin-top: 20px;
        }

        .seeder-log p {
            margin: 5px 0;
            padding: 5px;
            border-left: 3px solid #2D4FB3;
            padding-left: 10px;
        }

        .seeder-log p.success {
            border-left-color: #10B981;
            background: #d1fae5;
        }

        .seeder-log p.error {
            border-left-color: #EF4444;
            background: #fee2e2;
        }

        .seeder-danger-zone {
            background: #fee2e2;
            border: 2px solid #EF4444;
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
        }

        .seeder-danger-zone h2 {
            margin-top: 0;
            color: #991b1b;
        }

        .button-hero {
            font-size: 16px !important;
            height: auto !important;
            padding: 12px 30px !important;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            $('#run-seeder').on('click', function(e) {
                e.preventDefault();

                if (!confirm('<?php _e('Êtes-vous sûr de vouloir générer des données de démonstration ?', 'eia-lms-core'); ?>')) {
                    return;
                }

                const formData = {
                    action: 'eia_run_seeder',
                    nonce: $('#eia_seeder_nonce').val(),
                    seed_instructors: $('#seed_instructors').is(':checked') ? 1 : 0,
                    instructors_count: $('input[name="instructors_count"]').val(),
                    seed_students: $('#seed_students').is(':checked') ? 1 : 0,
                    students_count: $('input[name="students_count"]').val(),
                    seed_courses: $('#seed_courses').is(':checked') ? 1 : 0,
                    courses_count: $('input[name="courses_count"]').val(),
                    lessons_per_course: $('input[name="lessons_per_course"]').val(),
                    quizzes_per_course: $('input[name="quizzes_per_course"]').val(),
                    seed_enrollments: $('#seed_enrollments').is(':checked') ? 1 : 0,
                    seed_progress: $('#seed_progress').is(':checked') ? 1 : 0
                };

                $('#seeder-form').hide();
                $('#seeder-progress').show();
                $('.seeder-log').empty();

                $.post(ajaxurl, formData, function(response) {
                    if (response.success) {
                        updateProgress(100, response.data.logs);

                        setTimeout(function() {
                            alert('<?php _e('Données générées avec succès !', 'eia-lms-core'); ?>');
                            location.reload();
                        }, 1000);
                    } else {
                        alert('Erreur: ' + response.data.message);
                        $('#seeder-form').show();
                        $('#seeder-progress').hide();
                    }
                });
            });

            $('#clear-demo-data').on('click', function() {
                if (!confirm('<?php _e('⚠️ ATTENTION: Cette action est irréversible ! Supprimer toutes les données de démonstration ?', 'eia-lms-core'); ?>')) {
                    return;
                }

                $.post(ajaxurl, {
                    action: 'eia_clear_demo_data',
                    nonce: $('#eia_seeder_nonce').val()
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Erreur: ' + response.data.message);
                    }
                });
            });

            function updateProgress(percent, logs) {
                $('.progress-fill').css('width', percent + '%');
                $('.progress-text').text(percent + '%');

                if (logs && logs.length > 0) {
                    logs.forEach(function(log) {
                        const className = log.type || 'success';
                        $('.seeder-log').append('<p class="' + className + '">' + log.message + '</p>');
                    });

                    // Scroll to bottom
                    $('.seeder-log').scrollTop($('.seeder-log')[0].scrollHeight);
                }
            }
        });
        </script>
        <?php
    }

    /**
     * AJAX: Run seeder
     */
    public function ajax_run_seeder() {
        check_ajax_referer('eia_seeder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        $options = array(
            'seed_instructors' => isset($_POST['seed_instructors']) ? intval($_POST['seed_instructors']) : 0,
            'instructors_count' => isset($_POST['instructors_count']) ? intval($_POST['instructors_count']) : 5,
            'seed_students' => isset($_POST['seed_students']) ? intval($_POST['seed_students']) : 0,
            'students_count' => isset($_POST['students_count']) ? intval($_POST['students_count']) : 20,
            'seed_courses' => isset($_POST['seed_courses']) ? intval($_POST['seed_courses']) : 0,
            'courses_count' => isset($_POST['courses_count']) ? intval($_POST['courses_count']) : 10,
            'lessons_per_course' => isset($_POST['lessons_per_course']) ? intval($_POST['lessons_per_course']) : 5,
            'quizzes_per_course' => isset($_POST['quizzes_per_course']) ? intval($_POST['quizzes_per_course']) : 2,
            'seed_enrollments' => isset($_POST['seed_enrollments']) ? intval($_POST['seed_enrollments']) : 0,
            'seed_progress' => isset($_POST['seed_progress']) ? intval($_POST['seed_progress']) : 0,
        );

        $logs = array();

        try {
            // Create instructors
            $instructor_ids = array();
            if ($options['seed_instructors']) {
                $instructor_ids = $this->create_instructors($options['instructors_count'], $logs);
            }

            // Create students
            $student_ids = array();
            if ($options['seed_students']) {
                $student_ids = $this->create_students($options['students_count'], $logs);
            }

            // Create courses
            $course_ids = array();
            if ($options['seed_courses']) {
                $course_ids = $this->create_courses(
                    $options['courses_count'],
                    $instructor_ids,
                    $options['lessons_per_course'],
                    $options['quizzes_per_course'],
                    $logs
                );
            }

            // Create enrollments
            if ($options['seed_enrollments'] && !empty($student_ids) && !empty($course_ids)) {
                $this->create_enrollments($student_ids, $course_ids, $options['seed_progress'], $logs);
            }

            wp_send_json_success(array(
                'message' => __('Données générées avec succès !', 'eia-lms-core'),
                'logs' => $logs
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage(),
                'logs' => $logs
            ));
        }
    }

    /**
     * Create instructors
     */
    private function create_instructors($count, &$logs) {
        $instructor_ids = array();

        $first_names = array('Jean', 'Marie', 'Pierre', 'Sophie', 'Amadou', 'Fatou', 'Moussa', 'Aïssa', 'Ibrahima', 'Khady');
        $last_names = array('Diop', 'Ndiaye', 'Fall', 'Sarr', 'Sow', 'Gueye', 'Ba', 'Sy', 'Cissé', 'Diouf');

        for ($i = 1; $i <= $count; $i++) {
            $first_name = $first_names[array_rand($first_names)];
            $last_name = $last_names[array_rand($last_names)];
            $username = 'formateur_' . strtolower($first_name) . '_' . $i;
            $email = $username . '@eia-demo.sn';

            $user_id = wp_create_user($username, 'password123', $email);

            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('instructor');

                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $first_name . ' ' . $last_name,
                ));

                // Add meta for demo identification
                update_user_meta($user_id, '_eia_demo_data', '1');

                $instructor_ids[] = $user_id;

                $logs[] = array(
                    'type' => 'success',
                    'message' => sprintf('✓ Formateur créé: %s (%s)', $first_name . ' ' . $last_name, $email)
                );
            } else {
                $logs[] = array(
                    'type' => 'error',
                    'message' => sprintf('✗ Erreur création formateur: %s', $user_id->get_error_message())
                );
            }
        }

        return $instructor_ids;
    }

    /**
     * Create students
     */
    private function create_students($count, &$logs) {
        $student_ids = array();

        $first_names = array('Ousmane', 'Astou', 'Cheikh', 'Bineta', 'Mamadou', 'Awa', 'Abdou', 'Marieme', 'Lamine', 'Coumba');
        $last_names = array('Diop', 'Ndiaye', 'Fall', 'Sarr', 'Sow', 'Gueye', 'Ba', 'Sy', 'Cissé', 'Diouf');

        for ($i = 1; $i <= $count; $i++) {
            $first_name = $first_names[array_rand($first_names)];
            $last_name = $last_names[array_rand($last_names)];
            $username = 'etudiant_' . strtolower($first_name) . '_' . $i;
            $email = $username . '@eia-demo.sn';

            $user_id = wp_create_user($username, 'password123', $email);

            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('student');

                wp_update_user(array(
                    'ID' => $user_id,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'display_name' => $first_name . ' ' . $last_name,
                ));

                update_user_meta($user_id, '_eia_demo_data', '1');

                $student_ids[] = $user_id;

                $logs[] = array(
                    'type' => 'success',
                    'message' => sprintf('✓ Étudiant créé: %s (%s)', $first_name . ' ' . $last_name, $email)
                );
            }
        }

        return $student_ids;
    }

    /**
     * Create courses
     */
    private function create_courses($count, $instructor_ids, $lessons_per_course, $quizzes_per_course, &$logs) {
        $course_ids = array();

        $course_topics = array(
            'Management',
            'Marketing Digital',
            'Comptabilité',
            'Finance',
            'Ressources Humaines',
            'Entrepreneuriat',
            'Commerce International',
            'Gestion de Projet',
            'Leadership',
            'Communication'
        );

        $levels = array('Débutant', 'Intermédiaire', 'Avancé');

        for ($i = 1; $i <= $count; $i++) {
            $topic = $course_topics[array_rand($course_topics)];
            $level = $levels[array_rand($levels)];
            $title = $topic . ' ' . $level;

            $course_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => 'Ce cours couvre les fondamentaux du ' . $topic . ' au niveau ' . $level . '. Vous apprendrez les concepts essentiels et les meilleures pratiques de l\'industrie.',
                'post_status' => 'publish',
                'post_type' => 'lp_course',
                'post_author' => !empty($instructor_ids) ? $instructor_ids[array_rand($instructor_ids)] : 1,
            ));

            if (!is_wp_error($course_id)) {
                update_post_meta($course_id, '_eia_demo_data', '1');
                update_post_meta($course_id, '_lp_duration', rand(20, 100));
                update_post_meta($course_id, '_lp_max_students', rand(20, 50));

                // Create sections with lessons and quizzes
                $this->create_course_curriculum($course_id, $lessons_per_course, $quizzes_per_course, $logs);

                $course_ids[] = $course_id;

                $logs[] = array(
                    'type' => 'success',
                    'message' => sprintf('✓ Cours créé: %s (ID: %d)', $title, $course_id)
                );
            }
        }

        return $course_ids;
    }

    /**
     * Create course curriculum with sections using LearnPress tables
     */
    private function create_course_curriculum($course_id, $total_lessons, $total_quizzes, &$logs) {
        global $wpdb;

        // Define section templates
        $section_templates = array(
            array('title' => 'Introduction', 'lessons' => array('Bienvenue', 'Présentation du cours', 'Objectifs d\'apprentissage')),
            array('title' => 'Les Fondamentaux', 'lessons' => array('Concepts de base', 'Terminologie', 'Principes clés', 'Études de cas')),
            array('title' => 'Mise en pratique', 'lessons' => array('Exercices pratiques', 'Projet guidé', 'Analyse de scénarios')),
            array('title' => 'Techniques avancées', 'lessons' => array('Stratégies avancées', 'Outils professionnels', 'Best practices')),
            array('title' => 'Conclusion', 'lessons' => array('Récapitulatif', 'Ressources complémentaires', 'Prochaines étapes'))
        );

        // Calculate how many sections to create (3-4 sections per course)
        $num_sections = min(rand(3, 4), count($section_templates));

        $sections_table = $wpdb->prefix . 'learnpress_sections';
        $section_items_table = $wpdb->prefix . 'learnpress_section_items';

        for ($section_num = 0; $section_num < $num_sections; $section_num++) {
            $template = $section_templates[$section_num];

            // Insert section into LearnPress table
            $wpdb->insert(
                $sections_table,
                array(
                    'section_name' => $template['title'],
                    'section_course_id' => $course_id,
                    'section_order' => $section_num + 1,
                    'section_description' => ''
                ),
                array('%s', '%d', '%d', '%s')
            );

            $section_id = $wpdb->insert_id;

            if ($section_id) {
                $item_order = 1;

                // Create lessons for this section
                $lessons_in_section = count($template['lessons']);
                for ($lesson_num = 0; $lesson_num < $lessons_in_section; $lesson_num++) {
                    $lesson_title = $template['lessons'][$lesson_num];
                    $duration = rand(3, 15); // 3-15 minutes

                    // Create lesson post
                    $lesson_id = wp_insert_post(array(
                        'post_title' => $lesson_title,
                        'post_content' => 'Contenu de la leçon: ' . $lesson_title . '. Ce module couvre les aspects essentiels du sujet.',
                        'post_status' => 'publish',
                        'post_type' => 'lp_lesson',
                    ));

                    if (!is_wp_error($lesson_id)) {
                        update_post_meta($lesson_id, '_lp_course', $course_id);
                        update_post_meta($lesson_id, '_lp_duration', $duration);
                        update_post_meta($lesson_id, '_eia_demo_data', '1');

                        // Add to section_items table
                        $wpdb->insert(
                            $section_items_table,
                            array(
                                'section_id' => $section_id,
                                'item_id' => $lesson_id,
                                'item_order' => $item_order++,
                                'item_type' => 'lp_lesson'
                            ),
                            array('%d', '%d', '%d', '%s')
                        );
                    }
                }

                // Add quiz at end of section (except for intro section)
                if ($section_num > 0 && $total_quizzes > 0) {
                    $quiz_id = wp_insert_post(array(
                        'post_title' => 'Quiz: ' . $template['title'],
                        'post_content' => 'Évaluez vos connaissances sur ' . $template['title'] . '.',
                        'post_status' => 'publish',
                        'post_type' => 'lp_quiz',
                    ));

                    if (!is_wp_error($quiz_id)) {
                        update_post_meta($quiz_id, '_lp_course', $course_id);
                        update_post_meta($quiz_id, '_lp_duration', 10);
                        update_post_meta($quiz_id, '_lp_passing_grade', 70);
                        update_post_meta($quiz_id, '_eia_demo_data', '1');

                        // Add quiz to section_items table
                        $wpdb->insert(
                            $section_items_table,
                            array(
                                'section_id' => $section_id,
                                'item_id' => $quiz_id,
                                'item_order' => $item_order++,
                                'item_type' => 'lp_quiz'
                            ),
                            array('%d', '%d', '%d', '%s')
                        );
                    }
                }
            }
        }

        $logs[] = array(
            'type' => 'success',
            'message' => sprintf('  → %d sections créées dans les tables LearnPress', $num_sections)
        );
    }

    /**
     * Create lessons (legacy - kept for compatibility)
     */
    private function create_lessons($course_id, $count, &$logs) {
        for ($i = 1; $i <= $count; $i++) {
            $lesson_id = wp_insert_post(array(
                'post_title' => 'Leçon ' . $i,
                'post_content' => 'Contenu de la leçon ' . $i . '.',
                'post_status' => 'publish',
                'post_type' => 'lp_lesson',
            ));

            if (!is_wp_error($lesson_id)) {
                update_post_meta($lesson_id, '_lp_course', $course_id);
                update_post_meta($lesson_id, '_eia_demo_data', '1');
            }
        }
    }

    /**
     * Create quizzes
     */
    private function create_quizzes($course_id, $count, &$logs) {
        for ($i = 1; $i <= $count; $i++) {
            $quiz_id = wp_insert_post(array(
                'post_title' => 'Quiz ' . $i,
                'post_content' => 'Quiz d\'évaluation ' . $i . '.',
                'post_status' => 'publish',
                'post_type' => 'lp_quiz',
            ));

            if (!is_wp_error($quiz_id)) {
                update_post_meta($quiz_id, '_lp_course', $course_id);
                update_post_meta($quiz_id, '_eia_demo_data', '1');
                update_post_meta($quiz_id, '_lp_passing_grade', 70);
            }
        }
    }

    /**
     * Create enrollments
     */
    private function create_enrollments($student_ids, $course_ids, $with_progress, &$logs) {
        global $wpdb;

        foreach ($student_ids as $student_id) {
            // Each student enrolls in 2-5 random courses
            $enrollment_count = rand(2, min(5, count($course_ids)));
            $enrolled_courses = (array) array_rand(array_flip($course_ids), $enrollment_count);

            foreach ($enrolled_courses as $course_id) {
                // Insert enrollment into LearnPress table
                $wpdb->insert(
                    $wpdb->prefix . 'learnpress_user_items',
                    array(
                        'user_id' => $student_id,
                        'item_id' => $course_id,
                        'item_type' => 'lp_course',
                        'status' => 'enrolled',
                        'start_time' => current_time('mysql'),
                        'graduation' => 'in-progress',
                    ),
                    array('%d', '%d', '%s', '%s', '%s', '%s')
                );

                if ($with_progress) {
                    // Simulate progress (0-100%)
                    $progress = rand(0, 100);

                    // Update progress in LearnPress user item
                    $item_id = $wpdb->insert_id;
                    if ($item_id) {
                        $wpdb->update(
                            $wpdb->prefix . 'learnpress_user_items',
                            array('graduation' => $progress >= 70 ? 'passed' : 'in-progress'),
                            array('user_item_id' => $item_id),
                            array('%s'),
                            array('%d')
                        );
                    }
                }
            }

            $logs[] = array(
                'type' => 'success',
                'message' => sprintf('✓ Étudiant ID %d inscrit à %d cours', $student_id, $enrollment_count)
            );
        }
    }

    /**
     * AJAX: Clear demo data
     */
    public function ajax_clear_demo_data() {
        check_ajax_referer('eia_seeder_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'eia-lms-core')));
        }

        global $wpdb;

        // Delete demo users
        $demo_users = get_users(array(
            'meta_key' => '_eia_demo_data',
            'meta_value' => '1',
        ));

        foreach ($demo_users as $user) {
            wp_delete_user($user->ID);
        }

        // Delete demo courses
        $demo_courses = get_posts(array(
            'post_type' => array('lp_course', 'lp_lesson', 'lp_quiz'),
            'posts_per_page' => -1,
            'meta_key' => '_eia_demo_data',
            'meta_value' => '1',
        ));

        foreach ($demo_courses as $post) {
            wp_delete_post($post->ID, true);
        }

        // Delete sections from LearnPress tables BEFORE deleting courses
        $sections_table = $wpdb->prefix . 'learnpress_sections';
        $section_items_table = $wpdb->prefix . 'learnpress_section_items';

        // Get all demo course IDs
        $demo_course_ids = array();
        foreach ($demo_courses as $post) {
            if ($post->post_type === 'lp_course') {
                $demo_course_ids[] = $post->ID;
            }
        }

        if (!empty($demo_course_ids)) {
            // Get section IDs for demo courses
            $course_ids_format = implode(',', array_map('intval', $demo_course_ids));
            $section_ids = $wpdb->get_col(
                "SELECT section_id FROM $sections_table WHERE section_course_id IN ($course_ids_format)"
            );

            if (!empty($section_ids)) {
                $section_ids_format = implode(',', array_map('intval', $section_ids));

                // Delete section items first (foreign key)
                $wpdb->query("DELETE FROM $section_items_table WHERE section_id IN ($section_ids_format)");

                // Then delete sections
                $wpdb->query("DELETE FROM $sections_table WHERE section_id IN ($section_ids_format)");
            }
        }

        // Also clean enrollments from user_items table
        $user_items_table = $wpdb->prefix . 'learnpress_user_items';
        if (!empty($demo_course_ids)) {
            $wpdb->query("DELETE FROM $user_items_table WHERE item_type = 'lp_course' AND item_id IN ($course_ids_format)");
        }

        wp_send_json_success(array(
            'message' => sprintf(
                __('Données supprimées: %d utilisateurs, %d contenus, sections LearnPress nettoyées', 'eia-lms-core'),
                count($demo_users),
                count($demo_courses)
            )
        ));
    }
}
?>