'use strict';
//the callback 
function remotedata_jsonCallBack( poJson){
	var iErrors, iMax, iAvg, sID;
	
	sID= poJson.id;

	if (poJson.error){
		write_console("error " + sID);
		document.getElementById("R"+sID).style.display = 'none'
	}else{
		write_console("got " + sID);
		iErrors = "";
		iMax = "";
		iAvg = "";
		
		if (poJson.max){
			iMax = poJson.max.max;
			iAvg = poJson.max.value;
		}
		if (poJson.transErrors)
			iErrors = poJson.transErrors.value;
			
		document.getElementById("R"+sID+"_err").innerHTML = iErrors; 
		document.getElementById("R"+sID+"_max").innerHTML = iMax; 
		document.getElementById("R"+sID+"_avg").innerHTML = iAvg; 
	}
	
}

function remotedata_getUrl(poItem){
	var sUrl;
	sUrl="rest/getSummary.php?app="+poItem.app+"&tier="+poItem.tier+"&trans="+poItem.trans+"&id="+poItem.index;
	write_console("fetching: " + sUrl);
	return sUrl;
}



