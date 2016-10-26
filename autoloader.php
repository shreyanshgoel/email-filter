<?php

function autoload($class) {
	
	// $paths = explode(PATH_SEPARATOR, get_include_path());	// splits the returned string into separate directories.
	$paths = array(dirname(__FILE__));
	$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
	$file = str_replace("\\", DIRECTORY_SEPARATOR, trim($class, "\\")).".php";
	
	foreach ($paths as $path) {
		$combined = $path.DIRECTORY_SEPARATOR.$file;
		//var_dump($combined);
		if (file_exists($combined)) {
			include($combined);
			return;
		}
	}
	throw new Exception("{$class} not found");
}

class Autoloader {
	public static function autoload($class) {
		autoload($class);
	}
}

spl_autoload_register('autoload');	// Tells PHP to use autoload() method to load a class file by name

spl_autoload_register(array('autoloader', 'autoload')); // Tells PHP to use Autoloader::autoload() method to load a class file by name.

?>