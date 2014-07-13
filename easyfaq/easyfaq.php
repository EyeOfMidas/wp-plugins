<?php
/*
* Plugin Name: Easy FAQ
* Plugin URI: http://eyeofmidas.com/wp-plugins
* Description: Simple shortcode that allows you to add easy-to-read FAQs into content, but still have complex styling.
* Version: 1.0
* Author: Justin Gilman
* Author URI: http://eyeofmidas.com
*/
class EasyFaqPlugin {
	private static $count = 0;
	
	public function handle_shortcode($attributes, $content) {
		$question = $attributes['question'];
		$answer = $content;
		$uniqueId = ++EasyFaqPlugin::$count;
		ob_start();
		include ("easyfaq-template.php");
		$faqView = ob_get_clean();
		return $faqView;
	}
}

$easyfaq = new EasyFaqPlugin();
add_shortcode("faq", array(
		$easyfaq,
		"handle_shortcode" 
));