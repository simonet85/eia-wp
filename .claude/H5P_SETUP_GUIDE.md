# Guide H5P Interactive Video - EIA LMS

## 🎬 Configuration H5P Interactive Video

### Étape 1: Créer un H5P Interactive Video

1. **Accéder à H5P**
   ```
   WordPress Admin > H5P Content > Add New
   URL: http://eia-wp.test/wp-admin/admin.php?page=h5p_new
   ```

2. **Choisir le type de contenu**
   - Cliquer sur "Interactive Video"
   - Ou rechercher "Interactive Video" dans la liste

3. **Configurer la vidéo**

   **Upload Options:**
   - **YouTube URL**: `https://www.youtube.com/watch?v=VIDEO_ID`
   - **Vimeo URL**: `https://vimeo.com/VIDEO_ID`
   - **MP4 Upload**: Uploader un fichier vidéo local

4. **Ajouter des interactions (Optionnel)**
   - Cliquer sur "Add interactions" timeline
   - Ajouter des quiz, textes, liens à des moments spécifiques
   - Types disponibles:
     - Single Choice Set (QCM)
     - Multiple Choice
     - Text (Notes/Info)
     - True/False
     - Fill in the blanks

5. **Configurer les paramètres**
   - **Title**: Nom de la vidéo H5P
   - **Summary**: Résumé (optionnel)
   - **Behavioral settings**:
     ✅ Start video at: 0 (début)
     ✅ Auto-play: Selon préférence
     ✅ Show bookmarks: Recommandé
     ✅ Show rewind button: Oui

6. **Sauvegarder**
   - Cliquer sur "Create" ou "Update"
   - **Noter l'ID H5P** affiché dans l'URL ou la liste

### Étape 2: Lier H5P à une Leçon LearnPress

**Méthode 1: Via l'interface WordPress**

1. Aller dans **LearnPress > Lessons**
   ```
   http://eia-wp.test/wp-admin/edit.php?post_type=lp_lesson
   ```

2. Modifier une leçon existante ou créer une nouvelle

3. **Ajouter Custom Field** (en bas de page):
   - Si "Custom Fields" n'est pas visible:
     - Cliquer sur les 3 points en haut à droite
     - Cocher "Custom Fields"

   - **Name**: `_lesson_h5p_id`
   - **Value**: ID du H5P (exemple: `15`)
   - Cliquer "Add Custom Field"

4. **Sauvegarder la leçon**

**Méthode 2: Via Script PHP**

Utiliser le helper script:
```
http://eia-wp.test/link-h5p-to-lesson.php?lesson_id=X&h5p_id=Y
```

### Étape 3: Tester

1. **Accéder à la leçon en frontend**
   ```
   http://eia-wp.test/courses/[course-slug]/lessons/[lesson-slug]/
   ```

2. **Vérifier**:
   - ✅ Le lecteur H5P s'affiche
   - ✅ La sidebar de progression est visible
   - ✅ Les tabs fonctionnent (Overview, Q&A, Notes, Reviews)
   - ✅ La progression se met à jour

## 🛠️ Helper Scripts Disponibles

### 1. Lister les H5P Contents
```
http://eia-wp.test/list-h5p-contents.php
```

### 2. Lier H5P à une Leçon
```
http://eia-wp.test/link-h5p-to-lesson.php?lesson_id=123&h5p_id=15
```

### 3. Vérifier une Leçon
```
http://eia-wp.test/check-lesson-h5p.php?lesson_id=123
```

## 📊 Types de Vidéos H5P Recommandés

### 1. **Interactive Video** ⭐ Principal
- Idéal pour: Cours, tutoriels, démonstrations
- Fonctionnalités:
  - Quiz intégrés dans la vidéo
  - Bookmarks/Chapitres
  - Textes d'information
  - Navigation interactive

### 2. **Course Presentation** (Alternative)
- Idéal pour: Présentations slide-by-slide
- Combine: Slides + Vidéos + Quiz
- Plus structuré qu'une vidéo simple

### 3. **Video** (Simple)
- Vidéo basique sans interactions
- Utile pour: Introductions courtes, témoignages

## 🎨 Personnalisation CSS

Le lecteur H5P est déjà stylisé dans `lesson-video.css`:

```css
/* H5P Wrapper Override */
.eia-h5p-wrapper {
    width: 100%;
    height: 100%;
    border-radius: 12px;
    overflow: hidden;
}

.eia-h5p-wrapper iframe {
    border-radius: 12px;
}
```

