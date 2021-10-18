# Contexte
**BileMo** est une entreprise offrant toute une sélection de téléphones mobiles haut de gamme.   

Vous êtes en charge du développement de la vitrine de téléphones mobiles de l’entreprise BileMo. Le business modèle de BileMo n’est pas de vendre directement ses produits sur le site web, mais de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue via une API (Application Programming Interface). Il s’agit donc de vente exclusivement en B2B (business to business).   
   
Il va falloir que vous exposiez un certain nombre d’API pour que les applications des autres plateformes web puissent effectuer des opérations.   
   
## Besoin client

Le premier client a enfin signé un contrat de partenariat avec BileMo ! C’est le branle-bas de combat pour répondre aux besoins de ce premier client qui va permettre de mettre en place l’ensemble des API et de les éprouver tout de suite.   
   
Après une réunion dense avec le client, il a été identifié un certain nombre d’informations. Il doit être possible de:   
   
* consulter la liste des produits BileMo.
* consulter les détails d’un produit BileMo.
* consulter la liste des utilisateurs inscrits liés à un client sur le site web.
* consulter le détail d’un utilisateur inscrit lié à un client.
* ajouter un nouvel utilisateur lié à un client.
* supprimer un utilisateur ajouté par un client.
   
Seuls les clients référencés peuvent accéder aux API. Les clients de l’API doivent être authentifiés via OAuth ou JWT.
   
Vous avez le choix entre mettre en place un serveur OAuth et y faire appel (en utilisant le [FOSOAuthServerBundle](https://packagist.org/packages/friendsofsymfony/oauth-server-bundle)), et utiliser Facebook, Google ou LinkedIn. Si vous décidez d’utiliser JWT, il vous faudra [vérifier la validité du token](https://github.com/lexik/LexikJWTAuthenticationBundle); l’usage d’une librairie est autorisé.   
   
## Présentation des données   
   
Le premier partenaire de BileMo est très exigeant : il requiert que vous exposiez vos données en suivant les règles des niveaux 1, 2 et 3 du modèle de Richardson. Il a demandé à ce que vous serviez les données en JSON. Si possible, le client souhaite que les réponses soient mises en cache afin d’optimiser les performances des requêtes en direction de l’API.   

### De l’aide pour aborder le projet étape par étape

Afin de fluidifier votre avancement voici une proposition de manière de travailler:   

* **Étape 1** - Prenez connaissance entièrement de l’énoncé et des spécifications détaillées.   
* **Étape 2** - Créez le repository GitHub pour le projet.   
* **Étape 3** - Créez l’ensemble des issues sur le repository GitHub (https://github.com/username/nom_du_repo/issues/new).
* **Étape 4** - Faites les estimations de l’ensemble de vos issues.   
* **Étape 5** - Entamez le développement de l’application et proposez des pull requests pour chacune des fonctionnalités/issues tout en veillant à valider la qualité de votre code ainsi que ses performances.   
* **Étape 6** - Faites relire votre code à votre mentor (code proposé dans la ou les pull requests), et une fois validée(s) mergez la ou les pull requests dans la branche principale. (Cette relecture servira à valider votre implémentation des bonnes pratiques et la cohérence de votre code. La validation se fera en continu durant les sessions.)   
* **Étape 7** - Effectuez une démonstration de l’ensemble de l’application.   
* **Étape 8** - Préparez l’ensemble de vos livrables et soumettez-les sur la plateforme.   

Prenez le temps de valider chaque étape avec votre mentor afin de vous assurer que vous avancez dans la bonne direction.   
   
## Livrables   
   
* Un lien vers l’ensemble du projet (fichiers PHP/HTML/JS/CSS…) sur un repository Github   
* Diagrammes UML (modèles de données, classes, séquentiels)   
* Les instructions pour installer le projet (dans un fichier README à la racine du projet)   
* Les issues sur le repository GitHub   
* Documentation technique de l’API à destination des futurs utilisateurs   
