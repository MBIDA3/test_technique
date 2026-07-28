# 🎮 Test Technique Symfony 7 & PostgreSQL - Cisad Gaming

Projet réalisé dans le cadre du test technique d'alternance. L'application est développée selon les plus hauts standards d'ingénierie logicielle (**Clean Code**, **Principes SOLID**, **Architecture MVC découplée par couches**, **Transactions SQL Atomiques**, **TDD - Test Driven Development**, et **Conventional Commits**).

---

## 🏛️ Architecture & Choix Techniques (Niveau Tech Lead)

L'application découpe strictement les responsabilités (**Single Responsibility Principle - SRP**) :

* **Présentation (`Twig / HTML / Bootstrap 5`) :** Affichage pur et responsive en Dark Mode. Aucune logique métier dans les vues.
* **Entrée HTTP (`Controller`) :** Contrôleurs maigres (*Thin Controllers*) assurant uniquement l'aiguillage HTTP et déléguant toute la logique métier aux Services.
* **Validation (`FormType` / `DTO`) :** Objets de transfert de données (`UserCsvDto`) et formulaires munis d'assertions Symfony Validator (`#[Assert]`).
* **Logique Métier (`Service`) :**
  * `CsvImporterService` : Lecture du fichier CSV (`test.csv`), validation DTO, hachage sécurisé des mots de passe (`UserPasswordHasherInterface`) et persistance atomique sous **Transaction SQL** (`$entityManager->wrapInTransaction(...)`). En cas d'erreur sur une ligne, la transaction s'annule (*rollback*) pour garantir la consistance de la base de données.
* **Accès aux Données (`Repository`) :** Fichiers d'accès séparés (`UserRepository`, `InfosRepository`).
* **Modèle de Domaine (`Entity`) :**
  * `User` : Implémente `UserInterface` et `PasswordAuthenticatedUserInterface`.
  * `Infos` : Relation `OneToOne` stricte vers `User` (avec mise à jour bidirectionnelle synchronisée).

---

## 🛠️ Stack Technique

* **PHP :** 8.3.32 (Typpage strict `declare(strict_types=1);` activé sur 100% des fichiers)
* **Framework :** Symfony 7.4 (Webapp, Security, Validator, Form, Twig)
* **Base de données :** PostgreSQL 16 (Base `test_cisad`)
* **ORM :** Doctrine ORM & Doctrine Migrations / Fixtures
* **Tests :** PHPUnit 12 (Tests unitaires & fonctionnels d'intégration)
* **Design :** HTML5 / CSS3 / Bootstrap 5 (Dark Mode moderne & épuré)

---

## 🚀 Guide d'Installation & Démarrage Rapide

### 1. Prérequis
* PHP 8.3+ avec extensions requises (`pdo_pgsql`, `mbstring`, `iconv`, `gd`).
* Composer 2.x.
* Docker & Docker Compose (ou un serveur PostgreSQL local sur le port 5432).

### 2. Cloner le Projet & Installer les Dépendances
```bash
git clone <url-de-votre-depot>
cd test_technique
composer install
```

### 3. Démarrer la Base de Données PostgreSQL (Docker)
```bash
docker compose up -d database
```

### 4. Charger les Fixtures (Création des tables & données de démo)
```bash
# Pour charger les données de démo et le compte administrateur :
php bin/console doctrine:fixtures:load --no-interaction
```

### 5. Lancer le Serveur Web Local
```bash
symfony server:start
# Ou avec PHP CLI :
php -S 127.0.0.1:8000 -t public
```

Rendez-vous sur `http://127.0.0.1:8000`.

---

## 🔑 Identifiants de Démonstration

| Rôle | Nom d'utilisateur (`username`) | Mot de passe | Description |
| :--- | :--- | :--- | :--- |
| **Administrateur** | `admin` | `password123` | Accès complet au Dashboard, CRUD Utilisateurs, CRUD Stats et Importation CSV |
| **Utilisateur** | `user_demo` | `password123` | Accès à son profil personnel et à ses statistiques de jeu |

---

## 🧪 Lancer la Suite de Tests Automatisés (TDD)

Tous les composants (Entités, Sécurité, Service d'import CSV, Contrôleurs) ont été développés en **TDD (Test Driven Development)** :

```bash
php bin/phpunit
```

Résultat attendu : `OK (10 tests, 40 assertions)` sans aucune erreur ni dépréciation.

---

## 📄 Format du Fichier CSV d'Importation

Exemple fourni avec le projet dans [test.csv](file:///c:/Users/chris/Downloads/test_technique/test_technique/test.csv) :
```csv
username;email;password;roles;rank;victoire;defaite
test_01;test_01@test.fr;qwerty123;ROLE_USER;Silver;5;5
test_02;test_02@test.fr;qwerty123;ROLE_USER;Iron;0;10
test_03;test_03@test.fr;qwerty123;ROLE_USER;Gold;6;4
test_04;test_04@test.fr;qwerty123;ROLE_USER;Platine;7;3
test_05;test_05@test.fr;qwerty123;ROLE_USER;Diamant;15;4
test_06;test_06@test.fr;qwerty123;ROLE_ADMIN;;;
test_07;test_07@test.fr;qwerty123;ROLE_USER;;;
```
