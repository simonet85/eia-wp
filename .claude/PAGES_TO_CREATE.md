# Pages WordPress à Créer - EIA LMS

## 📄 Pages Obligatoires pour LMS

### 1. Pages Dashboards (Priorité HAUTE)

#### **Dashboard Étudiant**
- **Titre :** Dashboard Étudiant
- **Slug :** `student-dashboard`
- **Template :** Student Dashboard
- **Contenu :** (Laisser vide - géré par le template)
- **Status :** Publié
- **Visibilité :** Privée (accessible uniquement aux étudiants connectés)

**Comment créer :**
1. WordPress Admin > Pages > Ajouter
2. Titre : "Dashboard Étudiant"
3. Attributs de page > Modèle : "Student Dashboard"
4. Publier

#### **Dashboard Formateur**
- **Titre :** Dashboard Formateur
- **Slug :** `instructor-dashboard`
- **Template :** Instructor Dashboard
- **Contenu :** (Laisser vide - géré par le template)
- **Status :** Publié
- **Visibilité :** Privée (accessible uniquement aux formateurs connectés)

**Comment créer :**
1. WordPress Admin > Pages > Ajouter
2. Titre : "Dashboard Formateur"
3. Attributs de page > Modèle : "Instructor Dashboard"
4. Publier

---

### 2. Pages LearnPress (Priorité HAUTE)

Ces pages seront créées automatiquement par LearnPress lors de l'activation :

#### **Cours**
- **Titre :** Cours
- **Slug :** `cours`
- **Shortcode :** `[learn_press_courses]`
- **Description :** Page catalogue de tous les cours

#### **Profil**
- **Titre :** Profil
- **Slug :** `profil`
- **Shortcode :** `[learn_press_profile]`
- **Description :** Page profil utilisateur avec cours, progrès, certificats

#### **Checkout**
- **Titre :** Commander
- **Slug :** `commander`
- **Shortcode :** `[learn_press_checkout]`
- **Description :** Page de paiement pour cours payants

---

### 3. Pages BuddyPress (Priorité MOYENNE)

Ces pages seront créées automatiquement par BuddyPress lors de l'activation :

#### **Membres**
- **Titre :** Membres
- **Slug :** `membres`
- **Description :** Liste de tous les membres de la communauté

#### **Activité**
- **Titre :** Activité
- **Slug :** `activite`
- **Description :** Flux d'activité de la communauté

#### **Groupes**
- **Titre :** Groupes
- **Slug :** `groupes`
- **Description :** Groupes d'étude et communautés

---

### 4. Pages Supplémentaires (Priorité BASSE)

#### **À Propos de l'EIA**
- **Titre :** À Propos
- **Slug :** `a-propos`
- **Contenu :** Histoire, mission, valeurs de l'EIA
- **Template :** Page par défaut

#### **Contact**
- **Titre :** Contact
- **Slug :** `contact`
- **Contenu :** Formulaire de contact, coordonnées
- **Template :** Page par défaut

#### **FAQ**
- **Titre :** Questions Fréquentes
- **Slug :** `faq`
- **Contenu :** Questions/réponses sur l'inscription, les cours, etc.
- **Template :** Page par défaut

#### **Devenir Formateur**
- **Titre :** Devenir Formateur
- **Slug :** `devenir-formateur`
- **Contenu :** Informations pour devenir formateur à l'EIA
- **Template :** Page par défaut

---

## 🔧 Configuration des Menus

### Menu Principal (Primary Menu)
- Accueil
- À Propos
- Cours
- Admission
- Alumni
- Événement
- Contact

### Menu Étudiant (Student Menu)
- Dashboard
- Mes Cours
- Mon Profil
- Mes Certificats
- Messages

### Menu Formateur (Instructor Menu)
- Dashboard
- Mes Cours
- Créer un Cours
- Mes Étudiants
- Statistiques

### Menu Footer
- À Propos
- Cours
- Devenir Formateur
- FAQ
- Contact
- Conditions d'utilisation
- Politique de confidentialité

---

## 📋 Script SQL pour Création Rapide

Si vous avez accès à la base de données MySQL :

```sql
-- Créer Dashboard Étudiant
INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
VALUES (1, NOW(), NOW(), '', 'Dashboard Étudiant', '', 'publish', 'closed', 'closed', '', 'student-dashboard', '', '', NOW(), NOW(), '', 0, '', 0, 'page', '', 0);

-- Créer Dashboard Formateur
INSERT INTO wp_posts (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, post_status, comment_status, ping_status, post_password, post_name, to_ping, pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, guid, menu_order, post_type, post_mime_type, comment_count)
VALUES (1, NOW(), NOW(), '', 'Dashboard Formateur', '', 'publish', 'closed', 'closed', '', 'instructor-dashboard', '', '', NOW(), NOW(), '', 0, '', 0, 'page', '', 0);

-- Assigner template à Dashboard Étudiant
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT ID, '_wp_page_template', 'page-templates/student-dashboard.php'
FROM wp_posts WHERE post_name = 'student-dashboard' AND post_type = 'page';

-- Assigner template à Dashboard Formateur
INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
SELECT ID, '_wp_page_template', 'page-templates/instructor-dashboard.php'
FROM wp_posts WHERE post_name = 'instructor-dashboard' AND post_type = 'page';
```

---

## ✅ Checklist de Création

### Immédiat
- [ ] Créer page "Dashboard Étudiant" avec template
- [ ] Créer page "Dashboard Formateur" avec template
- [ ] Activer LearnPress (créera pages automatiquement)
- [ ] Activer BuddyPress (créera pages automatiquement)

### Configuration Menus
- [ ] Créer menu "Student Menu"
- [ ] Créer menu "Instructor Menu"
- [ ] Assigner menus aux emplacements du thème

### Tests
- [ ] Tester accès Dashboard Étudiant (avec compte étudiant)
- [ ] Tester accès Dashboard Formateur (avec compte formateur)
- [ ] Vérifier redirections login basées sur rôle
- [ ] Vérifier restrictions d'accès par rôle

---

## 🚀 URLs après Création

- **Dashboard Étudiant :** `http://localhost/eia-wp/student-dashboard/`
- **Dashboard Formateur :** `http://localhost/eia-wp/instructor-dashboard/`
- **Cours :** `http://localhost/eia-wp/cours/`
- **Profil :** `http://localhost/eia-wp/profil/`
- **Membres :** `http://localhost/eia-wp/membres/`

---

*Document créé automatiquement - Phase 1 LMS*
*Date : 30 septembre 2025*