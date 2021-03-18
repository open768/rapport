<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/


require_once("$appdlib/appdynamics.php");
require_once("$phpinc/ckinc/header.php");


$duration = cAppDynCommon::get_duration();
$aData=array();
$CSV_HEADER=true;

class cData {
	public $label, $data, $count;
}

class cAppDynCSV{
	//**************************************************************************
	private static function pr__has_data($poData){
		$bHasData = false;
		if ($poData && (count($poData) >0))
			foreach ($poData as $oRow){
				$value = $oRow->value;
				$max = $oRow->max;
				$current = $oRow->current;
				$sum = $value + $max + $current;
				if ($sum >0){
					$bHasData = true;
					break;
				}
			}
		return $bHasData;
	}

	//**************************************************************************
	private static function pr__build_dates($paData)
	{
		$aAssoc=array();

		foreach ($paData as $oMetric){
			$aData = $oMetric->data;
			foreach ($aData as $oRow){
				$milli = $oRow->startTimeInMillis;
				$label = "$milli";
				if (!isset($aAssoc[$label]) )
					$aAssoc[$label] = 1;
			}
		}
		$aKeys = array_keys($aAssoc);
		sort($aKeys);
		return $aKeys;
	}


	//**************************************************************************
	private static function pr__make_hashtables($paDates, $paData)
	{

		$aHashed = array();

		foreach ($paData as $oDataset){
			$aHashedDataset = array();
			$aRows = $oDataset->data;
			if ($aRows && (count($aRows)>0))
			{
				foreach ($aRows as $oRow)
				{
					$milli = $oRow->startTimeInMillis;
					$label = "$milli";
					$aHashedDataset[$label] = $oRow;
				}
				$aHashed[  $oDataset->label ] = $aHashedDataset;
			}
		}

		return $aHashed;
	}

	//###########################################################################
	//###########################################################################
	public static function push_data( &$paData, $psLabel, $poDataset){
		if (self::pr__has_data($poDataset)){
			usort($poDataset, "AD_sort_fn");
			$oInst = new cData();
			$oInst->label = $psLabel;
			$oInst->data = $poDataset;
			$oInst->count = count( $poDataset);

			array_push($paData, $oInst);
		}
	}
	
	//**************************************************************************
	public static function csv_export($paData, $psFilename, $pbFirstColumnSpecial)
	{
		global $CSV_HEADER;
		//*********** output the headers **********************
		if ($CSV_HEADER)
			cHeader::set_download_filename($psFilename);

		//********* ensure the dates line up by arranging into hashtables
		$aDates = self::pr__build_dates($paData);
		$aHashed =  self::pr__make_hashtables($aDates, $paData);

		//*********** output the labels 2nd row **********************
		$line = "TimeStamp,";
		$aKeys = array_keys($aHashed);
		foreach ($aKeys as $sLabel)
			$line .= "$sLabel (val), $sLabel (max),";
		echo "$line\n";

		//*********** output the data **********************
		foreach ($aDates as $sTimeStamp)
		{
			$sDate =  date("d/m/y H:i:s", $sTimeStamp/1000);
			$line =  "$sDate,";
			foreach ($aKeys as $sLabel){
				$aDataset = $aHashed[$sLabel];
				if (isset($aDataset[$sTimeStamp]) )
				{
					$oRow = $aDataset[$sTimeStamp];
					$line .= $oRow->current.",".$oRow->max.",";
				}
				else
					$line .= ",,";
			}
			echo "$line\n";
		}
	}
}

set_time_limit(200); // huge time limit as this takes a long time


?>
