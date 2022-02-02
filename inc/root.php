<?php
$phpinc = "not set";
$root = "not set";
$jsinc = "not set";
$ADlib = "not set";
$jsWidgets = "not set";

class cRoot{
	//**************************************************************************
	public static function set_root($psRoot){
		global $home, $root, $phpinc, $jsinc, $ADlib, $jsWidgets;
		
		$root=realpath($psRoot);
		$phpinc = realpath("$root/../phpinc");
		$jsinc = "$psRoot/../jsinc";
		$ADlib = realpath("$phpinc/appd");
		$jsWidgets = "$home/js/widgets";
	}
}
?>
