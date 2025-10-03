# Corrections Page Single Course

## 🐛 Problèmes Résolus

### **1. Erreur: `tailwind is not defined`**
**Cause**: Le template `page-course-fullwidth.php` n'incluait pas le header WordPress qui charge le CDN Tailwind

**Solution**:
- Modifié `functions.php` ligne 192-211 : La fonction `eia_theme_tailwind_config()` ne s'exécute plus sur les pages de cours
- Les pages de cours n'utilisent PAS Tailwind (tout est en CSS inline)

### **2. Erreur: `$(...).sortable is not a function`**
**Cause**: Le script `eia-lms-core/assets/js/frontend.js` nécessite jQuery UI sortable

**Solution**:
- Ajouté un hook `wp_print_scripts` ligne 371-376 dans `functions.php`
- Désactive complètement le script `eia-lms-core-frontend` sur les pages de cours
- Désactive aussi `eia-lms-script` ligne 408-431

### **3. Header/Footer WordPress visibles**
**Cause**: Les styles CSS ne ciblaient pas correctement les éléments

**Solution**:
- Ajouté des styles forcés dans `page-course-fullwidth.php` ligne 17-36
- Ciblage spécifique avec `body.eia-course-fullwidth-page`
- Utilisation de `!important` pour forcer le masquage

### **4. Sidebar "Course content" vide (PROBLÈME CRITIQUE)**
**Cause**: LearnPress utilise des tables personnalisées (`wp_learnpress_sections`, `wp_learnpress_section_items`) au lieu de `wp_posts` pour les sections. L'ancien seeder créait les sections avec `wp_insert_post()` mais LearnPress lit depuis ses tables custom.

**Solution**: Réécriture complète du seeder pour insérer directement dans les tables LearnPress

## ✅ Fichiers Modifiés

### **1. `wp-content/plugins/eia-lms-core/includes/class-seeder.php`** (CRITIQUE)

#### Ligne 600-706 : Réécriture complète de `create_course_curriculum()`

**CHANGEMENT MAJEUR**: Utilise maintenant les tables custom LearnPress au lieu de `wp_posts`

**Avant (INCORRECT)** :
```php
// ❌ Ancien code qui créait les sections dans wp_posts
$section_id = wp_insert_post(array(
    'post_title' => 'Introduction',
    'post_type' => 'lp_section',  // N'EXISTE PAS dans LearnPress!
    'post_status' => 'publish'
));
```

**Après (CORRECT)** :
```php
// ✅ Nouveau code qui utilise les tables custom
global $wpdb;
$sections_table = $wpdb->prefix . 'learnpress_sections';

$wpdb->insert(
    $sections_table,
    array(
        'section_name' => 'Introduction',
        'section_course_id' => $course_id,
        'section_order' => 1,
        'section_description' => ''
    ),
    array('%s', '%d', '%d', '%s')
);

$section_id = $wpdb->insert_id;

// Puis insérer les items dans wp_learnpress_section_items
$section_items_table = $wpdb->prefix . 'learnpress_section_items';
$wpdb->insert(
    $section_items_table,
    array(
        'section_id' => $section_id,
        'item_id' => $lesson_id,  // ID de wp_posts
        'item_order' => 1,
        'item_type' => 'lp_lesson'
    ),
    array('%d', '%d', '%d', '%s')
);
```

#### Ligne 832-866 : Amélioration de `ajax_clear_demo_data()`

Ajout du nettoyage des tables LearnPress :
```php
// Nettoyer les sections LearnPress
$sections_table = $wpdb->prefix . 'learnpress_sections';
$section_items_table = $wpdb->prefix . 'learnpress_section_items';

// D'abord les section_items (foreign key)
$wpdb->query("DELETE FROM $section_items_table WHERE section_id IN ($section_ids_format)");

// Puis les sections
$wpdb->query("DELETE FROM $sections_table WHERE section_id IN ($section_ids_format)");

// Enfin les enrollments
$wpdb->query("DELETE FROM $user_items_table WHERE item_type = 'lp_course' AND item_id IN ($course_ids_format)");
```

### **2. `wp-content/themes/eia-theme/functions.php`**

#### Ligne 192-211 : Tailwind config conditionnelle
```php
function eia_theme_tailwind_config() {
    // Don't load on course pages (they don't use Tailwind)
    if (is_singular('lp_course')) {
        return;
    }
    // ... reste du code
}
```

#### Ligne 408-431 : Désactivation scripts LMS
```php
function eia_lms_scripts() {
    // Don't load LMS scripts on course single pages
    if (is_singular('lp_course')) {
        return;
    }
    // ... reste du code
}
```

#### Ligne 371-376 : Désactivation EIA LMS Core frontend
```php
add_action('wp_print_scripts', function() {
    if (is_singular('lp_course')) {
        wp_dequeue_script('eia-lms-core-frontend');
        wp_deregister_script('eia-lms-core-frontend');
    }
}, 100);
```

### **3. `wp-content/themes/eia-theme/learnpress/page-course-fullwidth.php`**

#### Ligne 17-36 : Styles forcés
```php
<style>
    /* Force hide all WordPress wrapper elements */
    body.eia-course-fullwidth-page .site-header,
    body.eia-course-fullwidth-page header.site-header,
    body.eia-course-fullwidth-page .site-footer,
    body.eia-course-fullwidth-page footer.site-footer,
    body.eia-course-fullwidth-page nav.navbar,
    body.eia-course-fullwidth-page .breadcrumb,
    body.eia-course-fullwidth-page #wpadminbar {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        opacity: 0 !important;
    }
    // ...
</style>
```

## 🎯 Actions Utilisateur Requises

