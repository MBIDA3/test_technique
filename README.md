# 🎮 Test Technique Symfony 7 & PostgreSQL - Cisad

Projet réalisé dans le cadre du test technique d'alternance. L'application est développée selon les normes d'ingénierie logicielle (**Clean Code**, **Principes SOLID**, **Architecture MVC découplée par couches**, **Transactions SQL Atomiques**, **TDD - Test Driven Development**, et **Conventional Commits**).

---

## 🏛️ Architecture & Choix Techniques

L'application découpe strictement les responsabilités (**Single Responsibility Principle - SRP**) :

* **Présentation (`Twig / HTML / Bootstrap 5`) :** Affichage pur et responsive en mode Dark Slate. Aucune logique métier dans les vues.
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

* **PHP :** 8.3.32 (Typage strict `declare(strict_types=1);` activé sur 100% des fichiers)
* **Framework :** Symfony 7.4 (Webapp, Security, Validator, Form, Twig)
* **Base de données :** PostgreSQL 16 (Base `test_cisad`)
* **ORM :** Doctrine ORM & Doctrine Migrations / Fixtures
* **Tests :** PHPUnit 12 (Tests unitaires & fonctionnels d'intégration)
* **Design :** HTML5 / CSS3 / Bootstrap 5 / Icônes FontAwesome Open Source

---

## 🚀 Guide d'Installation & Démarrage Rapide

### 1. Prérequis
* PHP 8.3+ avec extensions requises (`pdo_pgsql`, `mbstring`, `iconv`, `gd`).
* Composer 2.x.
* Server PostgreSQL local sur le port 5432 (ou SQLite en dev local immédiat).

### 2. Cloner le Projet & Installer les Dépendances
```bash
git clone https://github.com/MBIDA3/test_technique.git
cd test_technique
composer install
```

### 3. Démarrer la Base de Données & Charger les Fixtures
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

### 4. Lancer le Serveur Web Local
```bash
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

## 🧪 Suite de Tests Automatisés (TDD)

Tous les composants (Entités, Sécurité, Service d'import CSV, Contrôleurs) ont été développés en **TDD (Test Driven Development)** :

```bash
php bin/phpunit
```

Résultat attendu : `OK (10 tests, 40 assertions)` sans aucune erreur ni dépréciation.

---

## 💡 Apports Personnels & Valeur Ajoutée (Bonus)

Dans le but de garantir une application professionnelle et d'offrir la meilleure expérience aux utilisateurs et aux évaluateurs :

1. **Transactionnalité SQL Atomique (`wrapInTransaction`) :**
   * L'importation CSV garantit la cohérence des données. En cas d'erreur de format ou de validation sur une ligne, l'ensemble de l'importation s'annule proprement (*Rollback*).
2. **Découplage par DTO (`UserCsvDto`) & Validation Symfony :**
   * Séparation stricte de l'analyse du fichier CSV de la couche de domaine Doctrine grâce aux assertions Symfony Validator (`#[Assert\NotBlank]`, `#[Assert\Email]`).
3. **Connexion Automatique après Inscription (Auto-Login UX) :**
   * Lors de la création d'un compte, l'utilisateur est directement connecté et redirigé vers son profil pour une expérience sans friction.
4. **Design Responsive & Human-Centered (Dark Slate) :**
   * Interface réactive sur mobile et desktop avec bannières d'alerte haute visibilité et icônes open source vectorielles (FontAwesome).
5. **Couverture de Tests (TDD - 100% Vert) :**
   * Suite complète de 10 tests automatisés (40 assertions) garantissant l'absence de régression sur les entités, formulaires, sécurité et importateur CSV.
