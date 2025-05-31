# Mini-Projet : Système de Gestion de Tickets de Cinéma - Backend

Ce dépôt contient la partie **backend** du mini-projet "Système de Gestion de Tickets de Cinéma", développé sur la base d'un boilerplate PHP fourni. L'objectif principal est de démontrer la capacité à comprendre, étendre et respecter une architecture PHP orientée objet existante pour implémenter des fonctionnalités métier complexes.

---

## Contexte du Projet Initial (Boilerplate)

Ce projet est un exemple de backend conçu pour être consommé par différentes interfaces frontend. Il suit une architecture structurée, mettant l'accent sur les principes de la POO.

## Objectif du Mini-Projet (Système de Cinéma)

Développer une API RESTful destinée aux caissiers d'un cinéma pour faciliter la gestion quotidienne des ventes de tickets et la programmation des séances. Ce projet ne nécessite pas de système d'authentification.

Les fonctionnalités clés incluent :
- Enregistrer et gérer les informations des clients.
- Consulter et gérer la programmation des séances (2D et 3D).
- Créer, consulter et gérer les ventes de tickets selon les disponibilités.
- Calculer automatiquement les coûts des tickets (prix de séance, TVA, lunettes 3D).
- Maintenir une base de données actualisée de toutes les informations.

## Entités du Système (Implémentées)

En plus de l'entité `Employee` (fournie par le boilerplate, conservée et fonctionnelle), le projet implémente les entités suivantes :

- **Client** : Représente les personnes achetant des tickets.
    - Attributs : `nom`, `email`, `phone`.
    - Contrainte : Un client peut acheter **5 places au maximum par séance**.
- **Seance** : Modélise les projections programmées dans le cinéma. Il s'agit d'une classe abstraite permettant l'extension selon les types de projection (2D ou 3D).
    - Attributs communs : `film`, `horaire`, `prix` (prix de base), `salle`, `placesDisponibles`.
    - **Seance2D** : Premier type de Séance.
        - Attributs spécifiques : `qualiteImage`.
        - Contrainte : Tarif standard pour projection classique.
    - **Seance3D** : Deuxième type de Séance.
        - Attributs spécifiques : `technologie3D`, `lunettesIncluses` (boolean et coûte 20 MAD si `true`).
        - Contrainte : Tarif majoré pour projection immersive.
- **Ticket** : Représente un ticket de cinéma vendu par le caissier.
    - Attributs : `nombrePlaces`, `montantTotal`, `statut` (VENDU, RÉSERVÉ, ANNULÉ).
    - Contrainte : Le `montantTotal` se calcule automatiquement selon le nombre de places, le type de séance (incluant le coût des lunettes 3D), et la TVA (20%).
    - Lié à une entité `Client` et une entité `Seance`.

## Objectifs d'apprentissage

Ce projet a pour objectif d'évaluer les compétences en POO, notamment :
- Comprendre les bases de la programmation orientée objet (encapsulation, héritage, polymorphisme).
- Concevoir et écrire des requêtes SQL efficaces qui permettent d'extraire précisément les données.
- Appliquer le principe de couplage faible et de séparation des préoccupations.
- Développer du code réutilisable.
- Comprendre et utiliser l'injection de dépendances.
- Utiliser le design pattern Singleton dans un contexte de serveur web (pour la connexion BDD).

---

## Architecture du Système