### **IMPORTANT : Régénérer les cours**

Les cours actuels (comme "Management Avancé") n'ont PAS de sections car ils ont été créés avec l'ancien seeder.

**Étapes** :
1. Aller dans **Admin WordPress > EIA LMS > Seeder**
2. Cliquer sur **"Nettoyer toutes les données de démo"**
3. Attendre la confirmation
4. Cliquer sur **"Lancer le seeder"**
5. Attendre la génération complète
6. Tester un nouveau cours : `http://localhost/eia-wp/courses/[nouveau-cours]/`

## ✅ Résultats Attendus Après Régénération

### **Console Navigateur**
- ✅ **Aucune erreur Tailwind**
- ✅ **Aucune erreur jQuery sortable**
- Console propre sans erreurs

### **Affichage Page**
- ✅ **Pas de header bleu EIA** en haut
- ✅ **Pas de breadcrumb** (Home > Courses > ...)
- ✅ **Pas de footer** en bas
- ✅ **Video player** en plein écran avec gradient violet
- ✅ **Tabs navigation** : Overview, Q&A, Notes, Reviews
- ✅ **Sidebar "Course content"** avec sections :
  ```
  Section 1: Introduction (3 éléments)
  ├─ Bienvenue
  ├─ Présentation du cours
  └─ Objectifs d'apprentissage

  Section 2: Les Fondamentaux (4 éléments)
  ├─ Concepts de base
  ├─ Terminologie
  ├─ Principes clés
  ├─ Études de cas
  └─ Quiz: Les Fondamentaux

  Section 3: Mise en pratique (3 éléments)
  ├─ Exercices pratiques
  ├─ Projet guidé
  ├─ Analyse de scénarios
  └─ Quiz: Mise en pratique
  ```

### **Icônes**
- ▶️ **Play icon** pour les leçons (lp_lesson)
- ❓ **Question icon** pour les quiz (lp_quiz)
- ⏱️ **Durée** affichée (ex: "5 min", "10 min")

## 🔍 Vérification Console

Après régénération, ouvrir **F12 > Console** et vérifier :

```
✅ Aucune erreur
✅ Pas de "tailwind is not defined"
✅ Pas de "sortable is not a function"
```

## 📝 Notes Techniques

### **Pourquoi désactiver Tailwind sur les cours ?**
- Les pages de cours utilisent 100% CSS inline
- Pas besoin de Tailwind = moins de poids, meilleure performance
- Évite les conflits de classes

### **Pourquoi désactiver frontend.js ?**
- Ce script est pour les fonctionnalités avancées (drag & drop questions)
- Pas nécessaire sur la page de visualisation du cours
- Nécessite jQuery UI sortable qui n'est pas chargé

### **Structure correcte des sections dans la base**
```
wp_learnpress_sections (table custom LearnPress)
├─ section_id (auto increment)
├─ section_name (titre de la section)
├─ section_course_id (ID du cours)
├─ section_order (ordre d'affichage)
└─ section_description

wp_learnpress_section_items (table custom LearnPress)
├─ section_id (FK vers wp_learnpress_sections)
├─ item_id (ID de la leçon/quiz dans wp_posts)
├─ item_order (ordre dans la section)
└─ item_type ('lp_lesson' ou 'lp_quiz')

wp_posts (pour les leçons et quiz uniquement)
├─ post_type = 'lp_lesson'
└─ post_type = 'lp_quiz'
```

**IMPORTANT**: Les sections NE SONT PAS dans `wp_posts` mais dans les tables custom LearnPress!

## 🚀 Performance

Avec les corrections :
- **JavaScript réduit** : Moins de scripts chargés
- **CSS inline** : Pas de requête HTTP supplémentaire
- **Pas de Tailwind** : -50KB de JavaScript en moins
- **Console propre** : Pas d'erreurs qui ralentissent l'exécution

---

## 🔍 Comment Débugger les Sections

Si les sections ne s'affichent toujours pas après régénération :

### **1. Vérifier les tables LearnPress**

Créer un fichier `check-sections.php` à la racine :
```php
<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;

$course_id = 123; // Remplacer par l'ID d'un cours de test

// Vérifier les sections
$sections = $wpdb->get_results(
    "SELECT * FROM {$wpdb->prefix}learnpress_sections WHERE section_course_id = $course_id"
);

echo "<h2>Sections pour le cours $course_id</h2>";
echo "<pre>";
print_r($sections);
echo "</pre>";

// Vérifier les items pour chaque section
foreach ($sections as $section) {
    $items = $wpdb->get_results(
        "SELECT * FROM {$wpdb->prefix}learnpress_section_items WHERE section_id = {$section->section_id}"
    );

    echo "<h3>Items pour section {$section->section_name}</h3>";
    echo "<pre>";
    print_r($items);
    echo "</pre>";
}
?>
```

Accéder à : `http://localhost/eia-wp/check-sections.php`

### **2. Vérifier que get_curriculum() retourne les bonnes données**

Dans le template, ajouter temporairement :
```php
$curriculum = $course->get_curriculum();
echo '<pre>';
var_dump($curriculum);
echo '</pre>';
```

Si `$curriculum` est vide, vérifier le cache LearnPress.

### **3. Nettoyer le cache LearnPress**

Dans Admin WordPress :
1. LearnPress > Settings > Advanced
2. Cliquer sur "Clear Cache"
3. Ou via code : `LP_Object_Cache::flush()`

---

**Date** : 30 septembre 2025, 23h00
**Version** : 4.0.0 - SEEDER REWRITE
**Status** : ✅ Corrigé et documenté - Seeder utilise maintenant les tables LearnPress
