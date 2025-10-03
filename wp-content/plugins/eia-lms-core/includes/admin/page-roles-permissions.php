<?php
/**
 * Admin page for managing roles and permissions
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['reset_roles']) && check_admin_referer('eia_reset_roles')) {
    EIA_Roles_Capabilities::reset_all_roles();
    echo '<div class="notice notice-success"><p>Les rôles et permissions ont été réinitialisés avec succès!</p></div>';
}
?>

<div class="wrap">
    <h1>
        <span class="dashicons dashicons-admin-users"></span>
        Gestion des Rôles et Permissions
    </h1>

    <div class="card" style="max-width: 1200px;">
        <h2>Résumé des Permissions par Rôle</h2>

        <!-- Students -->
        <div style="margin: 2rem 0; padding: 1.5rem; background: #e8f5e9; border-left: 4px solid #10B981;">
            <h3 style="margin-top: 0; color: #059669;">
                <span class="dashicons dashicons-welcome-learn-more"></span>
                Étudiants (Students)
            </h3>
            <p><strong>Rôle:</strong> <code>student</code></p>
            <p><strong>Couleur Admin Bar:</strong> <span style="background: #10B981; color: white; padding: 2px 8px; border-radius: 3px;">Vert</span></p>

            <h4>Permissions:</h4>
            <ul style="column-count: 2;">
                <li>✅ Lire le contenu</li>
                <li>✅ S'inscrire aux cours</li>
                <li>✅ Voir le contenu des cours</li>
                <li>✅ Compléter les leçons</li>
                <li>✅ Passer les quiz</li>
                <li>✅ Soumettre des devoirs</li>
                <li>✅ Télécharger ses fichiers</li>
                <li>✅ Voir sa propre progression</li>
                <li>✅ Télécharger ses certificats</li>
                <li>✅ Participer aux forums</li>
            </ul>

            <h4>Restrictions:</h4>
            <ul>
                <li>❌ Créer/modifier des cours</li>
                <li>❌ Accéder au backend WordPress</li>
                <li>❌ Voir les données des autres étudiants</li>
            </ul>
        </div>

        <!-- Instructors -->
        <div style="margin: 2rem 0; padding: 1.5rem; background: #fff3cd; border-left: 4px solid #F59E0B;">
            <h3 style="margin-top: 0; color: #D97706;">
                <span class="dashicons dashicons-businessperson"></span>
                Formateurs (Instructors)
            </h3>
            <p><strong>Rôle:</strong> <code>instructor</code></p>
            <p><strong>Couleur Admin Bar:</strong> <span style="background: #F59E0B; color: white; padding: 2px 8px; border-radius: 3px;">Orange</span></p>

            <h4>Permissions Cours:</h4>
            <ul style="column-count: 2;">
                <li>✅ Créer des cours</li>
                <li>✅ Modifier <strong>leurs propres</strong> cours</li>
                <li>✅ Supprimer <strong>leurs propres</strong> cours</li>
                <li>✅ Publier des cours</li>
                <li>✅ Créer/modifier des leçons</li>
                <li>✅ Créer/modifier des quiz</li>
                <li>✅ Créer/modifier des questions</li>
                <li>✅ Télécharger des fichiers</li>
            </ul>

            <h4>Permissions Étudiants:</h4>
            <ul style="column-count: 2;">
                <li>✅ Voir la liste de <strong>leurs</strong> étudiants</li>
                <li>✅ Voir la progression de <strong>leurs</strong> étudiants</li>
                <li>✅ Noter les devoirs</li>
                <li>✅ Envoyer des messages aux étudiants</li>
                <li>✅ Voir les rapports de <strong>leurs</strong> cours</li>
                <li>✅ Exporter les données de <strong>leurs</strong> cours</li>
            </ul>

            <h4>Restrictions:</h4>
            <ul>
                <li>❌ Modifier les cours des <strong>autres</strong> formateurs</li>
                <li>❌ Gérer les utilisateurs (créer/supprimer)</li>
                <li>❌ Installer/activer des plugins</li>
                <li>❌ Modifier les thèmes</li>
                <li>❌ Accéder aux paramètres WordPress</li>
            </ul>
        </div>

        <!-- LMS Managers -->
        <div style="margin: 2rem 0; padding: 1.5rem; background: #dbeafe; border-left: 4px solid #2D4FB3;">
            <h3 style="margin-top: 0; color: #1e3a8a;">
                <span class="dashicons dashicons-admin-generic"></span>
                Gestionnaires LMS (LMS Managers)
            </h3>
            <p><strong>Rôle:</strong> <code>lms_manager</code></p>
            <p><strong>Couleur Admin Bar:</strong> <span style="background: #2D4FB3; color: white; padding: 2px 8px; border-radius: 3px;">Bleu</span></p>

            <h4>Permissions Complètes:</h4>
            <ul style="column-count: 2;">
                <li>✅ Gérer <strong>TOUS</strong> les cours (tous formateurs)</li>
                <li>✅ Gérer <strong>TOUTES</strong> les leçons</li>
                <li>✅ Gérer <strong>TOUS</strong> les quiz</li>
                <li>✅ Gérer <strong>TOUTES</strong> les questions</li>
                <li>✅ Créer/modifier/supprimer des utilisateurs</li>
                <li>✅ Changer les rôles des utilisateurs</li>
                <li>✅ Voir tous les rapports</li>
                <li>✅ Exporter toutes les données</li>
                <li>✅ Voir toutes les statistiques</li>
                <li>✅ Gérer les paramètres LearnPress</li>
                <li>✅ Gérer les catégories</li>
            </ul>

            <h4>Restrictions (sécurité):</h4>
            <ul>
                <li>❌ Installer/modifier/supprimer des plugins</li>
                <li>❌ Installer/modifier/supprimer des thèmes</li>
                <li>❌ Modifier les paramètres core WordPress</li>
            </ul>
        </div>

        <!-- Administrators -->
        <div style="margin: 2rem 0; padding: 1.5rem; background: #fce7f3; border-left: 4px solid #dc2626;">
            <h3 style="margin-top: 0; color: #991b1b;">
                <span class="dashicons dashicons-admin-network"></span>
                Administrateurs (Administrators)
            </h3>
            <p><strong>Rôle:</strong> <code>administrator</code></p>
            <p><strong>Couleur Admin Bar:</strong> <span style="background: #2D4FB3; color: white; padding: 2px 8px; border-radius: 3px;">Bleu (défaut WP)</span></p>

            <h4>Permissions:</h4>
            <ul>
                <li>✅ <strong>TOUTES LES PERMISSIONS</strong> WordPress et LMS</li>
                <li>✅ Gérer les plugins, thèmes, paramètres système</li>
                <li>✅ Accès complet à la base de données</li>
            </ul>
        </div>
    </div>

    <div class="card" style="max-width: 1200px; margin-top: 2rem;">
        <h2>Actions de Gestion</h2>

        <form method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir réinitialiser tous les rôles ? Cette action va réappliquer toutes les permissions.');">
            <?php wp_nonce_field('eia_reset_roles'); ?>
            <p>
                <button type="submit" name="reset_roles" class="button button-secondary">
                    <span class="dashicons dashicons-update"></span>
                    Réinitialiser les Rôles et Permissions
                </button>
            </p>
            <p class="description">
                Cette action va réappliquer toutes les permissions définies pour chaque rôle.
                Utile si vous avez modifié les permissions manuellement avec un autre plugin.
            </p>
        </form>
    </div>

    <div class="card" style="max-width: 1200px; margin-top: 2rem;">
        <h2>Capabilities Techniques</h2>
        <p>Voici les capabilities WordPress utilisées pour chaque rôle:</p>

        <details>
            <summary style="cursor: pointer; font-weight: bold;">Voir les capabilities détaillées</summary>

            <h4>Students</h4>
            <pre style="background: #f5f5f5; padding: 1rem; overflow-x: auto;">
read, upload_files, lp_student, enroll_course, view_course_content,
complete_lesson, take_quiz, submit_assignment, view_own_progress,
download_certificate, bp_moderate
            </pre>

            <h4>Instructors</h4>
            <pre style="background: #f5f5f5; padding: 1rem; overflow-x: auto;">
read, upload_files, edit_posts, delete_posts,
edit_lp_courses, edit_published_lp_courses, publish_lp_courses, delete_lp_courses,
edit_lp_lessons, edit_published_lp_lessons, publish_lp_lessons, delete_lp_lessons,
edit_lp_quizzes, edit_published_lp_quizzes, publish_lp_quizzes, delete_lp_quizzes,
edit_lp_questions, edit_published_lp_questions, publish_lp_questions, delete_lp_questions,
view_students, view_student_progress, grade_assignments, send_messages_to_students,
view_own_course_reports, export_own_course_data
            </pre>

            <h4>LMS Managers</h4>
            <pre style="background: #f5f5f5; padding: 1rem; overflow-x: auto;">
Toutes les capabilities des instructors PLUS:
edit_others_lp_courses, delete_others_lp_courses, edit_others_lp_lessons,
delete_others_lp_lessons, edit_others_lp_quizzes, delete_others_lp_quizzes,
list_users, edit_users, create_users, delete_users, promote_users,
view_all_reports, export_all_data, view_statistics, manage_lp_settings,
manage_categories
            </pre>
        </details>
    </div>
</div>

<style>
    .card h3 .dashicons {
        vertical-align: middle;
        margin-right: 0.5rem;
    }

    .card ul {
        list-style: none;
        padding-left: 0;
    }

    .card ul li {
        padding: 0.3rem 0;
    }

    details summary {
        padding: 1rem;
        background: #f0f0f0;
        margin-bottom: 1rem;
    }

    details[open] summary {
        background: #2D4FB3;
        color: white;
    }
</style>
