<?php
$postUrl = 'http://api.datalot.com/contact/create/v2';

$passthrough = "CUSTOM REPLACE";

$dateOfBirth = explode("-", $formData['dob']);
$dob = array(
		"year" => intval($dateOfBirth[0]),
		"month" => intval($dateOfBirth[1]),
		"day" => intval($dateOfBirth[2]) 
);
$date = new DateTime($formData['dob']);
$now = new DateTime();
$interval = $now->diff($date);
$age = $interval->y;

$income = 0;

switch($formData['household_income']) {
	case "$0 to $59,000":
		$income = 59000;
		break;
	case "$59,001 to $94,000":
		$income = 94000;
		break;
	case "$94,001 and over":
		$income = 94001;
		break;
}

$stateCode = CustomPostTarget::getStateCode($formData['address_state']);

$rawData = array(
		'test' => "true",
		'access_key' => "REPLACE",
		'source_id' => "REPLACE",
		'product_id' => "REPLACE",
		'campaign_id' => "REPLACE",
		'passthrough' => $passthrough,
		'contact_permission' => array(
				'phone_explicit' => ($formData['authorize_phone'] != "" ? "true" : "false"),
				'mobile_explicit' => "false",
				'autodial_explicit' => ($formData['authorize_phone'] != "" ? "true" : "false") 
		),
		'contact_info' => array(
				'general_info' => array(
						'first_name' => $formData['name_first'],
						'last_name' => $formData['name_last'],
						'street1' => $formData['address_street_address'],
						'city' => $formData['address_city'],
						'state' => $stateCode,
						'zip_code' => intval($formData['address_zip']),
						'email' => $formData['email'],
						'phone_home' => $formData['phone'],
						'preferred_phone' => "H",
						'ip_address' => $formData['ip'],
						'dob_year' => $dob['year'],
						'dob_month' => $dob['month'],
						'dob_day' => $dob['day'],
						'datetime_collected' => date("c", strtotime($formData['date_created'])) 
				),
				'product_info' => array(
						'height_feet' => intval($formData['height_feet']),
						'height_inches' => intval($formData['height_inches']),
						'weight' => intval($formData['weight']),
						'age' => $age,
						'is_smoker' => ($formData['tobacco_user'] == "No" ? "false" : "true"),
						'is_insured' => ($formData['currently_insured'] == "No" ? "false" : "true"),
						'existing_condition' => $formData['pre_existing'],
						'expectant_parent' => ($formData['expectant_parent'] == "No" ? "false" : "true"),
						'previously_denied' => ($formData['denied_coverage'] == "No" ? "false" : "true"),
						'ss_or_disability' => ($formData['on_ss_or_disability'] == "No" ? "no" : "yes"),
						'household' => intval($formData['household_count']),
						'income' => $income 
				) 
		) 
);

$xmlString = CustomPostTarget::arrayToXML($rawData, "contact_create");
$response = CustomPostTarget::postAsXML($xmlString, $postUrl);

if (isset($formData['redirect'])) {
	header('Location: /');
}
