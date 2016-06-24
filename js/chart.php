<?php

/**************************************************************************
Copyright (C) Chicken Katsu 2013-2016 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/
$root=realpath("..");
$phpinc = realpath("$root/../phpinc");

//####################################################################
require_once("../inc/inc-render.php");
require_once("../inc/inc-charts.php");
//the next line is for notepad++ to get syntax coloring to work
?>
/*<script language="javascript">*/
const CHART__NODATA_EVENT = "nodata";

function chartData(){
	this.app= null;
	this.chart= null;
	this.metric= null;
	this.previous=null;
	this.caption=null;
}

function cChartBean(){
}

//what happens on an error
function onChartError( poData){
	var sChart;
	sChart = poData.oItem.chart;
	$("#"+sChart).html("error loading metric: " + poData.oItem.caption);
	$("#"+sChart).height(40);
	write_console("error loading metric: "+ poData.oItem.url);
}

//the callback - this draws the graph
function onChartJson( poData){
	var oChart, aValues, dDate, iValue, iItemMax, iMax, iMaxObserved, iAvgObs, iSum, sMax;
	
	var oRemoteItem = poData.oItem;
	var oJson = poData.oJson;
	
	//clear the div
	var sDivID = oJson.div;
	$("#"+sDivID).empty();
	
	//see if there is any data
	if (oJson.data.length==0)
		chart_nodata(poData);
	else{
		iMax = 0;
		iMaxObserved = 0;
		iSum = 0;
		
		//create the google data object
		var oData = new google.visualization.DataTable();
		oData.addColumn('datetime', 'Time');
		oData.addColumn('number', 'Value');		
		oData.addColumn('number', 'Max');		
		oData.addColumn({type: 'string', role: 'tooltip', p: {html: true}});		
		

		for (i=0; i<oJson.data.length; i++ ){
			var oItem = oJson.data[i];
			dDate = new Date(oItem.date);
			iValue = oItem.value;
			iItemMax = oItem.max;
			sMax= (iMax?"<br>Max: "+iItemMax:"");
			iMax = Math.max(iMax,iItemMax);
			iMaxObserved = Math.max(iMaxObserved,iValue);
			iSum += iValue;
			
			sTooltip = "<div class='charttooltip'>value:" + iValue + sMax + "<br>" + dDate.toString() + "</i></div>";
			
			oData.addRow([dDate, iValue, iItemMax, sTooltip]);
			
		}
		
		// set the display range of the chart to match the requested timerange
		
		// display maximumes and observed values
		iMax = Math.max(iMax, iMaxObserved);
		$("#"+sDivID+"max").html(iMax);
		$("#"+sDivID+"maxo").html(iMaxObserved);
		iAvgObs = Math.round(iSum/oJson.data.length);
		$("#"+sDivID+"avgo").html(iAvgObs);

		
		// draw the chart
		var oDiv = document.getElementById(sDivID);
		var dStart = new Date(oJson.epoch_start);
		var dEnd = new Date(oJson.epoch_end);
		
		oChart = new google.visualization.LineChart( oDiv );
		var oOptions = {
			title: oRemoteItem.caption,
			legend: "right",
			tooltip: {isHtml: true},
			pointSize: 3,
			dataOpacity: 0.8,
			theme: 'maximized' ,
			hAxis: {
				textStyle:{color: 'DarkCyan'},
				viewWindow:{
					min:dStart,
					max:dEnd,
				}			
			},
			vAxis: {
				textStyle:{color: 'DarkCyan'},
				viewWindow:{min:0}			
			}
		};
		oChart.draw(oData, oOptions);
		
		// show the buttons below the graph
		$("#"+sDivID+"numbers").show();
		$("#"+sDivID+"buttons").show();
		
	}
}

function chart_nodata(poData){
	var sDivID, sCaption, oDiv;
	
	sDivID = poData.oItem.chart;
	sCaption = poData.oItem.caption;
	oDiv = $("#"+sDivID);
	oDiv.html("Nothing found: "<?=(cChart::$showShortNoData?"":"+sCaption")?>);
	oDiv.height(<?=(cChart::$showShortNoData?40:90)?>);
	oDiv.attr('class', 'chartnodatadiv');
	
	//publish event
	bean.fire(cChartBean,CHART__NODATA_EVENT,poData)
}


//*******************************************************************
function chart_getUrl(poItem){
	var sUrl;
	$("#"+poItem.chart).html("loading..." + poItem.caption);
	sUrl="rest/getMetric.php?<?=cRender::METRIC_QS?>="+poItem.metric+"&<?=cRender::APP_QS?>="+poItem.app+"&<?=cRender::DIV_QS?>="+poItem.chart;
	if (poItem.previous) sUrl=sUrl+"&<?=cRender::PREVIOUS_QS?>=1";
	
	write_console("fetching: " + sUrl);
	return sUrl;
}

//*******************************************************************
function save_fave_chart(psApp, psMetric){
	window.alert("chart saving is not implemented");
}



