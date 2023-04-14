'use strict'
/*global cRenderQS, cCommon*/
/**************************************************************************
Copyright (C) Chicken Katsu 2013-2018 

This code is protected by copyright under the terms of the 
Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International License
http://creativecommons.org/licenses/by-nc-nd/4.0/legalcode

For licenses that allow for commercial use please contact cluck@chickenkatsu.co.uk

// USE AT YOUR OWN RISK - NO GUARANTEES OR ANY FORM ARE EITHER EXPRESSED OR IMPLIED
**************************************************************************/

// eslint-disable-next-line no-unused-vars
class cTransFlow {
	constructor(psDivID) {
		this.div_id = psDivID
		this.data_url = "rest/getTransFlow.php"
		this.home = "."
	}
	//**********************************************************************
	OnJSON(poResult) {
		var oDiv = $("#" + this.div_id)
		$("#" + this.div_id).css('height', 'auto')
		oDiv.empty()
		this.build_html(oDiv, poResult)
	}

	//**********************************************************************
	build_html(poContainer, poData) {
		var oList, oItem, oChild, i

		poContainer.append(poData.name)
		oList = $("<OL>")
		for (i = 0; i < poData.children.length; i++) {
			oChild = poData.children[i]
			oItem = $("<LI>")
			this.build_html(oItem, oChild)
			oList.append(oItem)
		}
		poContainer.append(oList)
	}

	//**********************************************************************
	OnJSONError() {
		$("#" + this.div_id).html("Oops there was an error getting the transaction map")
		$("#" + this.div_id).height(20)
	}

	//**********************************************************************
	load(psApp, psTier, psTrans) {
		var oParent = this
		var oOptions = {}
		oOptions[cRenderQS.APP_QS] = psApp
		oOptions[cRenderQS.TIER_QS] = psTier
		oOptions[cRenderQS.TRANS_QS] = psTrans
		var sUrl = this.home + "/" + this.data_url + "?" + $.param(oOptions)
		$("#" + this.div_id).html("loading transaction flow")
		cCommon.writeConsole("transaction flow url: " + sUrl)
		return $.ajax({
			url: sUrl,
			dataType: "json",
			async: true,
			success: function (psResult) { oParent.OnJSON(psResult) },
			error: function () { oParent.OnJSONError() },
			timeout: this.ajaxTimeout
		})
	}
}