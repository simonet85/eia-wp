<?php
/**
 * List all H5P Contents
 * URL: http://eia-wp.test/list-h5p-contents.php
 */

require_once('wp-load.php');

if (!class_exists('H5P_Plugin')) {
    die('H5P Plugin not active!');
}

global $wpdb;

echo "<h2>📹 H5P Contents Disponibles</h2>";
echo "<p>Liste de tous les contenus H5P créés</p>";
echo "<hr>";

// Get H5P contents
$h5p_contents = $wpdb->get_results("
    SELECT * FROM {$wpdb->prefix}h5p_contents
    ORDER BY id DESC
");

if (empty($h5p_contents)) {
    echo "<p><strong>Aucun contenu H5P trouvé.</strong></p>";
    echo "<p><a href='/wp-admin/admin.php?page=h5p_new' class='button'>➕ Créer un H5P Interactive Video</a></p>";
    exit;
}

echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #2D4FB3; color: white;'>";
echo "<th>ID</th>";
echo "<th>Titre</th>";
echo "<th>Type</th>";
echo "<th>Créé le</th>";
echo "<th>Actions</th>";
echo "</tr>";

foreach ($h5p_contents as $content) {
    // Get library info
    $library = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}h5p_libraries WHERE id = %d",
        $content->library_id
    ));

    $library_name = $library ? $library->title : 'Unknown';

    // Check if linked to lesson
    $linked_lesson = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_lesson_h5p_id' AND meta_value = %d",
        $content->id
    ));

    $lesson_title = '';
    if ($linked_lesson) {
        $lesson_post = get_post($linked_lesson);
        $lesson_title = $lesson_post ? $lesson_post->post_title : '';
    }

    echo "<tr>";
    echo "<td><strong>{$content->id}</strong></td>";
    echo "<td>{$content->title}</td>";
    echo "<td><span style='background: #10B981; color: white; padding: 4px 8px; border-radius: 4px;'>{$library_name}</span></td>";
    echo "<td>" . date('d/m/Y H:i', strtotime($content->created_at)) . "</td>";
    echo "<td>";

    if ($linked_lesson) {
        echo "✅ Lié à: <strong>{$lesson_title}</strong><br>";
        echo "<a href='" . get_permalink($linked_lesson) . "' target='_blank'>Voir la leçon →</a>";
    } else {
        echo "⚠️ Non lié<br>";
        echo "<a href='/link-h5p-to-lesson.php?h5p_id={$content->id}'>Lier à une leçon →</a>";
    }

    echo "<br><a href='/wp-admin/admin.php?page=h5p&task=show&id={$content->id}' target='_blank'>Modifier H5P</a>";
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<hr>";
echo "<h3>Actions Rapides</h3>";
echo "<p><a href='/wp-admin/admin.php?page=h5p_new' style='background: #2D4FB3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>➕ Créer un nouveau H5P</a></p>";
echo "<p><a href='/wp-admin/edit.php?post_type=lp_lesson'>📝 Gérer les leçons LearnPress</a></p>";
echo "<p><a href='/' style='color: #6b7280;'>← Retour à l'accueil</a></p>";
