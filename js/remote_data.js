'use strict'
/*global cCommon,cRenderQS */

//the callback 
// eslint-disable-next-line no-unused-vars
function remotedata_jsonCallBack( poJson){
	var iErrors, iMax, iAvg, sID
	
	sID= poJson.id

	if (poJson.error){
		cCommon.writeConsole("error " + sID)
		document.getElementById("R"+sID).style.display = 'none'
	}else{
		cCommon.writeConsole("got " + sID)
		iErrors = ""
		iMax = ""
		iAvg = ""
		
		if (poJson.max){
			iMax = poJson.max.max
			iAvg = poJson.max.value
		}
		if (poJson.transErrors)
			iErrors = poJson.transErrors.value
			
		document.getElementById("R"+sID+"_err").innerHTML = iErrors 
		document.getElementById("R"+sID+"_max").innerHTML = iMax 
		document.getElementById("R"+sID+"_avg").innerHTML = iAvg 
	}
	
}

// eslint-disable-next-line no-unused-vars
function remotedata_getUrl(poItem){
	
	var aParts = [
		"rest/getSummary.php?",
		cRenderQS.APP_QS,"=",poItem.app,"&",
		cRenderQS.TIER_QS,"=",poItem.tier,"&",
		cRenderQS.TRANS_QS,"=",poItem.trans,"&",
		cRenderQS.ID_QS,"=",poItem.index
	]
	var sUrl = aParts.join("") 
	cCommon.writeConsole("fetching: " + sUrl)
	return sUrl
}



