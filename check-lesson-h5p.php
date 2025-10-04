<?php
/**
 * Check Lesson H5P Configuration
 * URL: http://eia-wp.test/check-lesson-h5p.php?lesson_id=X
 */

require_once('wp-load.php');

$lesson_id = isset($_GET['lesson_id']) ? intval($_GET['lesson_id']) : 0;

echo "<h2>🔍 Vérification Configuration Leçon</h2>";
echo "<hr>";

if (!$lesson_id) {
    // Show lesson selector
    ?>
    <p>Sélectionnez une leçon à vérifier:</p>
    <form method="get">
        <select name="lesson_id" onchange="this.form.submit()" style="padding: 10px; font-size: 16px;">
            <option value="">-- Choisir une leçon --</option>
            <?php
            $lessons = get_posts(array(
                'post_type' => 'lp_lesson',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ));

            foreach ($lessons as $lesson) {
                echo "<option value='{$lesson->ID}'>{$lesson->post_title}</option>";
            }
            ?>
        </select>
    </form>
    <p><a href="/">← Retour</a></p>
    <?php
    exit;
}

$lesson = get_post($lesson_id);

if (!$lesson || $lesson->post_type !== 'lp_lesson') {
    echo "<p style='color: red;'>❌ Leçon non trouvée.</p>";
    echo "<p><a href='?'>← Retour</a></p>";
    exit;
}

echo "<h3>Leçon: {$lesson->post_title}</h3>";
echo "<p><a href='" . get_permalink($lesson_id) . "' target='_blank'>👁️ Voir en frontend</a> | ";
echo "<a href='/wp-admin/post.php?post={$lesson_id}&action=edit' target='_blank'>📝 Modifier</a></p>";

echo "<hr>";

// Check H5P ID
$h5p_id = get_post_meta($lesson_id, '_lesson_h5p_id', true);
$video_url = get_post_meta($lesson_id, '_lp_lesson_video_url', true);
$duration = get_post_meta($lesson_id, '_lp_lesson_video_duration', true);
$course_id = get_post_meta($lesson_id, '_lp_course', true);

// Status table
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Configuration</th><th>Valeur</th><th>Statut</th></tr>";

// H5P ID
echo "<tr>";
echo "<td><strong>H5P Content ID</strong></td>";
echo "<td>" . ($h5p_id ? "#{$h5p_id}" : "<em>Non défini</em>") . "</td>";

if ($h5p_id) {
    global $wpdb;
    $h5p_exists = $wpdb->get_row($wpdb->prepare(
        "SELECT c.*, l.title as library_title FROM {$wpdb->prefix}h5p_contents c
         LEFT JOIN {$wpdb->prefix}h5p_libraries l ON c.library_id = l.id
         WHERE c.id = %d",
        $h5p_id
    ));

    if ($h5p_exists) {
        echo "<td style='color: green;'>✅ H5P Valide: {$h5p_exists->title} ({$h5p_exists->library_title})</td>";
    } else {
        echo "<td style='color: red;'>❌ H5P #{$h5p_id} non trouvé!</td>";
    }
} else {
    echo "<td style='color: orange;'>⚠️ Pas de H5P lié</td>";
}
echo "</tr>";

// Video URL (fallback)
echo "<tr>";
echo "<td><strong>Video URL (fallback)</strong></td>";
echo "<td>" . ($video_url ? esc_html($video_url) : "<em>Non défini</em>") . "</td>";
echo "<td>" . ($video_url ? "✅ Défini" : "⚠️ Non défini") . "</td>";
echo "</tr>";

// Duration
echo "<tr>";
echo "<td><strong>Durée</strong></td>";
echo "<td>" . ($duration ? esc_html($duration) : "<em>Non défini</em>") . "</td>";
echo "<td>" . ($duration ? "✅ Défini" : "⚠️ Non défini") . "</td>";
echo "</tr>";

// Course
echo "<tr>";
echo "<td><strong>Cours associé</strong></td>";
if ($course_id) {
    $course = get_post($course_id);
    echo "<td><a href='" . get_permalink($course_id) . "' target='_blank'>{$course->post_title}</a></td>";
    echo "<td style='color: green;'>✅ Lié au cours</td>";
} else {
    echo "<td><em>Non lié</em></td>";
    echo "<td style='color: red;'>❌ Pas de cours</td>";
}
echo "</tr>";

echo "</table>";

// Recommendations
echo "<hr>";
echo "<h3>📋 Recommandations</h3>";
echo "<ul>";

if (!$h5p_id && !$video_url) {
    echo "<li style='color: red;'><strong>Aucune vidéo configurée!</strong> Ajoutez un H5P ou une URL vidéo.</li>";
}

if (!$h5p_id) {
    echo "<li>💡 <a href='/link-h5p-to-lesson.php?lesson_id={$lesson_id}'>Lier un H5P à cette leçon →</a></li>";
}

if (!$duration) {
    echo "<li>⏱️ Ajoutez la durée de la vidéo avec le custom field <code>_lp_lesson_video_duration</code> (ex: '15 min')</li>";
}

if (!$course_id) {
    echo "<li style='color: red;'>❌ Cette leçon n'est pas assignée à un cours!</li>";
}

echo "</ul>";

// Test shortcode
echo "<hr>";
echo "<h3>🧪 Test Shortcode</h3>";
if ($h5p_id) {
    echo "<p>Shortcode H5P: <code>[h5p id=\"{$h5p_id}\"]</code></p>";
    echo "<div style='border: 2px solid #e5e7eb; padding: 20px; border-radius: 8px;'>";
    echo do_shortcode("[h5p id=\"{$h5p_id}\"]");
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='?'>← Vérifier une autre leçon</a> | <a href='/'>Accueil</a></p>";
