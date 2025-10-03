# Checklist de Test - Infrastructure LMS EIA

## 🧪 Tests à Effectuer

### ✅ Phase 1: Tests de Base

#### 1. Vérification Thème
- [ ] Le thème EIA est actif
- [ ] Tailwind CSS se charge correctement
- [ ] Les couleurs EIA (blue/orange) s'affichent
- [ ] Navigation fonctionne (desktop + mobile)
- [ ] Footer s'affiche correctement

**Test :** Visiter `http://localhost/eia-wp/` et vérifier l'affichage

---

#### 2. Vérification Plugins Installés
- [ ] LearnPress installé dans `/wp-content/plugins/learnpress/`
- [ ] BuddyPress installé dans `/wp-content/plugins/buddypress/`
- [ ] GamiPress installé dans `/wp-content/plugins/gamipress/`
- [ ] bbPress installé dans `/wp-content/plugins/bbpress/`
- [ ] H5P installé dans `/wp-content/plugins/h5p/`
- [ ] User Registration installé dans `/wp-content/plugins/user-registration/`
- [ ] WP Mail SMTP installé dans `/wp-content/plugins/wp-mail-smtp/`

**Test :** Vérifier dossier `wp-content/plugins/` ✅ FAIT

---

#### 3. Activation des Plugins
- [ ] Aller sur `http://localhost/eia-wp/wp-admin/plugins.php`
- [ ] Activer **LearnPress**
- [ ] Activer **BuddyPress**
- [ ] Activer **GamiPress**
- [ ] Activer **bbPress** (optionnel)
- [ ] Activer **User Registration**
- [ ] Vérifier qu'aucune erreur PHP n'apparaît

**Résultat attendu :** Tous les plugins actifs sans erreur

---

### ✅ Phase 2: Tests Fichiers LMS

#### 4. Vérification Fichiers Créés
```bash
# Vérifier que tous les fichiers existent
ls -la wp-content/themes/eia-theme/inc/
ls -la wp-content/themes/eia-theme/page-templates/
ls -la wp-content/themes/eia-theme/assets/js/
ls -la wp-content/themes/eia-theme/assets/css/
```

**Fichiers attendus :**
- [x] `inc/user-roles.php` ✅
- [x] `inc/lms-functions.php` ✅
- [x] `inc/ajax-handlers.php` ✅
- [x] `page-templates/student-dashboard.php` ✅
- [x] `page-templates/instructor-dashboard.php` ✅
- [x] `assets/js/lms-scripts.js` ✅
- [x] `assets/css/lms-styles.css` ✅

---

#### 5. Vérification functions.php
- [ ] Ouvrir `wp-content/themes/eia-theme/functions.php`
- [ ] Vérifier présence de la section "LMS EXTENSIONS"
- [ ] Vérifier `require_once` pour les 3 fichiers inc/
- [ ] Vérifier fonction `eia_lms_support()`
- [ ] Vérifier fonction `eia_lms_scripts()`

**Test :** Rechercher "LMS EXTENSIONS" dans functions.php ✅ FAIT

---

### ✅ Phase 3: Tests Fonctionnels

#### 6. Test Chargement Assets
- [ ] Visiter la page d'accueil
- [ ] Ouvrir DevTools (F12) > Network
- [ ] Vérifier chargement de `lms-scripts.js`
- [ ] Vérifier chargement de `lms-styles.css`
- [ ] Vérifier qu'il n'y a pas d'erreur 404

**Test Console :**
```javascript
// Dans la console du navigateur
console.log(typeof eiaLMS); // Doit retourner "object"
console.log(eiaLMS.ajaxurl); // Doit retourner l'URL admin-ajax.php
```

---

#### 7. Test des Rôles Utilisateurs
- [ ] Aller sur Users > Add New
- [ ] Vérifier présence des nouveaux rôles :
  - [ ] Étudiant (student)
  - [ ] Formateur (instructor)
  - [ ] Gestionnaire LMS (lms_manager)

**Créer utilisateurs test :**
```
Étudiant Test:
- Username: etudiant_test
- Email: etudiant@eia-test.local
- Role: Étudiant

Formateur Test:
- Username: formateur_test
- Email: formateur@eia-test.local
- Role: Formateur
```

---

#### 8. Test Pages Dashboard

**A. Créer les pages :**
- [ ] Pages > Add New > "Dashboard Étudiant"
  - [ ] Template: Student Dashboard
  - [ ] Status: Publié
- [ ] Pages > Add New > "Dashboard Formateur"
  - [ ] Template: Instructor Dashboard
  - [ ] Status: Publié

**B. Tester accès :**
- [ ] Se connecter en tant qu'étudiant_test
- [ ] Visiter `/student-dashboard/`
- [ ] Vérifier affichage du dashboard étudiant
- [ ] Se déconnecter

- [ ] Se connecter en tant que formateur_test
- [ ] Visiter `/instructor-dashboard/`
- [ ] Vérifier affichage du dashboard formateur

---

#### 9. Test Redirections Login
- [ ] Se connecter avec compte étudiant
- [ ] Vérifier redirection vers `/student-dashboard/`
- [ ] Se déconnecter

- [ ] Se connecter avec compte formateur
- [ ] Vérifier redirection vers `/instructor-dashboard/`

**Code testé :** Fonction `eia_lms_login_redirect()` dans functions.php

---

