# Page Single Course - Design Moderne

## 🎨 Design Implémenté

Basé sur le design de référence `.claude/Course-Flutter-Dart-Complete-App-Development-Course.png`

### **Layout Principal**

```
┌─────────────────────────────────────────────────────────────┐
│                     HEADER GRADIENT BLEU                     │
│  Breadcrumb > Titre du Cours > Métadonnées                  │
└─────────────────────────────────────────────────────────────┘
┌─────────────────────────────┬───────────────────────────────┐
│                             │                               │
│   VIDEO PLAYER / PREVIEW    │   SIDEBAR: ENROLLMENT CARD   │
│   (Aspect ratio 16:9)       │   + Cours gratuit            │
│                             │   + Bouton inscription        │
├─────────────────────────────┤   + Bouton favoris           │
│                             │                               │
│   TABS NAVIGATION           ├───────────────────────────────┤
│   Overview | Q&A | Notes    │                               │
│   Announcements | Reviews   │   COURSE CONTENT SIDEBAR     │
│                             │   Section 1: Introduction     │
├─────────────────────────────┤   ├─ 1. Introduction (4min)  │
│                             │   ├─ 2. About Course (4min)  │
│   TAB CONTENT AREA          │   └─ 3. Setup (3min)         │
│   - Overview: Description   │                               │
│   - Q&A: Questions          │   Section 2: Windows Setup   │
│   - Notes: Prendre notes    │   ├─ 1. Install (14min)     │
│   - Announcements: Actus    │   └─ ...                     │
│   - Reviews: Avis           │                               │
│                             │   (Scrollable sidebar)        │
└─────────────────────────────┴───────────────────────────────┘
```

## ✨ Fonctionnalités Implémentées

### **1. Header Gradient**
- ✅ Gradient bleu-violet
- ✅ Breadcrumb (Accueil > Cours > Titre)
- ✅ Titre H1 du cours
- ✅ Métadonnées : Étudiants, Durée, Note

### **2. Video Player / Preview**
- ✅ Aspect ratio 16:9 responsive
- ✅ Player placeholder pour cours inscrits
- ✅ Image preview pour non-inscrits
- ✅ Contrôles vidéo simulés (Play, Volume, Fullscreen)

### **3. Tabs Navigation**
- ✅ **Overview** : Description complète du cours
- ✅ **Q&A** : Questions & réponses avec zone de texte
- ✅ **Notes** : Prendre des notes personnelles (si inscrit)
- ✅ **Announcements** : Annonces du formateur avec commentaires
- ✅ **Reviews** : Avis avec système de notation 5 étoiles

### **4. Sidebar Enrollment**
- ✅ Card sticky (reste visible au scroll)
- ✅ Prix "Gratuit"
- ✅ Bouton "S'inscrire maintenant" (orange)
- ✅ Bouton "Ajouter aux favoris" (outline)

### **5. Course Content Sidebar**
- ✅ Liste des sections accordéon
- ✅ Checkbox pour marquer progression
- ✅ Icônes différentes pour leçons/quiz
- ✅ Durée par leçon
- ✅ Progress tracking (0/4 complétées)
- ✅ Scrollable avec scrollbar personnalisée

### **6. Overview Tab - Contenu**
- ✅ Description complète
- ✅ 4 cartes de fonctionnalités :
  - Certificat de complétion
  - Accès illimité
  - Contenu vidéo HD
  - Support formateur
- ✅ Section "Votre formateur" avec avatar

### **7. Q&A Tab**
- ✅ Zone de texte pour poser question
- ✅ Bouton "Publier la question"
- ✅ Liste des questions (placeholder)

### **8. Notes Tab**
- ✅ Zone de texte pour prendre notes
- ✅ Bouton "Enregistrer la note"
- ✅ Restriction : uniquement pour inscrits

### **9. Announcements Tab**
- ✅ Avatar du formateur
- ✅ Titre et date de l'annonce
- ✅ Contenu de l'annonce
- ✅ Zone de commentaires
- ✅ Bouton "Commenter"

### **10. Reviews Tab**
- ✅ Rating summary (4.8/5)
- ✅ Breakdown par étoiles (5★ 70%, 4★ 20%...)
- ✅ Barre de progression visuelle
- ✅ Liste des avis (placeholder)

## 📁 Fichiers Créés

### **Template LearnPress**
```
wp-content/themes/eia-theme/learnpress/single-course.php
```
**Rôle** : Template principal pour afficher un cours

