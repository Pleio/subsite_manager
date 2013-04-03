<?php
/**
 * @uses $vars['language']
 */
global $CONFIG;

if(get_input("lastcached")){
	$etag = $CONFIG->lastcached;
	
	header('Expires: ' . date('r',time() + 864000));
	header("Pragma: public");
	header("Cache-Control: public");
}

$language = $vars['language'];

$translations = $CONFIG->translations['en'];

if ($language != 'en') {
	$translations = array_merge($translations, $CONFIG->translations[$language]);
}

// using @ to suppress warnings about invalid characters
echo @json_encode($translations);