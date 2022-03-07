'use strict';
$.widget( "ck.adchart",{
	//#################################################################
	//# Definition
	//#################################################################
	//TODO use attributes rather than options so that widget is self-contained and can work directly from DOM Element
	options:{
		pr__upper_div: null,
		pr__lower_div: null
	},

	consts:{
		METRIC_API:"rest/getMetric.php",
		CHART_PHP_FOLDER:"pages/charts",
		NO_DATA_HEIGHT: 90,
		SHORT_NO_DATA_HEIGHT: 40,
		csv_url:"allcsv.php",
		zoom_url:"metriczoom.php",
		compare_url:"compare.php",

		ATTR_APP : "aap",
		ATTR_TITLE : "ati",
		ATTR_METRIC : "ame",
		ATTR_WIDTH : "awi",
		ATTR_HEIGHT : "ahe",

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
		if (!cQueueifVisible)		$.error("Queue on visible class is missing! check includes");
		if (!bean)					$.error("bean class is missing! check includes");
		if (!cHttp2)				$.error("http2 class is missing! check includes");
		if (!oElement.gSpinner) 	$.error("gSpinner is missing! check includes");
		if (!oElement.slideout) 	$.error("slideout is missing! check includes");

		//check for required options
		if (!oElement.attr(cRenderQS.APP_ID_QS))			$.error("app ID  missing!");
		if (!oElement.attr(cChartConsts.ATTR_TITLE+"0"))	$.error("title  missing!")
		if (!oElement.attr(cRenderQS.METRIC_QS+"0"))		$.error("metric  missing!");
		if (!oElement.attr(cChartConsts.ATTR_WIDTH))					$.error("width missing!");
		if (!oElement.attr(cChartConsts.ATTR_HEIGHT))					$.error("height missing!");

		//set display style
		oElement.removeClass();
		oElement.addClass("chart_widget");

		//set the DIV size
		oElement.outerWidth(oElement.attr(cChartConsts.ATTR_WIDTH) );
		oElement.outerHeight(oElement.attr(cChartConsts.ATTR_HEIGHT) );
		oElement.css("max-width",""+oElement.attr(cChartConsts.ATTR_WIDTH)+"px");

		//create overlapping divs
		this.pr__create_overlapping_divs();


		//wait for widget to become visible
		this.pr__queue_element();
	},

	//*******************************************************************
	pr__queue_element: function(){
		var oThis, oElement;
		oThis = this;
		oElement = oThis.element;

		var oQueue = new cQueueifVisible();
		bean.on(oQueue, "status", 	function(psStatus){oThis.onStatus(psStatus);}	);
		bean.on(oQueue, "start", 	function(){oThis.onStart();}	);
		bean.on(oQueue, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);
		bean.on(oQueue, "error", 	function(poHttp){oThis.onError(poHttp);}	);
		oQueue.go(oElement, this.pr__get_url());
	},

	//*******************************************************************
	pr__create_overlapping_divs: function(){
		var oThis, oElement;
		var oOptions = this.options;
		oThis = this;
		oElement = oThis.element;

		oOptions.pr__upper_div = oElement.attr("id") + "_U";
		var oUpperDiv = $("<DIV>", {id:oOptions.pr__upper_div}).append("Please wait...");

oOptions.pr__lower_div = oElement.attr("id") + "_L";
		var oLowerDiv = $("<DIV>", {id:oOptions.pr__lower_div}).append("Please wait...");

		oElement.slideout({width:oElement.attr(cChartConsts.ATTR_WIDTH), height:oElement.attr(cChartConsts.ATTR_HEIGHT), uppercontent:oUpperDiv, lowercontent:oLowerDiv});
	},


	//#################################################################
	//# events
	//#################################################################`
	onStatus: function(psStatus){
		var oThis = this;
		var oOptions = this.options;
		var oUpperElement = $("#"+oOptions.pr__upper_div );

		oUpperElement.empty();
		oUpperElement.addClass("chart_widget");
		oUpperElement.addClass("chart_initialising");
		oUpperElement.append(psStatus);
	},


	//*******************************************************************
	onError: function(poHttp, psMessage){
		var oThis = this;
		var oOptions = this.options;
		var oTopElement = this.element;
		var oUpperElement = $("#"+oOptions.pr__upper_div );
		var oConsts = this.consts;

		oUpperElement.empty();
		oUpperElement.addClass("ui-state-error");
		oUpperElement.append("There was an error getting chart data for "+ oTopElement.attr(cChartConsts.ATTR_TITLE+"0"));
		var btnForce = $("<button>").append("load");
		oUpperElement.append(btnForce);
		btnForce.click( 		function(){oThis.pr__queue_element();}		);
		oUpperElement.height(oConsts.SHORT_NO_DATA_HEIGHT);
	},

	//*******************************************************************
	onStart: function(poItem){
		var oOptions = this.options;
		var oTopElement = this.element;
		var oUpperElement = $("#"+oOptions.pr__upper_div );

		oUpperElement.empty();
		oUpperElement.removeClass();
		oUpperElement.addClass("chart_widget");
		oUpperElement.addClass("chart_loading");

		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oUpperElement.append(oLoader).append("Loading: " + oTopElement.attr(cChartConsts.ATTR_TITLE+"0"));
	},

	//*******************************************************************
	onResponse: function(poHttp){
		var oThis = this;
		var oOptions = this.options;
		var oUpperElement = $("#"+oOptions.pr__upper_div );

		oUpperElement.empty();
		oUpperElement.removeClass();
		oUpperElement.addClass("chart_widget");
		oUpperElement.addClass("chart_drawing");

		var oResponse = poHttp.response;
		if (oResponse.data.length == 0){
			this.pr__no_data_found();
			return;
		}

		oUpperElement.empty();
		oUpperElement.append("Drawing Chart...");

		this.pr__draw_chart(poHttp.response);
	},

	//*******************************************************************
	onClickCSV: function(){
		var oConsts = this.consts;
		var oElement = this.element;

		var oParams={};
		oParams[cRenderQS.METRIC_QS]=oElement.attr(cRenderQS.METRIC_QS+"0");
		oParams[cRenderQS.APP_ID_QS]=oElement.attr(cRenderQS.APP_ID_QS);
		oParams[cRenderQS.TITLE_QS]=oElement.attr(cChartConsts.ATTR_TITLE+"0");
		oParams["csv"]=1;
		var sUrl = cBrowser.buildUrl(oElement.attr(cRenderQS.HOME_QS)+"/pages/"+oConsts.csv_url, oParams);
		window.open(sUrl);
	},

	//*******************************************************************
	onClickGo: function(){
		var oElement = this.element;
		document.location.href = oElement.attr(cChartConsts.ATTR_GO_URL);
	},

	//*******************************************************************
	onClickCompare: function(){
		var oConsts = this.consts;
		var oElement = this.element;

		var oParams={};
		oParams[cRenderQS.METRIC_QS]=oElement.attr(cRenderQS.METRIC_QS+"0");
		oParams[cRenderQS.APP_ID_QS]=oElement.attr(cRenderQS.APP_ID_QS);
		oParams[cRenderQS.TITLE_QS]=oElement.attr(cChartConsts.ATTR_TITLE+"0");
		var sUrl = cBrowser.buildUrl(oElement.attr(cRenderQS.HOME_QS)+"/"+oConsts.CHART_PHP_FOLDER+"/"+oConsts.compare_url, oParams);
		window.open(sUrl);
	},

	//*******************************************************************
	onClickZoom: function(){
		var oConsts = this.consts;
		var oElement = this.element;

		var oParams={};
		oParams[cRenderQS.METRIC_QS]=oElement.attr(cRenderQS.METRIC_QS+"0");
		oParams[cRenderQS.APP_ID_QS]=oElement.attr(cRenderQS.APP_ID_QS);
		oParams[cRenderQS.TITLE_QS]=oElement.attr(cChartConsts.ATTR_TITLE+"0");
		var sUrl = cBrowser.buildUrl(oElement.attr(cRenderQS.HOME_QS)+"/"+oConsts.CHART_PHP_FOLDER+"/"+oConsts.zoom_url, oParams);
		window.open(sUrl);
	},
	
	//*******************************************************************
	onClickStats: function(){
		var oConsts = this.consts;
		var oElement = this.element;

		var oParams={};
		oParams[cRenderQS.METRIC_QS]=oElement.attr(cRenderQS.METRIC_QS+"0");
		oParams[cRenderQS.APP_ID_QS]=oElement.attr(cRenderQS.APP_ID_QS);
		oParams[cRenderQS.TITLE_QS]=oElement.attr(cChartConsts.ATTR_TITLE+"0");
		var sUrl = cBrowser.buildUrl(oElement.attr(cRenderQS.HOME_QS)+"/pages/util/comparestats.php", oParams);
		//TBD not sure this is working correctly
		window.open(sUrl);
	},

	//#################################################################
	//# functions
	//#################################################################`
	pr__no_data_found: function(){
		var oThis = this;
		var oElement = this.element;
		var oConsts = this.consts;

		oElement.empty();
		oElement.removeClass();
		if (oElement.attr(cChartConsts.ATTR_HIDE_IF_NO_DATA)){
			oElement.addClass("charthidden");
			oElement.hide();
		}else {
			oElement.addClass("chartnodata");
			oElement.append("No data found for "+ oElement.attr(cChartConsts.ATTR_TITLE+"0"));
			oElement.height(oConsts.SHORT_NO_DATA_HEIGHT);
			if (oElement.attr(cChartConsts.ATTR_HIDE_GROUP_IF_NO_DATA)){
				//TODO hide parent if number of charts with data is 0
			}
		}
},

	//*******************************************************************
	pr__get_url: function (){
		var sUrl;
		var oConsts = this.consts;
		var oElement = this.element;

		var oParams = {};
		oParams[ cRenderQS.METRIC_QS ] = oElement.attr(cRenderQS.METRIC_QS+"0");
		oParams[ cRenderQS.APP_ID_QS ] = oElement.attr(cRenderQS.APP_ID_QS);
		oParams[ cRenderQS.DIV_QS ] = "";
		if (oElement.attr(cChartConsts.ATTR_PREVIOUS) == 1) {
			oParams[ cRenderQS.PREVIOUS_QS ] = 1;
			oElement.attr(cChartConsts.ATTR_TITLE+"0") = "(Previous) " + oElement.attr(cChartConsts.ATTR_TITLE+"0");
		}

		sUrl = cBrowser.buildUrl(oElement.attr(cRenderQS.HOME_QS)+"/"+this.consts.METRIC_API, oParams);
		return sUrl;
	},

	//*******************************************************************
	pr__draw_chart: function(poJson){
		var oThis = this;
		var oOptions = this.options;
		var oTopElement = this.element;
		var oUpperElement = $("#"+oOptions.pr__upper_div );
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
		for (var i=0; i<poJson.data.length; i++ ){
			var oItem = poJson.data[i];
			dDate = new Date(oItem.date);
			iValue = oItem.value;
			iItemMax = oItem.max;
			if (iItemMax == iValue) iItemMax = null;

			sMax= (iMax?"<br>Max: "+iItemMax:"");
			iMax = Math.max(iMax,iItemMax);
			iMaxObserved = Math.max(iMaxObserved,iValue);
			iSum += iValue;

			if (i==0)
				iMin = iValue;
			else
				iMin = Math.min(iMin, iValue);


			var sTooltip = "<div class='charttooltip'>value:" + iValue + sMax + "<br>" + dDate.toString() + "</i></div>";

			oData.addRow([dDate, iValue, iItemMax, sTooltip]);
		}
		iMax = Math.max(iMax, iMaxObserved);
		iAvgObs = Math.round(iSum/poJson.data.length);

		// set the display range of the chart to match the requested timerange
		oUpperElement.empty();

		// buttons the the left of the chart ----------------------------------------------
		var oSpan = $("<SPAN>", {class:"chartbuttonpanel"});

		var oButton = $("<button>",{class:"csv_button",title:"download as CSV"}).button({icon:"ui-icon-arrowthickstop-1-s"});
			oButton.click(		function(){ oThis.onClickCSV()}		);
		oSpan.append(oButton);

		var oButton = $("<button>",{class:"csv_button",title:"Zoom"}).button({icon:"ui-icon-arrow-4-diag"});
			oButton.click(		function(){ oThis.onClickZoom()}		);
		oSpan.append(oButton);
			
		var oButton = $("<button>",{class:"csv_button",title:"Compare"}).button({icon:"ui-icon-shuffle"});
			oButton.click(		function(){ oThis.onClickCompare()}		);
		oSpan.append(oButton);
		
		var oButton = $("<button>",{class:"csv_button",title:"Statistics"}).button({icon:"ui-icon-signal"});
			oButton.click(		function(){ oThis.onClickStats()}		);
		oSpan.append(oButton);

		if (oTopElement.attr(cChartConsts.ATTR_GO_URL)){
			var sGoLabel = "Go";
			if (oTopElement.attr(cChartConsts.ATTR_GO_LABEL)) sGoLabel = oTopElement.attr(cChartConsts.ATTR_GO_LABEL);
			var oButton = $("<button>",{class:"csv_button",title:sGoLabel}).button({icon:"ui-icon-arrowreturn-1-n"});
				oButton.click(		function(){ oThis.onClickGo()}		);
			oSpan.append(oButton);
		}
		oUpperElement.append(oSpan);

		// draw the chart ----------------------------------------------------------------
		var sChartID=oTopElement.attr("id")+"chart";
		var oChartDiv= $("<SPAN>",{
			id:sChartID, 	class:"chartgraph",
			width:oTopElement.attr(cChartConsts.ATTR_WIDTH)-this.consts.BUTTON_WIDTH-30, height:oTopElement.attr(cChartConsts.ATTR_HEIGHT) -5
		});
		oUpperElement.append(oChartDiv);

		var oDiv = oChartDiv[0];
		var dStart = new Date(poJson.epoch_start);
		var dEnd = new Date(poJson.epoch_end);
		if (oTopElement.attr(cChartConsts.ATTR_PREVIOUS) == 1){
			var iDiff = poJson.epoch_end - poJson.epoch_start;
			dEnd = new Date(poJson.epoch_start);
			dStart = new Date(dEnd - iDiff);
		}

		oChart = new google.visualization.LineChart( oDiv );
		var oChartOptions = {
			title: oTopElement.attr(cChartConsts.ATTR_TITLE+"0"),
			legend: "right",
			tooltip: {
				isHtml: true,
				style:{pointerEvents:'none'}
			},
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
			series: {
				0: {targetAxisIndex: 0, color:"blue", visibleInLegend:false},
				1: {targetAxisIndex: 1, color:"red", visibleInLegend:false}
			},
			vAxis: {
				0: {title: 'avg', textStyle: {color: 'blue'}},
				1: {title: 'max', textStyle: {color: 'red'}}
			},

			interpolateNulls: false
		};
		oChart.draw(oData, oChartOptions);

		//display maximumes and observed values --------------------------------------
		var oInfoDiv = $("#"+oOptions.pr__lower_div );
		oInfoDiv.empty();
		oInfoDiv.append("Max: "+ iMax + "<br>");
		oInfoDiv.append("Avg: "+ iAvgObs + "<br>");
		oInfoDiv.append("Min: "+ iMin + "<br>");
	}
});