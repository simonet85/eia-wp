<?php
/**
 * Template for displaying single course - EIA Custom Design V2
 * Full override with complete isolation
 *
 * @package EIA_Theme
 */

defined('ABSPATH') || exit;

// Get course object
$course = learn_press_get_course();
if (!$course) {
    return;
}

// Get course data
$course_id = get_the_ID();
$user = learn_press_get_current_user();
$is_enrolled = $user && $user->has_enrolled_course($course_id);

// Force hide everything except our content
?>
<style>
    /* FORCE HIDE ALL WORDPRESS WRAPPER ELEMENTS */
    body.single-lp_course .site-header,
    body.single-lp_course header,
    body.single-lp_course .site-footer,
    body.single-lp_course footer,
    body.single-lp_course .breadcrumb,
    body.single-lp_course nav,
    body.single-lp_course .course-summary::before,
    body.single-lp_course .course-summary::after {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* Reset all containers */
    body.single-lp_course {
        margin: 0 !important;
        padding: 0 !important;
        background: #f5f5f7 !important;
    }

    body.single-lp_course .site-content,
    body.single-lp_course #content,
    body.single-lp_course .content-area,
    body.single-lp_course main,
    body.single-lp_course .course-summary {
        margin: 0 !important;
        padding: 0 !important;
        max-width: none !important;
        width: 100% !important;
    }

    /* Our custom layout */
    .eia-course-layout {
        display: flex;
        min-height: 100vh;
        background: #f5f5f7;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    .eia-course-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* Video Player */
    .eia-video-container {
        background: #000;
        position: relative;
        width: 100%;
        aspect-ratio: 16/9;
        overflow: hidden;
    }

    .eia-video-preview {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .eia-video-content {
        text-align: center;
        color: white;
        padding: 2rem;
    }

    .eia-play-button {
        width: 120px;
        height: 120px;
        margin: 0 auto 2rem;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .eia-play-button:hover {
        background: rgba(255,255,255,0.3);
        transform: scale(1.05);
    }

    /* Video Controls */
    .eia-video-controls {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0,0,0,0.9);
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .eia-video-controls button {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0.5rem;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .eia-video-controls button:hover {
        opacity: 1;
    }

    /* Tabs Navigation */
    .eia-tabs-nav {
        background: white;
        border-bottom: 1px solid #e5e7eb;
        padding: 0 2rem;
        display: flex;
        align-items: center;
        overflow-x: auto;
    }

    .eia-tab {
        padding: 1rem 1.5rem;
        background: none;
        border: none;
        border-bottom: 2px solid transparent;
        font-weight: 500;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .eia-tab:hover {
        color: #000;
    }

    .eia-tab.active {
        color: #000;
        border-bottom-color: #000;
    }

    /* Tab Content */
    .eia-tab-content-area {
        flex: 1;
        overflow-y: auto;
        background: white;
        padding: 2rem;
    }

    .eia-tab-content {
        display: none;
    }

    .eia-tab-content.active {
        display: block;
    }

    /* Sidebar */
    .eia-sidebar {
        width: 400px;
        background: white;
        border-left: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        max-height: 100vh;
        position: sticky;
        top: 0;
    }

    .eia-sidebar-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .eia-sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
    }

    /* Course Sections */
    .eia-section {
        margin-bottom: 1rem;
    }

    .eia-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .eia-section-header:hover {
        background: #f3f4f6;
    }

    .eia-section-items {
        padding-left: 1rem;
        margin-top: 0.5rem;
    }

    .eia-item {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        border-radius: 0.375rem;
        cursor: pointer;
        transition: background 0.2s;
    }

    .eia-item:hover {
        background: #f9fafb;
    }

    .eia-item.active {
        background: #dbeafe;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .eia-course-layout {
            flex-direction: column;
        }

        .eia-sidebar {
            width: 100%;
            max-height: 500px;
            position: relative;
        }
    }
</style>

<div class="eia-course-layout">
    <!-- Main Content -->
    <div class="eia-course-main">

        <!-- Video Player -->
        <div class="eia-video-container">
            <div class="eia-video-preview">
                <div class="eia-video-content">
                    <div class="eia-play-button">
                        <svg style="width: 60px; height: 60px; fill: white;" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h2 style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;">
                        <?php echo $is_enrolled ? 'Bienvenue' : 'Aperçu du cours'; ?>
                    </h2>
                    <h3 style="font-size: 1.5rem; font-weight: 500; margin-bottom: 0.25rem;">
                        <?php echo get_the_title(); ?>
                    </h3>
                    <p style="font-size: 1rem; opacity: 0.9;">
                        <?php echo $is_enrolled ? 'Commencez votre apprentissage' : 'Inscrivez-vous pour accéder'; ?>
                    </p>

                    <?php if (!$is_enrolled) : ?>
                        <div style="margin-top: 1.5rem;">
                            <form method="post" class="eia-enroll-form">
                                <?php wp_nonce_field('eia_enroll_course', 'eia_enroll_nonce'); ?>
                                <input type="hidden" name="course_id" value="<?php echo $course_id; ?>">
                                <input type="hidden" name="eia_enroll_action" value="1">
                                <button type="submit" style="
                                    background: #10B981;
                                    color: white;
                                    padding: 0.75rem 2rem;
                                    border: none;
                                    border-radius: 0.5rem;
                                    font-size: 1rem;
                                    font-weight: 600;
                                    cursor: pointer;
                                    transition: all 0.3s;
                                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                                " onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10B981'">
                                    <span style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <svg style="width: 20px; height: 20px; fill: white;" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                                        </svg>
                                        S'inscrire au cours
                                    </span>
                                </button>
                            </form>
                        </div>
                    <?php else : ?>
                        <div style="margin-top: 1.5rem;">
                            <div style="
                                background: rgba(16, 185, 129, 0.1);
                                border: 2px solid #10B981;
                                color: white;
                                padding: 0.75rem 1.5rem;
                                border-radius: 0.5rem;
                                display: inline-flex;
                                align-items: center;
                                gap: 0.5rem;
                            ">
                                <svg style="width: 20px; height: 20px; fill: white;" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span style="font-weight: 600;">Vous êtes inscrit</span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Video Controls -->
            <div class="eia-video-controls">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button><svg style="width: 20px; height: 20px; fill: white;" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/></svg></button>
                    <span style="color: white; font-size: 0.875rem;">1x</span>
                    <span style="color: white; font-size: 0.875rem;">0:00 / 15:24</span>
                </div>
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button><svg style="width: 20px; height: 20px; fill: white;" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217z" clip-rule="evenodd"/></svg></button>
                    <button><svg style="width: 20px; height: 20px; fill: white;" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 11-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4z" clip-rule="evenodd"/></svg></button>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <div class="eia-tabs-nav">
            <button class="eia-tab active" data-tab="overview">Overview</button>
            <button class="eia-tab" data-tab="qa">Q&A</button>
            <button class="eia-tab" data-tab="notes">Notes</button>
            <button class="eia-tab" data-tab="reviews">Reviews</button>
        </div>

        <!-- Tab Content -->
        <div class="eia-tab-content-area">
            <div class="eia-tab-content active" id="tab-overview">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">À propos</h2>
                <p><?php echo get_the_content(); ?></p>
            </div>
            <div class="eia-tab-content" id="tab-qa">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Q&A</h2>
                <p>Questions & Réponses</p>
            </div>
            <div class="eia-tab-content" id="tab-notes">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Notes</h2>
                <p>Vos notes personnelles</p>
            </div>
            <div class="eia-tab-content" id="tab-reviews">
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 1rem;">Avis</h2>
                <p>Avis des étudiants</p>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="eia-sidebar">
        <div class="eia-sidebar-header">
            <h3 style="font-weight: 600; font-size: 1.125rem;">Course content</h3>
            <?php if ($is_enrolled) :
                // Calculate progress
                global $wpdb;
                $total_items = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}learnpress_section_items si
                    INNER JOIN {$wpdb->prefix}learnpress_sections s ON si.section_id = s.section_id
                    WHERE s.section_course_id = %d",
                    $course_id
                ));

                $completed_items = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}learnpress_user_items ui
                    INNER JOIN {$wpdb->prefix}learnpress_section_items si ON ui.item_id = si.item_id
                    INNER JOIN {$wpdb->prefix}learnpress_sections s ON si.section_id = s.section_id
                    WHERE s.section_course_id = %d
                    AND ui.user_id = %d
                    AND ui.status = 'completed'",
                    $course_id,
                    $user->get_id()
                ));

                $progress_percent = $total_items > 0 ? round(($completed_items / $total_items) * 100) : 0;
            ?>
                <div style="margin-top: 0.5rem; font-size: 0.875rem; color: #6b7280;">
                    <?php echo $completed_items; ?>/<?php echo $total_items; ?> complété
                </div>
            <?php endif; ?>
        </div>

        <?php if ($is_enrolled) : ?>
            <!-- Progress Bar -->
            <div style="padding: 0 1.5rem 1rem;">
                <div style="background: #e5e7eb; height: 8px; border-radius: 9999px; overflow: hidden;">
                    <div style="background: linear-gradient(90deg, #10B981 0%, #059669 100%); height: 100%; width: <?php echo $progress_percent; ?>%; transition: width 0.5s ease;"></div>
                </div>
                <div style="text-align: center; margin-top: 0.5rem; font-weight: 600; color: #10B981; font-size: 0.875rem;">
                    <?php echo $progress_percent; ?>% terminé
                </div>
            </div>
        <?php endif; ?>

        <div class="eia-sidebar-content">
            <?php
            // Read sections directly from LearnPress tables
            global $wpdb;
            $sections = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}learnpress_sections
                WHERE section_course_id = %d
                ORDER BY section_order ASC",
                $course->get_id()
            ));

            if (!empty($sections)) :
                $section_number = 0;

                foreach ($sections as $section) :
                    $section_number++;
                    $section_id = $section->section_id;
                    $section_title = $section->section_name;

                    // Get section items from LearnPress table
                    $section_items = $wpdb->get_results($wpdb->prepare(
                        "SELECT * FROM {$wpdb->prefix}learnpress_section_items
                        WHERE section_id = %d
                        ORDER BY item_order ASC",
                        $section_id
                    ));
            ?>
                <div class="eia-section">
                    <div class="eia-section-header">
                        <div>
                            <div style="font-weight: 600;">Section <?php echo $section_number; ?>: <?php echo esc_html($section_title); ?></div>
                            <div style="font-size: 0.875rem; color: #6b7280;"><?php echo count($section_items); ?> élément<?php echo count($section_items) > 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                    <div class="eia-section-items" style="display: block;">
                        <?php
                        if (!empty($section_items)) :
                            foreach ($section_items as $item) :
                                $item_obj = get_post($item->item_id);
                                if ($item_obj) :
                                    $item_type = $item->item_type;
                                    $duration = get_post_meta($item_obj->ID, '_lp_duration', true);

                                    // Check if item is completed
                                    $is_completed = false;
                                    if ($is_enrolled) {
                                        $user_item = $wpdb->get_row($wpdb->prepare(
                                            "SELECT status FROM {$wpdb->prefix}learnpress_user_items
                                            WHERE user_id = %d AND item_id = %d AND item_type = %s
                                            ORDER BY user_item_id DESC LIMIT 1",
                                            $user->get_id(),
                                            $item_obj->ID,
                                            $item_type
                                        ));
                                        $is_completed = $user_item && $user_item->status === 'completed';
                                    }
                        ?>
                            <div class="eia-item <?php echo $is_completed ? 'completed' : ''; ?>" data-item-id="<?php echo $item_obj->ID; ?>" data-item-type="<?php echo $item_type; ?>">
                                <?php if ($is_enrolled) : ?>
                                    <span class="item-checkbox" style="margin-right: 0.75rem;">
                                        <?php if ($is_completed) : ?>
                                            <svg style="width: 20px; height: 20px; color: #10B981;" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php else : ?>
                                            <svg style="width: 20px; height: 20px; color: #d1d5db;" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd"/>
                                            </svg>
                                        <?php endif; ?>
                                    </span>
                                <?php endif; ?>

                                <span class="item-icon" style="margin-right: 0.5rem;">
                                    <?php if ($item_type === 'lp_lesson') : ?>
                                        <svg style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php else : ?>
                                        <svg style="width: 16px; height: 16px;" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                                        </svg>
                                    <?php endif; ?>
                                </span>
                                <span style="flex: 1; <?php echo $is_completed ? 'text-decoration: line-through; opacity: 0.7;' : ''; ?>"><?php echo esc_html($item_obj->post_title); ?></span>
                                <?php if ($duration) : ?>
                                    <span style="color: #6b7280; font-size: 0.875rem; margin-left: auto;"><?php echo $duration; ?> min</span>
                                <?php endif; ?>

                                <?php if ($is_enrolled && !$is_completed) : ?>
                                    <button class="mark-complete-btn" style="
                                        margin-left: 0.5rem;
                                        padding: 0.25rem 0.75rem;
                                        background: #10B981;
                                        color: white;
                                        border: none;
                                        border-radius: 4px;
                                        font-size: 0.75rem;
                                        cursor: pointer;
                                        transition: background 0.2s;
                                    " onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10B981'">
                                        <i class="fas fa-check"></i> Terminé
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php
                                endif;
                            endforeach;
                        else : ?>
                            <div class="eia-item" style="color: #6b7280;">Aucun contenu</div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
                endforeach;

            else : ?>
                <div style="padding: 2rem; text-align: center; color: #6b7280;">
                    <p>Aucune section disponible.</p>
                    <p style="font-size: 0.875rem; margin-top: 1rem;">Ce cours n'a pas encore de contenu structuré.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Tab switching
document.querySelectorAll('.eia-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.eia-tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.eia-tab-content').forEach(c => c.classList.remove('active'));

        this.classList.add('active');
        const tabId = 'tab-' + this.dataset.tab;
        document.getElementById(tabId).classList.add('active');
    });
});

// Mark lesson/quiz as complete
document.querySelectorAll('.mark-complete-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const itemElement = this.closest('.eia-item');
        const itemId = itemElement.dataset.itemId;
        const itemType = itemElement.dataset.itemType;

        // Disable button
        this.disabled = true;
        this.textContent = '⏳ En cours...';

        // Send AJAX request
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'eia_mark_item_complete',
                item_id: itemId,
                item_type: itemType,
                course_id: <?php echo $course_id; ?>,
                nonce: '<?php echo wp_create_nonce('eia_mark_complete'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Reload page to update progress
                    location.reload();
                } else {
                    alert('Erreur: ' + (response.data.message || 'Une erreur est survenue'));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check"></i> Terminé';
                }
            },
            error: function() {
                alert('Erreur de connexion');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check"></i> Terminé';
            }
        });
    });
});
</script>
