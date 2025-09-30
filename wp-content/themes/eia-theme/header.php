<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'eia-blue': '#2D4FB3',
                        'eia-orange': '#F59E0B',
                    },
                },
            },
        };
    </script>

    <?php wp_head(); ?>
</head>

<body <?php body_class('bg-white'); ?>>
<?php wp_body_open(); ?>

    <!-- Top Header -->
    <div class="bg-eia-blue text-white py-2 px-4">
      <div class="max-w-7xl mx-auto flex justify-between items-center text-sm">
        <div class="flex items-center space-x-6">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path
                d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
              ></path>
              <path
                d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"
              ></path>
            </svg>
            contacts@eia.sn
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                clip-rule="evenodd"
              ></path>
            </svg>
            Cité batrain Dakar / SN
          </div>
        </div>
        <div class="flex items-center space-x-4">
          <button class="flex items-center">
            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                clip-rule="evenodd"
              ></path>
            </svg>
            Login
          </button>
          <button class="bg-eia-orange px-3 py-1 rounded text-sm">
            Contactez-nous
          </button>
        </div>
      </div>
    </div>

    <!-- Main Navigation -->
    <nav class="bg-white shadow-sm sticky top-0 z-50 relative">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between py-4">
          <!-- Logo -->
          <div class="flex items-center">
            <div class="bg-eia-blue text-white px-3 py-2 rounded-r-full">
              <span class="font-bold text-lg md:text-xl">E.I.A</span>
            </div>
            <div class="ml-2 md:ml-3">
              <div class="text-eia-blue font-bold text-xs md:text-sm">
                ÉCOLE INTERNATIONALE
              </div>
              <div class="text-eia-blue font-bold text-xs md:text-sm">DES AFFAIRES</div>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <button id="mobile-menu-button" class="lg:hidden text-eia-blue p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </button>

          <!-- Desktop Navigation Menu -->
          <div class="hidden lg:flex bg-eia-blue rounded-lg">
            <?php
            $menu_items = array(
                array('title' => 'Accueil', 'url' => home_url('/'), 'active' => is_front_page()),
                array('title' => 'À propos', 'url' => '#', 'active' => false),
                array('title' => 'Admission', 'url' => '#', 'active' => false),
                array('title' => 'Alumni', 'url' => '#', 'active' => false),
                array('title' => 'Événement', 'url' => '#', 'active' => false),
                array('title' => 'Contact', 'url' => '#', 'active' => false),
            );

            foreach ($menu_items as $index => $item) {
                $first_class = $index === 0 ? ' rounded-l-lg' : '';
                $last_class = $index === count($menu_items) - 1 ? ' rounded-r-lg' : '';
                $active_indicator = $item['active'] ? ' font-bold' : '';

                echo '<a href="' . esc_url($item['url']) . '" class="text-white px-3 xl:px-4 py-2 bg-eia-blue hover:bg-blue-700 text-sm' . $first_class . $last_class . $active_indicator . '">' . esc_html($item['title']) . '</a>';
            }
            ?>
          </div>

          <!-- Desktop Search Bar -->
          <div class="hidden xl:flex items-center">
            <form role="search" method="get" action="<?php echo home_url('/'); ?>">
                <div class="flex">
                    <input type="search" name="s" placeholder="Recherche" class="px-3 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-eia-blue text-sm" value="<?php echo get_search_query(); ?>" />
                    <button type="submit" class="bg-gray-200 px-3 py-2 rounded-r-lg">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                      </svg>
                    </button>
                </div>
            </form>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden lg:hidden absolute top-full left-0 right-0 bg-white shadow-lg z-50 border-t">
          <div class="px-4 py-4 space-y-2">
            <?php
            foreach ($menu_items as $item) {
                echo '<a href="' . esc_url($item['url']) . '" class="block text-eia-blue hover:bg-gray-100 px-3 py-2 rounded">' . esc_html($item['title']) . '</a>';
            }
            ?>
            <!-- Mobile Search -->
            <form role="search" method="get" action="<?php echo home_url('/'); ?>">
                <div class="flex items-center mt-4">
                  <input type="search" name="s" placeholder="Recherche" class="flex-1 px-3 py-2 border rounded-l-lg focus:outline-none focus:ring-2 focus:ring-eia-blue text-sm" value="<?php echo get_search_query(); ?>" />
                  <button type="submit" class="bg-gray-200 px-3 py-2 rounded-r-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                    </svg>
                  </button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </nav>

    <!-- Mobile Menu JavaScript -->
    <script>
      document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
      });
    </script>

<div id="page" class="site">
    <main id="main" class="site-main">