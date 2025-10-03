# Identifiants de Démonstration - EIA LMS

## 🔑 Comptes Générés par le Seeder

Tous les comptes créés par le seeder utilisent le même mot de passe pour faciliter les tests.

### **Mot de passe universel**
```
password123
```

⚠️ **IMPORTANT** : Ne jamais utiliser ces identifiants en production !

---

## 👨‍🏫 Comptes Formateurs (Instructors)

Les formateurs ont accès à :
- Dashboard Formateur
- Création de cours
- Gestion des étudiants
- Carnet de notes
- Statistiques de leurs cours

### **Format des identifiants**
```
Username : formateur_[prenom]_[numero]
Email    : formateur_[prenom]_[numero]@eia-demo.sn
Password : password123
```

### **Exemples**
```
formateur_jean_1@eia-demo.sn
formateur_marie_2@eia-demo.sn
formateur_pierre_3@eia-demo.sn
formateur_sophie_4@eia-demo.sn
formateur_amadou_5@eia-demo.sn
```

---

## 👨‍🎓 Comptes Étudiants (Students)

Les étudiants ont accès à :
- Dashboard Étudiant
- Cours inscrits
- Progression
- Certificats
- Quiz et leçons

### **Format des identifiants**
```
Username : etudiant_[prenom]_[numero]
Email    : etudiant_[prenom]_[numero]@eia-demo.sn
Password : password123
```

### **Exemples**
```
etudiant_ousmane_1@eia-demo.sn
etudiant_astou_2@eia-demo.sn
etudiant_cheikh_3@eia-demo.sn
etudiant_bineta_4@eia-demo.sn
etudiant_mamadou_5@eia-demo.sn
```

---

## 🔐 Connexion

### **URL de connexion**
```
http://localhost/eia-wp/wp-login.php
```

### **Étapes**
1. Allez sur l'URL de connexion
2. Entrez un username ou email (ex: `formateur_jean_1` ou `formateur_jean_1@eia-demo.sn`)
3. Entrez le mot de passe : `password123`
4. Cliquez sur "Se connecter"

---

## 📊 Tableau Récapitulatif

| Rôle | Username Pattern | Email Pattern | Dashboard |
|------|------------------|---------------|-----------|
| **Formateur** | `formateur_X_Y` | `formateur_X_Y@eia-demo.sn` | `/instructor-dashboard/` |
| **Étudiant** | `etudiant_X_Y` | `etudiant_X_Y@eia-demo.sn` | `/student-dashboard/` |
| **Admin** | `admin` | (votre email) | `/wp-admin/` |

---

## 🧪 Scénarios de Test

### **Test 1 : Dashboard Formateur**
```
Username : formateur_jean_1
Password : password123
URL      : http://localhost/eia-wp/instructor-dashboard/
```

**Actions à tester :**
- ✅ Voir mes cours
- ✅ Voir mes étudiants
- ✅ Consulter les statistiques
- ✅ Créer un nouveau cours

---

### **Test 2 : Dashboard Étudiant**
```
Username : etudiant_ousmane_1
Password : password123
URL      : http://localhost/eia-wp/student-dashboard/
```

**Actions à tester :**
- ✅ Voir mes cours en cours
- ✅ Voir ma progression
- ✅ Accéder à une leçon
- ✅ Passer un quiz

---

### **Test 3 : Inscription à un Cours**
```
1. Se connecter en tant qu'étudiant
2. Aller sur /cours/
3. Choisir un cours
4. Cliquer sur "S'inscrire"
5. Vérifier que le cours apparaît dans "Mes cours"
```

---

### **Test 4 : Gradebook (Formateur)**
```
1. Se connecter en tant que formateur
2. Aller dans LearnPress > Courses
3. Éditer un de vos cours
4. Voir la metabox "Carnet de Notes"
5. Modifier une note manuelle
6. Exporter le carnet en CSV
```

---

### **Test 5 : Quiz Extended**
```
1. Se connecter en tant qu'étudiant
2. Accéder à un cours avec quiz
3. Lancer le quiz
4. Observer le timer (si activé)
5. Répondre aux questions
6. Soumettre le quiz
7. Voir les résultats
```

---

## 🎯 Profils Pré-configurés

Si vous générez les données avec les options par défaut, vous aurez :

### **5 Formateurs**
```
formateur_jean_1@eia-demo.sn
formateur_marie_2@eia-demo.sn
formateur_pierre_3@eia-demo.sn
formateur_sophie_4@eia-demo.sn
formateur_amadou_5@eia-demo.sn
```

### **20 Étudiants**
```
etudiant_ousmane_1@eia-demo.sn
etudiant_astou_2@eia-demo.sn
etudiant_cheikh_3@eia-demo.sn
etudiant_bineta_4@eia-demo.sn
etudiant_mamadou_5@eia-demo.sn
...jusqu'à 20
```

### **10 Cours**
Exemples de cours générés :
- Management Débutant
- Marketing Digital Avancé
- Comptabilité Intermédiaire
- Finance Avancé
- Ressources Humaines Débutant
- Entrepreneuriat Intermédiaire
- Commerce International Avancé
- Gestion de Projet Débutant
- Leadership Intermédiaire
- Communication Avancé

---

## 🔍 Vérification des Données

### **Vérifier les utilisateurs créés**
```
WordPress Admin > Utilisateurs
```

Filtrez par rôle :
- Formateur (Instructor)
- Étudiant (Student)

### **Vérifier les cours créés**
```
LearnPress > Courses
```

### **Vérifier les inscriptions**
```
1. Se connecter en tant qu'étudiant
2. Aller sur le profil : /profil/
3. Voir l'onglet "Mes Cours"
```

---

## 🗑️ Réinitialisation

Pour supprimer tous les comptes de démonstration :

1. Allez dans **EIA LMS > Seeder**
2. Section **"Zone Dangereuse"**
3. Cliquez sur **"Supprimer les données démo"**
4. Confirmez

Tous les comptes avec les emails `@eia-demo.sn` seront supprimés.

---

## 📧 Emails de Test

Tous les emails générés utilisent le domaine fictif :
```
@eia-demo.sn
```

**Ces emails ne sont pas réels** et ne peuvent pas recevoir d'emails.

Pour tester l'envoi d'emails, configurez **WP Mail SMTP** avec un vrai service (Gmail, SendGrid, etc.)

---

## 🔐 Sécurité - Recommandations

### **En Développement**
✅ OK d'utiliser `password123`

### **En Production**
❌ Ne JAMAIS utiliser le seeder
❌ Ne JAMAIS utiliser `password123`
✅ Utiliser des mots de passe forts
✅ Forcer la réinitialisation au premier login
✅ Utiliser 2FA (Two-Factor Authentication)

---

## 📚 Ressources Complémentaires

- **Guide Seeder** : `.claude/SEEDER_GUIDE.md`
- **Test Checklist** : `.claude/TEST_CHECKLIST.md`
- **Pages à créer** : `.claude/PAGES_TO_CREATE.md`

---

**Dernière mise à jour** : 30 septembre 2025
**Version** : 1.0.0