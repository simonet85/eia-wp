# Stratégie Lecteur Vidéo - EIA LMS

**Date**: 04 Octobre 2025
**Objectif**: Développer un lecteur vidéo moderne pour les cours avec progression tracking

## 📦 Plugins Disponibles

### Plugins Actifs
1. **LearnPress 4.2.9.3** ✓
   - LMS principal avec cours/leçons/quiz
   - Support basique de contenu
   - Système de progression natif

2. **H5P 1.16.0** ✓
   - Contenu interactif (vidéos, quiz, présentations)
   - Support vidéo interactif avec H5P.InteractiveVideo
   - Analytics intégrées
   - **POTENTIEL ÉLEVÉ pour lecteur vidéo**

3. **BuddyPress 14.4.0** ✓
   - Social learning (utilisé pour messagerie/notifications)

4. **GamiPress 7.5.4** ✓
   - Gamification (badges, points)
   - Peut tracker completion vidéo

5. **bbPress 2.6.14** ✓
   - Forums (Q&A sous les vidéos)

## 🎯 Stratégie Recommandée: 3 Options

### **Option A: H5P Interactive Video** ⭐ RECOMMANDÉ
**Avantages**:
- ✅ Plugin déjà installé et actif
- ✅ Lecteur vidéo interactif natif avec:
  - Quiz intégrés dans la vidéo
  - Marqueurs de progression
  - Analytics de visionnage
  - Support multi-sources (YouTube, Vimeo, MP4)
- ✅ Interface moderne prête à l'emploi
- ✅ Responsive et accessible
- ✅ Shortcode facile: `[h5p id="X"]`

**Inconvénients**:
- ⚠️ Nécessite création de contenu H5P
- ⚠️ Moins de contrôle sur le design

**Implementation**:
```php
// 1. Intégrer H5P dans les leçons LearnPress
add_filter('learn-press/lesson-content', 'eia_add_h5p_to_lesson');

// 2. Tracker progression vidéo avec H5P xAPI
add_action('h5p_alter_user_result', 'eia_track_video_progress');

// 3. Afficher dans template
echo do_shortcode('[h5p id="' . $h5p_id . '"]');
```

