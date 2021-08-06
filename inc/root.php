<?php
$phpinc = "not set";
$root = "not set";
$jsinc = "not set";
$appdlib = "not set";

class cRoot{
	//**************************************************************************
	public static function set_root($psRoot){
		global $home, $root, $phpinc, $jsinc, $appdlib;
		
		$root=realpath($psRoot);
		$phpinc = realpath("$root/../phpinc");
		$jsinc = "$psRoot/../jsinc";
		$appdlib = realpath("$phpinc/appd");
	}
}
?>
