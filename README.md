# masilia/ibexa-icons

The icons of the content types and content type groups of Ibexa DXP 5, chosen **at run time** instead of written in YAML.

In Ibexa the icon of a content type is a setting of the configuration (`content_type.<identifier>.thumbnail`), read when the container is built: a type created later, by a command, by the assistant or in the admin, has no icon until someone edits a YAML file and clears the cache. This package keeps the icons in the database, finds every content type and group that has none, checks the names against the icon set of Ibexa, and lets the assistant of [`masilia/ibexa-ai`](https://github.com/MasiliaLabs/ibexa-ai) choose them.

## Features

- **A registry of icons** (`masilia_icon` table): the icon of each content type and group, and who chose it (`admin`, `ai`, `import`, `default`). Ibexa reads it through its own configuration resolvers: the admin shows the icon in the content tree, the lists of types and the create menu at once, without clearing the cache.
- **An admin page** (Admin > Icons): every content type and group with its icon and where it comes from, a filter bar, and a panel with the icons of Ibexa to choose from (search by name, grid of symbols). Built with `masilia/ibexa-admin-kit`; policies `masilia_icon/view` and `masilia_icon/manage`.
- **Detection**: `masilia:icons:list` lists every content type and group of the repository with its icon and where it comes from (the registry, the YAML of the project, or nothing).
- **The icon set of Ibexa**: only the name of a symbol of its SVG sprite is kept; the names are checked against the sprite (a typo gets the closest names), and the path of the sprite is in one place of the package.
- **Import**: `masilia:icons:import` brings the thumbnails of an existing Ibexa YAML file into the registry.
- **Defaults**: `masilia:icons:defaults` gives the icons of the socle of [`masilia/ibexa-blocks`](https://github.com/MasiliaLabs/ibexa-blocks) (home page, page, site, layout, blocks and their groups) to what has none.
- **The assistant chooses**: with `masilia/ibexa-ai`, three capabilities, `icons.list`, `icons.search` and `icons.set` (with an approval card), let the model give an icon to each type and group that has none. What a person chose is never replaced by the assistant.
- **Ibexa's YAML still works**: a type with no icon in the registry keeps the one of the YAML, then Ibexa's default icon.

## Requirements

- PHP 8.3 or 8.4
- Ibexa DXP 5.0 (`ibexa/core`, `ibexa/admin-ui`), with `doctrine/orm` and `doctrine/doctrine-migrations-bundle`
- MySQL or MariaDB
- `masilia/ibexa-admin-kit` (the components of the admin page), with Node.js and Yarn to build the admin assets
- Optional: `masilia/ibexa-ai` (the assistant), `masilia/ibexa-blocks` (the icons of its socle)

## Installation

```bash
composer require masilia/ibexa-icons
```

1. Enable the bundle in `config/bundles.php` (Symfony Flex does it):

   ```php
   Masilia\Bundle\IbexaIcons\MasiliaIbexaIconsBundle::class => ['all' => true],
   ```

2. Import the routes of the page (`config/routes/masilia_ibexa_icons.yaml`):

   ```yaml
   masilia_ibexa_icons:
       resource: '@MasiliaIbexaIconsBundle/Resources/config/routes.yaml'
   ```

3. Create the table and build the admin assets:

   ```bash
   php bin/console doctrine:migrations:migrate
   php bin/console cache:clear
   yarn ibexa-generate-tsconfig
   php bin/console bazinga:js-translation:dump public/assets --merge-domains
   php bin/console ibexa:encore:compile
   ```

   Inside DDEV, prefix the commands with `ddev exec`. `MasiliaIbexaAdminKitBundle` must be enabled too.

4. Give a role the policies `masilia_icon / view` (see the page) and `masilia_icon / manage` (choose icons). The Administrator role has them.

5. Optional: give the icons of the socle of the Masilia packages, or bring the ones you already have:

   ```bash
   php bin/console masilia:icons:defaults
   php bin/console masilia:icons:import config/packages/ibexa_admin_icons.yaml
   ```

### With Symfony Flex

The Masilia recipes do the first steps for you. Add their endpoint once to the `composer.json` of your project, after the one of Ibexa:

```json
"extra": {
    "symfony": {
        "endpoint": [
            "https://api.github.com/repos/ibexa/recipes/contents/index.json?ref=flex/main",
            "https://api.github.com/repos/MasiliaLabs/recipes/contents/index.json?ref=flex/main",
            "flex://defaults"
        ]
    }
}
```

Then `composer require masilia/ibexa-icons` enables the bundle and writes `config/routes/masilia_ibexa_icons.yaml`. What is left is yours: the migration, the admin build and the policies. The recipe prints these steps once the `auto-scripts` of Composer have run.

## Usage

Open **Admin > Icons**: search or filter the list (by type or group, by where the icon comes from), choose the pencil on a row, search the icon set by name, pick a symbol and save. The change shows in the content tree at once. The other button takes a choice back: the YAML of the project, then Ibexa's default icon, answer again.

From the console:

```bash
# What has an icon, and where it comes from (registry: admin, ai, import, default; config: your YAML; none)
php bin/console masilia:icons:list [--missing] [--json]

# Give an icon to a content type or a group
php bin/console masilia:icons:set type hero_banner banner
php bin/console masilia:icons:set group "Block items" box-component

# Bring the thumbnails of a YAML file into the registry (--dry-run shows them first)
php bin/console masilia:icons:import config/packages/ibexa_admin_icons.yaml [--dry-run]

# The icons that the Masilia packages suggest for what has none
php bin/console masilia:icons:defaults [--dry-run]
```

The icons of the YAML of the project are read in the scope of the siteaccess: run `masilia:icons:list --siteaccess=admin` to see the ones of your admin group as `config`.

### Names of icons

An icon is the name of a symbol of the sprite of Ibexa (`/bundles/ibexaadminuiassets/vendors/ids-assets/dist/img/all-icons.svg`): `home`, `calendar`, `folder`, `user-group`... A name that is not in the sprite is refused with the closest names. The old path of Ibexa 4 (`/bundles/ibexaadminui/img/ibexa-icons.svg#...`) and the names that Ibexa 5 renamed are not valid: `masilia:icons:import` reports them.

### Groups

Ibexa writes the name of a group slugged in its settings (the group "Block items" is `content_type_group.block_items`). The registry keeps the real name of the group and answers to that form, so a YAML key `block_items` in an import finds the group "Block items".

### Who wins

| Icon chosen by | Replaced by |
| --- | --- |
| `default` | `default`, `import`, `ai`, `admin` |
| `import` | `import`, `ai`, `admin` |
| `ai` | `ai`, `admin` |
| `admin` | `admin` only |

For one type, the answer comes from: the registry, then the YAML of the project, then `default-config` of Ibexa.

## The assistant

With `masilia/ibexa-ai` installed, the assistant gets three capabilities (nothing to configure):

| Capability | Risk | What it does |
| --- | --- | --- |
| `icons.list` | read | Lists the types and groups with their icon; `missing_only` keeps the ones without |
| `icons.search` | read | Finds icon names from English words ("event calendar"), so the model chooses among a few instead of hundreds |
| `icons.set` | write (approval card) | Gives icons to several types and groups, each with a short reason shown for approval |

Ask the assistant: *"Give an icon to the content types and groups that have none."* It lists what is missing, searches an icon for each, and proposes the choices on one approval card.

## In code

Read and write through `Masilia\Contracts\IbexaIcons\IconRegistry` (`find`, `all`, `set`, `remove`), never the table. `set()` returns `false` when a choice of a higher rank is kept and throws `InvalidIcon` for a name that is not in the sprite.

## Tests

```bash
composer install
vendor/bin/phpunit
vendor/bin/phpstan analyse
```

## Stay informed

New versions are announced as **GitHub releases**, with their notes taken from the [CHANGELOG](CHANGELOG.md):

- **On GitHub**: *Watch* this repository, *Custom*, *Releases*, to be notified of each release; or follow the feed `https://github.com/MasiliaLabs/ibexa-icons/releases.atom` in your reader.
- **In your project**: `composer outdated "masilia/*"` lists what is behind. To be told by a pull request, let Dependabot (or Renovate) watch the Masilia packages:

  ```yaml
  # .github/dependabot.yml
  version: 2
  updates:
    - package-ecosystem: composer
      directory: /
      schedule: { interval: weekly }
      allow:
        - dependency-name: "masilia/*"
  ```

- **Before updating**: the CHANGELOG says what changes; a change that breaks something is marked **Breaking** and explained in `UPGRADE.md` when there is one. Before 1.0, a minor version (`0.x`) may contain breaking changes: read the notes of the versions you skip. Security problems are announced as GitHub security advisories (see [SECURITY.md](SECURITY.md)).

## Support

[GitHub issues](https://github.com/MasiliaLabs/ibexa-icons/issues). Security: see [SECURITY.md](SECURITY.md). Contributions: see [CONTRIBUTING.md](CONTRIBUTING.md).

## License

MIT
