<?php
	
/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
//####################################################################
$home="..";
require_once "$home/inc/common.php";
require_once("$root/inc/common.php");

$url = cHeader::get("url");


if (cHeader::get("fromh")){
	// from
	$hh1 = cHeader::get("fromh");
	$min1 = cHeader::get("fromm");
	
	$_SESSION["fromh"] = $hh1;
	$_SESSION["fromm"] = $min1 ;
	
	$dd = cHeader::get("fromdd");
	$mm = cHeader::get("fromdm");
	$yy = cHeader::get("fromdy");
	$msFrom = mktime( $hh1, $min1, 0,  $mm, $dd,$yy) *1000;
	$_SESSION["fromd"] = "$dd/$mm/$yy";
	$_SESSION["fromdd"] = $dd;
	$_SESSION["fromdm"] = $mm;
	$_SESSION["fromdy"] = $yy;
	
	//to
	$hh2 = cHeader::get("toh");
	$min2 = cHeader::get("tom");
	$_SESSION["toh"] = $hh2 ;
	$_SESSION["tom"] = $min2;
	$dd2 = cHeader::get("todd");
	$mm2 = cHeader::get("todm");
	$yy2 = cHeader::get("tody");
	$msTo = mktime( $hh2, $min2, 0,  $mm2, $dd2,$yy2) *1000;
	$_SESSION["tod"] = "$dd2/$mm2/$yy2";
	$_SESSION["todd"] = $dd2;
	$_SESSION["todm"] = $mm2;
	$_SESSION["tody"] = $yy2;
	
	//session variables
	$_SESSION[cADCommon::TIME_SESS_KEY] = cADCommon::TIME_CUSTOM ;
	$_SESSION[cADCommon::TIME_CUSTOM_FROM_KEY] = $msFrom ;
	$_SESSION[cADCommon::TIME_CUSTOM_TO_KEY] = $msTo;
	header( "Location: $url" );
}else{
	if (isset($_SESSION["fromdd"])) {
		$fromdd = $_SESSION["fromdd"];
		$fromdm = $_SESSION["fromdm"];
		$fromdy = $_SESSION["fromdy"];
		$todd = $_SESSION["todd"];
		$todm = $_SESSION["todm"];
		$tody = $_SESSION["tody"];
	} else{
		$fromdd = date("d");
		$fromdm = date("m");
		$fromdy = date("y");
		$todd = $fromdd;
		$todm = $fromdm;
		$tody = $fromdy;
	}

	?>
	<form> 
		<table> 
			<tr>
				<td width=150> From </td>
				<td>
					<input type="text" size="2" name="fromdd" value="<?=$fromdd?>" >
					<input type="text" size="2" name="fromdm" value="<?=$fromdm?>" >
					<input type="text" size="2" name="fromdy" value="<?=$fromdy?>" > - 
				</td>
				<td>
					<input type="text" size="2" name="fromh" value="" >:
					<input type="text" size="2" name="fromm" value="" >
				</td>
			</tr>
			<tr>
				<td width="150"> To </td>
				<td>
					<input type="text" size="2" name="todd" value="<?=$todd?>">
					<input type="text" size="2" name="todm" value="<?=$todm?>">
					<input type="text" size="2" name="tody" value="<?=$tody?>"> -
				</td>
				<td>
					<input type="text" size=2 name="toh" value="" >:
					<input type="text" size=2 name="tom" value="" >
				</td>
			</tr>
		</table>
		<input type="hidden" name="url" value="<?=$url?>">
		<input type=submit value="set custom time">
	</form>
	<?php
} ?>