### **Option B: Custom Video Player avec Video.js**
**Avantages**:
- ✅ Contrôle total du design (comme l'image)
- ✅ Personnalisation complète
- ✅ Intégration LearnPress native

**Inconvénients**:
- ⚠️ Développement from scratch
- ⚠️ Maintenance requise
- ⚠️ Temps de développement: ~8-12h

**Implementation**:
```php
// 1. Ajouter Video.js CDN
wp_enqueue_script('videojs', 'https://vjs.zencdn.net/8.10.0/video.min.js');

// 2. Custom player template
// 3. AJAX progress tracking
// 4. Custom controls overlay
```

### **Option C: Hybrid - H5P + Custom UI**
**Avantages**:
- ✅ Utilise H5P pour le lecteur
- ✅ Custom sidebar et progression
- ✅ Meilleur des deux mondes

**Inconvénients**:
- ⚠️ Complexité moyenne

## 🏆 Recommandation: Option A (H5P) avec Personnalisation

### Phase 1: Setup H5P (2-3h)
1. Créer un H5P Interactive Video type
2. Configurer template LearnPress custom
3. Intégrer dans `single-lp_lesson.php`

### Phase 2: Custom Sidebar (3-4h)
1. Développer sidebar de progression
2. Liste des leçons avec états
3. Badge "Terminé"
4. Barre de progression globale

### Phase 3: Onglets Q&A, Notes, Reviews (4-5h)
1. Tab "Overview" - Description leçon
2. Tab "Q&A" - Intégration bbPress
3. Tab "Notes" - Système de prise de notes
4. Tab "Reviews" - Avis sur la leçon

### Phase 4: Progression Tracking (2-3h)
1. Hook H5P xAPI events
2. Update LearnPress progression
3. Sync avec GamiPress (points/badges)

## 📋 Structure de Base

### Template: `single-lp_lesson.php`
```php
<?php
get_header();

while (have_posts()) : the_post();
    $lesson_id = get_the_ID();
    $course_id = get_post_meta($lesson_id, '_lp_course', true);
    $h5p_id = get_post_meta($lesson_id, '_lesson_h5p_id', true);
?>

<div class="eia-lesson-container">
    <!-- Video Player Zone -->
    <div class="eia-video-section">
        <?php if($h5p_id): ?>
            <?php echo do_shortcode("[h5p id='$h5p_id']"); ?>
        <?php endif; ?>

        <!-- Tabs: Overview, Q&A, Notes, Reviews -->
        <div class="eia-lesson-tabs">
            <!-- Tab content -->
        </div>
    </div>

    <!-- Course Content Sidebar -->
    <div class="eia-course-sidebar">
        <?php echo do_shortcode('[eia_course_content course_id="' . $course_id . '"]'); ?>
    </div>
</div>

<?php
endwhile;
get_footer();
```

### Shortcode: Course Content Sidebar
```php
function eia_course_content_shortcode($atts) {
    $course_id = $atts['course_id'];
    $sections = /* Get course sections */

    ob_start();
    ?>
    <div class="eia-sidebar-content">
        <div class="eia-progress-header">
            <h3>Course content</h3>
            <span class="eia-completion">1/12 complété</span>
            <div class="eia-progress-bar">
                <div class="eia-progress-fill" style="width: 8%"></div>
            </div>
            <span class="eia-progress-percent">8% terminé</span>
        </div>

        <?php foreach($sections as $section): ?>
        <div class="eia-section">
            <h4><?php echo $section->section_name; ?></h4>
            <span class="eia-section-count"><?php echo count($section->items); ?> éléments</span>

            <?php foreach($section->items as $item): ?>
            <div class="eia-lesson-item <?php echo $item->completed ? 'completed' : ''; ?>">
                <input type="radio" <?php checked($item->is_current); ?>>
                <i class="fas fa-play-circle"></i>
                <div class="eia-lesson-info">
                    <span class="eia-lesson-title"><?php echo $item->title; ?></span>
                    <span class="eia-lesson-duration"><?php echo $item->duration; ?> min</span>
                </div>
                <?php if($item->completed): ?>
                    <span class="eia-badge-complete">✓ Terminé</span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('eia_course_content', 'eia_course_content_shortcode');
```

## 🎨 CSS Requirements

```css
/* Video Section */
.eia-lesson-container {
    display: flex;
    gap: 30px;
    max-width: 1400px;
    margin: 0 auto;
}

.eia-video-section {
    flex: 1;
}

/* H5P Video Override */
.h5p-iframe-wrapper {
    border-radius: 12px;
    overflow: hidden;
}

/* Sidebar */
.eia-course-sidebar {
    width: 400px;
    background: white;
    border-radius: 12px;
    padding: 24px;
}

.eia-progress-bar {
    height: 6px;
    background: #e5e7eb;
    border-radius: 3px;
}

.eia-progress-fill {
    height: 100%;
    background: #10B981;
    border-radius: 3px;
}

.eia-badge-complete {
    background: #10B981;
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
}

/* Tabs */
.eia-lesson-tabs {
    margin-top: 30px;
    background: white;
    border-radius: 12px;
    padding: 20px;
}

.eia-tab-nav {
    display: flex;
    gap: 30px;
    border-bottom: 2px solid #e5e7eb;
}

.eia-tab-nav button {
    padding: 12px 0;
    border: none;
    background: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    position: relative;
}

.eia-tab-nav button.active {
    color: #2D4FB3;
}

.eia-tab-nav button.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 2px;
    background: #2D4FB3;
}
```

## 📊 Database Schema (si nécessaire)

```sql
-- Table pour prise de notes
CREATE TABLE wp_eia_lesson_notes (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    lesson_id BIGINT NOT NULL,
    course_id BIGINT NOT NULL,
    note_content LONGTEXT,
    video_timestamp VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY user_lesson (user_id, lesson_id)
);

-- Table pour Q&A (ou utiliser bbPress topics)
CREATE TABLE wp_eia_lesson_qa (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    question TEXT NOT NULL,
    answer TEXT,
    answered_by BIGINT,
    video_timestamp VARCHAR(20),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY lesson_id (lesson_id)
);
```

## ⚙️ Hooks H5P pour Progression

```php
// Track H5P video completion
add_action('h5p_alter_user_result', 'eia_track_h5p_video_progress', 10, 4);

function eia_track_h5p_video_progress($data, $result_id, $content_id, $user_id) {
    // Get lesson ID from H5P content
    $lesson_id = get_post_meta($content_id, '_h5p_lesson_id', true);

    if($lesson_id) {
        // Update LearnPress progression
        $course = LP_Course::get_course_by_lesson($lesson_id);

        if($course) {
            $user = learn_press_get_user($user_id);
            $user->complete_lesson($lesson_id, $course->get_id());

            // Award GamiPress points
            do_action('gamipress_complete_lesson', $lesson_id, $user_id, $course->get_id());
        }
    }
}
```

## 🚀 Plan d'Action

### Étape 1: Validation H5P (30 min)
- [ ] Tester création H5P Interactive Video
- [ ] Vérifier compatibilité avec LearnPress
- [ ] Tester tracking xAPI

### Étape 2: Template Custom (2h)
- [ ] Créer `single-lp_lesson.php` custom
- [ ] Intégrer H5P shortcode
- [ ] Ajouter structure tabs

### Étape 3: Sidebar Progression (3h)
- [ ] Développer shortcode `[eia_course_content]`
- [ ] Requêtes sections/leçons
- [ ] Barre de progression
- [ ] États leçons

### Étape 4: Tabs Functionality (4h)
- [ ] Tab Overview
- [ ] Tab Q&A (bbPress integration)
- [ ] Tab Notes (CRUD)
- [ ] Tab Reviews

### Étape 5: Progress Tracking (2h)
- [ ] Hooks H5P events
- [ ] Update LearnPress completion
- [ ] GamiPress integration

**Temps Total Estimé**: 11-12 heures

---

## ✅ Décision Finale

**Recommandation**: Utiliser **H5P Interactive Video** comme base du lecteur vidéo.

**Pourquoi?**
1. ✅ Déjà installé et fonctionnel
2. ✅ Features riches (quiz, interactions)
3. ✅ Analytics intégrées
4. ✅ Gain de temps ~60% vs custom
5. ✅ Peut être personnalisé visuellement avec CSS

**Prochaine étape**: Créer le premier H5P Interactive Video test et le template custom LearnPress.
