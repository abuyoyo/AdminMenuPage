<?php
/**
 * SettingsPage
 * 
 * Helper class
 * Create WordPress Setting page.
 * 
 * @author  abuyoyo
 */
namespace WPHelper;

use function register_setting;
use function add_settings_section;
use function add_settings_field;
use function checked;
use function get_option;

if ( ! class_exists( 'WPHelper\SettingsPage' ) ):
class SettingsPage{
	
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
     * Constructor.
     *
     * @param array $options
     */
    public function __construct($page_slug,$settings)
    {
		$this->page = $page_slug;

		if ( ! empty($settings['option_name']) )
			$this->option_name = $settings['option_name'];
		else
			$this->option_name = str_replace( '-', '_' , strtolower( $this->page ) );

		if ( ! empty($settings['option_group']) )
			$this->option_group = $settings['option_group'];
		else
			$this->option_group = $this->page . '_option_group';
			
		foreach ($settings['sections'] as $section){
			// extract fields
			foreach ($section['fields'] as $field){
				$field['section_id'] = $section['id'];
				$this->fields[] = $field;
			}
			unset($section['fields']);
			$this->sections[] = $section; // save without fields
		}
	}

	function setup(){
		add_action( 'admin_init', [$this,'register_settings'] );
	}


	public function register_settings(){
		register_setting(
			$this->option_group, // $option_group - A settings group name. Must exist prior to the register_setting call. This must match the group name in settings_fields()
			$this->option_name, // $option_name - The name of an option to sanitize and save.
			[$this,'sanitize_settings'] // $sanitize_callback - A callback function that sanitizes the option's value. (see also: built-in php callbacks)
		);

		foreach ($this->sections as $section){
			add_settings_section(
				$section['id'], // $id - Slug-name to identify the section. Used in the 'id' attribute of tags.
				$section['title'], // $title - Formatted title of the section. Shown as the heading for the section.
				$this->section_description_cb($section), // $callback - Function that echos out any content at the top of the section (between heading and fields).
				$this->page // $page - The slug-name of the settings page on which to show the section.
											//Built-in pages include 'general', 'reading', 'writing', 'discussion', 'media', etc.
			);
		}

		foreach ($this->fields as $field){
			add_settings_field(
				$field['id'],
				$field['title'],
				[$this, "print_{$field['type']}"],
				$this->page, // can built-in pages: (general, reading, writing, ...)
				$field['section_id'],
				$field //send setting array as $args for print function
			);
		}
		
	}

	function print_checkbox( $field ){
		extract($field);
		
		$options = get_option( $this->option_name );
		
		$input_tag = sprintf(
			'<label for="%1$s">
				<input name="%2$s[%1$s]" type="checkbox" id="%1$s" value="1"  %4$s />
			%3$s</label>',
			$id,
			$this->option_name,
			$description,
			checked( $options[$id], '1', false)
		);

		$input_tag = apply_filters( 'wphelper/settings_page/input_checkbox', $input_tag, $field, $this->option_name, $options );

		echo $input_tag;
		
	}

	/**
	 * Sanitizes entire $options array.
	 * WordPress is horrible.
	 * 
	 * 
	 */
	function sanitize_settings($options){
		$new_options = [];

		foreach($options as $id => $option){
			$field = reset(
				array_filter(
					$this->fields,
					function($item)use($id){
						return $item['id'] == $id;
					}
				)
			);
			switch ($field['type']){
				case 'checkbox':			
					$new_options[$id] = $option == 1 ? 1 : 0;
					break;
				default:
					break;
			}
		}

		return $new_options;
	}

	function section_description_cb($section){
		if (! empty($section['description'])){
			return function() use ($section){
				echo "<p>{$section['description']}</p>";
			};
		}
	}
}
endif;