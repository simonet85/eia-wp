# Guide: Système de Devoirs et Soumissions

## 📋 Fonctionnalités implémentées

### Pour les étudiants
- ✅ Voir tous leurs devoirs sur le tableau de bord
- ✅ Soumettre des devoirs (fichiers ou texte)
- ✅ Resoumission possible (si autorisée)
- ✅ Voir les notes et feedback des instructeurs
- ✅ Alertes pour devoirs en retard
- ✅ Statuts visuels (À faire, Soumis, Noté, Dépassé)

### Pour les instructeurs
- ✅ Créer des devoirs liés aux cours
- ✅ Configurer les paramètres (date limite, note max, type de soumission)
- ✅ Voir toutes les soumissions
- ✅ Noter les devoirs avec feedback
- ✅ Vue détaillée par étudiant

## 🚀 Installation

### 1. Créer la table de base de données

Accédez à :
```
http://eia-wp.test/create-assignments-table.php
```

Vous devriez voir :
- ✅ Table créée avec succès
- Structure de la table avec 11 colonnes

### 2. Activer le module

Le module est automatiquement chargé avec le plugin **EIA LMS Core**.

Pour vérifier :
1. Allez dans **Plugins** dans l'admin WordPress
2. Assurez-vous que **EIA LMS Core** est activé
3. Si nécessaire, désactivez puis réactivez le plugin

### 3. Vérifier l'installation

Après activation, vous devriez voir dans le menu admin :
- **LearnPress > Devoirs** (nouveau menu)

## 📝 Guide d'utilisation - Instructeur

### Créer un devoir

1. **Aller dans LearnPress > Devoirs > Ajouter un devoir**

2. **Remplir les informations principales :**
   - Titre du devoir
   - Description/Instructions (éditeur complet)
   - Image à la une (optionnel)

3. **Configurer les paramètres :**
   - **Date limite** : Date et heure de soumission
   - **Note maximale** : Points possibles (ex: 100)
   - **Type de soumission** :
     - Upload de fichier
     - Texte en ligne
     - Les deux
   - **Taille max fichier** : En MB (par défaut 10 MB)
   - **Permettre la resoumission** : Oui/Non

4. **Associer à un cours :**
   - Dans la sidebar droite
   - Sélectionner le cours concerné

5. **Publier**

### Voir et noter les soumissions

**Option 1 : Via le shortcode**

Créez une page et ajoutez :
```
[eia_assignment_submissions id="ASSIGNMENT_ID"]
```

**Option 2 : Accès direct**

Dans **LearnPress > Devoirs**, cliquez sur un devoir et vous verrez :
- Nombre total de soumissions
- Liste des étudiants ayant soumis
- Statut de chaque soumission

Pour chaque soumission, vous pouvez :
- Lire le texte de l'étudiant
- Télécharger le fichier
- Entrer une note (0 à note maximale)
- Ajouter un feedback/commentaire
- Enregistrer la notation

## 🎓 Guide d'utilisation - Étudiant

### Voir ses devoirs

1. **Se connecter comme étudiant**

2. **Accéder au tableau de bord**
   - Cliquer sur "Mes Cours" dans l'admin bar verte
   - Ou aller sur `http://eia-wp.test/mes-cours/`

3. **Le tableau de bord affiche :**
   - **Statistiques** :
     - Nombre de cours inscrits
     - Nombre de devoirs disponibles
     - Nombre de cours terminés
   - **Section "Mes Devoirs"** :
     - Jusqu'à 6 devoirs récents
     - Badges de statut colorés
     - Date d'échéance
     - Bouton d'action

### Soumettre un devoir

1. **Cliquer sur un devoir** dans le tableau de bord

2. **Lire les instructions** et les détails :
   - Description du devoir
   - Date limite
   - Note maximale
   - Type de soumission accepté

3. **Soumettre le travail :**

   **Si "Texte en ligne" :**
   - Rédiger directement dans la zone de texte
   - Utiliser l'éditeur pour formater

   **Si "Upload de fichier" :**
   - Cliquer sur "Choisir un fichier"
   - Sélectionner le fichier (formats acceptés : PDF, DOC, DOCX, TXT, JPG, PNG, ZIP)
   - Vérifier que la taille ne dépasse pas la limite

   **Si "Les deux" :**
   - Rédiger du texte ET upload un fichier

4. **Cliquer sur "Soumettre le devoir"**

5. **Confirmation :**
   - Message de succès
   - Badge "Soumis" s'affiche
   - Email de confirmation (si configuré)

### Voir ses notes

1. **Retourner sur le devoir soumis**

2. **Si le devoir est noté, vous verrez :**
   - Badge "Noté" en vert
   - Note obtenue / Note maximale
   - **Section "Feedback de l'instructeur"** avec les commentaires

3. **Resoumission (si autorisée) :**
   - Message "Vous pouvez resoummettre"
   - Même formulaire disponible
   - L'ancienne soumission sera remplacée

## 🧪 Test complet du système

### Préparation

1. **Créer la table :**
   ```
   http://eia-wp.test/create-assignments-table.php
   ```

2. **Vérifier que vous avez :**
   - Un cours publié (ex: "Communication Débutant")
   - Un étudiant inscrit au cours (ex: etudiant_abdou_2@eia-demo.sn)

### Test - Créer un devoir

1. Connectez-vous comme **admin**

2. **LearnPress > Devoirs > Ajouter un devoir**

3. **Remplir :**
   - Titre : "Analyse de cas pratique"
   - Description : "Analysez le cas d'étude fourni et rédigez un rapport de 500 mots"
   - Date limite : Demain à 23h59
   - Note maximale : 20
   - Type : Les deux
   - Taille max : 10 MB
   - Resoumission : Oui
   - Cours : Communication Débutant

