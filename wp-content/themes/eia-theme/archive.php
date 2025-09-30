<?php
/**
 * The template for displaying archive pages
 *
 * @package EIA_Theme
 */

get_header(); ?>

<div class="container" style="padding: 2rem 0;">
    <header class="page-header" style="margin-bottom: 3rem; text-align: center; padding: 2rem 0; background: linear-gradient(135deg, var(--eia-blue), #1e40af); color: white; border-radius: 0.5rem;">
        <?php
        the_archive_title('<h1 class="page-title" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 1rem;">', '</h1>');
        the_archive_description('<div class="archive-description" style="font-size: 1.125rem; opacity: 0.9; max-width: 600px; margin: 0 auto;">', '</div>');
        ?>
    </header>

    <?php if (have_posts()) : ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('archive-post'); ?> style="background: white; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">

                    <?php if (has_post_thumbnail()) : ?>
                        <div style="height: 200px; overflow: hidden; position: relative;">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;')); ?>
                            </a>
                            <div style="position: absolute; top: 1rem; left: 1rem;">
                                <span style="background: var(--eia-orange); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                                    ACTUALITÉS
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="padding: 1.5rem;">
                        <header class="entry-header" style="margin-bottom: 1rem;">
                            <h2 style="font-size: 1.25rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 0.75rem; line-height: 1.3;">
                                <a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                                    <?php the_title(); ?>
                                </a>
                            </h2>

                            <div style="display: flex; align-items: center; gap: 1rem; color: var(--gray-600); font-size: 0.875rem; margin-bottom: 1rem; flex-wrap: wrap;">
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
                            </div>
                        </header>

                        <div class="entry-summary" style="color: var(--gray-700); margin-bottom: 1.5rem; line-height: 1.6;">
                            <p style="margin: 0;">
                                <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                            </p>
                        </div>

                        <footer class="entry-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <a href="<?php the_permalink(); ?>" style="color: var(--eia-blue); font-weight: 600; text-decoration: none; font-size: 0.875rem; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                                Lire la suite →
                            </a>

                            <?php if (has_tag()) : ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                                    <?php
                                    $tags = get_the_tags();
                                    if ($tags) {
                                        $tag_count = 0;
                                        foreach ($tags as $tag) {
                                            if ($tag_count >= 2) break; // Limit to 2 tags
                                            echo '<span style="background: var(--gray-100); color: var(--gray-600); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">' . $tag->name . '</span>';
                                            $tag_count++;
                                        }
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>
                        </footer>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>

        <!-- Pagination -->
        <nav style="margin: 3rem 0; text-align: center;">
            <?php
            $pagination_args = array(
                'mid_size'  => 2,
                'prev_text' => '<i class="fas fa-chevron-left"></i> Précédent',
                'next_text' => 'Suivant <i class="fas fa-chevron-right"></i>',
                'type'      => 'array',
            );

            $pagination_links = paginate_links($pagination_args);

            if ($pagination_links) :
                echo '<div style="display: flex; justify-content: center; align-items: center; flex-wrap: wrap; gap: 0.5rem;">';
                foreach ($pagination_links as $link) {
                    // Style the pagination links
                    $styled_link = str_replace(
                        array('page-numbers', 'current'),
                        array('page-numbers', 'current'),
                        $link
                    );

                    // Add inline styles
                    $styled_link = str_replace(
                        '<a class="page-numbers"',
                        '<a class="page-numbers" style="display: inline-block; padding: 0.5rem 1rem; background: white; color: var(--eia-blue); text-decoration: none; border: 1px solid var(--gray-300); border-radius: 0.25rem; transition: all 0.3s;" onmouseover="this.style.background=\'var(--eia-blue)\'; this.style.color=\'white\';" onmouseout="this.style.background=\'white\'; this.style.color=\'var(--eia-blue)\';"',
                        $styled_link
                    );

                    $styled_link = str_replace(
                        '<span aria-current="page" class="page-numbers current">',
                        '<span aria-current="page" class="page-numbers current" style="display: inline-block; padding: 0.5rem 1rem; background: var(--eia-blue); color: white; border: 1px solid var(--eia-blue); border-radius: 0.25rem;">',
                        $styled_link
                    );

                    echo $styled_link;
                }
                echo '</div>';
            endif;
            ?>
        </nav>

    <?php else : ?>
        <!-- No posts found -->
        <div style="text-align: center; padding: 4rem 2rem; background: var(--gray-50); border-radius: 0.5rem;">
            <i class="fas fa-search" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1.5rem;"></i>
            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 1rem;">
                Aucun article trouvé
            </h2>
            <p style="color: var(--gray-600); margin-bottom: 2rem; font-size: 1.125rem;">
                Désolé, aucun article ne correspond à vos critères de recherche.
            </p>
            <a href="<?php echo home_url(); ?>" class="btn btn-primary" style="display: inline-block; background: var(--eia-orange); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='var(--eia-orange)'">
                Retour à l'accueil
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Sidebar for categories and tags -->
<aside style="background: var(--gray-50); padding: 2rem 0; margin-top: 3rem;">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
            <!-- Categories -->
            <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 1.25rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-folder" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                    Catégories
                </h3>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <?php
                    $categories = get_categories();
                    foreach ($categories as $category) {
                        echo '<li style="margin-bottom: 0.5rem;">';
                        echo '<a href="' . get_category_link($category->term_id) . '" style="display: flex; justify-content: space-between; align-items: center; color: var(--gray-700); text-decoration: none; padding: 0.5rem; border-radius: 0.25rem; transition: all 0.3s;" onmouseover="this.style.background=\'var(--gray-100)\'; this.style.color=\'var(--eia-blue)\';" onmouseout="this.style.background=\'transparent\'; this.style.color=\'var(--gray-700)\';">';
                        echo '<span>' . $category->name . '</span>';
                        echo '<span style="background: var(--eia-orange); color: white; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem;">' . $category->count . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Popular Tags -->
            <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 1.25rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-tags" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                    Tags Populaires
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <?php
                    $tags = get_tags(array('orderby' => 'count', 'order' => 'DESC', 'number' => 10));
                    foreach ($tags as $tag) {
                        echo '<a href="' . get_tag_link($tag->term_id) . '" style="background: var(--gray-100); color: var(--gray-700); padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; text-decoration: none; transition: all 0.3s;" onmouseover="this.style.background=\'var(--eia-blue)\'; this.style.color=\'white\';" onmouseout="this.style.background=\'var(--gray-100)\'; this.style.color=\'var(--gray-700)\';">';
                        echo $tag->name . ' (' . $tag->count . ')';
                        echo '</a>';
                    }
                    ?>
                </div>
            </div>

            <!-- Recent Posts -->
            <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h3 style="font-size: 1.25rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 1rem; display: flex; align-items: center;">
                    <i class="fas fa-clock" style="margin-right: 0.5rem; color: var(--eia-orange);"></i>
                    Articles Récents
                </h3>
                <ul style="list-style: none; margin: 0; padding: 0;">
                    <?php
                    $recent_posts = get_posts(array('numberposts' => 5));
                    foreach ($recent_posts as $recent_post) {
                        echo '<li style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--gray-200);">';
                        echo '<a href="' . get_permalink($recent_post) . '" style="color: var(--gray-700); text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color=\'var(--eia-blue)\'" onmouseout="this.style.color=\'var(--gray-700)\'">';
                        echo '<div style="font-weight: 600; margin-bottom: 0.25rem; line-height: 1.3;">' . wp_trim_words(get_the_title($recent_post), 8) . '</div>';
                        echo '<div style="font-size: 0.75rem; color: var(--gray-500);">' . get_the_date('', $recent_post) . '</div>';
                        echo '</a>';
                        echo '</li>';
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>
</aside>

<?php get_footer(); ?>