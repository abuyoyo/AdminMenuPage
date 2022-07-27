# Changelog
WPHelper\AdminMenuPage

## 0.19

### Added
- SettingsPage supports `text`, `url`, `email` fields.
- CMB2_OptionsPage supports all admin menu top-level slugs.

### Fixed
- Fixed PHP fatal error: cannot redeclare function `wph_extra_plugin_headers()`.

### Changed
- If CMB2 plugin is not activated - show missing plugin card on `cmb2` and `cmb2-tabs` pages.

## 0.18

### Added

- Add `wrap` parameter to output WordPress admin `.wrap` template. Accepts `simple` and `sidebar`.
- Accept `plugin_info = true` to output default plugin info meta box and wrap.
- Add `Last Update` and `Release Date` optional headers to WordPress theme headers (Used in plugin info-box).

### Changed
- All classes are pluggable.
- Prevent direct access if not withing WordPress environment.

## 0.17

### Changed

- Various improvements to CMB2 settings pages.
- Make use of CMB2 2.9.0's `options_page_tab_nav_output()` to render tabs on non-CMB2 pages.
- Plugins can provide their own plugin info-box render callback.
- Parent item's first sub-menu page (itself) uses item's `tab_title` instead of `menu_title`

### Added
- Add action `wphelper/adminpage/plugin_info_box/$slug` to render plugin info-box.
- Add `Last Update` and `Release Date` optional headers to WordPress plugin headers (Used in plugin info-box).

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
