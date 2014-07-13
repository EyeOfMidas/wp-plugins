<?php

/*
Plugin Name: Child Page Summary
Description: Adds a shortcode for the page to display summaries of the current page's children.
Plugin URI: http://eyeofmidas.com/wp-plugins
Version: 1.0
Author: Justin Gilman
Author URI: http://eyeofmidas.com
*/
class ChildPageSummaryPlugin {
	private $walker;
	public function __construct() {
		add_action('init', array(
				$this,
				'init' 
		));
		add_shortcode('childpagesummary', array(
				$this,
				'handleShortcode' 
		));
		// 		add_action("wp_enqueue_scripts", array(
		// 				&$this,
		// 				"attachScripts" 
		// 		));
		

		add_filter("plugin_row_meta", array(
				$this,
				"pluginRowMeta" 
		), 10, 2);
	}
	public function init() {
		$this->walker = new WalkerSummary();
	}
	public function attachScripts() {
	}
	public function pluginRowMeta($links, $file) {
		$pname = plugin_basename(__FILE__);
		if ($pname === $file) {
			//TODO: something custom here
		}
		return $links;
	}
	private function getChildrenOf($parentId) {
		$defaults = array(
				'depth' => 1,
				'show_date' => '',
				'date_format' => get_option('date_format'),
				'child_of' => $parentId,
				'exclude' => '',
				'title_li' => __('Pages'),
				'echo' => 1,
				'authors' => '',
				'sort_column' => 'menu_order, post_title',
				'link_before' => '',
				'link_after' => '',
				'walker' => $this->walker 
		);
		
		$r = $defaults;
		extract($r, EXTR_SKIP);
		
		$current_page = 0;
		
		$r['exclude'] = preg_replace('/[^0-9,]/', '', $r['exclude']);
		$exclude_array = ($r['exclude']) ? explode(',', $r['exclude']) : array();
		
		$r['exclude'] = implode(',', apply_filters('wp_list_pages_excludes', $exclude_array));
		
		$r['hierarchical'] = 0;
		$pages = get_pages($r);
		
		if (!empty($pages)) {
			global $wp_query;
			if (is_page() || is_attachment() || $wp_query->is_posts_page) {
				$current_page = get_queried_object_id();
			} elseif (is_singular()) {
				$queried_object = get_queried_object();
				if (is_post_type_hierarchical($queried_object->post_type)) {
					$current_page = $queried_object->ID;
				}
			}
			
			$childrenPages = walk_page_tree($pages, $r['depth'], $current_page, $r);
		}
		
		return $childrenPages;
	}
	public function handleShortcode($attributes, $defaultHtml) {
		global $post;
		$children = $this->getChildrenOf($post->ID);
		$childrenView = "";
		if ($children) {
			ob_start();
			include ("childpagesummary-template.php");
			$childrenView = ob_get_clean();
		}
		return $childrenView;
	}
}
class WalkerSummary extends Walker {
	var $tree_type = 'page';
	var $db_fields = array(
			'parent' => 'post_parent',
			'id' => 'ID' 
	);
	function start_el(&$output, $page, $depth = 0, $args = array(), $current_page = 0) {
		extract($args, EXTR_SKIP);
		
		if (isset($args['pages_with_children'][$page->ID]))
			
			if (!empty($current_page)) {
				$_current_page = get_post($current_page);
			}
		
		if ('' === $page->post_title) {
			$page->post_title = sprintf(__('#%d (no title)'), $page->ID);
		}
		
		$output[] = array(
				"permalink" => get_permalink($page->ID),
				"name" => $page->post_name,
				"title" => $page->post_title,
				"date" => $page->post_date,
				"content" => $page->post_content,
				"summary" => $this->buildSummary($page->post_content, 400, 500) 
		);
	}
	private function buildSummary($rawString, $desiredLength, $maxLength) {
		$finishedSummary = "";
		$simpleString = strip_tags($this->strip_tags_content($rawString));
		
		$sentences = explode(".", $simpleString);
		foreach($sentences as $sentence) {
			if (strlen($sentence) + strlen($finishedSummary) > $desiredLength) {
				$finishedSummary .= $sentence . ". ";
				return $finishedSummary;
			} else if (strlen($sentence) + strlen($finishedSummary) < $maxLength) {
				$finishedSummary .= $sentence . ". ";
			} else {
				return $finishedSummary;
			}
		}
		return $finishedSummary;
	}
	private function strip_tags_content($text, $tags = '', $invert = true) {
		preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
		$tags = array_unique($tags[1]);
		
		if (is_array($tags) and count($tags) > 0) {
			if ($invert == FALSE) {
				return preg_replace('@<(?!(?:' . implode('|', $tags) . ')\b)(\w+)\b.*?>.*?</\1>@si', '', $text);
			} else {
				return preg_replace('@<(' . implode('|', $tags) . ')\b.*?>.*?</\1>@si', '', $text);
			}
		} elseif ($invert == FALSE) {
			return preg_replace('@<(\w+)\b.*?>.*?</\1>@si', '', $text);
		}
		return $text;
	}
}

$subpageSummary = new ChildPageSummaryPlugin();

