<?php
/*
Plugin Name: SearchWP Autocomplete
Description: Enable Autocomplete for terms stored in SearchWP Database
Version: 0.1.0
Author: EneMarine
Author URI: https://github.com/EneMarine

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/>.
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'SEARCHWP_AUTOCOMPLETE_VERSION' ) ) {
	define( 'SEARCHWP_AUTOCOMPLETE_VERSION', '0.1.0' );
}

/**
 * instantiate the updater
 */
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
 	'https://github.com/EneMarine/SearchWP-Autocomplete',
 	__FILE__,
 	'searchwp-autocomplete'
 );
 //Optional: Set the branch that contains the stable release.
$myUpdateChecker->setBranch('master');


class SearchWPAutocomplete {

	// required for all SearchWP extensions
	public $public                = true;               // should be shown in Extensions menu on SearchWP Settings screen
	public $slug                  = 'autocomplete';    // slug used for settings screen(s)
	public $name                  = 'Autocomplete';    // name used in various places
	public $min_searchwp_version  = '2.4.10';           // used in min version check

	// unique to this extension
	private $url;
	private $version    = SEARCHWP_AUTOCOMPLETE_VERSION;
	private $settings;

	/**
	 * SearchWPAutocomplete constructor.
	 */
	function __construct() {
		$this->url      = plugins_url( 'searchwp-autocomplete' );
		$this->settings = get_option( $this->prefix . 'settings' );

		add_action('wp_enqueue_scripts', array( $this, 'enqueue_plugin_javacript') );
		add_action('wp_ajax_nopriv_swp/autocomplete/get_terms', array( $this, 'get_swp_terms' ) );
		add_action('wp_ajax_swp/autocomplete/get_terms', array( $this, 'get_swp_terms' ) );
	}

	function enqueue_plugin_javacript(){
		wp_enqueue_script('autocomplete', $this->url.'/assets/js/jquery.auto-complete.min.js', array('jquery'), $version, true);
		wp_enqueue_script('swp-autocomplete', $this->url.'/assets/js/searchwp-autocomplete.js', array('jquery', 'autocomplete'), $version, true);
		wp_enqueue_style('autocomplete', $this->url.'/assets/css/jquery.auto-complete.css', array(), $version);
		wp_enqueue_style('swp-autocomplete', $this->url.'/assets/css/searchwp-autocomplete.css', array(), $version);
	}

	function get_swp_terms(){
		global $wpdb; //get access to the WordPress database object variable

		//get names of all businesses
		$name = $wpdb->esc_like(stripslashes($_POST['name'])).'%'; //escape for use in LIKE statement
		$prefix = $wpdb->prefix . SEARCHWP_DBPREFIX;
		$sql = "
			SELECT DISTINCT term
			FROM {$prefix}terms
			WHERE term like %s
		";

		$sql = $wpdb->prepare($sql, $name);

		$results = $wpdb->get_results($sql);

		//copy the business titles to a simple array
		$terms = array();
		foreach( $results as $r ){
			$terms[] = addslashes($r->term);
		}

		echo json_encode($terms); //encode into JSON format and output

		die(); //stop "0" from being output
	}

}

new SearchWPAutocomplete();
