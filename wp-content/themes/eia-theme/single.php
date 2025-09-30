<?php
/**
 * The template for displaying all single posts
 *
 * @package EIA_Theme
 */

get_header(); ?>

<div class="container" style="padding: 2rem 0; max-width: 800px; margin: 0 auto;">
    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header" style="margin-bottom: 2rem;">
                <h1 class="entry-title" style="font-size: 2.5rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 1rem; line-height: 1.2;">
                    <?php the_title(); ?>
                </h1>

                <div class="entry-meta" style="display: flex; align-items: center; gap: 1rem; color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                    <span style="display: flex; align-items: center;">
                        <i class="fas fa-calendar" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                        <?php echo get_the_date(); ?>
                    </span>

                    <span style="display: flex; align-items: center;">
                        <i class="fas fa-user" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                        <?php the_author(); ?>
                    </span>

                    <?php if (has_category()) : ?>
                        <span style="display: flex; align-items: center;">
                            <i class="fas fa-folder" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                            <?php the_category(', '); ?>
                        </span>
                    <?php endif; ?>

                    <?php if (has_tag()) : ?>
                        <span style="display: flex; align-items: center;">
                            <i class="fas fa-tags" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                            <?php the_tags('', ', '); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (has_post_thumbnail()) : ?>
                    <div class="entry-featured-image" style="margin-bottom: 2rem; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <?php the_post_thumbnail('large', array('style' => 'width: 100%; height: auto; display: block;')); ?>
                    </div>
                <?php endif; ?>
            </header>

            <div class="entry-content" style="line-height: 1.7; color: var(--gray-700);">
                <?php
                the_content();

                wp_link_pages(array(
                    'before' => '<div class="page-links" style="margin: 2rem 0; padding: 1rem; background: var(--gray-50); border-radius: 0.5rem;">',
                    'after'  => '</div>',
                    'pagelink' => '<span style="display: inline-block; padding: 0.5rem 1rem; margin: 0.25rem; background: var(--eia-blue); color: white; border-radius: 0.25rem; text-decoration: none;">%</span>',
                ));
                ?>
            </div>

            <footer class="entry-footer" style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid var(--gray-200);">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <?php if (has_tag()) : ?>
                            <div style="margin-bottom: 1rem;">
                                <strong style="color: var(--eia-blue);">Tags:</strong>
                                <?php
                                $tags = get_the_tags();
                                if ($tags) {
                                    foreach ($tags as $tag) {
                                        echo '<span style="display: inline-block; background: var(--eia-orange); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; margin-right: 0.5rem; margin-bottom: 0.5rem;">' . $tag->name . '</span>';
                                    }
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span style="font-weight: 600; color: var(--eia-blue);">Partager:</span>
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" target="_blank" style="color: var(--eia-blue); font-size: 1.25rem; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                            <i class="fab fa-facebook"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" target="_blank" style="color: var(--eia-blue); font-size: 1.25rem; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode(get_permalink()); ?>" target="_blank" style="color: var(--eia-blue); font-size: 1.25rem; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <a href="whatsapp://send?text=<?php echo urlencode(get_the_title() . ' - ' . get_permalink()); ?>" style="color: var(--eia-blue); font-size: 1.25rem; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </footer>
        </article>

        <!-- Author Bio -->
        <?php if (get_the_author_meta('description')) : ?>
            <div style="background: var(--gray-50); padding: 2rem; border-radius: 0.5rem; margin: 3rem 0;">
                <div style="display: flex; align-items: flex-start; gap: 1.5rem;">
                    <div style="flex-shrink: 0;">
                        <?php echo get_avatar(get_the_author_meta('ID'), 80, '', '', array('style' => 'border-radius: 50%; width: 80px; height: 80px;')); ?>
                    </div>
                    <div>
                        <h3 style="color: var(--eia-blue); font-size: 1.25rem; font-weight: bold; margin-bottom: 0.5rem;">
                            <?php the_author(); ?>
                        </h3>
                        <p style="color: var(--gray-700); line-height: 1.6; margin: 0;">
                            <?php echo get_the_author_meta('description'); ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Navigation between posts -->
        <nav style="margin: 3rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="flex: 1; min-width: 0;">
                <?php
                $prev_post = get_previous_post();
                if ($prev_post) :
                ?>
                    <a href="<?php echo get_permalink($prev_post); ?>" style="display: flex; align-items: center; text-decoration: none; color: var(--eia-blue); padding: 1rem; background: var(--gray-50); border-radius: 0.5rem; transition: all 0.3s;" onmouseover="this.style.background='var(--eia-blue)'; this.style.color='white';" onmouseout="this.style.background='var(--gray-50)'; this.style.color='var(--eia-blue)';">
                        <i class="fas fa-chevron-left" style="margin-right: 0.75rem;"></i>
                        <div>
                            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Article précédent</div>
                            <div style="font-weight: 600;"><?php echo wp_trim_words(get_the_title($prev_post), 8); ?></div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>

            <div style="flex: 1; min-width: 0; text-align: right;">
                <?php
                $next_post = get_next_post();
                if ($next_post) :
                ?>
                    <a href="<?php echo get_permalink($next_post); ?>" style="display: flex; align-items: center; justify-content: flex-end; text-decoration: none; color: var(--eia-blue); padding: 1rem; background: var(--gray-50); border-radius: 0.5rem; transition: all 0.3s;" onmouseover="this.style.background='var(--eia-blue)'; this.style.color='white';" onmouseout="this.style.background='var(--gray-50)'; this.style.color='var(--eia-blue)';">
                        <div style="text-align: right;">
                            <div style="font-size: 0.75rem; font-weight: 600; text-transform: uppercase; margin-bottom: 0.25rem;">Article suivant</div>
                            <div style="font-weight: 600;"><?php echo wp_trim_words(get_the_title($next_post), 8); ?></div>
                        </div>
                        <i class="fas fa-chevron-right" style="margin-left: 0.75rem;"></i>
                    </a>
                <?php endif; ?>
            </div>
        </nav>

        <!-- Related Posts -->
        <?php
        $related_posts = get_posts(array(
            'category__in' => wp_get_post_categories($post->ID),
            'numberposts'  => 3,
            'post__not_in' => array($post->ID),
        ));

        if ($related_posts) :
        ?>
            <section style="margin: 4rem 0;">
                <h2 style="font-size: 2rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 2rem; text-align: center;">
                    Articles Connexes
                </h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <?php foreach ($related_posts as $related_post) : ?>
                        <article style="background: white; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">
                            <?php if (has_post_thumbnail($related_post->ID)) : ?>
                                <div style="height: 150px; overflow: hidden;">
                                    <a href="<?php echo get_permalink($related_post); ?>">
                                        <?php echo get_the_post_thumbnail($related_post->ID, 'medium', array('style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;')); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                            <div style="padding: 1.5rem;">
                                <h3 style="font-size: 1.125rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 0.75rem;">
                                    <a href="<?php echo get_permalink($related_post); ?>" style="color: inherit; text-decoration: none;">
                                        <?php echo get_the_title($related_post); ?>
                                    </a>
                                </h3>
                                <p style="color: var(--gray-700); margin-bottom: 1rem; font-size: 0.875rem; line-height: 1.6;">
                                    <?php echo wp_trim_words(get_the_excerpt($related_post), 15, '...'); ?>
                                </p>
                                <a href="<?php echo get_permalink($related_post); ?>" style="color: var(--eia-orange); font-weight: 600; text-decoration: none; font-size: 0.875rem;">
                                    Lire la suite →
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Comments -->
        <?php
        if (comments_open() || get_comments_number()) :
            comments_template();
        endif;
        ?>

    <?php endwhile; ?>
</div>

<?php get_footer(); ?>