# Changelog
WPHelper\AdminMenuPage

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
