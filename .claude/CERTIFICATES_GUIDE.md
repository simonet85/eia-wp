# Guide Système de Certificats - EIA LMS

## 🎓 Vue d'ensemble

Système complet de génération automatique de certificats PDF pour valider la complétion des cours avec un design professionnel EIA.

## 📊 Architecture

### Table de base de données

**`wp_eia_certificates`** - Certificats émis
```sql
- id: Identifiant unique
- certificate_code: Code unique (EIA-XXXXXXXXXXXX)
- user_id: ID étudiant
- course_id: ID cours
- completion_date: Date de complétion
- grade_percentage: Note finale (%)
- instructor_id: ID instructeur
- generated_date: Date de génération
- metadata: JSON (titre cours, durée, etc.)
```

## 🎨 Design du Certificat

### Caractéristiques visuelles

**Format**: A4 paysage (297mm x 210mm)

**Éléments de design**:
- Bordure bleue EIA épaisse (20px, #2D4FB3)
- Coins décoratifs dorés (100px, #F59E0B)
- Dégradé de fond subtil blanc/gris
- Typographie: Georgia (serif) pour élégance
- Ligne de séparation dorée sous le titre

**Sections du certificat**:
1. **En-tête**: Logo/Nom EIA + ligne décorative
2. **Titre**: "Certificat de Réussite" (italique)
3. **Corps**: Nom étudiant, titre cours, note
4. **Pied**: Date, signature instructeur
5. **Code**: Code de vérification + URL

## 🔄 Fonctionnement Automatique

### Génération automatique

**Trigger**: Hook `learn-press/user/course-finished`

**Conditions**:
- Cours complété à 100%
- Pas de certificat existant pour ce cours
- Cours publié et valide

**Processus**:
1. Vérification si certificat existe déjà
2. Génération code unique (EIA-XXXXXXXXXXXX)
3. Calcul note finale (moyenne quiz si disponible)
4. Insertion en base de données
5. Attribution +75 XP (gamification)

### Calcul de la note

**Méthode**: Moyenne des résultats de quiz du cours

```php
Note = (Nombre quiz réussis / Total quiz) × 100
```

Si aucun quiz → Note = NULL (non affiché)

## 📱 Affichage Dashboard Étudiant

### Section "Mes Certificats"

**Emplacement**: Après "Mes Devoirs", avant "Mes Cours"

**Carte certificat** comprend:
- Icône certificat (vert)
- Titre du cours
- Date de complétion
- Note finale avec barre de progression
- Code de vérification
- Bouton "Télécharger le certificat"

**État vide**:
- Grande icône certificat (opacité 30%)
- Message encourageant
- Info bulle explicative

## 🔍 Système de Vérification

### Page de vérification

**URL**: `/verification-certificat/`

**Fonctionnalités**:
1. **Formulaire de recherche**
   - Champ code avec validation
   - Auto-uppercase
   - Design moderne avec transitions

2. **Affichage certificat valide**
   - Certificat complet en HTML
   - Bouton impression (masqué à l'impression)
   - Format optimisé A4

3. **Message d'erreur**
   - Design rouge clair si invalide
   - Affichage du code recherché
   - Suggestions de vérification

## 📄 Fichiers du Système

### Module Core

**`class-certificates.php`** - Classe principale
```
wp-content/plugins/eia-lms-core/includes/class-certificates.php
```

**Méthodes principales**:
```php
// Génération automatique
auto_generate_certificate($course_id, $user_id, $result)

// Récupération
get_user_certificates($user_id)
get_certificate_by_code($code)

// Affichage
generate_certificate_html($certificate_code)

// Calcul
calculate_course_grade($user_id, $course_id)
```

### Templates

**Page vérification**:
```
wp-content/themes/eia-theme/page-templates/certificate-verification.php
```

**Dashboard étudiant** (section certificats):
```
wp-content/themes/eia-theme/functions.php (lignes 1220-1313)
```

### Scripts d'installation

**Création page vérification**:
```
http://eia-wp.test/create-certificate-verification-page.php
```

**Note**: La table est créée automatiquement au chargement du plugin

## 🚀 Installation & Configuration

### 1. Activer le module

Le module est chargé automatiquement:
```php
// eia-lms-core.php ligne 94
require_once EIA_LMS_CORE_PLUGIN_DIR . 'includes/class-certificates.php';

// eia-lms-core.php ligne 116
EIA_Certificates::get_instance();
```

### 2. Créer la page de vérification

```
1. Accéder: http://eia-wp.test/create-certificate-verification-page.php
2. URL créée: http://eia-wp.test/verification-certificat/
```

### 3. Tester la génération

```
1. Se connecter comme étudiant
2. Compléter un cours à 100%
3. Vérifier dashboard → section "Mes Certificats"
4. Cliquer "Télécharger le certificat"
5. Vérifier affichage et impression
```

## 🔐 Sécurité

### Code de vérification

**Format**: `EIA-XXXXXXXXXXXX` (15 caractères)

**Génération**:
- Utilise `wp_generate_password()` sécurisé
- Vérifie unicité en base de données
- Stocké en majuscules

### Validations

**Côté serveur**:
- Sanitization avec `sanitize_text_field()`
- Vérification existence en base
- Protection contre SQL injection (prepared statements)

**Côté client**:
- Champ requis
- Auto-uppercase pour cohérence
- Validation format via regex possible

## 🎨 Personnalisation

### Modifier le design du certificat

**Fichier**: `class-certificates.php` méthode `generate_certificate_html()`

**Éléments personnalisables**:
```php
// Couleurs
Bordure principale: #2D4FB3 (bleu EIA)
Coins décoratifs: #F59E0B (orange EIA)
Fond: gradient blanc/gris

// Typographie
Police principale: Georgia, serif
Taille titre: 48px
Taille nom: 42px
Taille cours: 28px

// Dimensions
Largeur: 297mm (A4 paysage)
Hauteur: 210mm
Padding: 60px
Bordure: 20px
```

### Ajouter des éléments

Exemples d'ajouts possibles:
- Logo EIA en image
- QR Code de vérification
- Cachet/Tampon numérique
- Signature numérique instructeur
- Mentions légales
- Numéro d'accréditation

## 📊 Statistiques

### Requêtes utiles

**Certificats émis par mois**:
```sql
SELECT
    DATE_FORMAT(completion_date, '%Y-%m') as month,
    COUNT(*) as count
FROM wp_eia_certificates
GROUP BY month
ORDER BY month DESC;
```

**Certificats par cours**:
```sql
SELECT
    p.post_title,
    COUNT(c.id) as certificates_count,
    AVG(c.grade_percentage) as avg_grade
FROM wp_eia_certificates c
INNER JOIN wp_posts p ON c.course_id = p.ID
GROUP BY c.course_id
ORDER BY certificates_count DESC;
```

**Meilleurs étudiants**:
```sql
SELECT
    u.display_name,
    COUNT(c.id) as certificates_count,
    AVG(c.grade_percentage) as avg_grade
FROM wp_eia_certificates c
INNER JOIN wp_users u ON c.user_id = u.ID
GROUP BY c.user_id
ORDER BY certificates_count DESC, avg_grade DESC
LIMIT 10;
```

## 🔔 Notifications (Intégration)

### Email automatique

Lors de la génération du certificat:
```php
// À ajouter dans auto_generate_certificate()
if (class_exists('EIA_Notifications')) {
    $notifications = EIA_Notifications::get_instance();
    $notifications->notify_certificate_earned($user_id, $course_id, $certificate_code);
}
```

### Template email suggéré

**Sujet**: "🎓 Félicitations ! Votre certificat EIA est disponible"

**Contenu**:
- Message de félicitations
- Nom du cours
- Lien téléchargement certificat
- Code de vérification
- Lien partage réseaux sociaux

## 🌐 Intégration Réseaux Sociaux

### Partage certificat

Boutons à ajouter sur la page certificat:
```html
<!-- LinkedIn -->
<a href="https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME&name=COURSE_TITLE&organizationId=EIA&issueYear=YEAR&issueMonth=MONTH&certUrl=VERIFICATION_URL">
    Ajouter à LinkedIn
</a>

<!-- Twitter -->
<a href="https://twitter.com/intent/tweet?text=J'ai%20obtenu%20mon%20certificat%20EIA%20-%20COURSE_TITLE&url=VERIFICATION_URL">
    Partager sur Twitter
</a>

<!-- Facebook -->
<a href="https://www.facebook.com/sharer/sharer.php?u=VERIFICATION_URL">
    Partager sur Facebook
</a>
```

## 📱 Export & Formats

### Formats supportés

**Actuellement**: HTML (affichage + impression)

**Possibles ajouts futurs**:
1. **PDF natif** (avec bibliothèque TCPDF/FPDF)
2. **Image PNG** (via conversion HTML → Image)
3. **Badge numérique** (Open Badges standard)
4. **Blockchain** (certificat vérifiable sur blockchain)

### Génération PDF

Pour ajouter export PDF natif:
```php
// Installer: composer require tecnickcom/tcpdf

public function generate_pdf($certificate_code) {
    require_once('tcpdf/tcpdf.php');

    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetTitle('Certificat EIA');
    $pdf->AddPage();

    $html = $this->generate_certificate_html($certificate_code);
    $pdf->writeHTML($html, true, false, true, false, '');

    $pdf->Output('certificat-eia-'.$certificate_code.'.pdf', 'D');
}
```

## 🎯 Bonnes Pratiques

### Pour les étudiants

1. **Télécharger immédiatement** après obtention
2. **Sauvegarder code** de vérification en lieu sûr
3. **Imprimer version papier** pour archives
4. **Partager sur profils** professionnels (LinkedIn)
5. **Vérifier orthographe** nom avant impression

### Pour les instructeurs

1. **Configurer quiz** pour calcul de note
2. **Vérifier structure** cours avant publication
3. **Informer étudiants** sur certificats disponibles
4. **Promouvoir certificats** comme motivation
5. **Suivre statistiques** d'émission

### Pour les administrateurs

1. **Sauvegarder table** certificats régulièrement
2. **Monitorer codes** uniques (pas de collision)
3. **Vérifier permissions** page vérification (publique)
4. **Optimiser requêtes** pour grands volumes
5. **Archiver anciens** certificats si nécessaire

## 🐛 Dépannage

### Certificat non généré

**Causes possibles**:
- Cours non complété à 100%
- Hook LearnPress non déclenché
- Table certificats inexistante
- Erreur PHP silencieuse

**Solutions**:
```php
// Vérifier table existe
SHOW TABLES LIKE 'wp_eia_certificates';

// Vérifier complétion cours
SELECT * FROM wp_learnpress_user_items
WHERE user_id = X AND item_id = Y AND item_type = 'lp_course';

// Générer manuellement
$certs = EIA_Certificates::get_instance();
$certs->auto_generate_certificate($course_id, $user_id, $result);
```

### Code invalide

**Vérifications**:
1. Code exact (sensible à la casse normalement, mais converti uppercase)
2. Trait d'union présent (`EIA-` prefix)
3. Certificat existe en base
4. Pas de caractères spéciaux/espaces

### Affichage incorrect

**Problèmes courants**:
- CSS non chargé → vérifier Font Awesome
- Mise en page cassée → vérifier dimensions A4
- Impression coupée → vérifier marges navigateur

## 📈 Prochaines Améliorations

1. **Génération PDF natif** (bibliothèque TCPDF)
2. **QR Code sur certificat** (vérification rapide)
3. **Badge numérique** (standard Open Badges)
4. **Envoi email automatique** (avec PDF joint)
5. **Galerie certificats** (page publique showcase)
6. **Statistiques avancées** (tableau de bord admin)
7. **Templates multiples** (choisir design par cours)
8. **Cachet numérique** (signature cryptographique)

## 🔗 Ressources

**Standards**:
- Open Badges: https://openbadges.org/
- IMS Global: https://www.imsglobal.org/

**Bibliothèques PHP**:
- TCPDF: https://tcpdf.org/
- FPDF: http://www.fpdf.org/
- DomPDF: https://github.com/dompdf/dompdf

**Inspiration Design**:
- Canva Certificates: https://www.canva.com/templates/certificates/
- Certificate Templates: https://www.template.net/business/certificate-templates/

---

*Guide créé le 2 octobre 2025*
*Version 1.0.0*
