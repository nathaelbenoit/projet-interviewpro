# InterviewPro

**InterviewPro** est une application web universitaire de gestion d'interviews professionnelles. Elle permet aux étudiants de soumettre leurs comptes-rendus d'entretiens professionnels et aux enseignants de les évaluer selon une grille de critères personnalisable.

> Projet réalisé avec une deadline de 2 semaines.

---

## Fonctionnalités

### Espace Étudiant
- Soumission d'une interview (PDF) et d'une attestation (PDF)
- Enregistrement automatique du professionnel interviewé (nom, métier, entreprise, LinkedIn)
- Consultation de sa note finale et des commentaires de l'enseignant

### Espace Enseignant
- Vue d'ensemble des étudiants et de leur statut (soumis / corrigé)
- Téléchargement des PDF soumis (interview + attestation)
- Création et gestion d'une grille d'évaluation (critères + barème)
- Correction par critère avec attribution de notes et commentaires
- Modification des corrections existantes

### Annuaire des Professionnels
- Répertoire public des professionnels enregistrés
- Recherche par nom, prénom, email, métier ou entreprise
- Liens vers les profils LinkedIn

### Gestion de Compte
- Connexion par email et mot de passe avec gestion des rôles (étudiant / enseignant)
- Modification du mot de passe
- Déconnexion sécurisée par destruction de session

---

## Stack Technique

| Composant | Technologie |
|-----------|-------------|
| Frontend  | HTML5, CSS3, JavaScript (vanilla) |
| Backend   | PHP 7+ |
| Base de données | MySQL 5.7+ (MySQLi) |
| Serveur   | Apache ou Nginx |

---

## Structure du Projet

```
projet-interviewpro/
├── README.md
├── BDR/
│   └── nnn_sae.sql                  # Schéma + données initiales
└── page_web/
    ├── login.php                    # Authentification
    ├── logout.php                   # Déconnexion
    ├── compte.php                   # Gestion du compte
    ├── etudiant.php                 # Tableau de bord étudiant
    ├── professeur.php               # Tableau de bord enseignant
    ├── pro.php                      # Annuaire des professionnels
    ├── enregistrer_interview.php    # Endpoint AJAX - soumission interview
    ├── corriger.php                 # Formulaire de correction
    ├── enregistrer_correction.php   # Endpoint AJAX - sauvegarde correction
    ├── etablir_criteres.php         # Gestion des critères d'évaluation
    ├── consulter_correction.php     # Consultation des résultats (étudiant)
    ├── telecharger.php              # Téléchargement des PDF
    └── images/                      # Logos et assets
```

---

## Base de Données

Le schéma (`BDR/nnn_sae.sql`) contient 5 tables :

| Table | Description |
|-------|-------------|
| `users` | Comptes utilisateurs (étudiants et enseignants) |
| `professionnels` | Annuaire des professionnels interviewés |
| `interviews` | Soumissions (fichiers PDF stockés en LONGBLOB) |
| `criteres` | Critères d'évaluation définis par l'enseignant |
| `criteres_evaluation` | Notes et commentaires attribués par critère |

---

## Installation

### Prérequis
- PHP 7+ avec l'extension MySQLi activée
- MySQL 5.7+ ou MariaDB
- Serveur web Apache ou Nginx

### Étapes

1. **Cloner le dépôt**
   ```bash
   git clone https://github.com/<votre-utilisateur>/projet-interviewpro.git
   ```

2. **Importer la base de données**
   ```bash
   mysql -u root -p < BDR/nnn_sae.sql
   ```

3. **Configurer la connexion à la base de données**

   Les identifiants de connexion sont définis directement dans chaque fichier PHP. Par défaut :
   ```php
   $host = "localhost";
   $username = "root";
   $password = "";
   $dbname = "nnn_sae";
   ```
   Modifiez ces valeurs si nécessaire dans les fichiers du dossier `page_web/`.

4. **Configurer PHP** (recommandé dans `php.ini`)
   ```ini
   upload_max_filesize = 10M
   post_max_size = 10M
   ```

5. **Déployer**

   Placez le contenu de `page_web/` dans le répertoire servi par votre serveur web, puis accédez à :
   ```
   http://localhost/page_web/login.php
   ```

---

## Comptes de Démonstration

| Email | Mot de passe | Rôle |
|-------|-------------|------|
| `nolanfontaine@example.com` | `1234` | Étudiant |
| `noadouit@example.com` | `1234` | Étudiant |
| `nathaelbenoit@example.com` | `1234` | Enseignant |

---

## Parcours Utilisateur

### Étudiant
1. Se connecter sur `login.php`
2. Remplir le formulaire d'interview (infos du professionnel + 2 PDF)
3. Soumettre → le professionnel est automatiquement ajouté à l'annuaire
4. Consulter sa note et ses commentaires une fois la correction effectuée

### Enseignant
1. Se connecter sur `login.php`
2. Définir la grille d'évaluation via "Établir la grille d'évaluation"
3. Consulter la liste des étudiants et télécharger leurs PDF
4. Corriger chaque étudiant (notes + commentaires par critère)
5. Modifier une correction existante si nécessaire

---

## Auteurs

Projet universitaire — 2025