Pour plus de personnalisation, ajouter dans `functions.php`:

```php
function eia_custom_h5p_styles() {
    if (is_singular('lp_lesson')) {
        ?>
        <style>
            /* Personnaliser les couleurs H5P */
            .h5p-interactive-video .h5p-controls {
                background: #2D4FB3 !important;
            }
        </style>
        <?php
    }
}
add_action('wp_head', 'eia_custom_h5p_styles');
```

## 📈 Tracking et Analytics

### H5P xAPI Events (À implémenter)

H5P envoie des événements xAPI qu'on peut capturer:

```php
add_action('h5p_alter_user_result', 'eia_track_h5p_completion', 10, 4);

function eia_track_h5p_completion($data, $result_id, $content_id, $user_id) {
    // Récupérer la leçon liée
    global $wpdb;
    $lesson_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_lesson_h5p_id' AND meta_value = %d",
        $content_id
    ));

    if ($lesson_id) {
        // Marquer comme complété
        $course_id = get_post_meta($lesson_id, '_lp_course', true);
        $user = learn_press_get_user($user_id);
        $user->complete_lesson($lesson_id, $course_id);
    }
}
```

## 🔧 Dépannage

### Problème: H5P ne s'affiche pas

**Solution 1**: Vérifier le custom field
```sql
SELECT * FROM wp_postmeta
WHERE post_id = LESSON_ID AND meta_key = '_lesson_h5p_id';
```

**Solution 2**: Vérifier que le H5P existe
```
http://eia-wp.test/wp-admin/admin.php?page=h5p&task=show&id=H5P_ID
```

**Solution 3**: Permissions
- H5P nécessite que l'utilisateur soit connecté
- Vérifier les permissions dans H5P Settings

### Problème: Vidéo ne charge pas

**Vérifier**:
1. Format vidéo supporté (MP4, WebM)
2. URL YouTube/Vimeo correcte
3. Taille du fichier (max upload size PHP)

### Problème: Interactions ne fonctionnent pas

**Vérifier**:
1. JavaScript non bloqué
2. Console browser pour erreurs
3. H5P libraries installées (Settings > H5P)

## 📝 Exemples de Leçons H5P

### Exemple 1: Cours de Gestion de Projet
```
Vidéo: Introduction au Scrum (10 min)
Interactions:
- 2:30 - Quiz: "Qu'est-ce qu'un Sprint?"
- 5:00 - Text: "Les 3 piliers du Scrum"
- 8:00 - Quiz: "Rôles dans Scrum"
```

### Exemple 2: Cours de Comptabilité
```
Vidéo: Le bilan comptable (15 min)
Interactions:
- 3:00 - True/False: "L'actif = Passif?"
- 7:00 - Fill in blanks: "Équation comptable"
- 12:00 - Multiple Choice: "Types d'actifs"
```

## 🚀 Workflow Complet

1. **Créer le contenu H5P**
   - Upload/Link vidéo
   - Ajouter interactions
   - Sauvegarder (noter ID)

2. **Créer/Modifier la leçon**
   - Titre, description
   - Custom field: `_lesson_h5p_id` = ID
   - Durée: `_lp_lesson_video_duration` = "15 min"

3. **Assigner au cours**
   - Dans LearnPress > Courses
   - Curriculum Builder
   - Ajouter la leçon à une section

4. **Tester en tant qu'étudiant**
   - Se connecter comme étudiant
   - Accéder au cours
   - Cliquer sur la leçon
   - Vérifier lecteur + progression

## ✅ Checklist de Test

- [ ] H5P Interactive Video créé
- [ ] ID H5P noté
- [ ] Custom field ajouté à la leçon
- [ ] Leçon dans le curriculum du cours
- [ ] Lecteur s'affiche en frontend
- [ ] Sidebar de progression visible
- [ ] Tabs fonctionnent
- [ ] Progression se met à jour
- [ ] Badge "Terminé" apparaît
- [ ] Barre de progression globale correcte

## 📚 Ressources

- **H5P Official**: https://h5p.org/interactive-video
- **H5P Examples**: https://h5p.org/content-types-and-applications
- **Documentation EIA**: `.claude/VIDEO_PLAYER_STRATEGY.md`

---

**Prochaine étape**: Créer votre premier H5P Interactive Video test!
