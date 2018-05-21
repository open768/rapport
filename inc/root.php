<?php
$home = "not set";
$root = "not set";
$phpinc = "not set";
$jsinc = "not set";

class cRoot{
	//**************************************************************************
	public static function set_root($psRoot){
		global $home, $root, $phpinc, $jsinc;
		
		$home = $psRoot;
		$root=realpath($home);
		$phpinc = realpath("$root/../phpinc");
		$jsinc = "$home/../jsinc";
	}
}
?>
