# Changelog
WPHelper\AdminMenuPage

## 0.31
Release date: Feb 14 2024

### Fixed
- Fix plugin info box not appearing on CMB2 pages after last release.

## 0.30
Release date: Feb 14 2024

### Changed
- Add `.wp-header-end` hr element to all templates.
- Restore default h2 styling to forms inside `#poststuff` element.
- Remove `.meta-box-sortables` wrapper from main content on sidebar templates using `#poststuff` element hijacked from Edit Post page.

### Internal
- Add `_deprecated_argument` message to `render_cb` and `render_tpl` options.
- Only run `add_plugin_info_meta_box()` if `plugin_info` option is truthy.

## 0.29
Release date: Oct 5 2023

### Fixed
- `composer.json` - Require `abuyoyo/metabox`. WPHelper\Metabox has been a required library since 0.25.

### Dependencies
- lib: WPHelper\Metabox (`abuyoyo/metabox`) ~0.8.

## 0.28
Release date: Oct 4 2023

### Added
- Option `parent` accepts `tools` as shorthand for `tools.php`.
- Add link to Install Plugin page in "CMB2 plugin missing" template. 

## 0.27
Release date: Sep 10 2023

### Fixed
- Fix nav-tabs appearing on pages without `tab_group`.

## 0.26
Release date: Jun 20 2023

### Added
- Add `allow_on_front` setting to CMB2 pages. Hooks metabox on `cmb2_init` instead of `cmb2_admin_init`.
- If defined `WPH_DEBUG` add WPHelper classes debug information to plugin info meta box.

## 0.25
Release date: Jun 9 2023

### Added
- Non-CMB2 pages can be added to CMB2 tab groups. New options `tab_group` and `tab_title`

### Changed
- New method `render_plugin_info_meta_box`. Deprecate `render_plugin_info_box`.
- Plugin info meta box rendered using `WPHelper\MetaBox`.

### Fixed
- Fix several PHP undefined variable warnings.

### Internal
- Setting pages/wrap template uses WordPress Core `do_meta_boxes` to render `side` meta boxes div.
- Add variables to `AdminPage::options()` array.
- Multiple code refactoring and template restructuring.

## 0.24
Release date: Jan 28 2023

### Fixed
- Fix plugin info meta box when no PluginCore is available.
- Fix PHP deprecated notice.

## 0.23
Release date: Jan 15 2023

### Added
- Add action hook `wphelper/plugin_info_meta_box/{$slug}` to modify and render plugin info meta box.
- Add support for `textarea` input field in SettingsPage.
- Add `sanitize_callback` option - allow plugins to supply their own sanitize function.
- Add `render` to fields - allow plugins to supply their own render callback for fields.
- Add `placeholder` to fields - allow plugins to supply placeholder values for fields.

### Fixed
- Fix default value handling for fields.

### Internal
- Rename `tpl/` template parts.
- Minor changes and fixes.

## 0.22
Release date: Jan 1 2023

### Fixed
- Fix error when `plugin_info = true` but `plugin_core` is not set.

## 0.21

### Fixed
- Minor fixes.

## 0.20

### Added
- Add SettingsPage section option `description-container`. Accepts `card` div, `notice`, `notice-info` and `none`.
- Sanitize SettingsPage text, url and email fields.

## 0.19

### Added
- SettingsPage supports `text`, `url`, `email` fields.
- CMB2_OptionsPage supports all admin menu top-level slugs.

### Fixed
- Fix PHP fatal error: cannot redeclare function `wph_extra_plugin_headers()`.

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
