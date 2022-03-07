'use strict';
//###############################################################################
var cChartConsts={
	ALL_CSV_URL:"/pages/all_csv.php",
	WIDTH_3ACROSS: 330 ,
	LETTERBOX_HEIGHT: 125,
	ATTR_TITLE : "ati",
	ATTR_WIDTH : "awi",
	ATTR_HEIGHT : "ahe",
	ATTR_PREVIOUS : "apr",
	ATTR_SHOW_ZOOM : "asz",
	ATTR_SHOW_COMPARE : "asc",
	ATTR_HIDE_IF_NO_DATA : "ahn",
	ATTR_HIDE_GROUP_IF_NO_DATA : "ahgn",
	ATTR_GO_URL : "agu",
	ATTR_GO_LABEL : "agl"
}

//###############################################################################
var cChartItem={
	label: "",
	metric: null
}

//###############################################################################
var cCharts={
	queue: new cHttpQueue,
	show_export_all : true,
	home:"",		//populated by init
	
	
	//*********************************************************
	rememberChart: function(psApp, psMetric, psTitle){
		var oItem = new cChartItem(psApp, psMetric, psTitle);
		cCharts.allCharts.push(oItem);
	},
	
	//*********************************************************
	loadCharts: function(){	
		var iCount ,oInput;
		var oThis = this;
		
		iCount = 0;
		var oForm = $("<form>", {id:"AllMetricsForm",method:"POST",action:this.home+cChartConsts.ALL_CSV_URL,target:"_blank"});
		
		$("DIV[type='adchart']").each( //all  elements which have their type set to adchart
			function(pIndex, pElement){
				var oElement = $(pElement);
				
				var sAppName = oElement.attr(cRenderQS.APP_QS);
				var sMetric = oElement.attr(cRenderQS.METRIC_QS +"0");
				var sTitle = oElement.attr(cRenderQS.TITLE_QS+"0");
								
				oElement.adchart();
					
				//-------------build the form
				if(oThis.show_export_all){
					iCount++;
					oInput = $("<input>",{type:"hidden",name:cRenderQS.CHART_METRIC_FIELD+"."+iCount,value:sMetric}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cRenderQS.CHART_TITLE_FIELD+"."+iCount,value:sTitle}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cRenderQS.CHART_APP_FIELD+"."+iCount,value:sAppName}	);
					oForm.append(oInput);
				}
			}
		);
		
		//complete the form
		if ((iCount >0)  && this.show_export_all){
			oInput = $("<input>",{type:"hidden",name:cRenderQS.CHART_COUNT_FIELD,value:iCount}	);
			oForm.append(oInput);
			oInput = $("<input>",{type:"submit",name:"submit",value:"Export All as CSV", class:"mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"}	);
			oForm.append(oInput);
			$("#AllMetrics").empty().append(oForm);
		}
	},
	
	//*********************************************************
	init:function(psHome){
		var oThis = this;
		this.home=psHome;
		this.load_google_charts(function(){oThis.loadCharts()});
	},
	
	//*********************************************************
	load_google_charts: function( pfnCallback){
		//load google charts
		try{
			google.charts.load('current', {'packages':['corechart']});
		}
		catch (e){}
		google.charts.setOnLoadCallback( pfnCallback );
	},
	
	//*********************************************************
	isGoogleChartsLoaded: function(){
		if ((typeof google === 'undefined') || (typeof google.visualization === 'undefined')) 
		   return false;
		else
		 return true;
   	}	
}
cCharts.queue.maxTransfers = 5	; 	//dont overload the controller
