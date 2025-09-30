<?php
/**
 * The template for displaying search results pages
 *
 * @package EIA_Theme
 */

get_header(); ?>

<div class="container" style="padding: 2rem 0;">
    <header class="page-header" style="margin-bottom: 3rem; text-align: center; padding: 2rem 0; background: linear-gradient(135deg, var(--eia-orange), #d97706); color: white; border-radius: 0.5rem;">
        <h1 class="page-title" style="font-size: 2.5rem; font-weight: bold; margin-bottom: 1rem;">
            <?php printf(__('Résultats de recherche pour: %s', 'eia-theme'), '<span style="border-bottom: 2px solid white; padding-bottom: 0.25rem;">' . get_search_query() . '</span>'); ?>
        </h1>
        <div style="font-size: 1.125rem; opacity: 0.9;">
            <?php
            global $wp_query;
            $total_results = $wp_query->found_posts;
            printf(_n('%s résultat trouvé', '%s résultats trouvés', $total_results, 'eia-theme'), $total_results);
            ?>
        </div>
    </header>

    <!-- Search Form -->
    <div style="background: var(--gray-50); padding: 2rem; border-radius: 0.5rem; margin-bottom: 3rem; text-align: center;">
        <h2 style="color: var(--eia-blue); font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem;">
            Affiner votre recherche
        </h2>
        <form role="search" method="get" action="<?php echo home_url('/'); ?>" style="display: flex; justify-content: center; max-width: 600px; margin: 0 auto;">
            <input type="search" name="s" placeholder="Rechercher des articles..." style="flex: 1; padding: 0.75rem 1rem; border: 1px solid var(--gray-300); border-radius: 0.375rem 0 0 0.375rem; outline: none; font-size: 1rem;" value="<?php echo get_search_query(); ?>" onfocus="this.style.borderColor='var(--eia-blue)'; this.style.boxShadow='0 0 0 2px rgba(45, 79, 179, 0.1)'" onblur="this.style.borderColor='var(--gray-300)'; this.style.boxShadow='none'">
            <button type="submit" style="background: var(--eia-blue); color: white; padding: 0.75rem 1.5rem; border: 1px solid var(--eia-blue); border-radius: 0 0.375rem 0.375rem 0; cursor: pointer; font-weight: 600; transition: background-color 0.3s;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='var(--eia-blue)'">
                <i class="fas fa-search" style="margin-right: 0.5rem;"></i>
                Rechercher
            </button>
        </form>
    </div>

    <?php if (have_posts()) : ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('search-result'); ?> style="background: white; border-radius: 0.5rem; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: all 0.3s; border-left: 4px solid var(--eia-orange);" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.15)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)';">

                    <?php if (has_post_thumbnail()) : ?>
                        <div style="height: 180px; overflow: hidden; position: relative;">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail('medium', array('style' => 'width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;')); ?>
                            </a>
                            <div style="position: absolute; top: 1rem; left: 1rem;">
                                <span style="background: var(--eia-blue); color: white; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                                    <?php echo get_post_type_object(get_post_type())->labels->singular_name; ?>
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div style="padding: 1.5rem;">
                        <header class="entry-header" style="margin-bottom: 1rem;">
                            <h2 style="font-size: 1.25rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 0.75rem; line-height: 1.3;">
                                <a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                                    <?php
                                    $title = get_the_title();
                                    $search_query = get_search_query();
                                    if ($search_query) {
                                        $title = str_ireplace($search_query, '<mark style="background: var(--eia-orange); color: white; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">' . $search_query . '</mark>', $title);
                                    }
                                    echo $title;
                                    ?>
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

                                <!-- Relevance Score -->
                                <span style="background: var(--eia-orange); color: white; padding: 0.125rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                                    <i class="fas fa-star" style="margin-right: 0.25rem;"></i>
                                    Pertinent
                                </span>
                            </div>
                        </header>

                        <div class="entry-summary" style="color: var(--gray-700); margin-bottom: 1.5rem; line-height: 1.6;">
                            <p style="margin: 0;">
                                <?php
                                $excerpt = get_the_excerpt();
                                $search_query = get_search_query();
                                if ($search_query) {
                                    $excerpt = str_ireplace($search_query, '<mark style="background: var(--eia-orange); color: white; padding: 0.125rem 0.25rem; border-radius: 0.25rem;">' . $search_query . '</mark>', $excerpt);
                                }
                                echo wp_trim_words($excerpt, 25, '...');
                                ?>
                            </p>
                        </div>

                        <footer class="entry-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                            <a href="<?php the_permalink(); ?>" style="color: var(--eia-blue); font-weight: 600; text-decoration: none; font-size: 0.875rem; transition: color 0.3s;" onmouseover="this.style.color='var(--eia-orange)'" onmouseout="this.style.color='var(--eia-blue)'">
                                Lire l'article complet →
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
                        '<a class="page-numbers"',
                        '<a class="page-numbers" style="display: inline-block; padding: 0.5rem 1rem; background: white; color: var(--eia-blue); text-decoration: none; border: 1px solid var(--gray-300); border-radius: 0.25rem; transition: all 0.3s;" onmouseover="this.style.background=\'var(--eia-blue)\'; this.style.color=\'white\';" onmouseout="this.style.background=\'white\'; this.style.color=\'var(--eia-blue)\';"',
                        $link
                    );

                    $styled_link = str_replace(
                        '<span aria-current="page" class="page-numbers current">',
                        '<span aria-current="page" class="page-numbers current" style="display: inline-block; padding: 0.5rem 1rem; background: var(--eia-orange); color: white; border: 1px solid var(--eia-orange); border-radius: 0.25rem;">',
                        $styled_link
                    );

                    echo $styled_link;
                }
                echo '</div>';
            endif;
            ?>
        </nav>

    <?php else : ?>
        <!-- No search results -->
        <div style="text-align: center; padding: 4rem 2rem; background: var(--gray-50); border-radius: 0.5rem;">
            <i class="fas fa-search" style="font-size: 4rem; color: var(--gray-300); margin-bottom: 1.5rem;"></i>
            <h2 style="font-size: 1.5rem; font-weight: bold; color: var(--eia-blue); margin-bottom: 1rem;">
                Aucun résultat trouvé
            </h2>
            <p style="color: var(--gray-600); margin-bottom: 2rem; font-size: 1.125rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Désolé, aucun article ne correspond à votre recherche "<strong><?php echo get_search_query(); ?></strong>".
                Essayez avec des mots-clés différents ou consultez nos suggestions ci-dessous.
            </p>

            <!-- Search suggestions -->
            <div style="margin: 2rem 0;">
                <h3 style="color: var(--eia-blue); font-size: 1.125rem; font-weight: bold; margin-bottom: 1rem;">
                    Suggestions de recherche:
                </h3>
                <div style="display: flex; justify-content: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 2rem;">
                    <a href="<?php echo home_url('/?s=formation'); ?>" style="background: var(--eia-blue); color: white; padding: 0.5rem 1rem; border-radius: 9999px; text-decoration: none; font-size: 0.875rem; transition: background-color 0.3s;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='var(--eia-blue)'">Formation</a>
                    <a href="<?php echo home_url('/?s=admission'); ?>" style="background: var(--eia-blue); color: white; padding: 0.5rem 1rem; border-radius: 9999px; text-decoration: none; font-size: 0.875rem; transition: background-color 0.3s;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='var(--eia-blue)'">Admission</a>
                    <a href="<?php echo home_url('/?s=programme'); ?>" style="background: var(--eia-blue); color: white; padding: 0.5rem 1rem; border-radius: 9999px; text-decoration: none; font-size: 0.875rem; transition: background-color 0.3s;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='var(--eia-blue)'">Programme</a>
                    <a href="<?php echo home_url('/?s=master'); ?>" style="background: var(--eia-blue); color: white; padding: 0.5rem 1rem; border-radius: 9999px; text-decoration: none; font-size: 0.875rem; transition: background-color 0.3s;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='var(--eia-blue)'">Master</a>
                    <a href="<?php echo home_url('/?s=licence'); ?>" style="background: var(--eia-blue); color: white; padding: 0.5rem 1rem; border-radius: 9999px; text-decoration: none; font-size: 0.875rem; transition: background-color 0.3s;" onmouseover="this.style.background='#1e40af'" onmouseout="this.style.background='var(--eia-blue)'">Licence</a>
                </div>
            </div>

            <a href="<?php echo home_url(); ?>" class="btn btn-primary" style="display: inline-block; background: var(--eia-orange); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: all 0.3s;" onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='var(--eia-orange)'">
                Retour à l'accueil
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Search Tips -->
<aside style="background: var(--gray-50); padding: 2rem 0; margin-top: 3rem;">
    <div class="container">
        <div style="max-width: 800px; margin: 0 auto; text-align: center;">
            <h3 style="color: var(--eia-blue); font-size: 1.5rem; font-weight: bold; margin-bottom: 1.5rem;">
                Conseils pour une meilleure recherche
            </h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; text-align: left;">
                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-lightbulb" style="color: var(--eia-orange); font-size: 1.5rem; margin-bottom: 0.75rem;"></i>
                    <h4 style="color: var(--eia-blue); font-weight: bold; margin-bottom: 0.5rem;">Mots-clés spécifiques</h4>
                    <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0;">Utilisez des termes précis liés à l'éducation et aux formations.</p>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-quote-left" style="color: var(--eia-orange); font-size: 1.5rem; margin-bottom: 0.75rem;"></i>
                    <h4 style="color: var(--eia-blue); font-weight: bold; margin-bottom: 0.5rem;">Expressions exactes</h4>
                    <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0;">Utilisez des guillemets pour rechercher une phrase exacte.</p>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-spell-check" style="color: var(--eia-orange); font-size: 1.5rem; margin-bottom: 0.75rem;"></i>
                    <h4 style="color: var(--eia-blue); font-weight: bold; margin-bottom: 0.5rem;">Orthographe</h4>
                    <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0;">Vérifiez l'orthographe de vos termes de recherche.</p>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 0.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <i class="fas fa-filter" style="color: var(--eia-orange); font-size: 1.5rem; margin-bottom: 0.75rem;"></i>
                    <h4 style="color: var(--eia-blue); font-weight: bold; margin-bottom: 0.5rem;">Filtres</h4>
                    <p style="color: var(--gray-600); font-size: 0.875rem; margin: 0;">Utilisez les catégories pour affiner vos résultats.</p>
                </div>
            </div>
        </div>
    </div>
</aside>

<?php get_footer(); ?>