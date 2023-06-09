# WPHelper \ AdminMenuPage

> Helper class for simple admin menu page registration in WordPress.

## Requirements
* PHP >= 7.4
* [Composer](https://getcomposer.org/)
* [WordPress](https://wordpress.org)

## Installation

Install with [Composer](https://getcomposer.org/) as a library.

```PHP
// Require the Composer autoloader anywhere in your code.
require __DIR__ . '/vendor/autoload.php';

```

WPHelper\AdminMenuPage uses [PSR-4](https://www.php-fig.org/psr/psr-4/) to autoload.

OR

Install as WordPress plugin and activate.

## Basic Usage

```PHP
// Import AdminPage.
use WPHelper\AdminPage;

// Register the admin menu page.
$args = [
    'title' => 'The Tile of My Page', // title - passed to add_menu_page
    'menu_title' => 'Page Title', // menu_title - passed to add_menu_page (optional - will use title if none provided)
    'capability' => 'manage_options', // capability - passed to add_menu_page (optional - will default to 'manage_options')
    'slug' => 'my_page', // slug - passed to add_menu_page
    'template' => 'tpl/my_admin_page.php', // template - include file to print the page. wrapped in callback and passed to add_menu_page
    'parent' => 'parent_page_slug'; // optional - slug of parent page if creating submenu
    'icon_url' => $icon_url; // optional - icon url passed to add_menu_page/add_submenu_page
    'position' => 4; // optional - passed to add_menu_page
    'scripts' => [ // optional - script parameters passed to enqueue_scripts. Will only enqueue scripts on admin page
        [ 'script_handle', 'js/my_script.js', ['jquery'], false, true ],
        [ 'another_script', 'js/my_other_script.js', ['jquery', 'script_handle'], false, true ]
    ];
];

// Register the admin menu page.
$admin_menu_page = new AdminPage( $args );

// That's it. We're done.
// This function can be called from anywhere. No need to wrap in any hook.
```
