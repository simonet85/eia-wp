<?php
/**
 * The main template file
 *
 * @package EIA_Theme
 */

get_header(); ?>

    <!-- Hero Section -->
    <section
      class="relative min-h-screen flex items-center"
      style="
        background: linear-gradient(
            135deg,
            rgba(45, 79, 179, 0.9),
            rgba(45, 79, 179, 0.7)
          ),
          url('<?php echo get_template_directory_uri(); ?>/images/image-2.jpeg') center/cover;
      "
    >
      <div class="absolute inset-0 bg-black opacity-40"></div>
      <div class="hero-section relative max-w-6xl mx-auto px-4 text-white">
        <div class="max-w-2xl">
          <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-bold mb-4 md:mb-6 leading-tight">
            ÉCOLE INTERNATIONALE<br />
            DES AFFAIRES
          </h1>
          <p class="text-lg sm:text-xl md:text-2xl mb-6 md:mb-8 leading-relaxed">
            Confiez-nous votre avenir<br />
            pour des lendemains meilleures
          </p>
          <button
            class="bg-transparent border-2 border-white text-white px-6 py-2 md:px-8 md:py-3 rounded-lg text-base md:text-lg hover:bg-white hover:text-eia-blue transition duration-300"
          >
            Découvrir
          </button>
        </div>
      </div>
    </section>

    <!-- About Section -->
    <section class="py-16">
      <!-- Orange Feature Cards -->
      <div class="py-8 md:py-12 relative -mt-20 md:-mt-40">
        <div
          class="max-w-6xl mx-auto px-4 bg-eia-orange rounded-lg shadow-lg p-4 md:p-8"
        >
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 text-white">
            <div
              class="text-center bg-white bg-opacity-10 p-3 md:p-4 rounded-lg hover:bg-opacity-20 transition-all duration-300 hover:transform hover:-translate-y-1 md:hover:-translate-y-2 shadow-lg"
            >
              <div
                class="w-16 h-16 mx-auto mb-4 flex items-center justify-center"
              >
                <i class="fas fa-graduation-cap text-4xl"></i>
              </div>
              <h3 class="text-lg md:text-xl font-bold mb-2">Formation de qualité</h3>
              <p class="mb-3 md:mb-4 text-sm md:text-base">Découvrir</p>
              <a
                href="#"
                class="inline-flex items-center text-white hover:underline"
              >
                Découvrir →
              </a>
            </div>
            <div
              class="text-center bg-white bg-opacity-10 p-3 md:p-4 rounded-lg hover:bg-opacity-20 transition-all duration-300 hover:transform hover:-translate-y-1 md:hover:-translate-y-2 shadow-lg"
            >
              <div
                class="w-16 h-16 mx-auto mb-4 flex items-center justify-center"
              >
                <i class="fas fa-briefcase text-4xl"></i>
              </div>
              <h3 class="text-lg md:text-xl font-bold mb-2">Politique d'insertion</h3>
              <p class="mb-3 md:mb-4 text-sm md:text-base">Découvrir</p>
              <a
                href="#"
                class="inline-flex items-center text-white hover:underline"
              >
                Découvrir →
              </a>
            </div>
            <div
              class="text-center bg-white bg-opacity-10 p-3 md:p-4 rounded-lg hover:bg-opacity-20 transition-all duration-300 hover:transform hover:-translate-y-1 md:hover:-translate-y-2 shadow-lg"
            >
              <div
                class="w-16 h-16 mx-auto mb-4 flex items-center justify-center"
              >
                <i class="fas fa-certificate text-4xl"></i>
              </div>
              <h3 class="text-lg md:text-xl font-bold mb-2 leading-tight">
                Enseignement reconnu<br />par les normes qualités
              </h3>
              <p class="mb-3 md:mb-4 text-sm md:text-base">Découvrir</p>
              <a
                href="#"
                class="inline-flex items-center text-white hover:underline"
              >
                Découvrir →
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- À Propos Content -->
      <div class="max-w-6xl mx-auto px-4 py-8 md:py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-12 items-stretch">
          <div class="flex flex-col order-2 md:order-1">
            <div class="grid grid-cols-2 gap-2 md:gap-4 h-48 md:h-full">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-1.jpg"
                alt="Graduation Success"
                class="rounded-lg shadow-lg w-full h-full object-cover"
              />
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-3.jpeg"
                alt="Happy Graduates"
                class="rounded-lg shadow-lg w-full h-full object-cover"
              />
            </div>
          </div>
          <div class="order-1 md:order-2">
            <h2 class="text-2xl md:text-3xl font-bold text-eia-blue mb-4 md:mb-6">À PROPOS</h2>
            <p class="text-gray-700 mb-4 md:mb-6 text-sm md:text-base">
              L'École Internationale des Affaires (EIA) est un Établissement
              privé d'Enseignement supérieur (EPES) instituée par une
              autorisation et un agrément définitif. L'établissement possède une
              habilitation institutionnelle ANAQ.
            </p>
            <div class="space-y-4">
              <h3 class="font-bold text-eia-blue text-sm md:text-base">L'EIA forme sur :</h3>
              <ul class="space-y-2 md:space-y-3 text-gray-700">
                <li class="flex items-center">
                  <i class="fas fa-cogs text-eia-blue mr-2 md:mr-3 text-sm md:text-lg"></i>
                  <span class="text-xs md:text-sm">les parcours techniques et professionnels (BT, BTS);</span>
                </li>
                <li class="flex items-center">
                  <i class="fas fa-university text-eia-blue mr-3 text-lg"></i>
                  les parcours LMD (Licence et Master);
                </li>
                <li class="flex items-center">
                  <i class="fas fa-award text-eia-blue mr-3 text-lg"></i>
                  les certifications et les séminaires professionnels.
                </li>
              </ul>
              <div class="mt-6">
                <h3 class="font-bold text-eia-blue mb-2">
                  L'École Internationale des Affaires (EIA) c'est aussi:
                </h3>
                <ul class="space-y-3 text-gray-700">
                  <li class="flex items-center">
                    <i class="fas fa-building text-eia-blue mr-3 text-lg"></i>
                    deux campus, un sur Dakar et un dans la région de Thiès;
                  </li>
                  <li class="flex items-center">
                    <i class="fas fa-school text-eia-blue mr-3 text-lg"></i>
                    un collège et un lycée sur Thiès.
                  </li>
                </ul>
              </div>
            </div>
            <button
              class="bg-eia-orange text-white px-6 py-2 rounded mt-6 hover:bg-orange-600 transition duration-300"
            >
              VOIR TOUT
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- News Section -->
    <section class="bg-gradient-to-r from-blue-900 to-blue-600 py-16">
      <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-4xl font-bold text-white mb-12 text-left">
          ACTUALITÉS
        </h2>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mb-8 md:mb-12">
          <!-- Main News Item - 1/3 width -->
          <div class="bg-white rounded-lg overflow-hidden shadow-lg">
            <div class="bg-eia-orange text-white px-4 py-2 text-sm text-left">
              <span class="text-sm font-bold">ACTUALITÉS</span>
            </div>

            <?php
            // Get the latest post for the main news item
            $latest_post = get_posts(array('numberposts' => 1));
            if ($latest_post) :
                $post = $latest_post[0];
                setup_postdata($post);
            ?>
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('medium', array('class' => 'w-full h-48 object-cover')); ?>
                <?php else : ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/images/image-4.jpeg" alt="<?php the_title(); ?>" class="w-full h-48 object-cover" />
                <?php endif; ?>

                <div class="p-6">
                  <h3 class="text-xl font-bold text-eia-blue mb-3">
                    <?php the_title(); ?>
                  </h3>
                  <p class="text-gray-700 mb-4">
                    <?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?>
                  </p>
                  <a href="<?php the_permalink(); ?>" class="text-eia-blue font-semibold hover:underline">Voir tout →</a>
                </div>
            <?php
                wp_reset_postdata();
            else :
            ?>
                <img src="<?php echo get_template_directory_uri(); ?>/images/image-4.jpeg" alt="Rentrée 2024" class="w-full h-48 object-cover" />
                <div class="p-6">
                  <h3 class="text-xl font-bold text-eia-blue mb-3">
                    C'EST LA RENTRÉE 2024 À L'EIA
                  </h3>
                  <p class="text-gray-700 mb-4">
                    C'est avec un immense enthousiasme que nous annonçons la rentrée
                    scolaire le 15 octobre 2024 à l'École Internationale des
                    Affaires. Fondée en 1998, notre institution a su évoluer et
                    s'adapter aux...
                  </p>
                  <a href="#" class="text-eia-blue font-semibold hover:underline">Voir tout →</a>
                </div>
            <?php endif; ?>
          </div>

          <!-- Video and Counters Section - 2/3 width -->
          <div class="md:col-span-2">
            <!-- Video Section -->
            <div
              class="bg-black rounded-lg overflow-hidden shadow-lg relative mb-6"
            >
              <div class="bg-eia-orange text-white px-4 py-2">
                <span class="text-sm font-bold">ACTUALITÉS</span>
              </div>
              <div
                class="relative h-64 flex items-center justify-center"
                style="
                  background: linear-gradient(
                      rgba(0, 0, 0, 0.5),
                      rgba(0, 0, 0, 0.5)
                    ),
                    url('<?php echo get_template_directory_uri(); ?>/doc/video.jpeg') center/cover;
                "
              >
                <div class="text-center text-white">
                  <div
                    class="w-16 h-16 mx-auto mb-4 bg-red-600 rounded-full flex items-center justify-center"
                  >
                    <svg
                      class="w-8 h-8"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                        clip-rule="evenodd"
                      ></path>
                    </svg>
                  </div>
                  <p class="text-sm">
                    Film institutionnel École International des Affaires (EIA)
                  </p>
                </div>
              </div>
              <div class="p-4 bg-black">
                <p class="text-white text-sm">
                  Regarder sur <span class="text-red-500">YouTube</span>
                </p>
              </div>
            </div>

            <!-- 3 Counters Section -->
            <div class="grid grid-cols-3 gap-2 md:gap-4">
              <div class="bg-white rounded-lg p-2 md:p-4 text-center shadow-lg">
                <div class="flex items-center justify-center mb-2 md:mb-3">
                  <i class="fas fa-users text-eia-blue text-lg md:text-2xl mr-1 md:mr-2"></i>
                  <span class="text-2xl md:text-4xl font-bold text-eia-blue">3+</span>
                </div>
                <p class="text-gray-600 text-xs md:text-sm">Découvrir</p>
              </div>
              <div class="bg-white rounded-lg p-2 md:p-4 text-center shadow-lg">
                <div class="flex items-center justify-center mb-3">
                  <i class="fas fa-laptop text-eia-blue text-2xl mr-2"></i>
                  <span class="text-4xl font-bold text-eia-blue">3+</span>
                </div>
                <p class="text-gray-600 text-sm">Découvrir</p>
              </div>
              <div class="bg-white rounded-lg p-2 md:p-4 text-center shadow-lg">
                <div class="flex items-center justify-center mb-3">
                  <i class="fas fa-star text-eia-blue text-2xl mr-2"></i>
                  <span class="text-4xl font-bold text-eia-blue">3+</span>
                </div>
                <p class="text-gray-600 text-sm">Découvrir</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Advertisement Cards Section -->
      <div class="max-w-6xl mx-auto px-4 py-6 md:py-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6">
          <!-- Rentrée Scolaire Card -->
          <div
            class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300"
          >
            <div class="relative">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-5.jpeg"
                alt="Rentrée Scolaire"
                class="w-full h-40 object-cover"
              />
              <div class="absolute top-3 left-3">
                <div
                  class="bg-eia-blue text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
                >
                  ACTUALITÉS
                </div>
              </div>
            </div>
            <div class="p-3 md:p-4">
              <h4 class="text-xs md:text-sm font-bold text-eia-blue mb-2 md:mb-3 leading-tight">
                RENTRÉE SCOLAIRE : 2024-2025
              </h4>
              <p class="text-xs text-gray-600 mb-2 md:mb-3 line-clamp-3">
                Bienvenue chez vous!!!! C'est parti. Let's GO!!!! Nous offrons
                une large gamme de formations adaptées à vos aspirations
                professionnelles, allant du...
              </p>
              <a
                href="#"
                class="inline-flex items-center text-xs text-eia-blue font-semibold hover:text-eia-orange transition-colors duration-200"
              >
                Voir tout
                <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <!-- Université d'Été Card -->
          <div
            class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300"
          >
            <div class="relative">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-1.jpg"
                alt="Université d'Été"
                class="w-full h-40 object-cover"
              />
              <div class="absolute top-3 left-3">
                <div
                  class="bg-eia-blue text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
                >
                  UNIVERSITÉ D'ÉTÉ
                </div>
              </div>
            </div>
            <div class="p-4">
              <h4 class="text-sm font-bold text-eia-blue mb-3 leading-tight">
                WEBIN'ARQUES
              </h4>
              <p class="text-xs text-gray-600 mb-3 line-clamp-3">
                Nous sommes ravis de vous présenter nos Webin'arques exclusifs,
                des webinaires conçus par des experts sur des thèmes essentiels
                ! Nos webin'arques sont l'occasion parfaite...
              </p>
              <a
                href="#"
                class="inline-flex items-center text-xs text-eia-blue font-semibold hover:text-eia-orange transition-colors duration-200"
              >
                Voir tout
                <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <!-- Programmes Internationaux Card -->
          <div
            class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300"
          >
            <div class="relative">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-2.jpeg"
                alt="Programmes Internationaux"
                class="w-full h-40 object-cover"
              />
              <div class="absolute top-3 left-3">
                <div
                  class="bg-eia-blue text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
                >
                  ACTUALITÉS
                </div>
              </div>
            </div>
            <div class="p-4">
              <h4 class="text-sm font-bold text-eia-blue mb-3 leading-tight">
                PROGRAMMES INTERNATIONAUX
              </h4>
              <p class="text-xs text-gray-600 mb-3 line-clamp-3">
                L'École Internationale des Affaires et l'American University of
                Leadership scellent un Partenariat Innovant. L'EIA forme sur les
                parcours internationaux...
              </p>
              <a
                href="#"
                class="inline-flex items-center text-xs text-eia-blue font-semibold hover:text-eia-orange transition-colors duration-200"
              >
                Voir tout
                <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <!-- Séminaires Internationaux Card -->
          <div
            class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300"
          >
            <div class="relative">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-4.jpeg"
                alt="Séminaires Internationaux"
                class="w-full h-40 object-cover"
              />
              <div class="absolute top-3 left-3">
                <div
                  class="bg-eia-blue text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
                >
                  ACTUALITÉS
                </div>
              </div>
            </div>
            <div class="p-4">
              <h4 class="text-sm font-bold text-eia-blue mb-3 leading-tight">
                SÉMINAIRES INTERNATIONAUX
              </h4>
              <p class="text-xs text-gray-600 mb-3 line-clamp-3">
                Bienvenue chez vous!!!! C'est parti. Let's GO!!!! Nous offrons
                une large gamme de formations adaptées à vos aspirations
                professionnelles, allant du...
              </p>
              <a
                href="#"
                class="inline-flex items-center text-xs text-eia-blue font-semibold hover:text-eia-orange transition-colors duration-200"
              >
                Voir tout
                <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>

          <!-- Corporate Training Card -->
          <div
            class="bg-white rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transform hover:-translate-y-2 transition-all duration-300"
          >
            <div class="relative">
              <img
                src="<?php echo get_template_directory_uri(); ?>/images/image-3.jpeg"
                alt="Corporate Training"
                class="w-full h-40 object-cover"
              />
              <div class="absolute top-3 left-3">
                <div
                  class="bg-eia-blue text-white px-3 py-1 rounded-full text-xs font-bold shadow-lg"
                >
                  ACTUALITÉS
                </div>
              </div>
            </div>
            <div class="p-4">
              <h4 class="text-sm font-bold text-eia-blue mb-3 leading-tight">
                RENFORCEMENT DE CAPACITÉ
              </h4>
              <p class="text-xs text-gray-600 mb-3 line-clamp-3">
                Perfectionniste et pragmatique ! L'Ecole Internationale des
                Affaires (EIA) vous présente ses séminaires. Prochaine rentrée
                janvier 2024...
              </p>
              <a
                href="#"
                class="inline-flex items-center text-xs text-eia-blue font-semibold hover:text-eia-orange transition-colors duration-200"
              >
                Voir tout
                <i class="fas fa-arrow-right ml-1 text-xs"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Programs Section -->
    <section class="py-8 md:py-16 bg-gray-50">
      <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-8">

          <?php
          // Get additional posts for the programs section
          $program_posts = get_posts(array('numberposts' => 3, 'offset' => 1));

          if ($program_posts) :
              foreach ($program_posts as $post) :
                  setup_postdata($post);
          ?>
              <div class="bg-white rounded-lg overflow-hidden shadow-lg">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('medium', array('class' => 'w-full h-48 object-cover')); ?>
                <?php else : ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/images/image-5.jpeg" alt="<?php the_title(); ?>" class="w-full h-48 object-cover" />
                <?php endif; ?>

                <div class="p-6">
                  <h3 class="text-lg font-bold text-eia-blue mb-3">
                    <?php the_title(); ?>
                  </h3>
                  <p class="text-gray-700 text-sm mb-4">
                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                  </p>
                  <a
                    href="<?php the_permalink(); ?>"
                    class="text-eia-blue font-semibold text-sm hover:underline"
                  >Voir tout →</a>
                </div>
              </div>
          <?php
              endforeach;
              wp_reset_postdata();
          else :
          ?>
              <!-- Default program cards if no posts -->
              <div class="bg-white rounded-lg overflow-hidden shadow-lg">
                <img
                  src="<?php echo get_template_directory_uri(); ?>/images/image-5.jpeg"
                  alt="Rentrée Scolaire"
                  class="w-full h-48 object-cover"
                />
                <div class="p-6">
                  <h3 class="text-lg font-bold text-eia-blue mb-3">
                    RENTRÉ SCOLAIRE : 2024-2025
                  </h3>
                  <p class="text-gray-700 text-sm mb-4">
                    Bienvenue chez vous!!!! C'est parti. Let's GO!!!! Nous offrons
                    une large gamme de formations adaptées à vos aspirations
                    professionnelles...
                  </p>
                  <a
                    href="#"
                    class="text-eia-blue font-semibold text-sm hover:underline"
                    >Voir tout →</a
                  >
                </div>
              </div>

              <div class="bg-white rounded-lg overflow-hidden shadow-lg">
                <img
                  src="<?php echo get_template_directory_uri(); ?>/images/image-1.jpg"
                  alt="Webinaires"
                  class="w-full h-48 object-cover"
                />
                <div class="p-6">
                  <h3 class="text-lg font-bold text-eia-blue mb-3">
                    UNIVERSITÉ D'ÉTÉ : WEBIN'ARQUES
                  </h3>
                  <p class="text-gray-700 text-sm mb-4">
                    Nous sommes ravis de vous présenter nos Webin'arques exclusifs,
                    des webinaires conçus par des experts sur des thèmes essentiels
                    !...
                  </p>
                  <a
                    href="#"
                    class="text-eia-blue font-semibold text-sm hover:underline"
                    >Voir tout →</a
                  >
                </div>
              </div>

              <div class="bg-white rounded-lg overflow-hidden shadow-lg">
                <img
                  src="<?php echo get_template_directory_uri(); ?>/images/image-2.jpeg"
                  alt="Programmes Internationaux"
                  class="w-full h-48 object-cover"
                />
                <div class="p-6">
                  <h3 class="text-lg font-bold text-eia-blue mb-3">
                    PROGRAMMES INTERNATIONAUX
                  </h3>
                  <p class="text-gray-700 text-sm mb-4">
                    L'École Internationale des Affaires et l'American University of
                    Leadership scellent un Partenariat Innovant...
                  </p>
                  <a
                    href="#"
                    class="text-eia-blue font-semibold text-sm hover:underline"
                    >Voir tout →</a
                  >
                </div>
              </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- Welcome Section -->
    <section
      class="relative py-16"
      style="
        background: linear-gradient(
            135deg,
            rgba(45, 79, 179, 0.85),
            rgba(45, 79, 179, 0.75)
          ),
          url('<?php echo get_template_directory_uri(); ?>/images/image-3.jpeg') center/cover;
      "
    >
      <div class="absolute inset-0 bg-black opacity-40"></div>
      <div class="relative max-w-7xl mx-auto px-4 text-center text-white">
        <h2 class="text-4xl font-bold mb-6">BIENVENUE SUR LES CAMPUS</h2>
        <p class="text-lg mb-8 max-w-4xl mx-auto">
          Conformément au système LMD, l'étudiant est au cœur de son
          enseignement et l'EIA lui procure les outils lui permettant d'assurer
          son autonomie.
        </p>
        <div
          class="w-24 h-24 mx-auto bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition duration-300 cursor-pointer"
        >
          <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
              clip-rule="evenodd"
            ></path>
          </svg>
        </div>
      </div>
    </section>

    <!-- Bottom Programs -->
    <section class="py-8 md:py-16">
      <div class="max-w-6xl mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
          <div class="bg-eia-blue text-white p-4 md:p-8 rounded-lg">
            <h3 class="text-xl md:text-2xl font-bold mb-3 md:mb-4">
              PREPARATION<br />AU CONCOURS<br />DE L'ENA
            </h3>
            <p class="mb-4 md:mb-6 text-sm md:text-base">
              C'est reparti pour les session de préparation du concours des
              corps de métiers au SENEGAL: ENA/ISMP, IGE, Cour des Comptes,
              Corps d'État...
            </p>
            <a
              href="#"
              class="inline-flex items-center text-white hover:underline"
            >
              Voir tout →
            </a>
          </div>

          <div class="bg-eia-orange text-white p-4 md:p-8 rounded-lg">
            <h3 class="text-xl md:text-2xl font-bold mb-3 md:mb-4">
              MASTER<br />INGÉNIERIE<br />FINANCIÈRE<br />ET TRADING
            </h3>
            <p class="mb-4 md:mb-6 text-sm md:text-base">
              Vous êtes professionnel ou étudiant en banque ou de la finance.
              Rejoignez le programme de Master en Ingénierie financière...
            </p>
            <a
              href="#"
              class="inline-flex items-center text-white hover:underline"
            >
              Voir tout →
            </a>
          </div>

          <div
            class="text-white p-4 md:p-8 rounded-lg relative overflow-hidden"
            style="
              background: linear-gradient(
                  rgba(0, 0, 0, 0.6),
                  rgba(0, 0, 0, 0.6)
                ),
                url('<?php echo get_template_directory_uri(); ?>/doc/eia-images/image-06.jpeg') center/cover;
            "
          >
            <div class="absolute top-4 right-4">
              <span class="bg-white text-black px-2 py-1 rounded text-sm"
                >1/4 de Siècle, ça se fête !!!</span
              >
            </div>
            <div class="flex items-center justify-center h-full">
              <div
                class="w-16 h-16 bg-red-600 rounded-full flex items-center justify-center"
              >
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"
                    clip-rule="evenodd"
                  ></path>
                </svg>
              </div>
            </div>
            <p class="text-left text-sm">
              Regarder sur <span class="text-red-500">YouTube</span>
            </p>
          </div>
        </div>
      </div>
    </section>

<?php get_footer(); ?>