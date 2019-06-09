<?php
/*
Plugin Name: plugin_name
Plugin URI: plugin_uri
Description: plugin_desc
Version: 1.0.0
Author: author_name
Author URI: author_uri
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
    Copyright current_year by author_name <author_email>
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.
    You may NOT assume that you can use any other version of the GPL.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html
*/


class Class_Name {

	private $textdomain = "text_domain";
	private $required_plugins = array();
	public static $_instance;

	function have_required_plugins() {
		if (empty($this->required_plugins))
			return true;
		$active_plugins = (array) get_option('active_plugins', array());
		if (is_multisite()) {
			$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
		}
		foreach ($this->required_plugins as $key => $required) {
			$required = (!is_numeric($key)) ? "{$key}/{$required}.php" : "{$required}/{$required}.php";
			if (!in_array($required, $active_plugins) && !array_key_exists($required, $active_plugins))
				return false;
			}
		return true;
	}

	function __construct() {
		if (!$this->have_required_plugins())
			return;
		load_plugin_textdomain($this->textdomain, false, dirname(plugin_basename(__FILE__)) . '/languages');
		spl_autoload_register(array($this,'autoload'));
       		$this->init();
	}

	private function init() {

		//add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ));

	}

	//Loads on wp_enque_scripts hook, uncomment to use
	private function load_assets() {
		
		//wp_enqueue_style( 'plugin_slug-main-style', plugin_dir_url( __FILE__ ) . 'css/main.css', array(), '1.0' ); 
		//wp_enqueue_script( 'plugin_slug-main-script', plugin_dir_url( __FILE__ ) . 'js/main.js', array(), '0.1' ); 
	}
	
	/* Autoload Classes */
   	function autoload($class) {
       		$class = strtolower(str_replace("_","-",$class));
       		$class_file = untrailingslashit(plugin_dir_path(__FILE__)) ."/includes/class-{$class}.php";
       		if (file_exists($class_file)) {
           		require_once($class_file);
       		}
   	}

	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}


if ( ! function_exists( 'SHORT' ) ) {
	/**
 	* Use this function to call up the class from anywhere like SHORT()->class_method();
 	*
 	* @param: None
 	* @return: Instance of Class_Name
 	*/
	function SHORT() {
		return Class_Name::instance();
	}

	SHORT();
}
