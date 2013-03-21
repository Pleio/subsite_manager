<?php 

// in specific contexts the rss feed is not needed
if(elgg_in_context("profile") || elgg_in_context("friends") || elgg_in_context("friends_of")){	
	global $autofeed;
	$autofeed = false;
}