#### 10. Test Restrictions d'Accès
- [ ] Connecté en tant qu'étudiant, essayer d'accéder à `/instructor-dashboard/`
- [ ] Doit être redirigé vers `/student-dashboard/`

- [ ] Connecté en tant que formateur, essayer d'accéder à `/student-dashboard/`
- [ ] Doit être redirigé vers `/instructor-dashboard/`

**Code testé :** Fonction `eia_restrict_access_by_role()` dans user-roles.php

---

### ✅ Phase 4: Tests AJAX

#### 11. Test Handlers AJAX
**Prérequis :** Avoir LearnPress activé et au moins 1 cours créé

**A. Test Enroll Course :**
```javascript
// Dans la console du navigateur (connecté en tant qu'étudiant)
jQuery.post(eiaLMS.ajaxurl, {
    action: 'eia_enroll_course',
    course_id: 1, // Remplacer par un ID de cours existant
    nonce: eiaLMS.nonce
}, function(response) {
    console.log(response);
});
```

**B. Test Wishlist :**
```javascript
jQuery.post(eiaLMS.ajaxurl, {
    action: 'eia_add_to_wishlist',
    course_id: 1,
    nonce: eiaLMS.nonce
}, function(response) {
    console.log(response);
});
```

---

### ✅ Phase 5: Tests Compatibilité LearnPress

#### 12. Configuration LearnPress
- [ ] Activer LearnPress
- [ ] Aller sur LearnPress > Settings
- [ ] General > Pages : Vérifier que les pages sont créées
- [ ] Vérifier support thème : "Your theme supports LearnPress" ✅

#### 13. Créer un Cours Test
- [ ] LearnPress > Courses > Add New
- [ ] Titre: "Cours de Test"
- [ ] Ajouter une description
- [ ] Publier
- [ ] Vérifier affichage sur le front-end

#### 14. Test Inscription Cours
- [ ] Se connecter en tant qu'étudiant
- [ ] Visiter le cours de test
- [ ] Cliquer sur "Enroll"
- [ ] Vérifier inscription réussie
- [ ] Visiter Dashboard Étudiant
- [ ] Vérifier que le cours apparaît dans "Mes cours en cours"

---

### ✅ Phase 6: Tests Performance

#### 15. Test Vitesse Chargement
- [ ] Ouvrir DevTools > Network
- [ ] Recharger page d'accueil
- [ ] Vérifier temps de chargement < 3 secondes
- [ ] Vérifier taille totale page < 2MB

#### 16. Test Erreurs PHP
- [ ] Activer WP_DEBUG dans wp-config.php
- [ ] Naviguer sur toutes les pages
- [ ] Vérifier qu'aucune erreur/warning PHP n'apparaît
- [ ] Désactiver WP_DEBUG

```php
// Dans wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

---

### ✅ Phase 7: Tests Responsive

#### 17. Test Mobile
- [ ] Ouvrir DevTools > Device Mode (Ctrl+Shift+M)
- [ ] Tester sur iPhone (375px)
- [ ] Tester sur iPad (768px)
- [ ] Vérifier :
  - [ ] Navigation mobile fonctionne
  - [ ] Dashboard s'affiche correctement
  - [ ] Cartes de cours sont responsive
  - [ ] Boutons accessibles

---

## 📊 Résultats Attendus

### ✅ Succès si :
- Thème EIA fonctionne parfaitement
- Tous les plugins s'activent sans erreur
- 7 fichiers LMS créés et fonctionnels
- Assets JS/CSS se chargent
- 3 rôles utilisateurs disponibles
- Dashboards accessibles avec templates corrects
- Redirections login fonctionnent
- Restrictions d'accès fonctionnent
- AJAX handlers répondent
- LearnPress compatible avec thème
- Aucune erreur PHP

### ❌ Échec si :
- Erreurs PHP au chargement
- Fichiers LMS manquants
- Assets 404
- Rôles non créés
- Templates non disponibles
- Redirections ne fonctionnent pas
- AJAX handlers en erreur
- Incompatibilité thème/plugins

---

## 🐛 Debug en Cas de Problème

### Erreur: "Call to undefined function"
```bash
# Vérifier que les fichiers inc/ sont bien inclus
grep -r "require_once.*inc/" wp-content/themes/eia-theme/functions.php
```

### Erreur: Templates non disponibles
```bash
# Vérifier présence des templates
ls -la wp-content/themes/eia-theme/page-templates/
```

### Erreur: AJAX non fonctionnel
```javascript
// Vérifier objet eiaLMS dans console
console.log(eiaLMS);
```

### Erreur: Assets 404
```bash
# Vérifier présence des assets
ls -la wp-content/themes/eia-theme/assets/js/lms-scripts.js
ls -la wp-content/themes/eia-theme/assets/css/lms-styles.css
```

---

## 📝 Notes de Test

**Date de test :** _________________

**Testeur :** _________________

**Environnement :**
- OS: Windows (Laragon)
- PHP: _____
- MySQL: _____
- WordPress: 6.7+

**Résultat global :** ☐ Succès  ☐ Échec partiel  ☐ Échec complet

**Problèmes rencontrés :**
_____________________________________________________________________
_____________________________________________________________________

**Actions correctives :**
_____________________________________________________________________
_____________________________________________________________________

---

*Checklist créée automatiquement - Phase 1 LMS*
*Date : 30 septembre 2025*