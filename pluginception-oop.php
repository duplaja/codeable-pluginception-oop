<?php
/*
Plugin Name: Pluginception - OOP Version
Plugin URI: https://dandulaney.com
Description: A plugin to create other plugins, in a simple OOP fashion. Pluginception.
Version: 1.0
Author: Dan Dulaney (forked from a plugin by Otto)
Author URI: https://dandulaney.com
Text Domain: pluginception_oop
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html


    Copyright 2011-2013  Samuel Wood  (email : otto@ottodestruct.com)
    Copyright 2019 by Dan Dulaney <dan.dulaney07@gmail.com>

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

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
define("AUTHOR_NAME", "Dan Dulaney");
define("AUTHOR_EMAIL", "dan.dulaney07@gmail.com");
define("AUTHOR_SITE", "https://dandulaney.com");
define("PLUGIN_SITE", "https://codeable.io/developers/dan-dulaney/");


add_action('admin_menu', 'pluginception_oop_admin_add_page');
function pluginception_oop_admin_add_page() {
	add_plugins_page(
		'Create Plugin - OOP',
		'Create Plugin - OOP',
		'edit_plugins',
		'pluginception_oop',
		'pluginception_oop_options_page'
	);
}

function pluginception_oop_options_page() {
	$results = pluginception_oop_create_plugin();

	if ( $results === true ) return;
	
	echo '<div class="wrap">
		
		<h2>Create a New OOP Plugin</h2>';
		settings_errors();
		echo '<form method="post" action="">';
		wp_nonce_field('pluginception_oop_nonce');
		echo '<table class="form-table">';
		$opts = array(
			'name' => 'Plugin Name',
			'slug' => 'Plugin Slug (optional)',
			'uri' => 'Plugin URI (optional)',
			'description' => 'Description (optional)',
			'author' => 'Author (optional)',
			'author_uri' => 'Author URI (optional)',
			'author_email'=> 'Author Email (optional)',
			'text_domain'=> 'Text Domain for Translations (optional)',
			'class_name'=> 'Main Class_Name (in given format, optional)',
			'short_name'=> 'Short abbreviation for calling class (string)'
			
		);

		foreach ($opts as $slug=>$title) {
			$value = '';
			if (!empty($results['pluginception_oop_'.$slug])) $value = esc_attr($results['pluginception_oop_'.$slug]);
			
			switch($slug) {
				case 'uri':
					if(empty($value)) $value= PLUGIN_SITE;
					break;
				case 'author':
					if(empty($value)) $value = AUTHOR_NAME;
					break;
				case 'author_uri':
					if(empty($value)) $value = AUTHOR_SITE;
					break;
				case 'author_email':
					if(empty($value)) $value = AUTHOR_EMAIL;
					break;

			}
			
			
			echo "<tr valign='top'><th scope='row'>{$title}</th><td><input class='regular-text' type='text' name='" . esc_attr("pluginception_oop_{$slug}") . "' value='{$value}'></td></tr>\n";
		}

		echo "<tr valign='top'><th scope='row'>Add CSS / JS?</th><td><select class='regular-text' name='pluginception_oop_subdirs'>
		<option value='neither'>Neither</option>
		<option value='both'>Both</option>
		<option value='css'>Just CSS</option>
		<option value='js'>Just JS</option>
		</select></td></tr>\n";		

		echo '</table>';
		submit_button( 'Create a blank OOP plugin and activate it!' );
		echo '</form>
	</div>';
}


function pluginception_oop_create_plugin() {
	if ( 'POST' != $_SERVER['REQUEST_METHOD'] )
		return false;

	check_admin_referer('pluginception_oop_nonce');

	// remove the magic quotes
	$_POST = stripslashes_deep( $_POST );

	if (empty($_POST['pluginception_oop_name'])) {
		add_settings_error( 'pluginception_oop', 'required_name','Plugin Name is required', 'error' );
		return $_POST;
	}

	if ( empty($_POST['pluginception_oop_slug'] ) ) {
		$_POST['pluginception_oop_slug'] = sanitize_title($_POST['pluginception_oop_name']);
	} else {
		$_POST['pluginception_oop_slug'] = sanitize_title($_POST['pluginception_oop_slug']);
	}
	
	if( empty($_POST['pluginception_oop_class_name'])) {
		$_POST['pluginception_oop_class_name'] = str_replace(' ','_',preg_replace('/[^a-z\d ]/i', '', $_POST['pluginception_oop_name']));
	} else {
		$_POST['pluginception_oop_class_name'] = str_replace(' ','_',preg_replace('/[^a-z\d ]/i', '', $_POST['pluginception_oop_class_name']));	
	}

	if( empty($_POST['pluginception_oop_short_name'])) {

		$words = explode(" ","{$_POST['pluginception_oop_name']}");
		$acronym = "";

		if(count($words) > 2) {
			foreach ($words as $w) {
			  	$acronym .= $w[0];
			}
		}
		else {

			$acronym .= substr(str_replace(' ','',$_POST['pluginception_oop_name']), 0, 4);
		}

		$acronym =strtoupper($acronym);

		$_POST['pluginception_oop_short_name'] = $acronym;

	} else {
		$_POST['pluginception_oop_short_name'] = strtoupper(str_replace(' ','_',preg_replace('/[^a-z\d ]/i', '', $_POST['pluginception_oop_short_name'])));
	}

	if ( file_exists(trailingslashit(WP_PLUGIN_DIR).$_POST['pluginception_oop_slug'] ) ) {
		add_settings_error( 'pluginception_oop', 'existing_plugin', 'That plugin appears to already exist. Use a different slug or name.', 'error' );
		return $_POST;
	}

	$form_fields = array ('pluginception_oop_name', 'pluginception_oop_slug', 'pluginception_oop_uri', 'pluginception_oop_description','pluginception_oop_author', 'pluginception_oop_author_uri','pluginception_oop_author_email','pluginception_oop_text_domain','pluginception_oop_class_name','pluginception_oop_short_name');
		
	
	$method = ''; // TODO TESTING

	// okay, let's see about getting credentials
	$url = wp_nonce_url('plugins.php?page=pluginception_oop','pluginception_oop_nonce');
	if (false === ($creds = request_filesystem_credentials($url, $method, false, false, $form_fields) ) ) {
		return true;
	}

	// now we have some credentials, try to get the wp_filesystem running
	if ( ! WP_Filesystem($creds) ) {
		// our credentials were no good, ask the user for them again
		request_filesystem_credentials($url, $method, true, false, $form_fields);
		return true;
	}

	global $wp_filesystem;

	// create the plugin directory
	$plugdir = $wp_filesystem->wp_plugins_dir() . $_POST['pluginception_oop_slug'];

	$cssdir = $plugdir.'/css';
	$jsdir = $plugdir.'/js';

	if ( ! $wp_filesystem->mkdir($plugdir) ) {
		add_settings_error( 'pluginception_oop', 'create_directory', 'Unable to create the plugin directory.', 'error' );
		return $_POST;
	}

	if('both' == $_POST['pluginception_oop_subdirs'] || 'css' == $_POST['pluginception_oop_subdirs']) {

		if ( ! $wp_filesystem->mkdir($cssdir) ) {
			add_settings_error( 'pluginception_oop', 'create_directory', 'Unable to create the CSS subdirectory.', 'error' );
			return $_POST;
		}
	}

	if('both' == $_POST['pluginception_oop_subdirs'] || 'js' == $_POST['pluginception_oop_subdirs']) {
		if ( ! $wp_filesystem->mkdir($jsdir) ) {
			add_settings_error( 'pluginception_oop', 'create_directory', 'Unable to create the JS subdirectory.', 'error' );
			return $_POST;
		}
	}

	// create the plugin main file header

	$curyear = date('Y');
	$plugfile = trailingslashit($plugdir).$_POST['pluginception_oop_slug'].'.php';
	$blankplugfile = trailingslashit($plugdir).'index.php';
	$cssfile = trailingslashit($plugdir).'/css/main.css';
	$jsfile = trailingslashit($plugdir).'/js/main.js';
	$blankjsindex = trailingslashit($plugdir).'/js/index.php';
	$blankcssindex = trailingslashit($plugdir).'/css/index.php';

	$template_file = plugin_dir_url( __FILE__ ).'wordpress-plugin-oop-boilerplate.php.template';

	$main_plugin_file = file_get_contents($template_file);

	if(!$main_plugin_file) {
		add_settings_error( 'pluginception_oop', 'create_file', 'Unable to open template file.', 'error' );
		return $_POST;
	} else {

		$to_replace = array(
			'plugin_name'=>"{$_POST['pluginception_oop_name']}",
			'plugin_uri'=>"{$_POST['pluginception_oop_uri']}",
			'plugin_desc'=>"{$_POST['pluginception_oop_description']}",
			'plugin_slug'=>"{$_POST['pluginception_oop_slug']}",
			'author_name'=>"{$_POST['pluginception_oop_author']}",
			'author_uri'=>"{$_POST['pluginception_oop_author_uri']}",
			'author_email'=>"{$_POST['pluginception_oop_author_email']}",
			'text_domain'=>"{$_POST['pluginception_oop_text_domain']}",
			'current_year'=>"$curyear",
			'Class_Name'=>"{$_POST['pluginception_oop_class_name']}",
			'SHORT'=>"{$_POST['pluginception_oop_short_name']}"
		);

		foreach($to_replace as $key => $value) {

			$main_plugin_file = str_replace("$key","$value",$main_plugin_file);
		}

		
	}

//Header templates for blank files if needed
$index_header = <<<END
<?php
if ( ! defined( 'ABSPATH' ) ) { 
	exit; // Exit if accessed directly
}
END;

$css_file_header = <<<END
/* {$_POST['pluginception_oop_name']} - CSS */	
END;

