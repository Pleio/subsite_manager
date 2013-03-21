<?php 

	$site_guid = (int) get_input("site_guid");
	
	$size = get_input("size", "medium");
	if (!in_array($size, array("large", "medium", "small", "tiny", "master", "topbar"))) {
		$size = "medium";
	}
	
	if($subsite = elgg_get_site_entity($site_guid)){
		if($icon = $subsite->getIconContents($size)){
			$etag = md5($site_guid . $size . $subsite->icontime);
			if (isset($_SERVER["HTTP_IF_NONE_MATCH"]) && trim($_SERVER["HTTP_IF_NONE_MATCH"]) == $etag) {
				header("HTTP/1.1 304 Not Modified");
				exit();
			}
			
			// show icon
			header("Content-type: image/jpeg");
			header('Expires: ' . date('r',time() + 864000));
			header("Pragma: public");
			header("Cache-Control: public");
			header("Content-Length: " . strlen($icon));
			header("ETag: " . $etag);
			
			$splitString = str_split($icon, 1024);
			foreach($splitString as $chunk){
				echo $chunk;
			}
			
			exit();
		}
	}
	
	// should not come here
	header("Location: " . elgg_get_site_url() . "_graphics/icons/default/" . $size . ".png");
	exit();