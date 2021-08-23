<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

//####################################################################
$home="../..";
require_once "$home/inc/common.php";
require_once "$root/inc/inc-charts.php";



//####################################################################
cRenderHtml::header("All Servers - MQ");
cRender::force_login(); 
cChart::do_header();
cChart::$hideGroupIfNoData = true;

//********************************************************************
if (cAppdyn::is_demo()){
	cRender::errorbox("function not supported for Demo");
	cRenderHtml::footer();
	exit;
}

//####################################################################
cRender::show_top_banner( "All servers - MQ"); 		

//####################################################################
?>
<div id="page_content">
	<?php
		cRender::button("Back to servers", "servers.php");	
		if (cRender::is_list_mode())
			cRender::button("show as buttons", "mq.php");
		else
			cRender::button("show as list", "mq.php?".cRender::LIST_MODE_QS);
	?>
	<p>
	<h2>Pick a Node</h2>
	All these nodes have MQ Queue managers associated with them
	<p>&nbsp;<p>
	<?php
		// get the list of all servers that have MQ metrics
		$aData = cAppDynController::GET_server_nodes_with_MQ();
		$iCount = count($aData);
		if ($iCount == 0)
			cRender::errorbox("sorry - no nodes found");
		else{
			//echo "found $iCount nodes<p>";
			$sPrevious = "";
			$iColumn=0;
			foreach ($aData as $sNode){
				if (cRender::is_list_mode())
						echo "$sNode<br>";	
					else{
						$sChar = strtolower($sNode[0]);
						if ($sChar !== $sPrevious){
							$sPrevious = $sChar;
							echo "<h2>$sChar</h2>";
						}
						$sUrl=cHttp::build_url("mqnode.php", cRender::NODE_QS, $sNode);
						cRender::button($sNode, $sUrl);	
					}
			}
		}
	?>
</div>
<?php

//####################################################################
cChart::do_footer();
cRenderHtml::footer();
?>
