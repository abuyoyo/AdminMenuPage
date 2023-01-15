<?php
namespace WPHelper;

defined( 'ABSPATH' ) || die( 'No soup for you!' );

use function register_setting;
use function add_settings_section;
use function add_settings_field;
use function checked;
use function get_option;

if ( ! class_exists( 'WPHelper\SettingsPage' ) ):
/**
 * SettingsPage
 * 
 * Helper class
 * Create WordPress Setting page.
 * 
 * @author  abuyoyo
 * 
 * @since 0.11
 */
class SettingsPage{

	/**
	 * AdminPage instance that called this class
	 * 
	 * @var AdminPage $admin_page instance
	 */
	protected $admin_page;

	/**
	 * Page slug to display sections
	 * 
	 * @var String $page
	 */
	protected $page;

	/**
	 * option_name key used in wp_options table
	 * 
	 * @var String $option_name
	 */
	protected $option_name;

	/**
	 * option_group used by register_setting() and settings_fields()
	 * 
	 * @var String $option_group
	 */
	public $option_group;

	/**
	 * Sections
	 * 
	 * @var Array[] $sections
	 */
	public $sections = [];

	/**
	 * Fields
	 * 
	 * @var Array[] $fields
	 */
	public $fields = [];

	/**
	 * Sanitize Callback
	 * 
	 * @var Callable $sanitize_callback
	 */
	public $sanitize_callback;

	/**
	 * Constructor.
	 *
	 * @param AdminPage $admin_page instance
	 * @param null $settings deprecated
	 */
	public function __construct( $admin_page, $settings = null )
	{

		if ( ! empty( $settings ) ) {
			_deprecated_argument( __FUNCTION__, '3.0.0' );
		}

		// save reference to caller instance
		$this->admin_page = $admin_page;

		$admin_options = $admin_page->options();
		$settings = $admin_options['settings'];

		$this->page = $admin_options['slug'];

		$this->option_name = $settings['option_name'] ?? str_replace( '-', '_' , strtolower( $this->page ) );

		$this->option_group = $settings['option_group'] ?? $this->page . '_option_group';

		$this->sanitize_callback = $settings['sanitize_callback'] ?? null;

		foreach ( $settings['sections'] as $section ) {
			// extract fields
			foreach ( $section['fields'] as $field ){
				$field['section_id'] = $section['id']; // create back-reference in field to section. ( @see add_settings_field() )
				$field['name'] = $this->option_name . '[' . $field['id'] . ']';
				$this->fields[] = $field;
			}
			unset( $section['fields'] );
			$this->sections[] = $section; // save without fields
		}
	}

	function setup() {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
	}

	public function register_settings() {
		register_setting(
			$this->option_group, // $option_group - A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
			$this->option_name, // $option_name - The name of an option to sanitize and save.
			$this->sanitize_callback ?? [ $this,'sanitize_settings' ] // callback ?? fallback // $sanitize_callback - A callback function that sanitizes the option's value. (see also: built-in php callbacks)
		);

		foreach ( $this->sections as $section ){
			add_settings_section(
				$section['id'], // $id - Slug-name to identify the section. Used in the 'id' attribute of tags.
				$section['title'] ?? null, // $title - Formatted title of the section. Shown as the heading for the section.
				$this->section_description_cb( $section ), // $callback - Function that echos out any content at the top of the section (between heading and fields).
				$this->page // $page - The slug-name of the settings page on which to show the section.
											//Built-in pages include 'general', 'reading', 'writing', 'discussion', 'media', etc.
			);
		}

		foreach ( $this->fields as $field ) {
			add_settings_field(
				$field['id'],
				$field['title'],
				$field['render'] ?? [ $this, "print_{$field['type']}" ],
				$this->page, // can built-in pages: (general, reading, writing, ...)
				$field['section_id'],
				$field //send setting array as $args for print function
			);
		}

	}

	/**
	 * Print checkbox input field
	 * Support field type 'checkbox'
	 * 
	 * @since 0.11
	 */
	function print_checkbox( $field ) {
		extract($field);

		$options = get_option( $this->option_name );

		$input_tag = sprintf(
			'<label for="%1$s">
				<input name="%2$s" type="checkbox" id="%1$s" aria-describedby="%1$s-description" value="1"  %4$s />
				%3$s
			</label>',
			$id,
			$name,
			$description,
			checked( ( $options[$id] ?? false ), '1', false)
		);

		/**
		 * Allow plugins to directly manipulate field HTML
		 */
		$input_tag = apply_filters( 'wphelper/settings_page/input_checkbox', $input_tag, $field, $this->option_name, $options );

		echo $input_tag;

	}

