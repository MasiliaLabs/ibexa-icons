# Changelog

All notable changes to `masilia/ibexa-icons` are documented here. The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/) (before 1.0, a minor version may contain breaking changes).

## [Unreleased]

## [0.1.2] - 2026-11-22

### Added
- Labels (English and French, domain `forms`) for the policies of the module `masilia_icon` in Admin > Roles: `role.policy.masilia_icon` and its functions. Before, the role editor showed the raw keys.

## [0.1.1] - 2026-11-20

### Added
- Docs: a Stay informed section in the README.

## [0.1.0] - 2026-11-20

### Added
- A registry of the icons of the content types and groups (table `masilia_icon`), read by Ibexa through a configuration resolver ahead of its YAML, so an icon changes without a deploy or a cache clear.
- `masilia:icons:list`, `masilia:icons:set`, `masilia:icons:import` (the thumbnails of an Ibexa YAML file) and `masilia:icons:defaults` (the icons of the socle of `masilia/ibexa-blocks`).
- The names of icons are checked against the sprite of Ibexa; a wrong name is refused with the closest ones.
- The page Admin > Icons: the content types and groups with their icon and its origin, filters, and a panel to choose an icon among those of Ibexa. Policies `masilia_icon/view` and `masilia_icon/manage`, JSON API under `/api/masilia/icons`, texts in English and French.
- Assistant capabilities `icons.list`, `icons.search` and `icons.set` when `masilia/ibexa-ai` is installed. A choice made in the admin is never replaced by the assistant, an import or the defaults.

[Unreleased]: https://github.com/MasiliaLabs/ibexa-icons/compare/0.1.2...HEAD
[0.1.2]: https://github.com/MasiliaLabs/ibexa-icons/compare/0.1.1...0.1.2
[0.1.1]: https://github.com/MasiliaLabs/ibexa-icons/compare/0.1.0...0.1.1
[0.1.0]: https://github.com/MasiliaLabs/ibexa-icons/releases/tag/0.1.0
