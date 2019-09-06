# Changelog
WPHelper\AdminMenuPage

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