	/**
	 * Print text input field
	 * Support field type 'text'
	 * 
	 * @since 0.19
	 */
	function print_text( $field ) {
		extract($field);

		$options = get_option( $this->option_name );

		$input_tag = sprintf(
			'<input name="%2$s" type="text" id="%1$s" aria-describedby="%1$s-description" value="%3$s" placeholder="%4$s" class="regular-text">',
			$id,
			$name,
			$options[$id] ?: $default ?? '',
			$placeholder ?? ''
		);

		if ( ! empty( $description ) ) {
			$input_tag .= sprintf(
				'<p class="description" id="%1$s-description">%2$s</p>',
				$id,
				$description
			);
		}

		/**
		 * Allow plugins to directly manipulate field HTML
		 */
		$input_tag = apply_filters( 'wphelper/settings_page/input_text', $input_tag, $field, $this->option_name, $options );

		echo $input_tag;

	}

	/**
	 * Print url input field
	 * Support field type 'url'
	 * 
	 * @since 0.19
	 */
	function print_url( $field ){
		extract($field);

		$options = get_option( $this->option_name );

		$input_tag = sprintf(
			'<input name="%2$s" type="url" id="%1$s" aria-describedby="%1$s-description" placeholder="%4$s" value="%3$s" class="regular-text code ">',
			$id,
			$name,
			$options[$id] ?: $default ?? '',
			$placeholder ?? ''
		);

		if ( ! empty( $description ) ) {
			$input_tag .= sprintf(
				'<p class="description" id="%1$s-description">%2$s</p>',
				$id,
				$description
			);
		}

		/**
		 * Allow plugins to directly manipulate field HTML
		 */
		$input_tag = apply_filters( 'wphelper/settings_page/input_url', $input_tag, $field, $this->option_name, $options );

		echo $input_tag;

	}

	/**
	 * Print email input field
	 * Support field type 'email'
	 * 
	 * @since 0.19
	 */
	function print_email( $field ){
		extract($field);

		$options = get_option( $this->option_name );

		$input_tag = sprintf(
			'<input name="%2$s" type="email" id="%1$s" aria-describedby="%1$s-description" placeholder="%4$s" value="%3$s" class="regular-text ltr">',
			$id,
			$name,
			$options[$id] ?: $default ?? '',
			$placeholder ?? ''
		);

		if ( ! empty( $description ) ) {
			$input_tag .= sprintf(
				'<p class="description" id="%1$s-description">%2$s</p>',
				$id,
				$description
			);
		}

		/**
		 * Allow plugins to directly manipulate field HTML
		 */
		$input_tag = apply_filters( 'wphelper/settings_page/input_email', $input_tag, $field, $this->option_name, $options );

		echo $input_tag;

	}

	/**
	 * Print email input field
	 * Support field type 'email'
	 * 
	 * @since 0.23
	 */
	function print_textarea( $field ){
		extract($field);

		$options = get_option( $this->option_name );

		$textarea = sprintf(
			'<textarea class="regular-text" rows="5" id="%1$s-description" name="%2$s" placeholder="%4$s">%3$s</textarea>',
			$id,
			$name,
			$options[$id] ?: $default ?? '',
			$placeholder ?? ''
		);

		if ( ! empty( $description ) ) {
			$textarea .= sprintf(
				'<p class="description" id="%1$s-description">%2$s</p>',
				$id,
				$description
			);
		}

		/**
		 * Allow plugins to directly manipulate field HTML
		 */
		$textarea = apply_filters( 'wphelper/settings_page/textarea', $textarea, $field, $this->option_name, $options );

		echo $textarea;

	}

	/**
	 * Sanitizes entire $options array.
	 */
	function sanitize_settings( $options ) {
		$new_options = [];

		foreach( $options as $id => $option ) {
			$field = current(
				array_filter(
					$this->fields,
					fn($item) => $item['id'] == $id
				)
			);
			switch ( $field['type'] ) {
				case 'checkbox':			
					$new_options[$id] = $option == 1 ? 1 : 0;
					break;
				case 'text':			
				case 'textarea':			
					$new_options[$id] = sanitize_text_field( $option );
					break;
				case 'email':			
					$new_options[$id] = sanitize_email( $option );
					break;
				case 'url':			
					$new_options[$id] = sanitize_url( $option );
					break;
				default:
					break;
			}
		}

		return $new_options;
	}

	function section_description_cb( $section ) {
		if ( ! empty( $section['description'] ) ) {
			switch ( $section[ 'description_container' ] ?? '' ){
				case 'card':
					$container = '<div class="card">%s</div>';
					break;
				case 'notice':
				case 'notice-info':
					$container = '<div class="notice notice-info inline"><p>%s</p></div>';
					break;
				case 'none':
					$container = '%s';
					break;
				default:
					$container = '<p>%s</p>';
					break;
			}
			return fn() => printf( $container, $section['description'] );
		}
	}
}
endif;