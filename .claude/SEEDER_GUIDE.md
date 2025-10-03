# Guide d'Utilisation du Seeder EIA LMS

## 🎯 Objectif

Le **Seeder** permet de générer automatiquement des données de démonstration pour tester la plateforme LMS EIA sans avoir à créer manuellement des centaines d'utilisateurs et de cours.

## 📍 Accès

1. Connectez-vous à WordPress Admin
2. Allez dans le menu **EIA LMS > Seeder**

## ✨ Fonctionnalités

### **Génération de Données**

Le seeder peut créer :

- ✅ **Formateurs** (instructors)
- ✅ **Étudiants** (students)
- ✅ **Cours** (courses)
- ✅ **Leçons** (lessons)
- ✅ **Quiz** (quizzes)
- ✅ **Inscriptions** (enrollments)
- ✅ **Progrès aléatoires** (progress)

### **Options Configurables**

| Option | Par Défaut | Min | Max | Description |
|--------|-----------|-----|-----|-------------|
| Formateurs | 5 | 1 | 20 | Nombre de formateurs à créer |
| Étudiants | 20 | 1 | 100 | Nombre d'étudiants à créer |
| Cours | 10 | 1 | 50 | Nombre de cours à créer |
| Leçons par cours | 5 | 1 | 20 | Nombre de leçons dans chaque cours |
| Quiz par cours | 2 | 0 | 10 | Nombre de quiz dans chaque cours |

## 🚀 Utilisation

### **Étape 1 : Configuration**

1. Accédez à **EIA LMS > Seeder**
2. Cochez les types de données à générer :
   - ☑️ Créer des formateurs
   - ☑️ Créer des étudiants
   - ☑️ Créer des cours
   - ☑️ Inscrire des étudiants aux cours
   - ☑️ Générer des progrès aléatoires

3. Ajustez les quantités selon vos besoins

### **Étape 2 : Lancement**

1. Cliquez sur **"Lancer le Seeder"**
2. Confirmez l'action
3. Attendez la génération (cela peut prendre quelques secondes à quelques minutes selon la quantité)

### **Étape 3 : Vérification**

Une fois terminé, vérifiez :

- **Utilisateurs** : `WordPress Admin > Utilisateurs`
- **Cours** : `LearnPress > Courses`
- **Leçons** : `LearnPress > Lessons`
- **Quiz** : `LearnPress > Quizzes`

## 📊 Exemple de Configuration Recommandée

### **Configuration Petite** (Test rapide)
```
Formateurs : 3
Étudiants : 10
Cours : 5
Leçons/cours : 3
Quiz/cours : 1
```

### **Configuration Moyenne** (Démo réaliste)
```
Formateurs : 5
Étudiants : 20
Cours : 10
Leçons/cours : 5
Quiz/cours : 2
```

### **Configuration Grande** (Test de charge)
```
Formateurs : 10
Étudiants : 50
Cours : 30
Leçons/cours : 8
Quiz/cours : 3
```

## 📝 Données Générées

### **Formateurs**

- **Username** : `formateur_prenom_X`
- **Email** : `formateur_prenom_X@eia-demo.sn`
- **Password** : `password123`
- **Rôle** : `instructor`
- **Prénoms** : Jean, Marie, Pierre, Sophie, Amadou, Fatou, Moussa, Aïssa, Ibrahima, Khady
- **Noms** : Diop, Ndiaye, Fall, Sarr, Sow, Gueye, Ba, Sy, Cissé, Diouf

### **Étudiants**

- **Username** : `etudiant_prenom_X`
- **Email** : `etudiant_prenom_X@eia-demo.sn`
- **Password** : `password123`
- **Rôle** : `student`
- **Prénoms** : Ousmane, Astou, Cheikh, Bineta, Mamadou, Awa, Abdou, Marieme, Lamine, Coumba
- **Noms** : Diop, Ndiaye, Fall, Sarr, Sow, Gueye, Ba, Sy, Cissé, Diouf

### **Cours**

