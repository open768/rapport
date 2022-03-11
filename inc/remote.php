<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2021 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/


//array of charts to display
$chart_items = array();

class cRemote{
	static $remote_items = [];
	//****************************************************************************
	public static function add( $psApp, $psTier, $psTrans, $piIndex){ 
		array_push(self::$remote_items, array("app"=>$psApp, "tier"=>$psTier, "trans"=>$psTrans, "index"=>$piIndex));
	}
	
	//****************************************************************************
	public static function do_footer(){
	?>
		<script type="text/javascript" >
			window.onload = function(){
			<?php
				foreach (self::$remote_items as $aRow) {
					$sApp = $aRow["app"];
					$sTier = $aRow["tier"];
					$sTrans = rawurlencode($aRow["trans"]);
					$iIndex = $aRow["index"];
					?>
						cRemote.aItems.push( {app:"<?=$sApp?>",tier:"<?=$sTier?>", trans:"<?=$sTrans?>",index:"<?=$iIndex?>"});
					<?php
				}
			?>
			cRemote.fnUrlCallBack=remotedata_getUrl;
			bean.on(cRemote, 'onJSON',remotedata_jsonCallBack);
			cRemote.go();
			}		
		</script>
		<?php
	}

	//****************************************************************************
	public static function do_header(){
		global $js_extra;
	?>
		<script type="text/javascript" src="<?=$js_extra?>/rgraph/libraries/RGraph.common.core.js"></script>
		<script type="text/javascript" src="<?=$js_extra?>/rgraph/libraries/RGraph.common.dynamic.js"></script>
		<script type="text/javascript" src="<?=$js_extra?>/bean/bean.js"></script>
	<?php
	}
}
?>
