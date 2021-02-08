# Changelog
WPHelper\AdminMenuPage

## 0.16

### Fixed

- Fix CMB2 "multi" options page to actually override fields.

### Changed

- Add CMB2 fields directly in options array instead of using `add_field` method.

## 0.15

### Changed

- Restore deprecated param to SettingsPage constructor and add `_deprecated_argument` message.

## 0.14

### Added

- Add CMB2 Options-page delegation. Allows adding CMB2 options page.
- Add CMB2 Options "multi" page. Allows CMB2 options page that saves each field to its own row in options table.
- Supports CMB2 tabs in CMB2 option-pages.
- Add Plugin Info metabox to CMB2 tables.

### Changed

- Deprecate `AdminPage->setup` - add `_doing_it_wrong` message.
- Admin Page method `bootstrap()` runs on `init` hook instead of constructor. Allows setter functions to have effect.

## 0.13

### Added

- Add `methods` option to load functions on `load-{hook_suffix}` hook.
- Add `get_hook_suffix()` getter method (`hook_suffix` variable is no longer public).

## v0.12

### Changed

- New `AdminPage` class.
- Deprecate class `AdminMenuPage` in favor of `AdminPage`.
- Restructure source files.

## v0.11

### Added

- Setting Page - class and template for registering WordPress settings page.
- Options_Menu - use WordPress core `add_options_page` to register page.

### Changed

- No longer require call to `setup()` method. Bootstrap into WordPress from constructor method.

## v0.10

### Added

- Styles - enqueue styles to registered admin page

## v0.9

### Changed

- Don't use extract() in constructor
- Use setter methods for all variables

### Fixed

- Fix PHP notices: undefined property

## v0.8

### Fixed

- Removed calls to AdminNotice causing errors.

## v0.7

### Fixed

- Fixed error when no scripts are added

### Changed

- Accept `render_cb` and `render_tpl` args. Use `render` method instead of `template`
- Print default template if no callback or template provided

## v0.6

### Added

- Initial public release
- Register and print top-level or submenu pages to WordPress admin menu
- Enqueue scripts to registered admin page
