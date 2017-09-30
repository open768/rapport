function ck_appd_chart_loadCharts(){
	$("DIV[type='appdchart']").each( 
		function(pIndex, pElement){
			var oElement = $(pElement);
			oElement.appdchart({
				appName:oElement.attr("appName"),
				title:oElement.attr("title"),
				metric:oElement.attr("metric")
			});
		}
	);
}

goAppdChartQueue = new cHttpQueue;

//load google charts
try{
	google.charts.load('current', {'packages':['corechart']});
}
catch (e){}

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.appdchart",{
	//#################################################################
	//# Definition
	//#################################################################
	options:{
		title:null,
		appName:null,
		metric: null,
		width:400,
		onClick:null,
		shortNoData:false
	},
	
	consts:{
		APP_QS:"app",
		METRIC_QS:"met",
		TITLE_QS:"tit",
		DIV_QS:"div",
		METRIC_API:"rest/getMetric.php",
		NO_DATA_HEIGHT: 90,
		SHORT_NO_DATA_HEIGHT: 40,
		csv_url:"rest/getMetric.php",
		zoom_url:"metriczoom.php",
		compare_url:"compare.php"
	},

	//#################################################################
	//# Constructor
	//#################################################################`
	_create: function(){
		var oThis, oElement;
		
		//set basic stuff
		oThis = this;
		oElement = oThis.element;
		oElement.uniqueId();
		oElement.empty();
		
		//check for necessary classes
		if (!bean){						$.error("bean class is missing! check includes");	}
		if (!cHttp2){					$.error("http2 class is missing! check includes");	}
		if (!this.element.gSpinner){ 	$.error("gSpinner is missing! check includes");		}
		if (!$.event.special.inview){	$.error("inview class is missing! check includes");	}
		if (!oElement.visible ) 		$.error("visible class is missing! check includes");	
		
		//check for required options
		var oOptions = this.options;
		if (!oOptions.title)	{		$.error("title  missing!");			}
		if (!oOptions.appName)	{		$.error("application  missing!");	}
		if (!oOptions.metric)	{		$.error("metric  missing!");		}
		
		//load content
		oElement.on('inview', 	function(poEvent, pbIsInView){oThis.onInView(pbIsInView);}	);

		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oElement.append(oLoader).append("please wait...");
	},

	//#################################################################
	//# events
	//#################################################################`
	onInView: function(pbIsInView){
		var oThis = this;
		var oElement = oThis.element;

		//check if element is visible
		if (!pbIsInView) return;		
		this.element.off('inview');	//turn off the inview listener
		oElement.append (" Loading...");		

		//add the data request to the http queue
		if (goAppdChartQueue.stopping) return;
		var oItem = new cHttpQueueItem();
		oItem.url = this.pr__get_chart_url();

		bean.on(oItem, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oItem, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		goAppdChartQueue.add(oItem);
	},
	
	//*******************************************************************
	onError: function(poHttp, psMessage){
		var oThis = this;
		var oOptions = this.options;
		var oElement = oThis.element;
		
		oElement.empty();
		var oDiv = $("<DIV>",{class:"ui-state-error"});
		oDiv.append((psMessage?psMessage:"There was an error getting chart data"));
		oElement.append(oDiv);
	},

	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oOptions = this.options;
		var oElement = oThis.element;

		var oResponse = poHttp.response;
		if (oResponse.data.length == 0){
			this.pr__no_data_found();
			return;
		}
		
		oElement.empty();
		oElement.append("Drawing Chart...");
		
		this.pr__draw_chart(poHttp.response);
	},

	//*******************************************************************
	onClickCSV: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		oParams[oConsts.APP_QS]=oOptions.appName;
		oParams[oConsts.TITLE_QS]=oOptions.title;
		oParams["csv"]=1;
		var sUrl = cBrowser.buildUrl(oConsts.csv_url, oParams);
		window.open(sUrl);
	},
	
	//*******************************************************************
	onClickCompare: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		oParams[oConsts.APP_QS]=oOptions.appName;
		oParams[oConsts.TITLE_QS]=oOptions.title;
		var sUrl = cBrowser.buildUrl(oConsts.compare_url, oParams);
		window.open(sUrl);		
	},
	
	//*******************************************************************
	onClickZoom: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		oParams[oConsts.APP_QS]=oOptions.appName;
		oParams[oConsts.TITLE_QS]=oOptions.title;
		var sUrl = cBrowser.buildUrl(oConsts.zoom_url, oParams);
		window.open(sUrl);		
	},
	
	//#################################################################
	//# functions
	//#################################################################`
	pr__no_data_found: function(){
		var oThis = this;
		var oOptions = this.options;
		var oElement = oThis.element;
		var oConsts = this.consts;
		
		oElement.empty();
		var oDiv = $("<DIV>",{class:"chartnodatadiv"});
		oDiv.height((oOptions.shortNoData?oConsts.SHORT_NO_DATA_HEIGHT:oConsts.NO_DATA_HEIGHT));
		oDiv.append("No data found for "+ oOptions.title);
		oElement.append(oDiv);		
	},

	//*******************************************************************
	pr__get_chart_url: function (){
		var sUrl;
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams = {};
		oParams[ oConsts.METRIC_QS ] = oOptions.metric;
		oParams[ oConsts.APP_QS ] = oOptions.appName;
		oParams[ oConsts.DIV_QS ] = "";
		//if (poItem.previous) sUrl=sUrl+"&<?=cRender::PREVIOUS_QS?>=1";
		
		sUrl = cBrowser.buildUrl(this.consts.METRIC_API, oParams);
		return sUrl;
	},
	
	//*******************************************************************
	pr__draw_chart: function(poJson){
		var oThis = this;
		var oOptions = this.options;
		var oElement = oThis.element;	
		var oConsts = this.consts;

		var oChart, aValues, dDate, iValue, iItemMax, iMax, iMaxObserved, iAvgObs, iSum, sMax, iMin;
		

		iMax = 0;
		iMaxObserved = 0;
		iSum = 0;
		
		//create the google data object
		var oData = new google.visualization.DataTable();
		oData.addColumn('datetime', 'Time');
		oData.addColumn('number', 'Value');		
		oData.addColumn('number', 'Max');		
		oData.addColumn({type: 'string', role: 'tooltip', p: {html: true}});		
		

		//build up the data array
		for (i=0; i<poJson.data.length; i++ ){
			var oItem = poJson.data[i];
			dDate = new Date(oItem.date);
			iValue = oItem.value;
			iItemMax = oItem.max;
			
			sMax= (iMax?"<br>Max: "+iItemMax:"");
			iMax = Math.max(iMax,iItemMax);
			iMaxObserved = Math.max(iMaxObserved,iValue);
			iSum += iValue;
			
			if (i==0)
				iMin = iValue;
			else
				iMin = Math.min(iMin, iValue);
			
			
			sTooltip = "<div class='charttooltip'>value:" + iValue + sMax + "<br>" + dDate.toString() + "</i></div>";
			
			oData.addRow([dDate, iValue, iItemMax, sTooltip]);
		}
		iMax = Math.max(iMax, iMaxObserved);
		iAvgObs = Math.round(iSum/poJson.data.length);
		
		// set the display range of the chart to match the requested timerange
		oElement.empty();
		
		// create a table
		var oTable = $("<TABLE>", {border:0,width:"100%"});
		var oRow = $("<TR>");
		oTable.append(oRow);
		oElement.append(oTable);
		
		// draw the chart
		var sChartID=oElement.attr("id")+"chart";
		var oChartDiv= $("<DIV>",{id:sChartID,class:"chartdiv",width:oOptions.width});
		var oCell = $("<TD>");
		oCell.append(oChartDiv);
		oRow.append(oCell);
		
		var oDiv = oChartDiv[0];
		var dStart = new Date(poJson.epoch_start);
		var dEnd = new Date(poJson.epoch_end);
		
		oChart = new google.visualization.LineChart( oDiv );
		var oChartOptions = {
			title: oOptions.title,
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
			},
			interpolateNulls: false
		};
		oChart.draw(oData, oChartOptions);
		
		//display maximumes and observed values
		var oCell = $("<TD>");
		oCell.append("<nobr>Max: "+ iMax + "</nobr><p>");
		oCell.append("<b>Observed:</b><br>");
		oCell.append("<nobr>Max: "+ iMax + "</nobr><br>");
		oCell.append("<nobr>Avg: "+ iAvgObs + "</nobr><br>");
		oCell.append("<nobr>Min: "+ iMin + "</nobr><br>");
		oRow.append(oCell);

		// show the buttons below the graph for now
		// todo make the buttons a context dropdown to save screen space
		var oRow = $("<TR>");
		var oCell = $("<TD>",{colspan:2});
		
		var oButton = $("<button>",{class:"csv_button"});
			oButton.append("CSV");
			oButton.click(		function(){ oThis.onClickCSV()}		);
			oCell.append(oButton);
		
		var oButton = $("<button>",{class:"csv_button"});
			oButton.append("Zoom");
			oButton.click(		function(){ oThis.onClickZoom()}		);
			oCell.append(oButton);

		/*var oButton = $("<button>",{class:"csv_button"});
			oButton.append("Save");
			oCell.append(oButton);
		*/

		var oButton = $("<button>",{class:"csv_button"});
			oButton.append("Compare");
			oButton.click(		function(){ oThis.onClickCompare()}		);
			oCell.append(oButton);
			
		oRow.append(oCell);
		oTable.append(oRow);
	}
});