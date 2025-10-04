<?php
/**
 * Create Lesson Video Tables
 * Run once: http://eia-wp.test/create-lesson-tables.php
 */

require_once('wp-load.php');

global $wpdb;
$charset_collate = $wpdb->get_charset_collate();

echo "<h2>Création des tables pour le lecteur vidéo...</h2>";

// Table: Lesson Notes
$table_notes = $wpdb->prefix . 'eia_lesson_notes';
$sql_notes = "CREATE TABLE IF NOT EXISTS {$table_notes} (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    lesson_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    note_content LONGTEXT,
    video_timestamp VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY user_lesson (user_id, lesson_id),
    KEY course_id (course_id)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql_notes);

if ($wpdb->get_var("SHOW TABLES LIKE '{$table_notes}'") == $table_notes) {
    echo "<p>✅ Table <code>{$table_notes}</code> créée avec succès!</p>";
} else {
    echo "<p>❌ Erreur lors de la création de la table <code>{$table_notes}</code></p>";
}

// Table: Lesson Q&A
$table_qa = $wpdb->prefix . 'eia_lesson_qa';
$sql_qa = "CREATE TABLE IF NOT EXISTS {$table_qa} (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT,
    answered_by BIGINT,
    video_timestamp VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY lesson_id (lesson_id),
    KEY user_id (user_id)
) $charset_collate;";

dbDelta($sql_qa);

if ($wpdb->get_var("SHOW TABLES LIKE '{$table_qa}'") == $table_qa) {
    echo "<p>✅ Table <code>{$table_qa}</code> créée avec succès!</p>";
} else {
    echo "<p>❌ Erreur lors de la création de la table <code>{$table_qa}</code></p>";
}

// Table: Lesson Reviews
$table_reviews = $wpdb->prefix . 'eia_lesson_reviews';
$sql_reviews = "CREATE TABLE IF NOT EXISTS {$table_reviews} (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    rating INT NOT NULL,
    review_text TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY lesson_id (lesson_id),
    KEY user_id (user_id),
    UNIQUE KEY user_lesson (user_id, lesson_id)
) $charset_collate;";

dbDelta($sql_reviews);

if ($wpdb->get_var("SHOW TABLES LIKE '{$table_reviews}'") == $table_reviews) {
    echo "<p>✅ Table <code>{$table_reviews}</code> créée avec succès!</p>";
} else {
    echo "<p>❌ Erreur lors de la création de la table <code>{$table_reviews}</code></p>";
}

echo "<hr>";
echo "<h3>✅ Toutes les tables ont été créées!</h3>";
echo "<p><a href='/'>← Retour à l'accueil</a></p>";
