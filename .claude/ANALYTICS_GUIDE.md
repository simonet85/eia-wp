# 📊 Analytics & Rapports - Guide Complet

## Vue d'ensemble

Le système d'analytics fournit aux instructeurs des insights détaillés sur la performance de leurs cours, l'engagement des étudiants, et des visualisations graphiques avec Chart.js.

## Fonctionnalités

### 1. Dashboard Analytics Instructeur
**URL**: `/analytics-instructeur/`
**Accès**: Instructeurs et Administrateurs uniquement

**Statistiques clés affichées**:
- **Étudiants inscrits**: Nombre total d'étudiants dans le cours sélectionné
- **Taux de complétion**: Pourcentage d'étudiants ayant terminé le cours
- **Durée moyenne**: Temps moyen (en jours) pour compléter le cours
- **Nouvelles inscriptions**: Nombre d'inscriptions pendant la période sélectionnée

### 2. Graphiques de Performance

#### **Graphique 1: Inscriptions par jour**
- Type: Line chart (Chart.js)
- Données: Nombre d'inscriptions quotidiennes
- Couleur: Bleu (#3B82F6)
- Période: Configurable via filtres de date

#### **Graphique 2: Complétions par jour**
- Type: Line chart (Chart.js)
- Données: Nombre de cours terminés par jour
- Couleur: Vert (#10B981)
- Période: Configurable via filtres de date

### 3. Liste des Étudiants

Tableau détaillé avec:
- Nom de l'étudiant
- Email
- Date d'inscription
- Statut (En cours / Terminé / Inconnu)
- Barre de progression visuelle avec pourcentage

### 4. Filtres

**Filtres disponibles**:
- **Sélection du cours**: Dropdown listant tous les cours de l'instructeur
- **Date début**: Filtre la période de début
- **Date fin**: Filtre la période de fin

Par défaut: 30 derniers jours

### 5. Export de Données

#### **Export CSV**
Génère un fichier CSV contenant:
```
Rapport Analytics - [Nom du cours]
Période: [date_debut] au [date_fin]

Statistiques Générales
Étudiants inscrits,[nombre]
Taux de complétion,[pourcentage]%
Durée moyenne,[jours] jours

Liste des Étudiants
Nom,Email,Date inscription,Statut,Progression
[données étudiants...]
```

**Nom du fichier**: `analytics-[nom-cours]-[date].csv`

#### **Export PDF**
Utilise `window.print()` pour générer un PDF via l'impression navigateur

## Architecture Technique

### Fichiers principaux

**Template de page**:
- `wp-content/themes/eia-theme/page-templates/instructor-analytics.php` (1084 lignes)

**Module backend**:
- `wp-content/plugins/eia-lms-core/includes/class-reports.php` (607 lignes)

**Bibliothèque graphique**:
- Chart.js 4.4.0 (CDN)

### Requêtes SQL principales

#### Nombre d'étudiants par cours
```sql
SELECT COUNT(DISTINCT user_id)
FROM wp_learnpress_user_items
WHERE item_id = %d AND item_type = 'lp_course'
```

#### Taux de complétion
```sql
-- Total inscriptions
SELECT COUNT(*) FROM wp_learnpress_user_items
WHERE item_id = %d AND item_type = 'lp_course'

-- Complétions
SELECT COUNT(*) FROM wp_learnpress_user_items
WHERE item_id = %d AND item_type = 'lp_course' AND status = 'finished'

-- Calcul: (complétions / total) * 100
```

#### Durée moyenne de complétion
```sql
SELECT AVG(TIMESTAMPDIFF(DAY, start_time, end_time)) as avg_days
FROM wp_learnpress_user_items
WHERE item_id = %d AND item_type = 'lp_course' AND status = 'finished'
```

#### Inscriptions par jour
```sql
SELECT DATE(start_time) as date, COUNT(*) as count
FROM wp_learnpress_user_items
WHERE item_id = %d AND item_type = 'lp_course'
  AND DATE(start_time) BETWEEN %s AND %s
GROUP BY DATE(start_time)
ORDER BY date ASC
```

### Méthodes de la classe EIA_Reports

#### Méthodes publiques

**`get_course_analytics($course_id, $date_from, $date_to)`**
- Retourne analytics complètes pour un cours
- Inclut: enrollments, completions, engagement, total_students, completion_rate, avg_duration

**`get_dashboard_stats()`**
- Statistiques globales de la plateforme (admin uniquement)
- Utilisé pour le dashboard global (non implémenté dans cette version)

**`get_student_report($user_id)`**
- Rapport individuel d'un étudiant
- Inclut: cours, quiz, certificats, statistiques

#### Méthodes privées

- `get_course_student_count($course_id)`: Compte les étudiants uniques
- `get_course_completion_rate($course_id)`: Calcule le taux de complétion
- `get_avg_course_duration($course_id)`: Durée moyenne en jours

#### Méthodes d'export

- `generate_report_csv($report_type)`: Génère CSV selon le type
- `generate_dashboard_csv()`: Export statistiques globales
- `generate_courses_csv()`: Export liste des cours avec stats
- `generate_students_csv()`: Export liste des étudiants avec progression

### AJAX Handlers (non utilisés dans cette version)

La classe `EIA_Reports` fournit des handlers AJAX pour une future intégration dynamique:
- `wp_ajax_eia_get_dashboard_stats`
- `wp_ajax_eia_get_course_analytics`
- `wp_ajax_eia_get_student_report`
- `wp_ajax_eia_export_report`

## Configuration Chart.js

### Options globales
```javascript
const chartConfig = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0
            }
        }
    }
};
```

### Graphiques instanciés
```javascript
new Chart(document.getElementById('enrollmentsChart'), {
    type: 'line',
    data: enrollmentsData,
    options: chartConfig
});
```

## Intégration au Dashboard Instructeur

**Lien ajouté dans**: `page-templates/instructor-dashboard.php` (ligne 91-106)

```php
<a href="<?php echo site_url('/analytics-instructeur/'); ?>">
    <i class="fas fa-chart-line"></i> Analytics
</a>
```

**Position**: Header du dashboard, à côté du bouton "Créer un cours"

## Permissions et Sécurité

**Contrôle d'accès**:
```php
if (!current_user_can('edit_posts') && !current_user_can('manage_options')) {
    wp_redirect(home_url());
    exit;
}
```

**Restrictions**:
- Les instructeurs voient uniquement leurs propres cours
- Les admins voient tous les cours
- Requêtes SQL préparées avec `$wpdb->prepare()`

## Design et UX

**Palette de couleurs**:
- Bleu: `#3B82F6` (Étudiants)
- Vert: `#10B981` (Complétion)
- Orange: `#F59E0B` (Durée)
- Violet: `#8B5CF6` (Inscriptions)

**Éléments visuels**:
- Cartes statistiques avec icônes Font Awesome
- Graphiques avec dégradés et animations
- Tableau responsive avec hover effects
- Barres de progression animées
- Status badges colorés

**Responsive**:
```css
@media (max-width: 768px) {
    .filters-grid { grid-template-columns: 1fr; }
    .charts-section { grid-template-columns: 1fr; }
    .stats-grid { grid-template-columns: 1fr; }
}
```

## Tests et Validation

### Test Manuel

1. **Connexion instructeur**:
   ```
   Email: formateur_1_sarr@eia-demo.sn
   Password: password123
   ```

2. **Accéder au dashboard**:
   - `http://eia-wp.test/tableau-de-bord-instructeur/`
   - Cliquer sur "Analytics"

3. **Vérifier les données**:
   - Statistiques affichées correctement
   - Graphiques rendus
   - Tableau des étudiants complet
   - Filtres fonctionnels

4. **Tester l'export**:
   - Bouton "Exporter CSV" → fichier téléchargé
   - Bouton "Exporter PDF" → fenêtre d'impression

### Test Automatisé

Script de test: `create-analytics-page.php` (à usage unique)

Test backend disponible via:
```bash
php -r "require 'wp-load.php'; \$reports = EIA_Reports::get_instance(); var_dump(\$reports->get_course_analytics(772));"
```

## Améliorations Futures

### Phase 2 (recommandé)
1. **Graphique de progression temps réel**: Mise à jour auto toutes les 5min
2. **Comparaison multi-cours**: Comparer 2+ cours côte à côte
3. **Alertes automatiques**: Notifications si taux de complétion < 50%
4. **Analyse par section**: Quelle section pose problème?
5. **Prédictions IA**: Prédire quels étudiants risquent d'abandonner

### Phase 3 (avancé)
1. **Dashboard temps réel**: WebSocket pour données live
2. **Heatmap d'engagement**: Carte thermique des heures d'activité
3. **A/B Testing**: Tester différentes versions de cours
4. **Rapports personnalisables**: Builder de rapports drag & drop
5. **API externe**: Webhook pour intégrer à Zapier/Make

## Dépendances

**Frontend**:
- Chart.js 4.4.0 (CDN)
- Font Awesome 6.4.0 (déjà inclus dans le thème)

**Backend**:
- WordPress 5.0+
- LearnPress 4.0+
- PHP 7.4+
- MySQL 5.7+

**Tables requises**:
- `wp_learnpress_user_items` (LearnPress core)
- `wp_learnpress_sections` (LearnPress core)
- `wp_learnpress_section_items` (LearnPress core)
- `wp_posts` (WordPress core)
- `wp_users` (WordPress core)

**Tables optionnelles** (pour tracking avancé):
- `wp_eia_course_analytics` (EIA LMS Core - créée mais non utilisée dans v1)

## Migration depuis version précédente

Aucune migration nécessaire - Le système utilise les données LearnPress existantes.

Si vous avez la table `wp_eia_course_analytics` créée, elle peut être utilisée pour tracking avancé en ajoutant:
```php
add_action('learn-press/user/lesson-viewed', array($reports, 'track_lesson_view'), 10, 3);
add_action('learn-press/user/quiz-started', array($reports, 'track_quiz_start'), 10, 3);
```

## Support et Documentation

**Fichiers de référence**:
- Ce guide: `.claude/ANALYTICS_GUIDE.md`
- Guide principal: `CLAUDE.md`
- Guide devoirs: `.claude/ASSIGNMENTS_GUIDE.md`
- Guide certificats: `.claude/CERTIFICATES_GUIDE.md`

**Captures d'écran**: (à ajouter)
- Vue d'ensemble du dashboard analytics
- Graphiques de performance
- Tableau des étudiants

## Contributeurs

- Module Analytics: EIA LMS Core Team
- Chart.js: Chart.js Contributors (MIT License)
- Design: Inspiré des dashboards Moodle & Canvas LMS

---

**Version**: 1.0.0
**Dernière mise à jour**: 2025-10-03
**Statut**: ✅ Production Ready
