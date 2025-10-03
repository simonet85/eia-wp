# Status des Plugins LMS - EIA

## ✅ Plugins Installés

Les plugins suivants sont **installés** dans `wp-content/plugins/` :

| Plugin | Fichier principal | Status Installation |
|--------|------------------|---------------------|
| **LearnPress** | `learnpress/learnpress.php` | ✅ Installé |
| **BuddyPress** | `buddypress/bp-loader.php` | ✅ Installé |
| **bbPress** | `bbpress/bbpress.php` | ✅ Installé |
| **GamiPress** | `gamipress/gamipress.php` | ✅ Installé |
| **H5P** | `h5p/h5p.php` | ✅ Installé |
| **WP Mail SMTP** | `wp-mail-smtp/wp-mail-smtp.php` | ✅ Installé |
| **User Registration** | `user-registration/user-registration.php` | ✅ Installé |

## 🔄 Activation des Plugins

Pour activer tous les plugins, connectez-vous à l'admin WordPress :

1. **Via l'interface WordPress :**
   - Allez sur `http://localhost/eia-wp/wp-admin/plugins.php`
   - Activez chaque plugin un par un

2. **Via WP-CLI (si disponible) :**
   ```bash
   wp plugin activate learnpress
   wp plugin activate buddypress
   wp plugin activate bbpress
   wp plugin activate gamipress
   wp plugin activate h5p
   wp plugin activate wp-mail-smtp
   wp plugin activate user-registration
   ```

3. **Activation manuelle (base de données) :**
   Les plugins peuvent aussi être activés en ajoutant leurs chemins dans la table `wp_options`, clé `active_plugins`.

## 📋 Configuration Recommandée

### LearnPress
- **Pages requises :** Cours, Profil, Checkout
- **Configuration :** Paramètres > LearnPress
- **Add-ons gratuits à installer :**
  - LearnPress - Certificates
  - LearnPress - Course Review
  - LearnPress - Prerequisites

### BuddyPress
- **Pages requises :** Membres, Activité, Groupes
- **Configuration :** Paramètres > BuddyPress
- **Composants à activer :** Profils étendus, Groupes, Messages privés

### bbPress
- **Configuration :** Forums > Paramètres
- **Structure recommandée :** Forums par cours

### GamiPress
- **Configuration :** GamiPress > Paramètres
- **Types de récompenses :** Points, Badges, Classements
- **Intégration :** LearnPress, BuddyPress

### User Registration
- **Formulaires :** Créer formulaires inscription étudiant/formateur
- **Configuration :** User Registration > Paramètres

## ⚠️ Actions Requises

1. **Activer tous les plugins** via admin WordPress
2. **Configurer LearnPress** (créer pages requises)
3. **Configurer BuddyPress** (sélectionner composants)
4. **Configurer GamiPress** (créer premiers badges/points)
5. **Tester compatibilité** avec thème EIA

## 🔗 Intégration avec Thème EIA

Le thème EIA supporte déjà :
```php
add_theme_support('learnpress');
add_theme_support('buddypress');
add_theme_support('bbpress');
add_theme_support('woocommerce'); // Pour e-commerce futur
```

## 📊 Vérification Status

Pour vérifier si les plugins sont activés, consultez :
- Admin WordPress > Extensions
- Ou fichier `wp-content/mu-plugins/` (plugins obligatoires)
- Ou table `wp_options` → clé `active_plugins`

---

*Document créé automatiquement - Phase 1 LMS*
*Date : 30 septembre 2025*