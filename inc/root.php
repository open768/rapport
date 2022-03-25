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

		//php can follow symlinks but not apache apparently
		
		$root=realpath($psRoot);
		//$phpinc = realpath("$root/lib/phpinc");
		//$jsinc = "$psRoot/lib/jsinc";
		$phpinc = realpath("$root/../phpinc");
		$jsinc = "$psRoot/../jsinc";
		$ADlib = realpath("$phpinc/appd");
		$jsWidgets = "$home/js/widgets";

	}
}
?>
