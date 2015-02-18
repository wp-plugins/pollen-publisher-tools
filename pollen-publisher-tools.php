<?php

/*
Plugin Name: Pollen Publisher Tools for WP
Plugin URI: https://bitbucket.org/vnteam/pollen-plugin-for-wordpress/
Description: This plugin will additional widgets from https://getpollen.co.
Version: 0.0.1
Author: Goh Bing Han
Author URI: http://www.binghan.me/
Author Email: binghan.goh@gmail.com
License:

  Copyright 2013-2014 GBinghan (binghan.goh@gmail.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

class PollenPlugin {

	private $options;
	private $version = '0.0.1';
	private $name = 'Pollen Publisher Tools';
	private $menu_slug = 'vm_pollenplugin_menu_slug';
	private $settings_option_id = 'vm_pollenplugin_option_group';
	private $settings_option_name = 'vm_pollenplugin_option_name';

	private $settings_recommendation_page_slug = 'my-setting-admin';

	/**
	 * Start up
	 */
	public function __construct() {

		$this->wpplugin_directory = dirname( __FILE__ ) . '/';
		$this->wplugin_path       = plugin_dir_path( __FILE__ );
		$this->wpplugin_url       = plugin_dir_url( __FILE__ );

		if ( $_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1' ) {
			$this->pollenplugin_environment = 'LOCAL';
			$this->pollenplugin_url         = 'http://getpollen.localhost.com';
		} else {
			$this->pollenplugin_environment = 'LIVE';
			$this->pollenplugin_url         = 'https://getpollen.co';
		}

		if ( ! defined( 'pollenplugin_url' ) ) {
			define( 'pollenplugin_url', $this->pollenplugin_url );
		}

		// Load plugin text domain
		load_plugin_textdomain( 'POLLEN_PLUGIN', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

		add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'page_init' ) );

		/* wait for theme to load, then continue... */
		add_action( 'after_setup_theme', array( $this, '__construct_after_theme' ) );

		// Include Pollen widgets
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'pollenplugin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'pollenplugin_scripts' ) );
	}

	public function __construct_after_theme() {
		add_filter( "the_content", array( $this, "content_add_recommendation_widget" ), 20 );
	}

	public static function getWpPluginUrl() {
		return plugin_dir_url( __FILE__ );
	}

	function pollenplugin_scripts() {
		wp_enqueue_script( 'wppollenplugin-name', $this->pollenplugin_url . '/assets/scripts/widget.min.js', array( 'jquery' ), $this->version, true );
	}

	/**
	 * Add options page
	 */
	public function add_plugin_page() {

		$menu_label = __( 'Pollen Tools', 'POLLEN_PLUGIN' );
		add_menu_page(
			__( 'Dashboard', 'POLLEN_PLUGIN' ),
			__( 'Pollen Tools', 'POLLEN_PLUGIN' ),
			'edit_posts',
			$this->menu_slug,
			array(
				$this,
				'create_pollenplugin_general_page'
			),
			$this->wpplugin_url . 'assets/images/logo-16x16.png'
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Dashboard', 'POLLEN_PLUGIN' ),
			__( 'Dashboard', 'POLLEN_PLUGIN' ),
			'update_core',
			$this->menu_slug,
			array(
				&$this,
				'create_pollenplugin_general_page'
			)
		);

		add_submenu_page(
			$this->menu_slug,
			__( 'Recommendation Widget', 'POLLEN_PLUGIN' ),
			__( 'Recommendation Widget', 'POLLEN_PLUGIN' ),
			'update_core',
			$this->menu_slug . '_recommendation',
			array(
				&$this,
				'create_pollenplugin_settings_page'
			)
		);

	}

	/**
	 * Register and add settings
	 */
	public function page_init() {

		register_setting(
			$this->settings_option_id, // Option group
			$this->settings_option_name, // Option name
			array( $this, 'sanitize' ) // Sanitize
		);

		add_settings_section(
			'setting_section_id', // ID
			'Pollen Recommendation Widget Settings', // Title
			array( $this, 'print_section_info' ), // Callback
			$this->settings_recommendation_page_slug // Page
		);

		add_settings_field(
			'show_recommendation_on_post_type_field', // ID
			'Show Recommendation On', // Title
			array( $this, 'show_recommendation_on_post_type_field_callback' ), // Callback
			$this->settings_recommendation_page_slug, // Page
			'setting_section_id' // Section
		);

		add_settings_field(
			'show_recommendation_on_position_field',
			'Show Recommendation On Position',
			array( $this, 'show_recommendation_on_position_field_callback' ),
			$this->settings_recommendation_page_slug,
			'setting_section_id'
		);

	}


	/**
	 * Sanitize each setting field as needed
	 *
	 * @param array $input Contains all settings fields as array keys
	 */
	public function sanitize( $input ) {
		if ( $input ) {
			foreach ( $input as $key => $val ) {
				if ( isset ( $input[ $key ] ) ) {
					$input[ $key ] = ( strip_tags( stripslashes( $input[ $key ] ) ) );
				} // end if
			} // end foreach
		}

		$allowed_input = [
			[
				'name' => 'show_recommendation_on_post_type_post',
				'type' => 'boolean'
			],
			[
				'name' => 'show_recommendation_on_post_type_page',
				'type' => 'boolean'
			],
			[
				'name' => 'show_recommendation_on_position_top',
				'type' => 'boolean'
			],
			[
				'name' => 'show_recommendation_on_position_bottom',
				'type' => 'boolean'
			],
			[
				'name' => 'publisher_hash',
				'type' => 'string'
			],
			[
				'name' => 'publisher_id',
				'type' => 'integer'
			]
		];

		$new_input = array();
		foreach ( $allowed_input as $field ) {
			switch ( $field['type'] ) {
				case 'boolean':
					if ( isset( $input[ $field['name'] ] ) ) {
						$new_input[ $field['name'] ] = filter_var( $input[ $field['name'] ], FILTER_VALIDATE_BOOLEAN );
					}
					break;
				case 'integer':
					if ( isset( $input[ $field['name'] ] ) ) {
						$new_input[ $field['name'] ] = intval( $input[ $field['name'] ] );
					}
					break;
				case 'string':
				default :
					if ( isset( $input[ $field['name'] ] ) ) {
						$new_input[ $field['name'] ] = $input[ $field['name'] ];
					}
					break;
			}
		}

		return $new_input;
	}

	/**
	 * Print the Section text
	 */
	public function print_section_info() {
//		print 'Enter your settings below:';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function show_recommendation_on_post_type_field_callback() {
		echo '<span style="margin: 10px;">';
		echo '<input type="checkbox" id="show_recommendation_on_post_type_post" name="' . $this->settings_option_name . '[show_recommendation_on_post_type_post]" ' . checked( 1, isset( $this->options['show_recommendation_on_post_type_post'] ) ? $this->options['show_recommendation_on_post_type_post'] : '', false ) . '/>';
		echo '<label>Post</label>';
		echo '</span>';

		echo '<span style="margin: 10px;">';
		echo '<input type="checkbox" id="show_recommendation_on_post_type_page" name="' . $this->settings_option_name . '[show_recommendation_on_post_type_page]" ' . checked( 1, isset( $this->options['show_recommendation_on_post_type_page'] ) ? $this->options['show_recommendation_on_post_type_page'] : '', false ) . '/>';
		echo '<label>Page</label>';
		echo '</span>';
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function show_recommendation_on_position_field_callback() {
		echo '<span style="margin: 10px;">';
		echo '<input type="checkbox" id="show_recommendation_on_position_top" name="' . $this->settings_option_name . '[show_recommendation_on_position_top]" ' . checked( 1, isset( $this->options['show_recommendation_on_position_top'] ) ? $this->options['show_recommendation_on_position_top'] : '', false ) . '/>';
		echo '<label>Top</label>';
		echo '</span>';

		echo '<span style="margin: 10px;">';
		echo '<input type="checkbox" id="show_recommendation_on_position_bottom" name="' . $this->settings_option_name . '[show_recommendation_on_position_bottom]" ' . checked( 1, isset( $this->options['show_recommendation_on_position_bottom'] ) ? $this->options['show_recommendation_on_position_bottom'] : '', false ) . '/>';
		echo '<label>Bottom</label>';
		echo '</span>';
	}

	public function create_pollenplugin_general_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$this->options = get_option( $this->settings_option_name );
		include( dirname( __FILE__ ) . '/pages/dashboard.php' );
	}

	public function create_pollenplugin_settings_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$this->options = get_option( $this->settings_option_name );
		include( dirname( __FILE__ ) . '/pages/recommendation.php' );
	}

	public function content_add_recommendation_widget( $content ) {
		global $post;

		$this->options = get_option( $this->settings_option_name );

		$show = false;

		if ( isset( $this->options['show_recommendation_on_post_type_post'] ) && $this->options['show_recommendation_on_post_type_post'] == true && $post->post_type === 'post' ) {
			$show = true;
		} else if ( isset( $this->options['show_recommendation_on_post_type_page'] ) && $this->options['show_recommendation_on_post_type_page'] == true && $post->post_type === 'page' ) {
			$show = true;
		}

		if ( ! $show ) {
			return $content;
		}

		if ( isset( $this->options['show_recommendation_on_position_top'] ) && $this->options['show_recommendation_on_position_top'] == true ) {
			$content = '<iframe class="pollen-pending-init" id="vplgn_recommendation_0_' . rand() . '" frameborder="0" scrolling="no" style="width: 100%; height: 200px; opacity: 1; overflow: hidden;"  data-widget-id="0"  data-widget-type="recommendation" data-widget-base-url="' . $this->pollenplugin_url . '"></iframe>' . PHP_EOL . PHP_EOL . $content;
		}
		if ( isset( $this->options['show_recommendation_on_position_bottom'] ) && $this->options['show_recommendation_on_position_bottom'] == true ) {
			$content = $content . PHP_EOL . PHP_EOL . '<iframe class="pollen-pending-init" id="vplgn_recommendation_0_' . rand() . '" frameborder="0" scrolling="no" style="width: 100%; height: 200px; opacity: 1; overflow: hidden;"  data-widget-id="0"  data-widget-type="recommendation" data-widget-base-url="' . $this->pollenplugin_url . '"></iframe>';
		}

		return $content;
	}


	/**
	 * Register available widgets.
	 *
	 * @since 1.1
	 *
	 * @uses register_widget()
	 * @return void
	 */
	public function widgets_init() {
		$widget_directory = $this->wplugin_path . 'modules/widgets/';

		$widgets = array(
			'pollen-widget' => 'Pollen_Widget'
		);

		foreach ( $widgets as $filename => $classname ) {
			if ( class_exists( $classname ) ) {
				register_widget( $classname );
			} else {
				$file = $widget_directory . $filename . '.php';
				if ( file_exists( $file ) ) {
					include_once( $file );
					if ( class_exists( $classname ) ) {
						register_widget( $classname );
					}
				}
				unset( $file );
			}
		}
	}
}


function pollen_plugin_init() {
	global $pollen_plugin;
	$pollen_plugin = new PollenPlugin();
}

add_action( 'plugins_loaded', 'pollen_plugin_init', 15 );

//if ( is_admin() ) {
//	$my_settings_page = new PollenPlugin();
//}