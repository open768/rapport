var DEBUG_ON = false;

function cRemoteData(){
	this.oItem = null;
	this.oJson = null;
}

function cRemote(){
	this.iActive=0;
	this.aItems=[];
	this.iNextUp=0;
	this.iMaxAsync=5;
	this.fnUrlCallBack = null;
	this.ajaxTimeout=30000;
	this.stopping = false;
	
	//********************************************************************************
	this.go = function()
	{
		if (this.stopping) return;
		
		if (this.iActive < this.iMaxAsync){
			if (this.iNextUp < this.aItems.length){	
				var iItem = this.iNextUp;
				this.iNextUp++;
				this.iActive++;
				var oItem = this.aItems[iItem ];
				write_console("go: item " + iItem );
				
				this.loadData(oItem, iItem);	//this is async so will come back immediately
				this.go();
			}
		}else
			write_console("waiting..");
	};
	
	//********************************************************************************
	this.loadData = function(poItem, piIndex){
		if (this.stopping) return;
		var oParent = this;
		
		var sUrl = this.fnUrlCallBack(poItem);
		poItem.url = sUrl;
		
		debug_console(sUrl);
		return $.ajax({ //default is async
          url: sUrl,
          dataType: "json",
          async: true,
		  success: function(psResult){oParent.OnJSON(psResult, piIndex, sUrl)},
		  error: function(){oParent.OnJSONError(piIndex, sUrl)},
		  timeout:this.ajaxTimeout
          });
	};
	
	//********************************************************************************
	this.OnJSONError = function(piIndex, psUrl){
		if (this.stopping) return;
		write_console("OnJSONerror: ("+piIndex+") " + psUrl);
		
		var oData = new cRemoteData();
		oData.oItem = this.aItems[piIndex ];
		
		bean.fire(this,"onJSONError",oData);
		this.iActive--;
		this.go();
	};
	
	//********************************************************************************
	this.OnJSON = function(poJson, piIndex, psUrl){
		if (this.stopping) return;
		write_console("OnJSON: ("+piIndex+") " + psUrl);
		
		var oData = new cRemoteData();
		oData.oItem = this.aItems[piIndex ];
		oData.oJson = poJson;
		
		bean.fire(this,"onJSON",oData);
		this.iActive--;
		this.go();
	}
	
};


function debug_console(psMessage){
	if (DEBUG_ON && console) console.log("DEBUG> " + psMessage);
}
function write_console(psMessage){
	if (console) console.log(psMessage);
}
