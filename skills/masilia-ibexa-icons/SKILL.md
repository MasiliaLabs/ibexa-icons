---
name: masilia-ibexa-icons
description: Give icons to the content types and content type groups of an Ibexa 5 admin with masilia/ibexa-icons (registry in the database, detection of types without icon, names checked against the Ibexa icon set, import of an old ibexa_admin_icons.yaml, assistant capabilities icons.list / icons.search / icons.set). Use whenever the user talks about icons of content types or groups, the thumbnail setting, ibexa_admin_icons, a type that shows the default icon, `masilia:icons:*` commands, or masilia/ibexa-icons, even without the package name.
---

# masilia/ibexa-icons

Ibexa reads the icon of a type from `content_type.<identifier>.thumbnail` (and of a group from `content_type_group.<slug>`), built into the container. This package keeps the icons in the table `masilia_icon` and answers those settings through a configuration resolver ahead of the YAML, so a change shows in the admin at once. Only the **name of a symbol** of the Ibexa sprite is stored (`home`, `calendar`...); the path of the sprite is in the package.

## Install
`composer require masilia/ibexa-icons`; enable `MasiliaIbexaIconsBundle` (Flex does it); import `@MasiliaIbexaIconsBundle/Resources/config/routes.yaml`; `php bin/console doctrine:migrations:migrate`; `cache:clear`; `yarn ibexa-generate-tsconfig`; `bazinga:js-translation:dump public/assets --merge-domains`; `php bin/console ibexa:encore:compile`; give a role `masilia_icon/view` and `masilia_icon/manage`. Needs `MasiliaIbexaAdminKitBundle`. Inside DDEV prefix with `ddev exec`.

## Everyday work
- Admin > **Icons** (`/icons`): list, filters, and a panel to choose an icon among those of Ibexa. The browser caches the admin scripts: hard reload after a rebuild.
- See what has an icon: `masilia:icons:list [--missing] [--json]`. "From" says who chose it: `admin`, `ai`, `import`, `default`, `config` (YAML of the project, read in the scope of the siteaccess: add `--siteaccess=admin`), `none` (Ibexa shows its default icon).
- Set one: `masilia:icons:set type <identifier> <icon>` or `masilia:icons:set group "<Group name>" <icon>`. A wrong name is refused with the closest ones.
- Bring an old file: `masilia:icons:import <file.yaml> [--dry-run]`; types that do not exist in the repository are listed as skipped.
- The suggestions of the Masilia packages for their socle and groups: `masilia:icons:defaults [--dry-run]`.
- Assistant (needs `masilia/ibexa-ai`): `icons.list` (`missing_only`), `icons.search` (English words), `icons.set` (a list of `{kind, identifier, icon, reason}`, approval card). The assistant never replaces an icon a person chose.

## Rules
- The group is identified by its real name (`Block items`); Ibexa looks it up by its slug (`block_items`), and the registry answers to that.
- Who wins: admin > ai > import > default. For one type: registry, then YAML, then `default-config`.
- Code reads and writes through `Masilia\Contracts\IbexaIcons\IconRegistry`, never the table.

## Troubleshooting
- An icon does not show: `masilia:icons:list` for the type; the admin siteaccess must be the one that reads it (the registry is global, the YAML is not); hard reload the admin.
- "is not in the Ibexa icon set": the name is not a symbol of `all-icons.svg` (an Ibexa 4 name, a typo). `masilia:icons:set` suggests the closest; the assistant uses `icons.search`.
- The sprite is not found: `assets:install` was not run (the package looks in `public/bundles/ibexaadminuiassets/...` then in `vendor/ibexa/admin-ui-assets`).
- After SQL changes or restores, clear `cache:pool:clear cache.app` (the registry is cached there).
Never type the user's passwords or API keys on their behalf.