### **CSS Personnalisé**
```
wp-content/themes/eia-theme/assets/css/course-single.css
```
**Rôle** : Styles additionnels (animations, transitions, responsive)

### **functions.php (modifié)**
Ajout de la ligne pour charger le CSS sur les pages de cours :
```php
if (is_singular('lp_course')) {
    wp_enqueue_style('eia-course-single', ...);
}
```

## 🎯 Comment Tester

### **1. Accéder à un cours**
```
http://localhost/eia-wp/courses/commerce-international-debutant/
```
ou n'importe quel cours créé par le seeder.

### **2. Vérifier l'affichage**
- ✅ Header gradient avec titre
- ✅ Video player/preview
- ✅ Tabs fonctionnels (cliquer pour changer)
- ✅ Sidebar collante à droite
- ✅ Sections accordéon qui s'ouvrent/ferment

### **3. Tester les interactions**
- **Cliquer sur les tabs** → Le contenu change
- **Cliquer sur une section** → Elle se déplie/replie
- **Scroller la page** → Le sidebar reste fixe
- **Mode mobile** → Layout responsive (sidebar en bas)

## 🎨 Couleurs Utilisées

### **Palette EIA**
- **Bleu principal** : `#2D4FB3`
- **Orange** : `#F59E0B`
- **Violet** : `#8B5CF6`
- **Vert** : `#10B981`
- **Rouge** : `#EF4444`

### **Grays**
- Gray-50 : `#F9FAFB`
- Gray-100 : `#F3F4F6`
- Gray-200 : `#E5E7EB`
- Gray-600 : `#4B5563`
- Gray-800 : `#1F2937`

## 🔧 Personnalisation

### **Modifier le gradient du header**
Ligne 30 de `single-course.php` :
```php
<div class="bg-gradient-to-r from-eia-blue to-purple-700 text-white py-8">
```

### **Changer la couleur des boutons**
Ligne 115 :
```php
<button class="w-full bg-eia-orange text-white ...">
```

### **Ajuster le nombre d'étoiles**
Ligne 286 (Reviews tab) :
```php
<?php for ($i = 0; $i < 5; $i++) : ?>
```

## 📱 Responsive Design

### **Breakpoints**
- **Mobile** : < 640px → Single column
- **Tablet** : 640px - 1024px → Stacked layout
- **Desktop** : > 1024px → Sidebar layout

### **Comportement Mobile**
- Sidebar passe en dessous du contenu
- Tabs deviennent scrollables horizontalement
- Video player garde aspect ratio 16:9
- Font sizes adaptés

## ♿ Accessibilité

- ✅ **Focus visible** sur tous les éléments interactifs
- ✅ **ARIA labels** pour screen readers
- ✅ **Keyboard navigation** complète
- ✅ **Contrast ratio** WCAG AA compliant
- ✅ **Reduced motion** support

## 🚀 Performance

### **Optimisations**
- CSS chargé uniquement sur pages de cours (`is_singular('lp_course')`)
- Animations CSS (pas de JS lourd)
- Images lazy-loaded (si post_thumbnail)
- Scrollbar customisée sans JS

### **Metrics attendus**
- **First Paint** : < 1s
- **Interactive** : < 2s
- **CSS Size** : ~12KB
- **HTML Size** : ~30KB

## 🐛 Debugging

### **Template non chargé ?**
Vérifier que le dossier existe :
```bash
ls wp-content/themes/eia-theme/learnpress/
```

### **Styles non appliqués ?**
Vérifier dans DevTools que `course-single.css` est chargé :
```
Network > Filter: CSS
```

### **Tabs ne changent pas ?**
Vérifier la console JavaScript pour erreurs :
```
F12 > Console
```

## 📚 Ressources

- **Tailwind CSS** : https://tailwindcss.com/
- **LearnPress Docs** : https://learnpress.com/docs/
- **Design inspiration** : `.claude/Course-Flutter-Dart-Complete-App-Development-Course.png`

## 🎓 Prochaines Étapes

### **Améliorations Possibles**
1. **Intégrer un vrai video player** (Video.js, Plyr)
2. **AJAX pour Q&A** (poster questions sans reload)
3. **Notes enregistrées** en base de données
4. **Reviews fonctionnelles** avec système de vote
5. **Progression en temps réel** avec AJAX
6. **Certificat téléchargeable** après complétion

---

**Date de création** : 30 septembre 2025
**Version** : 1.0.0
**Designer** : Claude Code AI