4. **Publier**

### Test - Soumettre comme étudiant

1. **Déconnectez-vous** et reconnectez avec :
   - Email : `etudiant_abdou_2@eia-demo.sn`
   - Mot de passe : `password123`

2. **Cliquer sur "Mes Cours"** dans l'admin bar verte

3. **Vérifier le tableau de bord :**
   - Statistiques affichées
   - Section "Mes Devoirs" visible
   - Devoir "Analyse de cas pratique" présent
   - Badge "À faire" en orange

4. **Cliquer sur le devoir**

5. **Soumettre :**
   - Réponse : "Voici mon analyse du cas..."
   - Fichier : Télécharger un PDF test
   - Cliquer "Soumettre le devoir"

6. **Vérifier :**
   - Message de succès
   - Badge passe à "Soumis" (bleu)
   - Date de soumission affichée

### Test - Noter comme instructeur

1. **Déconnectez-vous** et reconnectez comme **admin** ou **instructor**

2. **Créer une page de notation** (temporaire) :
   - Pages > Ajouter
   - Titre : "Notation devoirs"
   - Contenu : `[eia_assignment_submissions id="ID_DU_DEVOIR"]`
   - Remplacer `ID_DU_DEVOIR` par l'ID réel
   - Publier

3. **Accéder à la page** et vous verrez :
   - Liste des soumissions
   - Nom de l'étudiant
   - Date de soumission
   - Texte soumis
   - Lien pour télécharger le fichier

4. **Noter :**
   - Entrer une note (ex: 15)
   - Ajouter un feedback : "Très bon travail, mais..."
   - Cliquer "Enregistrer la note"

5. **Vérifier :**
   - Badge passe à "Noté" (vert)
   - Note affichée

### Test - Voir la note comme étudiant

1. **Reconnectez-vous comme étudiant**

2. **Aller sur "Mes Cours"**

3. **Vérifier le tableau de bord :**
   - Badge "Noté" en vert
   - Note affichée directement sur la carte

4. **Cliquer sur le devoir**

5. **Vérifier la page détaillée :**
   - Section "Feedback de l'instructeur" visible
   - Note et commentaires affichés

## 📊 Structure de la base de données

### Table: `wp_eia_assignment_submissions`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | bigint(20) | ID unique de la soumission |
| `assignment_id` | bigint(20) | ID du devoir |
| `user_id` | bigint(20) | ID de l'étudiant |
| `submission_text` | longtext | Texte soumis |
| `file_url` | varchar(500) | URL du fichier uploadé |
| `submitted_date` | datetime | Date de soumission |
| `grade` | float | Note attribuée |
| `feedback` | text | Commentaire de l'instructeur |
| `graded_by` | bigint(20) | ID de l'instructeur |
| `graded_date` | datetime | Date de notation |
| `status` | varchar(20) | Statut (submitted/graded) |

## 🎨 Codes couleur des badges

- 🟠 **Orange (À faire)** : Devoir non soumis, dans les délais
- 🔴 **Rouge (Dépassé)** : Devoir non soumis, date limite dépassée
- 🔵 **Bleu (Soumis)** : Devoir soumis, en attente de notation
- 🟢 **Vert (Noté)** : Devoir noté avec la note affichée

## 🔧 Shortcodes disponibles

### Pour les étudiants

**Liste de tous les devoirs :**
```
[eia_my_assignments]
```

**Formulaire de soumission pour un devoir spécifique :**
```
[eia_assignment_submit id="123"]
```

### Pour les instructeurs

**Liste des soumissions pour un devoir :**
```
[eia_assignment_submissions id="123"]
```

## 🐛 Dépannage

### Les devoirs n'apparaissent pas

1. Vérifier que le devoir est **publié**
2. Vérifier que le devoir est **associé à un cours**
3. Vérifier que l'étudiant est **inscrit au cours**

### Erreur lors de la soumission

1. Vérifier la taille du fichier (max 10 MB par défaut)
2. Vérifier le format du fichier (PDF, DOC, DOCX, TXT, JPG, PNG, ZIP)
3. Vérifier que la resoumission est autorisée si c'est une 2e tentative

### La table n'existe pas

Exécuter :
```
http://eia-wp.test/create-assignments-table.php
```

Ou désactiver/réactiver le plugin **EIA LMS Core**.

## 📁 Fichiers créés

### Backend
- `wp-content/plugins/eia-lms-core/includes/class-assignments.php`
- `wp-content/plugins/eia-lms-core/templates/assignment-submission-form.php`
- `wp-content/plugins/eia-lms-core/templates/student-assignments-list.php`
- `wp-content/plugins/eia-lms-core/templates/instructor-submissions.php`
- `wp-content/plugins/eia-lms-core/assets/js/assignments.js`
- `wp-content/plugins/eia-lms-core/assets/css/assignments.css`

### Frontend
- `wp-content/themes/eia-theme/single-lp_assignment.php`
- `wp-content/themes/eia-theme/functions.php` (modifié pour dashboard)

### Utilitaires
- `create-assignments-table.php`

## ✨ Prochaines améliorations possibles

- [ ] Notifications email automatiques
- [ ] Système de commentaires sur les devoirs
- [ ] Export des notes en CSV
- [ ] Analyse statistique des performances
- [ ] Intégration avec le système de certificats
- [ ] Devoirs de groupe
- [ ] Peer review (évaluation par les pairs)

---

**Documentation créée le :** 01/10/2025
**Version :** 1.0.0
**Plugin :** EIA LMS Core
