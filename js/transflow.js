/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

function cTransFlow( psDivID){
	this.div_id = psDivID;
	this.data_url = "rest/getTransFlow.php";
	this.home = ".";
	this.APP_QS = "";
	this.TIER_QS = "";
	this.TRANS_QS = "";
	
	//**********************************************************************
	this.OnJSON = function(poResult){
		var oDiv = $("#"+this.div_id);
		$("#"+this.div_id).css('height','auto');
		oDiv.empty();
		this.build_html(oDiv, poResult);
	};
	
	//**********************************************************************
	this.build_html = function(poContainer, poData){
		var oList, oItem, oChild, i;
		
		poContainer.append(poData.name);
		oList = $("<OL>");
		for (i = 0; i < poData.children.length;i++ ){
			oChild = poData.children[i];
			oItem = $("<LI>");
			this.build_html(oItem, oChild);
			oList.append(oItem);
		}
		poContainer.append(oList);
	};
	
	//**********************************************************************
	this.OnJSONError = function(){
		$("#"+this.div_id).html("Oops there was an error getting the transaction map");
		$("#"+this.div_id).height(20);
	};
	
	//**********************************************************************
	this.load = function(psApp, psTier, psTrans) {
		var oParent = this;
		var oOptions = {};
			oOptions[this.APP_QS] = psApp;
			oOptions[this.TIER_QS] = psTier;
			oOptions[this.TRANS_QS]= psTrans;
		var sUrl = this.home + "/" + this.data_url + "?" + $.param(oOptions);
		$("#"+this.div_id).html("loading transaction flow")
		write_console("transaction flow url: "+ sUrl);
		return $.ajax({ //default is async
          url: sUrl,
          dataType: "json",
          async: true,
		  success: function(psResult){oParent.OnJSON(psResult)},
		  error: function(){oParent.OnJSONError()},
		  timeout:this.ajaxTimeout
        });
	}
}