Le backend est basé sur le principe **MVC2** (Model-View-Controller avec un Front Controller), intégrant un **router dispatcher** qui analyse les URLs et appelle dynamiquement la méthode du contrôleur correspondante (inspiré d'un dispatcher servlet).

Il respecte une architecture en couches claires :

-   **Models** : Classes PHP représentant les entités de la base de données (ex: `Client.php`, `Seance.php`, `Ticket.php`). Elles respectent l'encapsulation et implémentent `JsonSerializable`.
-   **Repositories** : Classes d'accès aux données (DAO). Chaque Repository étend `Core\Repository` ou `Core\Facades\RepositoryMutations` (pour les méthodes CRUD génériques `save`, `update`, `delete`).
-   **Services** : Couche de logique métier. Les services implémentent leurs interfaces correspondantes (ex: `ClientService.php` et `ClientDefault.php`), garantissant un couplage faible et une meilleure testabilité.
-   **Controllers** : Gèrent les requêtes HTTP. Ils sont placés dans `App/controllers/`, terminent par `*Controller.php`, héritent de `Core\Controller`, et peuvent implémenter `Core\Contracts\ResourceController` pour le routage RESTful automatique.

## Routage

-   **Convention RESTful** : Les contrôleurs implémentant `ResourceController` bénéficient d'un routage automatique pour les opérations CRUD standard (`index`, `show`, `store`, `update`, `destroy`).
    | Méthode HTTP | Chemin              | Méthode Contrôleur |
    | :----------- | :------------------ | :----------------- |
    | `GET`        | `/api/v1/plural`    | `index`            |
    | `GET`        | `/api/v1/plural/{id}` | `show`             |
    | `POST`       | `/api/v1/plural`    | `store`            |
    | `PUT`/`PATCH`| `/api/v1/plural/{id}` | `update`           |
    | `DELETE`     | `/api/v1/plural/{id}` | `destroy`          |
-   **Par annotation/attribut** : Il est également possible de définir des routes spécifiques sur des méthodes via l'attribut `#[Core\Decorators\Route('chemin', method: RouteMethod::VERB)]`.
-   **Gestion des CORS** : Le routeur et les contrôleurs sont configurés pour gérer les requêtes CORS (Cross-Origin Resource Sharing) afin de permettre la communication avec un frontend sur une origine différente.

## Installation et Configuration (avec Laragon et PostgreSQL)

1.  **Cloner le dépôt :**
    ```bash
    git clone https://github.com/belal-allala/youcode-cinema-backend.git cinema_backend
    ```
2.  **Placer le projet :** Copiez le dossier `cinema_backend` dans le répertoire `www/Cinema/` de votre installation Laragon (par exemple, `c:/Laragon/www/Cinema/cinema_backend`).
3.  **Installer les dépendances Composer :**
    Naviguez vers le dossier racine du projet (`cinema_backend`) dans votre terminal et exécutez :
    ```bash
    composer install
    ```
4.  **Configuration de la base de données PostgreSQL :**
    -   Ouvrez votre client PostgreSQL (pgAdmin, DBeaver, etc.).
    -   Créez une base de données nommée `fil_rouge_rattrapage`.
    -   Ouvrez le fichier `Database/init.sql` à la racine de ce projet.
    -   Copiez TOUT le contenu de `init.sql` et exécutez-le dans votre base de données `fil_rouge_rattrapage`. Cela créera les tables `employees`, `clients`, `seances`, `tickets` et les remplira avec des données d'exemple.
    -   **Adaptez la connexion dans `index.php` :** Ouvrez `index.php` à la racine du projet et assurez-vous que la configuration `PostgreDataSource` correspond à vos identifiants PostgreSQL :
        ```php
        // Dans index.php
        use Core\DataSources\PostgreDataSource;

        $ds = new PostgreDataSource(
            'localhost',            // Hôte de votre BDD
            5432,                   // Port PostgreSQL (souvent 5432)
            'fil_rouge_rattrapage', // Nom de votre base de données
            'your_user',            // Votre utilisateur PostgreSQL
            'your_password'         // Votre mot de passe PostgreSQL
        );
        Database::init($ds);
        ```
5.  **Démarrer Laragon :** Assurez-vous que les services Apache/Nginx et PostgreSQL de Laragon sont en cours d'exécution.
6.  **Accéder à l'API :** Votre API devrait être accessible via l'hôte virtuel créé par Laragon, par exemple : `http://cinema.test/cinema_backend/`. Accéder à cette URL devrait afficher la liste des routes de l'API en format JSON.

## Documentation et Tests

-   Pour tester les endpoints de l'API (Clients, Séances, Tickets), utilisez un outil comme **Postman** ou **Insomnia**.
-   Vous pouvez consulter la [Collection Postman originale du boilerplate](https://www.postman.com/simplon-devs/youcode-fil-rouge-a1/collection/9x2u8lq/youcode-fil-rouge-rattrapage) pour voir des exemples de requêtes (pour l'entité `Employee`). Adaptez ces exemples pour vos nouvelles entités (`clients`, `seances`, `tickets`).

## Recommandations

-   Respectez scrupuleusement la structure du dossier `App/`.
-   Trouvez du plaisir dans la réalisation du projet !
-   **Bon courage !!**