Ositel Symfony3 test
========================

### Bundles/Outils ajoutés:
- **knplabs/knp-paginator-bundle** : pour la pagination.
- **symfony/maker-bundle** : pour la génération des Entities, CRUD ...
- **doctrine/doctrine-fixtures-bundle** : pour la génération des données de tests (commande `php bin/console doctrine:fixtures:load -n` ou bien `php bin/console doctrine:fixtures:load -n --purge-with-truncate --env=test` pour l'environment de test)
- **sebastian/phpcpd** : pour la détection de code dupliqué pour le refactoring du code (commande `vendor/bin/phpcpd src` pour détecter le code dupliqué et appliquer le refactoring manuellement)
- **friendsofphp/php-cs-fixer** : Pour la validation/Amélioration de la qualité du code (Fichier config ".php-cs",  commandes `vendor/bin/php-cs-fixer fix --diff --dry-run` pour valider la qualité du code et afficher les modifications nécessaires et `vendor/bin/php-cs-fixer fix` pour appliquer les corrections sur le code)

### Instalaltion du projet:
- `composer install` pour l'installation des dépondances
- `php bin/console do:da:cr` et `php bin/console do:da:cr --env=test` : pour la creation de la base de données
- `php bin/console do:sc:up -f` et `php bin/console do:sc:up -f --env=test` : pour la mise à jour du schema de la base de données
- `php bin/console doctrine:fixtures:load -n` et `php bin/console doctrine:fixtures:load -n --purge-with-truncate --env=test` pour la génération des données de test
- `vendor/bin/simple-phpunit` pour lancer phpunit (j'ai écrit quelques tests fonctionnels)
