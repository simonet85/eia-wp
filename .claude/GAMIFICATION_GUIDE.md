# Guide Système de Gamification - EIA LMS

## 🎮 Vue d'ensemble

Système complet de badges, points d'expérience (XP), niveaux et classement pour augmenter l'engagement des étudiants.

## 📊 Architecture

### Tables de base de données

**1. `wp_eia_user_badges`** - Badges gagnés
```sql
- id: Identifiant unique
- user_id: ID utilisateur
- badge_type: Type de badge (clé)
- badge_name: Nom du badge
- badge_description: Description
- earned_date: Date d'obtention
- metadata: JSON (icon, color)
```

**2. `wp_eia_user_points`** - Points et niveaux
```sql
- id: Identifiant unique
- user_id: ID utilisateur
- points: Points actuels
- level: Niveau actuel
- total_xp: Total XP accumulé
- last_updated: Dernière mise à jour
```

**3. `wp_eia_points_history`** - Historique des points
```sql
- id: Identifiant unique
- user_id: ID utilisateur
- action_type: Type d'action
- points: Points gagnés
- description: Description
- reference_id: ID de référence
- reference_type: Type de référence
- created_date: Date de création
```

## 🏆 Badges Disponibles

### 1. Première Inscription
- **Nom**: Première Inscription
- **Condition**: S'inscrire au premier cours
- **Icône**: ⭐ (fas fa-star)
- **Couleur**: #3B82F6 (Bleu)
- **Points**: 50 XP

### 2. Collectionneur de Cours
- **Nom**: Collectionneur de Cours
- **Condition**: S'inscrire à 5 cours
- **Icône**: 📚 (fas fa-book-reader)
- **Couleur**: #8B5CF6 (Violet)
- **Points**: 200 XP

### 3. Premier Succès
- **Nom**: Premier Succès
- **Condition**: Compléter le premier cours
- **Icône**: 🏆 (fas fa-trophy)
- **Couleur**: #F59E0B (Orange)
- **Points**: 100 XP

### 4. Maître des Cours
- **Nom**: Maître des Cours
- **Condition**: Compléter 5 cours
- **Icône**: 🎓 (fas fa-graduation-cap)
- **Couleur**: #10B981 (Vert)
- **Points**: 500 XP

### 5. As du Devoir
- **Nom**: As du Devoir
- **Condition**: Note parfaite (100%) sur un devoir
- **Icône**: 🏅 (fas fa-medal)
- **Couleur**: #EF4444 (Rouge)
- **Points**: 150 XP

### 6. Série de 7 jours
- **Nom**: Série de 7 jours
- **Condition**: Se connecter 7 jours consécutifs
- **Icône**: 🔥 (fas fa-fire)
- **Couleur**: #F97316 (Orange foncé)
- **Points**: 300 XP

### 7. Expert Quiz
- **Nom**: Expert Quiz
- **Condition**: Réussir 10 quiz avec 80%+
- **Icône**: 🧠 (fas fa-brain)
- **Couleur**: #06B6D4 (Cyan)
- **Points**: 400 XP

### 8. Apprenant Rapide
- **Nom**: Apprenant Rapide
- **Condition**: Compléter un cours en moins de 7 jours
- **Icône**: 🚀 (fas fa-rocket)
- **Couleur**: #EC4899 (Rose)
- **Points**: 250 XP

## 📈 Système de Points (XP)

### Attribution automatique

**Inscription à un cours**: 25 XP
```php
Action: 'course_enrolled'
```

**Complétion d'un cours**: 100 XP
```php
Action: 'course_completed'
```

**Devoir noté**:
- 90%+ : 50 XP
- 70-89% : 30 XP
- <70% : 10 XP
```php
Action: 'assignment_graded'
```

**Connexion quotidienne**: 10 XP (1x par jour)
```php
Action: 'daily_login'
```

**Badge gagné**: Points variables selon badge
```php
Action: 'badge_earned'
```

## 🎯 Système de Niveaux

### Tableau de progression

| Niveau | XP Requis |
|--------|-----------|
| 1      | 0         |
| 2      | 100       |
| 3      | 250       |
| 4      | 500       |
| 5      | 1000      |
| 6      | 2000      |
| 7      | 3500      |
| 8      | 5500      |
| 9      | 8000      |
| 10     | 12000     |

### Calcul automatique

Le niveau est calculé automatiquement en fonction du `total_xp` de l'utilisateur.

## 🏅 Tableau de Classement

### URL
`http://eia-wp.test/classement/`

### Fonctionnalités

1. **Podium Top 3**
   - Animation couronne pour le 1er
   - Médailles or/argent/bronze
   - Avatars colorés par rang

2. **Liste complète**
   - Top 50 étudiants
   - Mise en évidence utilisateur actuel
   - Affichage rang, nom, niveau, XP

3. **Design**
   - Fond gradient violet
   - Carte blanche avec ombres
   - Animations et effets hover

## 💻 Intégration Dashboard

### Dashboard Étudiant (`/mes-cours/`)

**Section Gamification** :
- Carte Niveau avec progression
- Barre de progression XP
- Rang dans le classement
- Lien vers tableau complet
- Grille de badges (6 premiers)
- État vide si aucun badge

## 🔧 Fichiers du Système

### Plugin Core

**`class-gamification.php`** - Classe principale
```
wp-content/plugins/eia-lms-core/includes/class-gamification.php
```

