//###############################################################################
var cCharts={
	queue: new cHttpQueue,
	METRIC_FIELD: "cmf.",
	COUNT_FIELD: "ccf",
	TITLE_FIELD: "ctf.",
	APP_FIELD: "caf.",
	show_export_all : true,
	
	
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
		var oForm = $("<form>", {id:"AllMetricsForm",method:"POST",action:"all_csv.php",target:"_blank"});
		
		$("DIV[type='appdchart']").each( //all div elements which have their type set to appdchart
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
				
				var sGoURL = oElement.attr("goUrl");
				var sGoLabel = oElement.attr("goLabel");
				
				oElement.appdchart({
					appName:sAppName,
					title:sTitle,
					metric:sMetric,
					goUrl:sGoURL,
					goCaption:sGoLabel,
					previous_period:iPrevious,
					width:oElement.attr("width"),
					height:oElement.attr("height"),
					showZoom:oElement.attr("showZoom"),
					showCompare:oElement.attr("showCompare")
				});
					
				//-------------build the form
				if(oThis.show_export_all){
					iCount++;
					oInput = $("<input>",{type:"hidden",name:cCharts.METRIC_FIELD+iCount,value:sMetric}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cCharts.TITLE_FIELD+iCount,value:sTitle}	);
					oForm.append(oInput);
					oInput = $("<input>",{type:"hidden",name:cCharts.APP_FIELD+iCount,value:sAppName}	);
					oForm.append(oInput);
				}
			}
		);
		
		//complete the form
		if (iCount >0){
			oInput = $("<input>",{type:"hidden",name:cCharts.COUNT_FIELD,value:iCount}	);
			oForm.append(oInput);
			oInput = $("<input>",{type:"submit",name:"submit",value:"Export All as CSV"}	);
			oForm.append(oInput);
			$("#AllMetrics").empty().append(oForm);
		}
	},
	
	init:function(){
		var oThis = this;
		//load google charts
		try{
			google.charts.load('current', {'packages':['corechart']});
		}
		catch (e){}
		google.charts.setOnLoadCallback( function(){oThis.loadCharts()})
	},
}
cCharts.queue.maxTransfers = 3	; 	//dont overload the controller


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
		width:null,
		height:null,
		onClick:null,
		shortNoData:false,
		showZoom:true,
		onSelect: null,
		previous_period:false,
		goUrl:null,
		goCaption:"Go"
	},
	
	consts:{
		APP_QS:"app",
		METRIC_QS:"met",
		TITLE_QS:"tit",
		DIV_QS:"div",
		PREVIOUS_QS: "prv",
		METRIC_API:"rest/getMetric.php",
		NO_DATA_HEIGHT: 90,
		SHORT_NO_DATA_HEIGHT: 40,
		csv_url:"rest/getMetric.php",
		zoom_url:"metriczoom.php",
		compare_url:"compare.php",
		WAIT_VISIBLE:1200,
		INFO_WIDTH:70,
		BUTTON_WIDTH:30
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
		
		//check for necessary classes
		if (!bean){						$.error("bean class is missing! check includes");	}
		if (!cHttp2){					$.error("http2 class is missing! check includes");	}
		if (!this.element.gSpinner){ 	$.error("gSpinner is missing! check includes");		}
		if (!$.event.special.inview){	$.error("inview class is missing! check includes");	}
		//if (!oElement.visible ) 		$.error("visible class is missing! check includes");	
		if (!oElement.inViewport ) 		$.error("inViewport class is missing! check includes");	
		
		//check for required options
		var oOptions = this.options;
		if (!oOptions.title)	{		$.error("title  missing!");			}
		//if (!oOptions.appName)	{	$.error("application  missing!");	}
		if (!oOptions.metric)	{		$.error("metric  missing!");		}
		if (!oOptions.width)	{		$.error("width missing!");		}
		if (!oOptions.height)	{		$.error("height missing!");		}
		
		//set the DIV size
		oElement.outerWidth(oOptions.width );
		oElement.outerHeight(oOptions.height );
		oElement.css("max-width",""+oOptions.width+"px");
		oElement.css("display","inline-block");
		
		//load content
		this.pr__setInView();
	},
	
	pr__setInView: function(){
		var oThis = this;
		var oElement = oThis.element;
		
		oElement.empty();
		oElement.append("Waiting to become visible ");
		var btnForce = $("<button>").append("load");
		oElement.append(btnForce);
		btnForce.click( 		function(){oThis.onInView(true);}		);
		
		oElement.on('inview', 	function(poEvent, pbIsInView){oThis.onInView(pbIsInView);}	);		
	},

	//#################################################################
	//# events
	//#################################################################`
	onInView: function(pbIsInView){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;

		//check if element is visible
		if (!pbIsInView) return;		
		oElement.off('inview');	//turn off the inview listener
		oElement.empty();
		oElement.removeClass();
		oElement.addClass("chart_initialising");
		oElement.append("Initialising: " + oOptions.title);

		setTimeout(	function(){	oThis.onTimer()}, this.consts.WAIT_VISIBLE);
	},
	
	//*******************************************************************
	onTimer: function(){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;
		if (cCharts.queue.stopping) return;
		
		if (!oElement.inViewport()){
			this.pr__setInView();
			return;
		}

		if (cCharts.queue.stopping) return;
		
		//loading message
		oElement.empty();
		oElement.removeClass();
		oElement.addClass("chart_queuing");
		oElement.append("Queueing: " + oOptions.title);
		
		//add the data request to the http queue
		var oItem = new cHttpQueueItem();
		oItem.url = this.pr__get_chart_url();
		oItem.fnCheckContinue = function(){return oThis.checkContinue();};

		bean.on(oItem, "start", 	function(){oThis.onStart(oItem);}	);				
		bean.on(oItem, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oItem, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		cCharts.queue.add(oItem);
	},
	
	//*******************************************************************
	checkContinue: function(){
		var oThis = this;
		var oOptions = this.options;
		var oElement = oThis.element;
		var bOK = true;
		
		if (!oElement.inViewport()){
			this.pr__setInView();
			bOK = false;
		}
		
		cDebug.write("Chart is visible:" + bOK.toString() + " app:" + oOptions.appName + " Metric:" + oOptions.metric);
		
		return bOK;
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
	onStart: function(poItem){
		var oElement = this.element;
		var oOptions = this.options;

		if (cCharts.queue.stopping) return;
		
		if (!oElement.inViewport()){
			poItem.abort = true;
			this.pr__setInView();
			return;
		}
		
		oElement.empty();
		oElement.removeClass();
		oElement.addClass("chart_loading");
		
		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oElement.append(oLoader).append("Loading: " + oOptions.title);
	},
	
	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oOptions = this.options;
		var oElement = oThis.element;

		if (cCharts.queue.stopping) return;
		oElement.attr("class","chart_drawing");
		
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
	onClickGo: function(){
		var oOptions = this.options;
		document.location.href = oOptions.goUrl;
	},
	
	//*******************************************************************
	onClickCompare: function(){
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams={};
		oParams[oConsts.METRIC_QS]=oOptions.metric;
		if (oOptions.appName)
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
		oElement.removeClass();
		oElement.addClass("chartnodatadiv");
		oElement.append("No data found for "+ oOptions.title);
		oElement.height(oConsts.SHORT_NO_DATA_HEIGHT);
	},

	//*******************************************************************
	pr__get_chart_url: function (){
		var sUrl;
		var oOptions = this.options;
		var oConsts = this.consts;
		
		var oParams = {};
		oParams[ oConsts.METRIC_QS ] = oOptions.metric;
		if (oOptions.appName) oParams[ oConsts.APP_QS ] = oOptions.appName;
		oParams[ oConsts.DIV_QS ] = "";
		if (oOptions.previous_period == 1) {
			oParams[ oConsts.PREVIOUS_QS ] = 1;
			oOptions.title = "(Previous) " + oOptions.title;
		}
		
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
		oElement.append(oTable);		//have to add the element here otherwise google charts donw work properly
		
		// buttons the the left of the chart
		var oCell = $("<TD>", {class:"buttonpanel"});
		var oButton = $("<button>",{class:"csv_button",title:"download as CSV"}).button({icon:"ui-icon-document"});
			oButton.click(		function(){ oThis.onClickCSV()}		);
			oCell.append(oButton);
		
		if (oOptions.showZoom){
			var oButton = $("<button>",{class:"csv_button",title:"Zoom"}).button({icon:"ui-icon-zoomin"});
			oButton.click(		function(){ oThis.onClickZoom()}		);
			oCell.append(oButton);
		}

		if (oOptions.showCompare){
			var oButton = $("<button>",{class:"csv_button",title:"Compare"}).button({icon:"ui-icon-shuffle"});
			oButton.click(		function(){ oThis.onClickCompare()}		);
			oCell.append(oButton);
		}

		if (oOptions.goUrl){
			var oButton = $("<button>",{class:"csv_button",title:oOptions.goCaption}).button({icon:"ui-icon-arrowreturn-1-n"});
			oButton.click(		function(){ oThis.onClickGo()}		);
			oCell.append(oButton);
		}
		oRow.append(oCell);
		
		// draw the chart
		var sChartID=oElement.attr("id")+"chart";
		var oChartDiv= $("<DIV>",{id:sChartID,class:"chartdiv",width:oOptions.width-this.consts.INFO_WIDTH-this.consts.BUTTON_WIDTH, height:oOptions.height -5});
		var oCell = $("<TD>");
		oCell.append(oChartDiv);
		oRow.append(oCell);
		
		var oDiv = oChartDiv[0];
		var dStart = new Date(poJson.epoch_start);
		var dEnd = new Date(poJson.epoch_end);
		if (oOptions.previous_period == 1){
			var iDiff = poJson.epoch_end - poJson.epoch_start;
			dEnd = new Date(poJson.epoch_start);
			dStart = new Date(dEnd - iDiff);
		}
		
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
		var oCell = $("<TD>", {class:"infopanel"});
		oCell.append("Max: "+ iMax + "<br>");
		oCell.append("Avg: "+ iAvgObs + "<br>");
		oCell.append("Min: "+ iMin + "<br>");
		oRow.append(oCell);

		// show the buttons below the graph for now
		// todo make the buttons a context dropdown to save screen space
		
	}
});