<?php

/**
 *
 * @package CustomPostTarget
 * @version 1.0
 */
/*
 * Plugin Name: Custom Post Target
 * Plugin URI: http://eyeofmidas.com/wp-plugins
 * Description: This plugin catches gravity forms post actions for custom form actions. This requires the Gravity Forms plugin.
 * Author: Justin Gilman
 * Version: 1.0
 * Author URI: http://eyeofmidas.com
 */
//TODO: check if gravity forms plugin is enabled
include_once (WP_PLUGIN_DIR . "/" . 'gravityforms' . "/common.php");
include_once (WP_PLUGIN_DIR . "/" . 'gravityforms' . "/forms_model.php");
include_once (WP_PLUGIN_DIR . "/" . 'gravityforms' . "/widget.php");
class CustomPostTarget {
	private static $instance;
	public static function getInstance() {
		if (!$instance) {
			$instance = new CustomPostTarget();
		}
		return $instance;
	}
	public function init() {
		add_action('gform_after_submission', array(
				$this,
				'post_to_third_party' 
		), 10, 2);
	}
	public function post_to_third_party($entry, $form) {
		$formData = array();
		foreach($entry as $key => $value) {
			if (is_numeric($key)) {
				$label = $this->getLabel($entry['form_id'], $key);
			} else {
				$label = $key;
			}
			if ($label) {
				$formData[$label] = $value;
			}
		}
		
		if (file_exists(dirname(__FILE__) . '/targets')) {
			$allFiles = scandir(dirname(__FILE__) . '/targets');
		} else {
			//mkdir(dirname(__FILE__) . '/targets', 0775);
			$allFiles = array();
		}
		$validTargets = array();
		foreach($allFiles as $file) {
			if (preg_match("/^target-.+\.php$/", $file)) {
				$validTargets[] = $file;
			}
		}
		foreach($validTargets as $file) {
			include ("targets/" . $file);
		}
	}
	function getValidLabels() {
		return array(
				'First' => 'first',
				'Last' => "last",
				'Street Address' => "street_address",
				'Address Line 2' => "address_line_2",
				'City' => "city",
				'ZIP / Postal Code' => 'zip',
				'Country' => 'country',
				'State / Province' => 'state' 
		);
	}
	private function isValidLabel($label) {
		return in_array($label, array_keys($this->getValidLabels()));
	}
	private function labelTranslator($label) {
		$validLabels = $this->getValidLabels();
		return $validLabels[$label];
	}
	private function getSubLabels($key, $field) {
		foreach($field["inputs"] as $input) {
			if ($input['id'] == $key) {
				if (!$input['adminLabel']) {
					if ($this->isValidLabel($input['label'])) {
						return $field['adminLabel'] . "_" . $this->labelTranslator($input['label']);
					}
					return $field['adminLabel'];
				}
				return $input['adminLabel'];
			}
		}
	}
	private function getLabel($formId, $key) {
		$formMeta = GFFormsModel::get_form_meta($formId);
		$field = GFFormsModel::get_field($formMeta, $key);
		$label = $field['adminLabel'];
		if (is_array(rgar($field, "inputs"))) {
			$label = $this->getSubLabels($key, $field);
		}
		return $label;
	}
	public static function arrayToXML($arrayData, $parentNode) {
		$domDocument = new DOMDocument('1.0');
		$domElement = new DOMElement($parentNode);
		$domDocument->appendChild($domElement);
		CustomPostTarget::buildElement($arrayData, $domElement, $domDocument);
		
		return $domDocument->saveXML($domDocument, LIBXML_NOEMPTYTAG);
	}
	private static function buildElement($arrayData, &$parentElement, $domDocument) {
		foreach($arrayData as $key => $value) {
			if (is_array($value)) {
				$subnode = $domDocument->createElement("$key");
				$parentElement->appendChild($subnode);
				CustomPostTarget::buildElement($value, $subnode, $domDocument);
			} else {
				$subnode = $domDocument->createElement("$key", htmlspecialchars("$value"));
				$parentElement->appendChild($subnode);
			}
		}
	}
	public static function postAsXML($xml, $url) {
		$ch = curl_init($url);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/xml' 
		));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		$response = curl_exec($ch);
		curl_close($ch);
		
		return $response;
	}
	public static function getStateCode($stateString) {
		$states = array(
				'alabama' => 'al',
				'alaska' => 'ak',
				'arizona' => 'az',
				'arkansas' => 'ar',
				'california' => 'ca',
				'colorado' => 'co',
				'connecticut' => 'ct',
				'delaware' => 'de',
				'district of columbia' => 'dc',
				'florida' => 'fl',
				'georgia' => 'ga',
				'hawaii' => 'hi',
				'idaho' => 'id',
				'illinois' => 'il',
				'indiana' => 'in',
				'iowa' => 'ia',
				'kansas' => 'ks',
				'kentucky' => 'ky',
				'louisiana' => 'la',
				'maine' => 'me',
				'maryland' => 'md',
				'massachusetts' => 'ma',
				'michigan' => 'mi',
				'minnesota' => 'mn',
				'mississippi' => 'ms',
				'missouri' => 'mo',
				'montana' => 'mt',
				'nebraska' => 'ne',
				'nevada' => 'nv',
				'new hampshire' => 'nh',
				'new jersey' => 'nj',
				'new mexico' => 'nm',
				'new york' => 'ny',
				'north carolina' => 'nc',
				'north dakota' => 'nd',
				'ohio' => 'oh',
				'oklahoma' => 'ok',
				'oregon' => 'or',
				'pennsylvania' => 'pa',
				'rhode island' => 'ri',
				'south carolina' => 'sc',
				'south dakota' => 'sd',
				'tennessee' => 'tn',
				'texas' => 'tx',
				'utah' => 'ut',
				'vermont' => 'vt',
				'virginia' => 'va',
				'washington' => 'wa',
				'west virginia' => 'wv',
				'wisconsin' => 'wi',
				'wyoming' => 'wy' 
		);
		
		return $states[strtolower($stateString)];
	}
}

$customPost = CustomPostTarget::getInstance();
add_action('init', array(
		$customPost,
		'init' 
));
