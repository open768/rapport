//###############################################################################
var cChartConsts={
	ALL_CSV_URL:"/pages/all_csv.php"
}

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
				
				var sAppName = oElement.attr("appName");
				var sMetric = oElement.attr("metric0");
				var sTitle = oElement.attr("title0");
				var iPrevious = 0;
				try {
					var sPrevious = oElement.attr("previous");
					iPrevious = parseInt(sPrevious);
				} catch (e){}
								
				oElement.adchart({
					appName:sAppName,
					home:oThis.home,
					title:sTitle,
					metric:sMetric,
					goUrl:oElement.attr("goUrl"),
					goCaption:oElement.attr("goLabel"),
					previous_period:iPrevious,
					width:oElement.attr("width"),
					height:oElement.attr("height"),
					showZoom:oElement.attr("showZoom"),
					showCompare:oElement.attr("showCompare"),
					hideIfNoData:oElement.attr("hideIfNoData"),
					hideGroupIfNoData:oElement.attr("hideGroupIfNoData")
				});
					
				//-------------build the form
				if(oThis.show_export_all){
					iCount++;
					oInput = $("<input>",{type:"hidden",name:cRender.CHART_METRIC_FIELD+"."+iCount,value:sMetric}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cRender.CHART_TITLE_FIELD+"."+iCount,value:sTitle}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cRender.CHART_APP_FIELD+"."+iCount,value:sAppName}	);
					oForm.append(oInput);
				}
			}
		);
		
		//complete the form
		if (iCount >0){
			oInput = $("<input>",{type:"hidden",name:cRender.CHART_COUNT_FIELD,value:iCount}	);
			oForm.append(oInput);
			oInput = $("<input>",{type:"submit",name:"submit",value:"Export All as CSV", class:"mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"}	);
			oForm.append(oInput);
			$("#AllMetrics").empty().append(oForm);
		}
	},
	
	init:function(psHome){
		var oThis = this;
		this.home=psHome;
		//load google charts
		try{
			google.charts.load('current', {'packages':['corechart']});
		}
		catch (e){}
		google.charts.setOnLoadCallback( function(){oThis.loadCharts()})
	},
}
cCharts.queue.maxTransfers = 5	; 	//dont overload the controller
