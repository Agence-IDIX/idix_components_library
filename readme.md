# Documentation

## Setup

### Pré-requis

- Installation du projet Drupal
- Node.js

### Installation des dépendances du thème

Se placer à la racine du thème Drupal par défaut et lancer la commande :

```
npm install
```

## Accès à la bibliothèque de composants

`[project_domain]/idix_components_library`

Exemple : https://dargaud.local/idix_components_library

## Création d'un nouveau composant

La création d'un nouveau composant se fait en ligne de commande. Il faut se placer dans le répertoire racine du projet Drupal.

### Utilisation simple

Avec la Drupal Console accessible au global :

```
drupal --uri=[drupal_site] iclgc
```

Exemple :

```
drupal --uri=dargaud.com iclgc
```

### Utilisation spécifique

Si besoin d'utiliser une version spécifique de php et de la Drupal Console local du projet :

```
[php_path]/php ./vendor/drupal/console/bin/drupal --uri=[drupal_site] iclgc
```

Exemple :

```
/Applications/MAMP/bin/php/php7.2.1/bin/php ./vendor/drupal/console/bin/drupal --uri=dargaud.com iclgc
```

### Options de configuration

Après avoir lancé la commande, des questions sont posées pour configurer le composant.

<details>
<summary>Détails des quesions</summary>

```
Enter the component name (human readable)
```

Saisir le nom du composant tel qu'il sera affiché dans la bibliothèque.

- bon exemple `"Mon beau composant"`
- mauvais exemple `"monbeaucomposant"`

```
Enter the component machine name
```

Nom système utilisé par Drupal pour faire référence à ce composant. Automatiquement généré, il peut être modifié si besoin.
Pour information, ce nom sera aussi utilisé comme class de base dans le HTML.

```
Enter the theme name
```

Sélectionner le thème Drupal où sera installé le composant.

```
Enter the component path (relative to the theme)
```

Sous-dossier d'installation. Utiliser le chemin par défaut proposé (sauf cas particulier).

```
Do you want to generate a twig file?
```

Si oui, un template twig sera automatiquement généré. De base, ce dernier inclus un code HTML minimaliste et le code permettant le chargement des fichiers CSS et/ou JS associés au composant (cf [Drupal asset libraries](https://www.drupal.org/docs/8/theming/adding-stylesheets-css-and-javascript-js-to-a-drupal-8-theme)).

```
Do you want to generate a javascript file?
```

Création d'un fichier javascript associé au composant. Ce dernier inclus un code JS minimaliste basé sur le principe des `behaviors` (cf [Drupal JavaScript API](https://www.drupal.org/docs/8/api/javascript-api/javascript-api-overview)).

</details>

## Développement d'un composant

### Structure du dossier composant

- `[component].twig`

  Code HTML avec templating twig

- `[component].scss`

  Source des styles Sass

- `[component].css`

  Feuille de style générée

- `[component].js`

  Fichier javascript

- `[component].styleguide.yml`

  Ce fichier permet de documenter le composant et d'alimenter sa page dans la biliothèque (cf détails au point suivant)

A cela peut s'ajouter certains assets dédiés au composant (images...).

Pour information le fichier `scss` est obligatoirement généré à la création du composant.

### Fichier styleguide.yml

Pour chaque composant, un fichier `[component].styleguide.yml` est créé. Il fait office de documentation du composant et définit son utilisation, ses différents types d'affichage...

Il est structuré ainsi :

- `name` nom du composant

- `variables` tableau listant les variables twig utilisées (si un fichier twig accompagne le composant utilisé) - une variable est définit ainsi :

  - `name` nom de la variable
  - `type` type (string, array...)
  - `default` valeur par défaut
  - `description` texte au format HTML (render array ou markup) décrivant la variable

- `samples` tableau listant les exemples d'affichage et d'utilisation du composant - un sample est définit ainsi :

  - `name` nom de l'exemple
  - `variables` tableau listant des variables injectées automatiquement dans le template twig pour le rendu de l'exemple
  - `template` code HTML en dur

  Si le compsant se base sur un template twig, `variables` sera préféré à `template`.

- `overrides` permet de surcharger l'affichage du composant dans la bibliothèque

  - `css` surcharge de style

- `readme` permet d'indiquer des infos complémentaires

[Exemple avec template twig](https://github.com/Agence-IDIX/idix_components_library/blob/master/example-twig.styleguide.yml)

[Exemple sans template twig](https://github.com/Agence-IDIX/idix_components_library/blob/master/example.styleguide.yml)

### Build

[Gulp](https://gulpjs.com/) est utilisé comme "task runner" pour le builder les assets du thème (principalement la compilation des CSS via Sass).

Se placer à la racine du thème Drupal par défaut et lancer la commande :

```
npm run build

```

Un "watcher" est aussi disponible pour faciliter le développement.

```
npm run watch

```
