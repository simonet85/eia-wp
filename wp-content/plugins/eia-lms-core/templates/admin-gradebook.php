<?php
/**
 * Admin Gradebook Template
 *
 * @package EIA_LMS_Core
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$gradebook = EIA_Gradebook::get_instance();
?>

<div class="wrap eia-admin-gradebook">
    <h1 class="wp-heading-inline">
        <i class="dashicons dashicons-book-alt"></i>
        <?php _e('Carnet de Notes Global', 'eia-lms-core'); ?>
    </h1>

    <hr class="wp-header-end">

    <!-- Course Filter -->
    <div class="gradebook-filters">
        <label for="filter-course"><?php _e('Filtrer par cours', 'eia-lms-core'); ?></label>
        <select id="filter-course" class="regular-text">
            <option value=""><?php _e('Tous les cours', 'eia-lms-core'); ?></option>
            <?php
            $courses = get_posts(array(
                'post_type' => 'lp_course',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ));

            foreach ($courses as $course) :
            ?>
                <option value="<?php echo $course->ID; ?>"><?php echo esc_html($course->post_title); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Gradebook Content -->
    <div id="gradebook-content">
        <p style="text-align: center; padding: 40px; color: #666;">
            <?php _e('Sélectionnez un cours pour voir le carnet de notes.', 'eia-lms-core'); ?>
        </p>
    </div>
</div>

<style>
.eia-admin-gradebook {
    margin: 20px 20px 0 0;
}

.gradebook-filters {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}

.gradebook-filters label {
    font-weight: 600;
    margin-right: 10px;
}

#gradebook-content {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin: 20px 0;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#filter-course').on('change', function() {
        const courseId = $(this).val();

        if (!courseId) {
            $('#gradebook-content').html('<p style="text-align: center; padding: 40px; color: #666;"><?php _e('Sélectionnez un cours pour voir le carnet de notes.', 'eia-lms-core'); ?></p>');
            return;
        }

        // Load gradebook for selected course
        $('#gradebook-content').html('<p style="text-align: center; padding: 40px;"><?php _e('Chargement...', 'eia-lms-core'); ?></p>');

        $.post(ajaxurl, {
            action: 'eia_load_course_gradebook',
            nonce: eiaLMSCore.nonce,
            course_id: courseId
        }, function(response) {
            if (response.success) {
                $('#gradebook-content').html(response.data.html);
            } else {
                $('#gradebook-content').html('<p style="text-align: center; padding: 40px; color: #dc2626;">' + response.data.message + '</p>');
            }
        });
    });
});
</script>