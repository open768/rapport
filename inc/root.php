<?php
$phpinc = "not set";
$root = "not set";
$jsinc = "not set";
$ADlib = "not set";

class cRoot{
	//**************************************************************************
	public static function set_root($psRoot){
		global $home, $root, $phpinc, $jsinc, $ADlib;
		
		$root=realpath($psRoot);
		$phpinc = realpath("$root/../phpinc");
		$jsinc = "$psRoot/../jsinc";
		$ADlib = realpath("$phpinc/appd");
	}
}
?>