Méthodes principales:
- `award_points()` - Attribuer points
- `award_badge()` - Attribuer badge
- `get_user_points()` - Récupérer points utilisateur
- `get_user_badges()` - Récupérer badges utilisateur
- `get_leaderboard()` - Récupérer classement
- `get_user_rank()` - Récupérer rang utilisateur

### Templates

**Template Classement**
```
wp-content/themes/eia-theme/page-templates/leaderboard.php
```

**Dashboard Étudiant** (section gamification)
```
wp-content/themes/eia-theme/functions.php (lignes 919-1008)
```

### Scripts d'installation

**Création tables**
```
http://eia-wp.test/create-gamification-tables.php
```

**Création page classement**
```
http://eia-wp.test/create-leaderboard-page.php
```

## 🚀 Installation

### 1. Créer les tables
```
1. Accéder: http://eia-wp.test/create-gamification-tables.php
2. Vérifier: 3 tables créées avec succès
```

### 2. Créer la page classement
```
1. Accéder: http://eia-wp.test/create-leaderboard-page.php
2. URL créée: http://eia-wp.test/classement/
```

### 3. Activer le module
Le module est déjà chargé automatiquement via:
```php
// eia-lms-core.php ligne 93
require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-gamification.php';

// eia-lms-core.php ligne 114
EIA_Gamification::get_instance();
```

## 🎯 Actions WordPress Écoutées

### Hooks LearnPress

```php
// Inscription cours
add_action('learn-press/user/course-enrolled', [$this, 'on_course_enrolled'], 10, 3);

// Complétion cours
add_action('learn-press/user/course-finished', [$this, 'on_course_completed'], 10, 3);
```

### Hooks Personnalisés

```php
// Devoir noté
add_action('eia_assignment_graded', [$this, 'on_assignment_graded'], 10, 3);

// Connexion utilisateur
add_action('wp_login', [$this, 'on_user_login'], 10, 2);
```

## 📊 Utilisation API

### Attribuer des points manuellement

```php
$gamification = EIA_Gamification::get_instance();
$gamification->award_points(
    $user_id,
    100,                    // Points
    'custom_action',        // Type d'action
    'Description',          // Description
    $reference_id,          // ID de référence (optionnel)
    'reference_type'        // Type de référence (optionnel)
);
```

### Attribuer un badge manuellement

```php
$gamification = EIA_Gamification::get_instance();
$gamification->award_badge($user_id, 'first_enrollment');
```

### Récupérer les données utilisateur

```php
$gamification = EIA_Gamification::get_instance();

// Points et niveau
$user_points = $gamification->get_user_points($user_id);
echo $user_points->level;
echo $user_points->total_xp;

// Badges
$user_badges = $gamification->get_user_badges($user_id);

// Rang
$rank = $gamification->get_user_rank($user_id);

// Classement
$leaderboard = $gamification->get_leaderboard(10); // Top 10
```

## 🎨 Personnalisation

### Ajouter un nouveau badge

```php
// Dans class-gamification.php, ajouter à l'array $badges
'nouveau_badge' => array(
    'name' => 'Nom du Badge',
    'description' => 'Description',
    'icon' => 'fas fa-icon-name',
    'color' => '#HEXCOLOR',
    'points' => 100,
    'condition' => 'condition_type',
    'threshold' => 1
),
```

### Modifier les niveaux

```php
// Dans class-gamification.php, modifier l'array $levels
private $levels = array(
    1 => 0,
    2 => 100,
    3 => 250,
    // ... ajouter ou modifier
);
```

## 🧪 Tests

### Tester l'attribution de points

1. Se connecter avec un compte étudiant
2. S'inscrire à un cours → devrait gagner 25 XP
3. Compléter un cours → devrait gagner 100 XP
4. Soumettre un devoir → devrait gagner 10-50 XP selon note

### Tester les badges

1. Vérifier badge "Première Inscription" après 1er cours
2. Vérifier badge "Premier Succès" après complétion
3. Vérifier badge "As du Devoir" après note 100%

### Vérifier le classement

1. Accéder à `/classement/`
2. Vérifier podium top 3
3. Vérifier mise en évidence utilisateur actuel
4. Vérifier tri par XP décroissant

## 📈 Statistiques et Métriques

### Requêtes utiles

**Top 10 badges les plus gagnés**
```sql
SELECT badge_type, badge_name, COUNT(*) as count
FROM wp_eia_user_badges
GROUP BY badge_type
ORDER BY count DESC
LIMIT 10;
```

**Moyenne XP par utilisateur**
```sql
SELECT AVG(total_xp) as avg_xp
FROM wp_eia_user_points;
```

**Répartition par niveau**
```sql
SELECT level, COUNT(*) as count
FROM wp_eia_user_points
GROUP BY level
ORDER BY level;
```

## 🔔 Notifications (Futur)

### À implémenter

- Notification badge gagné
- Notification niveau atteint
- Notification progression classement
- Email récapitulatif hebdomadaire

## 🎯 Prochaines Améliorations

1. **Challenges hebdomadaires**
   - Objectifs temporaires
   - Récompenses bonus

2. **Badges personnalisables**
   - Upload images custom
   - Création par instructeur

3. **Système de parrainage**
   - Points pour inviter amis
   - Badge "Ambassadeur"

4. **Boutique de récompenses**
   - Échanger XP contre avantages
   - Débloquer contenus premium

---

*Guide créé le 2 octobre 2025*
*Version 1.0.0*
