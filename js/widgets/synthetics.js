//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
var cSynthetics = {
	queue: new cHttpQueue,
	METRIC_API:"rest/getMetric.php",
	SYNLIST_API:"rest/getSynList.php"
};
cSynthetics.queue.maxTransfers = 3	; 	//dont overload the controller


$.widget( "ck.adsynlist",{
	//#################################################################
	//# Definition
	//#################################################################
	options:{
		home: null,
		app: null,
		app_id: null
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
		
		//check for required options
		var oOptions = this.options;
		if (oOptions.home==null){		$.error("home is missing! check options");	}
		if (oOptions.app==null){		$.error("app is missing! check options");	}
		if (oOptions.app_id==null){		$.error("app id is missing! check options");	}
				
		//set display style
		oElement.removeClass();
	
		//load content
		this.pr__load_synthetics();
	},
	
	//#################################################################
	//# privates
	//#################################################################`
	pr__load_synthetics: function(){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;
		var oConsts = this.consts;
		
		oElement.empty();
		oElement.append("initialising Synthetics for " + oOptions.app);
		
		//create http object and add to the queue
		var oParams = {};
		oParams[ cRender.APP_QS ] = oOptions.app;
		oParams[ cRender.APP_ID_QS ] = oOptions.app_id;
		oParams[ cRender.SYNTH_DETAILS_QS] = 0;
		sUrl = cBrowser.buildUrl(this.options.home+"/"+cSynthetics.SYNLIST_API, oParams);

		var oItem = new cHttpQueueItem();
		oItem.url = sUrl;

		bean.on(oItem, "start", 	function(){oThis.onStart(oItem);}	);				
		bean.on(oItem, "result", 	function(poHttp){oThis.onResponse(poHttp);}	);				
		bean.on(oItem, "error", 	function(poHttp){oThis.onError(poHttp);}	);				
		cSynthetics.queue.add(oItem);

	},
	
	//#################################################################
	//# Events
	//#################################################################`
	onStart: function(){
		var oThis = this;
		var oElement = oThis.element;
		oElement.append("...loading");
	},
	
	onError: function(){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;
		oElement.empty();
		oElement.addClass("ui-state-error");
		oElement.append("There was an error getting synthetic data for " + oOptions.app);
		
		setTimeout( function(){oThis.element.hide()}, 500);
	},
	
	onResponse: function(poHttp){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;

		oElement.empty();
		var aJobs =poHttp.response; 
		if (aJobs.length == 0){
			oElement.append("No Synthetic jobs found for " + oOptions.app);
			setTimeout( function(){oThis.element.hide()}, 500);
		}else{
			var oParams={app:oOptions.app,aid:oOptions.app_id};
			var sUrl = cBrowser.buildUrl(oOptions.home + "/pages/rum/synthetic.php", oParams);
			var oLink = $("<a>", {href:sUrl}).append("See Synthetic jobs for: " + oOptions.app) ;
			var oList = $("<ul>");
			oElement.append(oLink );
			for (var i=0; i<aJobs.length; i++){
				var oJob =  aJobs[i];
				var oLi = $("<li>").append(oJob.name);
				oList.append(oLi);
			}
			oElement.append(oList );
		}
	},
	
});
function cVisJSData(){
	this.chart = null;
	this.data = new vis.DataSet();
	this.groups = new vis.DataSet();
};

//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
$.widget( "ck.appdsyntimeline",{
	//#################################################################
	//# Definition
	//#################################################################
	options:{
		timeperiod:2,
		home: null,
		app: null,
		app_id: null,
		
		visjs_data: null
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
		if (typeof bean === 'undefined')	$.error("bean class is missing! check includes");
		if (typeof cHttp2 === 'undefined')	$.error("http2 class is missing! check includes");
		if (!this.element.gSpinner) 		$.error("gSpinner is missing! check includes");
		if (typeof parseURL === 'undefined') $.error("parseURL is missing! check includes");
		if (typeof vis === 'undefined') 	$.error("visjs is missing! check includes");
		
		//check for required options
		var oOptions = this.options;
		if (oOptions.home== null){		$.error("home is missing! check options");	}
		
		var oURI = parseURL(document.URL);
		oOptions.app = oURI.params.app;
		oOptions.app_id = oURI.params.aid;
		
		this.visjs_data = new cVisJSData;
		
		//set display style
		oElement.removeClass();
	
		//load content
		this.pr__load_synthetics();
	},
	
	//#################################################################
	//# privates
	//#################################################################`
	pr__load_synthetics: function(){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;
		var oConsts = this.consts;
		
		oElement.empty();
		var oLoader = $("<DIV>");
		oLoader.gSpinner({scale: .25});
		oElement.append(oLoader).append("Loading: Synthetics");
				
		var oParams = {};
		oParams[ cRender.APP_QS ] = oOptions.app;
		oParams[ cRender.APP_ID_QS ] = oOptions.app_id;
		oParams[ cRender.SYNTH_DETAILS_QS] = 1;
		sUrl = cBrowser.buildUrl(this.options.home+"/"+cSynthetics.SYNLIST_API, oParams);
		
		ohttp = new cHttp2();
		bean.on(ohttp,"result",		function(poHttp){ oThis.onLoadSynNames(poHttp)}	);
		bean.on(ohttp,"error",		function(poHttp) {oThis.onErrSynNames()}		);
		ohttp.fetch_json(sUrl);
	},
	
	//#################################################################
	//# Events
	//#################################################################`
	onErrSynNames: function(){
		var oThis = this;
		var oElement = oThis.element;
		oElement.empty();
		oElement.addClass("ui-state-error");
		oElement.append("unable to get synthetic data for " + oOptions.app);
	},
	
	//*******************************************************************
	onLoadSynNames: function(poHttp){
		var oThis = this;
		var oElement = oThis.element;
		var oOptions = this.options;

		oElement.empty();
		
		var oData = poHttp.response;
		if (oData.error){
			oElement.addClass("ui-state-error");
			oElement.append("there are no synthetic Jobs for " + oOptions.app);
		}else{
			for ( var i=0 ; i< oData.length; i++){
				var oItem = oData[i];
				oElement.append("<li>" + oItem.name)
			}
			
			//create a vis.js graph 
			//Queue fetching results for each monitor
		}
	},
	
});