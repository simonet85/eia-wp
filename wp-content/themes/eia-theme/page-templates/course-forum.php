<?php
/**
 * Template Name: Forum du Cours
 *
 * @package EIA_Theme
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink()));
    exit;
}

show_admin_bar(true);
get_header();

// Get course ID from URL
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$topic_id = isset($_GET['topic_id']) ? intval($_GET['topic_id']) : 0;

if (!$course_id) {
    echo '<p>Cours non spécifié</p>';
    get_footer();
    exit;
}

$course = get_post($course_id);
if (!$course || $course->post_type !== 'lp_course') {
    echo '<p>Cours introuvable</p>';
    get_footer();
    exit;
}

// Get forum instance
if (!class_exists('EIA_Forum')) {
    echo '<p>Module Forum non disponible</p>';
    get_footer();
    exit;
}

$forum = EIA_Forum::get_instance();
$current_user = wp_get_current_user();
$is_instructor = ($course->post_author == $current_user->ID) || current_user_can('manage_options');
?>

<style>
body.admin-bar { margin-top: 32px !important; }
#wpadminbar { display: block !important; position: fixed !important; top: 0 !important; z-index: 99999 !important; }
</style>

<div class="eia-forum-container">

    <?php if ($topic_id) :
        // TOPIC DETAIL VIEW
        $topic = $forum->get_topic($topic_id);
        if (!$topic) {
            echo '<p>Discussion introuvable</p>';
            get_footer();
            exit;
        }

        $forum->increment_views($topic_id);
        $replies = $forum->get_replies($topic_id);
        $user_vote = $forum->get_user_vote('topic', $topic_id, $current_user->ID);
    ?>

        <!-- Back to list -->
        <div style="margin-bottom: 1.5rem;">
            <a href="?course_id=<?php echo $course_id; ?>" style="color: #667eea; text-decoration: none; font-weight: 600;">
                <i class="fas fa-arrow-left"></i> Retour au forum
            </a>
        </div>

        <!-- Topic Detail -->
        <div class="topic-detail">
            <div class="topic-detail-header">
                <div class="topic-title">
                    <?php echo esc_html($topic->title); ?>
                    <span class="topic-badges">
                        <?php if ($topic->is_resolved) : ?>
                            <span class="badge resolved"><i class="fas fa-check"></i> Résolu</span>
                        <?php endif; ?>
                    </span>
                </div>

                <div class="topic-author-info">
                    <div class="topic-votes">
                        <button class="vote-btn upvote <?php echo ($user_vote == 1) ? 'active' : ''; ?>"
                                data-entity-type="topic" data-entity-id="<?php echo $topic->id; ?>">
                            <i class="fas fa-chevron-up"></i>
                        </button>
                        <span class="vote-score">
                            <?php
                            global $wpdb;
                            $score = $wpdb->get_var($wpdb->prepare(
                                "SELECT SUM(vote_type) FROM {$wpdb->prefix}eia_forum_votes WHERE entity_type = 'topic' AND entity_id = %d",
                                $topic->id
                            ));
                            echo intval($score);
                            ?>
                        </span>
                        <button class="vote-btn downvote <?php echo ($user_vote == -1) ? 'active' : ''; ?>"
                                data-entity-type="topic" data-entity-id="<?php echo $topic->id; ?>">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>

                    <div class="author-avatar">
                        <?php echo strtoupper(substr($topic->author_name, 0, 1)); ?>
                    </div>

                    <div class="author-details">
                        <p class="author-name"><?php echo esc_html($topic->author_name); ?></p>
                        <p class="topic-date">
                            <i class="far fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($topic->created_at)); ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="topic-detail-content">
                <?php echo wpautop(esc_html($topic->content)); ?>
            </div>

            <?php if ($is_instructor || $topic->user_id == $current_user->ID) : ?>
                <div class="topic-actions">
                    <button class="btn-topic-action btn-resolve" data-topic-id="<?php echo $topic->id; ?>">
                        <?php if ($topic->is_resolved) : ?>
                            <i class="fas fa-redo"></i> Rouvrir
                        <?php else : ?>
                            <i class="fas fa-check"></i> Marquer résolu
                        <?php endif; ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- Replies Section -->
        <div class="replies-section">
            <h3 class="replies-header"><i class="fas fa-comments"></i> <?php echo count($replies); ?> Réponses</h3>

            <div class="replies-list">
                <?php if ($replies) : ?>
                    <?php foreach ($replies as $reply) :
                        $reply_user_vote = $forum->get_user_vote('reply', $reply->id, $current_user->ID);
                    ?>
                        <div class="reply-item <?php echo $reply->is_best_answer ? 'best-answer' : ''; ?>">
                            <div class="reply-header">
                                <div class="reply-votes">
                                    <button class="vote-btn upvote <?php echo ($reply_user_vote == 1) ? 'active' : ''; ?>"
                                            data-entity-type="reply" data-entity-id="<?php echo $reply->id; ?>">
                                        <i class="fas fa-chevron-up"></i>
                                    </button>
                                    <span class="vote-score">
                                        <?php
                                        $reply_score = $wpdb->get_var($wpdb->prepare(
                                            "SELECT SUM(vote_type) FROM {$wpdb->prefix}eia_forum_votes WHERE entity_type = 'reply' AND entity_id = %d",
                                            $reply->id
                                        ));
                                        echo intval($reply_score);
                                        ?>
                                    </span>
                                    <button class="vote-btn downvote <?php echo ($reply_user_vote == -1) ? 'active' : ''; ?>"
                                            data-entity-type="reply" data-entity-id="<?php echo $reply->id; ?>">
                                        <i class="fas fa-chevron-down"></i>
                                    </button>
                                </div>

                                <div class="reply-content">
                                    <div class="author-details">
                                        <strong class="author-name"><?php echo esc_html($reply->author_name); ?></strong> •
                                        <span class="topic-date"><?php echo date('d/m/Y à H:i', strtotime($reply->created_at)); ?></span>
                                        <?php if ($reply->is_best_answer) : ?>
                                            <span class="badge best-answer"><i class="fas fa-check"></i> Meilleure réponse</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="reply-text">
                                        <?php echo wpautop(esc_html($reply->content)); ?>
                                    </div>

                                    <?php if (($is_instructor || $topic->user_id == $current_user->ID) && !$topic->is_resolved && !$reply->is_best_answer) : ?>
                                        <div class="reply-footer">
                                            <button class="btn-reply-action btn-best-answer" data-reply-id="<?php echo $reply->id; ?>">
                                                <i class="fas fa-star"></i> Meilleure réponse
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <div class="empty-state">
                        <i class="fas fa-comment"></i>
                        <h3>Aucune réponse</h3>
                        <p>Soyez le premier à répondre!</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Reply Form -->
            <div class="forum-form" style="margin-top: 2rem;">
                <h3><i class="fas fa-reply"></i> Votre réponse</h3>
                <form id="new-reply-form">
                    <input type="hidden" id="reply-topic-id" value="<?php echo $topic_id; ?>">

                    <div class="form-group">
                        <label for="reply-content">Réponse</label>
                        <textarea id="reply-content" name="content" required placeholder="Partagez votre réponse..."></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Publier la réponse
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php else :
        // TOPICS LIST VIEW
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $topics = $forum->get_topics($course_id, 50, 0, $search);
    ?>

        <!-- Header -->
        <div class="eia-forum-header">
            <h1><i class="fas fa-comments"></i> Forum : <?php echo esc_html($course->post_title); ?></h1>
            <p>Posez vos questions et partagez vos connaissances</p>
        </div>

        <!-- Actions Bar -->
        <div class="forum-actions-bar">
            <div class="forum-search">
                <input type="text" id="forum-search" placeholder="Rechercher dans les discussions..." value="<?php echo esc_attr($search); ?>">
                <input type="hidden" id="forum-course-id" value="<?php echo $course_id; ?>">
            </div>
            <button class="btn-new-topic">
                <i class="fas fa-plus"></i> Nouvelle question
            </button>
        </div>

        <!-- Topics List -->
        <div class="topics-list">
            <?php if ($topics) : ?>
                <?php foreach ($topics as $topic) : ?>
                    <div class="topic-item" onclick="window.location.href='?course_id=<?php echo $course_id; ?>&topic_id=<?php echo $topic->id; ?>'">
                        <div class="topic-header">
                            <div class="topic-votes">
                                <button class="vote-btn upvote" data-entity-type="topic" data-entity-id="<?php echo $topic->id; ?>" onclick="event.stopPropagation()">
                                    <i class="fas fa-chevron-up"></i>
                                </button>
                                <span class="vote-score"><?php echo intval($topic->vote_score); ?></span>
                                <button class="vote-btn downvote" data-entity-type="topic" data-entity-id="<?php echo $topic->id; ?>" onclick="event.stopPropagation()">
                                    <i class="fas fa-chevron-down"></i>
                                </button>
                            </div>

                            <div class="topic-content">
                                <h3 class="topic-title">
                                    <?php echo esc_html($topic->title); ?>
                                    <span class="topic-badges">
                                        <?php if ($topic->is_resolved) : ?>
                                            <span class="badge resolved"><i class="fas fa-check"></i> Résolu</span>
                                        <?php endif; ?>
                                    </span>
                                </h3>

                                <p class="topic-excerpt">
                                    <?php
                                    $excerpt = substr($topic->content, 0, 150);
                                    echo esc_html($excerpt) . (strlen($topic->content) > 150 ? '...' : '');
                                    ?>
                                </p>

                                <div class="topic-meta">
                                    <span class="topic-meta-item">
                                        <i class="fas fa-user"></i> <?php echo esc_html($topic->author_name); ?>
                                    </span>
                                    <span class="topic-meta-item">
                                        <i class="far fa-comment"></i> <?php echo intval($topic->reply_count); ?> réponses
                                    </span>
                                    <span class="topic-meta-item">
                                        <i class="far fa-eye"></i> <?php echo intval($topic->views); ?> vues
                                    </span>
                                    <span class="topic-meta-item">
                                        <i class="far fa-clock"></i> <?php echo human_time_diff(strtotime($topic->created_at), current_time('timestamp')); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h3>Aucune discussion</h3>
                    <p>Soyez le premier à poser une question!</p>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>

<!-- New Topic Modal -->
<div id="new-topic-modal" class="forum-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 10000;">
    <div style="display: flex; align-items: center; justify-content: center; width: 100%; height: 100%; padding: 2rem;">
        <div class="forum-form" style="max-width: 600px; width: 100%; position: relative;">
            <h3><i class="fas fa-plus-circle"></i> Nouvelle question</h3>

            <form id="new-topic-form">
                <input type="hidden" id="topic-course-id" value="<?php echo $course_id; ?>">

                <div class="form-group">
                    <label for="topic-title">Titre de la question *</label>
                    <input type="text" id="topic-title" name="title" required placeholder="Ex: Comment installer WordPress sur Laragon?">
                </div>

                <div class="form-group">
                    <label for="topic-content">Détails *</label>
                    <textarea id="topic-content" name="content" required placeholder="Décrivez votre question en détail..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Publier
                    </button>
                    <button type="button" class="btn-cancel">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Ensure modal closes properly
jQuery(document).ready(function($) {
    // Close on cancel button
    $(document).on('click', '.btn-cancel', function(e) {
        e.preventDefault();
        $('#new-topic-modal').fadeOut(200);
        $('#new-topic-form')[0].reset();
    });

    // Close on background click
    $('#new-topic-modal').on('click', function(e) {
        if (e.target === this) {
            $(this).fadeOut(200);
            $('#new-topic-form')[0].reset();
        }
    });
});
</script>

<?php get_footer(); ?>
