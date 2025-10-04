<?php
/**
 * Link H5P Content to LearnPress Lesson
 * URL: http://eia-wp.test/link-h5p-to-lesson.php?lesson_id=X&h5p_id=Y
 */

require_once('wp-load.php');

// Get parameters
$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;
$h5p_id = isset($_GET['h5p_id']) ? intval($_GET['h5p_id']) : 0;

echo "<h2>🔗 Lier H5P à une Leçon LearnPress</h2>";
echo "<hr>";

// If both provided, link them
if ($lesson_id && $h5p_id) {
    $lesson = get_post($lesson_id);

    if (!$lesson || $lesson->post_type !== 'lp_lesson') {
        echo "<p style='color: red;'>❌ Leçon #{$lesson_id} non trouvée ou invalide.</p>";
        echo "<p><a href='?'>← Retour</a></p>";
        exit;
    }

    global $wpdb;
    $h5p_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}h5p_contents WHERE id = %d",
        $h5p_id
    ));

    if (!$h5p_exists) {
        echo "<p style='color: red;'>❌ H5P #{$h5p_id} non trouvé.</p>";
        echo "<p><a href='?'>← Retour</a></p>";
        exit;
    }

    // Update lesson meta
    update_post_meta($lesson_id, '_lesson_h5p_id', $h5p_id);

    echo "<div style='background: #10B981; color: white; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h3 style='margin: 0 0 10px;'>✅ Liaison réussie!</h3>";
    echo "<p style='margin: 0;'>H5P #{$h5p_id} est maintenant lié à la leçon <strong>{$lesson->post_title}</strong></p>";
    echo "</div>";

    echo "<h3>Actions suivantes:</h3>";
    echo "<p><a href='" . get_permalink($lesson_id) . "' target='_blank' style='background: #2D4FB3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>👁️ Voir la leçon en frontend</a></p>";
    echo "<p><a href='/wp-admin/post.php?post={$lesson_id}&action=edit' target='_blank'>📝 Modifier la leçon</a></p>";
    echo "<p><a href='/list-h5p-contents.php'>📋 Liste des H5P</a></p>";
    exit;
}

// Show form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lier H5P à Leçon</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
        }
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
        }
        button {
            background: #2D4FB3;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        button:hover {
            background: #1e3a8a;
        }
        .info {
            background: #eff6ff;
            border-left: 4px solid #2D4FB3;
            padding: 16px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="info">
    <strong>ℹ️ Information</strong>
    <p>Cette page vous permet de lier un contenu H5P (vidéo interactive) à une leçon LearnPress.</p>
</div>

<form method="get">
    <div class="form-group">
        <label>1. Sélectionnez une Leçon LearnPress</label>
        <select name="lesson_id" required>
            <option value="">-- Choisir une leçon --</option>
            <?php
            $lessons = get_posts(array(
                'post_type' => 'lp_lesson',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));

            foreach ($lessons as $lesson) {
                $current_h5p = get_post_meta($lesson->ID, '_lesson_h5p_id', true);
                $linked_text = $current_h5p ? " [H5P: {$current_h5p}]" : '';
                echo "<option value='{$lesson->ID}'>{$lesson->post_title}{$linked_text}</option>";
            }
            ?>
        </select>
    </div>

    <div class="form-group">
        <label>2. Sélectionnez un Contenu H5P</label>
        <select name="h5p_id" required>
            <option value="">-- Choisir un H5P --</option>
            <?php
            global $wpdb;
            $h5p_contents = $wpdb->get_results("
                SELECT c.*, l.title as library_title
                FROM {$wpdb->prefix}h5p_contents c
                LEFT JOIN {$wpdb->prefix}h5p_libraries l ON c.library_id = l.id
                ORDER BY c.id DESC
            ");

            foreach ($h5p_contents as $h5p) {
                echo "<option value='{$h5p->id}'>[#{$h5p->id}] {$h5p->title} ({$h5p->library_title})</option>";
            }
            ?>
        </select>
    </div>

    <button type="submit">🔗 Lier H5P à la Leçon</button>
</form>

<hr style="margin: 40px 0;">

<h3>Actions Rapides</h3>
<p><a href="/list-h5p-contents.php">📋 Voir tous les H5P</a></p>
<p><a href="/wp-admin/admin.php?page=h5p_new" target="_blank">➕ Créer un nouveau H5P</a></p>
<p><a href="/" style="color: #6b7280;">← Retour à l'accueil</a></p>

</body>
</html>