$js_file_header = <<<END
/* {$_POST['pluginception_oop_name']} - JS */	
END;


	//Handles CSS Files
	if ('both' == $_POST['pluginception_oop_subdirs'] || 'css' == $_POST['pluginception_oop_subdirs']) {

		$main_plugin_file = str_replace("//add_action( 'wp_enqueue_scripts'","add_action( 'wp_enqueue_scripts'",$main_plugin_file);
		$main_plugin_file = str_replace("//wp_enqueue_style","wp_enqueue_style",$main_plugin_file);

		if ( ! $wp_filesystem->put_contents( $cssfile, $css_file_header, FS_CHMOD_FILE) ) {
			add_settings_error( 'pluginception_oop', 'create_file', 'Unable to create the plugin blank index file.', 'error' );
		}
		
		if ( ! $wp_filesystem->put_contents( $blankcssindex, $index_header, FS_CHMOD_FILE) ) {
			add_settings_error( 'pluginception_oop', 'create_file', 'Unable to create the plugin blank index file in css subfolder.', 'error' );
		}

	} 

	//Handle JS Files
	if ('both' == $_POST['pluginception_oop_subdirs'] || 'js' == $_POST['pluginception_oop_subdirs']) {

		$main_plugin_file = str_replace("//add_action( 'wp_enqueue_scripts'","add_action( 'wp_enqueue_scripts'",$main_plugin_file);
		$main_plugin_file = str_replace("//wp_enqueue_script","wp_enqueue_style",$main_plugin_file);

		if ( ! $wp_filesystem->put_contents( $jsfile, $js_file_header, FS_CHMOD_FILE) ) {
			add_settings_error( 'pluginception_oop', 'create_file', 'Unable to create the plugin blank index file.', 'error' );
		}
		if ( ! $wp_filesystem->put_contents( $blankjsindex, $index_header, FS_CHMOD_FILE) ) {
			add_settings_error( 'pluginception_oop', 'create_file', 'Unable to create the plugin blank index file in js subfolder.', 'error' );
		}
	}

	//Handles Main Plugin File
	if ( ! $wp_filesystem->put_contents( $plugfile, $main_plugin_file, FS_CHMOD_FILE) ) {
		add_settings_error( 'pluginception_oop', 'create_file', 'Unable to create the plugin file.', 'error' );
	}

	//Handles a blank index
	if ( ! $wp_filesystem->put_contents( $blankplugfile, $index_header, FS_CHMOD_FILE) ) {
		add_settings_error( 'pluginception_oop', 'create_file', 'Unable to create the plugin blank index file.', 'error' );
	}

	$plugslug = $_POST['pluginception_oop_slug'].'/'.$_POST['pluginception_oop_slug'].'.php';
	$plugeditor = admin_url('plugin-editor.php?file='.$_POST['pluginception_oop_slug'].'%2F'.$_POST['pluginception_oop_slug'].'.php');

	if ( null !== activate_plugin( $plugslug, '', false, true ) ) {
		add_settings_error( 'pluginception_oop', 'activate_plugin', 'Unable to activate the new plugin.', 'error' );
	}

	// plugin created and activated, redirect to the plugin editor
	?>
	<script type="text/javascript">
	<!--
	window.location = "<?php echo esc_url_raw( $plugeditor ); ?>"
	//-->
	</script>
	<?php

	/* translators: inline link to plugin editor */
	$message = '<a href="'.$plugeditor.'">The new plugin has been created and activated. You can go to the editor if your browser does not redirect you.</a>';

	add_settings_error('pluginception_oop', 'plugin_active', $message, 'pluginception_oop', 'updated');

	return true;
}