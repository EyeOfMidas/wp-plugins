<?php
$postUrl = 'http://localhost';

$request = new WP_Http();
$response = $request->post($postUrl, array(
		'body' => $formData 
));

if (isset($formData['banana'])) {
	header('Location: http://www.albinoblacksheep.com/flash/banana');
}