- **Sujets** : Management, Marketing Digital, Comptabilité, Finance, RH, Entrepreneuriat, Commerce International, Gestion de Projet, Leadership, Communication
- **Niveaux** : Débutant, Intermédiaire, Avancé
- **Titre** : `[Sujet] [Niveau]` (ex: "Marketing Digital Avancé")
- **Auteur** : Formateur aléatoire
- **Durée** : 20-100 heures (aléatoire)
- **Max étudiants** : 20-50 (aléatoire)

### **Inscriptions**

- Chaque étudiant est inscrit à **2-5 cours aléatoires**
- Progrès : **0-100%** (aléatoire si activé)

## 🗑️ Suppression des Données

### **Zone Dangereuse**

Pour supprimer **TOUTES** les données générées par le seeder :

1. Allez dans **EIA LMS > Seeder**
2. Scrollez jusqu'à la section **"Zone Dangereuse"**
3. Cliquez sur **"Supprimer les données démo"**
4. **⚠️ ATTENTION** : Cette action est **irréversible** !

### **Ce qui sera supprimé :**

- ✅ Tous les utilisateurs avec meta `_eia_demo_data = 1`
- ✅ Tous les cours avec meta `_eia_demo_data = 1`
- ✅ Toutes les leçons avec meta `_eia_demo_data = 1`
- ✅ Tous les quiz avec meta `_eia_demo_data = 1`

### **Ce qui sera conservé :**

- ✅ Utilisateurs créés manuellement
- ✅ Cours créés manuellement
- ✅ Données réelles de la plateforme

## 🔒 Identification des Données Démo

Toutes les données générées par le seeder ont un **meta field** spécial :

```php
_eia_demo_data = '1'
```

Cela permet de :
- Identifier facilement les données de test
- Supprimer uniquement les données démo
- Conserver les vraies données

## 🎓 Cas d'Usage

### **1. Test d'Interface**
```
Formateurs : 2
Étudiants : 5
Cours : 3
```
→ Pour tester les dashboards et l'UI

### **2. Démonstration Client**
```
Formateurs : 5
Étudiants : 20
Cours : 10
```
→ Pour montrer la plateforme à un client

### **3. Test de Performance**
```
Formateurs : 10
Étudiants : 100
Cours : 50
```
→ Pour tester les limites du système

### **4. Développement**
```
Formateurs : 3
Étudiants : 10
Cours : 5
Inscriptions : Oui
Progrès : Oui
```
→ Pour développer de nouvelles fonctionnalités

## 🐛 Dépannage

### **Erreur : "Extension LearnPress introuvable"**

**Solution** : Activez d'abord LearnPress avant d'utiliser le seeder.

```
WordPress Admin > Extensions > LearnPress > Activer
```

### **Erreur : "Permission refusée"**

**Solution** : Seuls les administrateurs peuvent utiliser le seeder.

Vérifiez votre rôle : `WordPress Admin > Utilisateurs > Votre Profil`

### **Le seeder est lent**

**Normal** : La génération de 100+ utilisateurs et 50+ cours peut prendre 2-3 minutes.

**Astuce** : Réduisez les quantités pour des tests rapides.

### **Erreur : "Memory limit exceeded"**

**Solution** : Augmentez la limite mémoire PHP dans `wp-config.php` :

```php
define('WP_MEMORY_LIMIT', '256M');
```

## 📊 Logs et Suivi

Le seeder affiche en temps réel :

- ✅ **Messages de succès** (fond vert)
- ❌ **Messages d'erreur** (fond rouge)
- 📊 **Barre de progression**
- 📝 **Log détaillé** de chaque action

## 🔐 Sécurité

### **Mot de passe par défaut**

Tous les comptes générés ont le même mot de passe : `password123`

⚠️ **IMPORTANT** : Ne jamais utiliser le seeder en production avec ce mot de passe !

### **Emails de démonstration**

Tous les emails sont au format : `@eia-demo.sn`

Ces emails ne sont **pas réels** et ne recevront aucun email.

## 📚 Ressources

- **Documentation Plugin** : `.claude/PLUGIN_ARCHITECTURE.md`
- **Test Checklist** : `.claude/TEST_CHECKLIST.md`
- **Pages à créer** : `.claude/PAGES_TO_CREATE.md`

---

**Créé le** : 30 septembre 2025
**Version** : 1.0.0
**Plugin** : EIA LMS Core