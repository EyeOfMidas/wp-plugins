<?php
/**
 *
 * @package ContentLinks
 * @version 1.0
 */
/*
 * Plugin Name: Content Links
* Plugin URI: http://eyeofmidas.com/wp-plugins
* Description: This plugin will allow you to put a "relevantlinks" shortcode on your page that will display links.
* Author: Justin Gilman
* Version: 1.0
* Author URI: http://eyeofmidas.com
*/
class ContentLinksPlugin {
	private $walker;
	public function __construct() {
		add_action('init', array(
				$this,
				'init' 
		));
	}
	public function init() {
		add_filter('pre_option_link_manager_enabled', '__return_true');
		add_shortcode('relatedlinks', array(
				$this,
				'handleShortcode' 
		));
		
		add_filter("plugin_row_meta", array(
				$this,
				"pluginRowMeta" 
		), 10, 2);
	}
	public function pluginRowMeta($links, $file) {
		$pname = plugin_basename(__FILE__);
		if ($pname === $file) {
			//TODO: something custom here
		}
		return $links;
	}
	public function handleShortcode($attributes, $defaultHtml) {
		global $post;
		$category = isset($attributes['category']) ? $attributes['category'] : $post->post_name;
		
		$args = array(
				'limit' => 4,
				'category_name' => $category,
				'hide_invisible' => 1,
				'show_updated' => 0 
		);
		$bookmarks = get_bookmarks($args);
		$bookmarkView = "";
		
		if ($bookmarks) {
			ob_start();
			include ("contentlinks-template.php");
			$bookmarkView = ob_get_clean();
		}
		return $bookmarkView;
	}
}

$contentLinks = new ContentLinksPlugin();

