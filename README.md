# 🎓 My Online Classroom - Plateforme E-learning

Une plateforme d'apprentissage complète réalisée avec **Symfony 8** et **API Platform** par ALexandre FABRE.
Elle permet aux professeurs de déposer des cours (PDF/Vidéo), de générer des QCM via IA, et aux élèves de suivre les cours et passer les examens.

## Prérequis

Avant de commencer, assurez-vous d'avoir installé :
* **PHP 8.2** ou supérieur (Extensions requises : `intl`, `pdo_mysql`, `xsl`, `mbstring`, `openssl`, `sodium`).
* **Composer**.
* **Symfony CLI**.
* **MySQL** ou **MariaDB**.
* **OpenSSL** (Généralement inclus avec Git Bash sur Windows).

---

## Installation

### 1. Récupérer le projet
Clonez le dépôt ou extrayez l'archive dans votre dossier de travail.

### 2. Installer les dépendances PHP
À la racine du projet, lancez :
```bash
composer install
```

### 3. Configuration de l'environnement (.env)
Dupliquez le fichier .env et renommez-le en .env.local.
Ouvrez .env.local et modifiez la ligne DATABASE_URL avec vos accès MySQL :
```bash 
DATABASE_URL="mysql://root:@127.0.0.1:3306/my_online_classroom?serverVersion=8.0.32&charset=utf8mb4"
# Adaptez root et le mot de passe selon votre configuration
```

### 4. Création de la Base de Données
Lancez les commandes suivantes dans l'ordre :
```bash
# Créer la base de données
php bin/console doctrine:database:create

# Créer les tables
php bin/console doctrine:migrations:migrate
```

### 5. Génération des clés JWT (Pour l'API)
Cette étape est cruciale pour que l'API et le login fonctionnent.
Si vous êtes sous Windows, utilisez Git Bash pour lancer cette commande (pour avoir accès à OpenSSL) :
```bash
php bin/console lexik:jwt:generate-keypair --overwrite
```
Si demandé, le mot de passe par défaut configuré dans les fichiers est mon_super_secret (ou vérifiez JWT_PASSPHRASE dans .env).

### 6. Chargement des fausses données (Fixtures)
Pour avoir des utilisateurs, des cours et des QCM de test dès le départ :
```bash
php bin/console doctrine:fixtures:load
```
(Répondez yes à la confirmation).

### 7. Création du dossier d'upload
Assurez-vous que le dossier de réception des fichiers existe :
```bash
mkdir -p public/uploads/courses
```
### 8. Lancer le projet
Démarrez le serveur local Symfony :
```bash 
symfony serve
```

Accédez au site via : http://127.0.0.1:8000🔑 

Identifiants de test

### 🔑 Comptes de Démonstration (Fixtures)

Les fixtures ont généré les comptes suivants (Mot de passe pour tous : **`password`**) :

| Rôle | Email | Accès |
| :--- | :--- | :--- |
| **Admin** | `admin@test.com` | Accès complet au Back-office (`/admin`) |
| **Professeur** | `prof1@test.com` | Gestion de ses propres cours et QCM |
| **Élève** | `eleve@test.com` | Espace étudiant, lecture cours, passage QCM (`/student`) |

---

## 🛠️ Fonctionnalités Principales

### 👨‍🏫 Espace Professeur / Admin
* **CRUD Cours** : Création de cours avec upload de PDF et Vidéo.
* **Génération QCM** : Upload d'un PDF de cours -> L'IA génère des questions automatiquement.
* **Édition QCM** : Modification des questions, ajout/suppression dynamique via JS.
* **Suivi** : Visualisation des résultats des élèves.

### 👨‍🎓 Espace Élève
* **Dashboard** : Liste des cours disponibles.
* **Lecture** : Visualiseur PDF intégré et lecteur vidéo.
* **Quiz** : Passage de QCM interactifs avec calcul de note immédiat.
* **Historique** : Consultation des notes obtenues.

### 🔌 API REST (Pour Mobile/Front externe)
Documentation disponible (si activée) sur `/api/docs`.
* **Authentification JWT** (`POST /api/login`).
* Récupération des cours et quiz.
* Soumission des résultats.

---

## ⚠️ Dépannage (Windows)

Si vous rencontrez des erreurs type `Extension not loaded` :

1.  Vérifiez votre fichier `php.ini`.
2.  Assurez-vous que ces lignes sont décommentées (sans point-virgule au début) :

```ini
extension=intl
extension=mbstring   ; Requis pour le convertisseur Markdown
extension=openssl    ; Requis pour pour la génération des clés JWT
extension=sodium     ; Requis pour l'encodage JWT
extension=pdo_mysql