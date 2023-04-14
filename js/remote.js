'use strict'
/*global cCommon,cDebug,bean */
class cRemoteData {
	oItem = null
	oJson = null
}

// eslint-disable-next-line no-unused-vars
class cRemote {
	constructor() {
		this.iActive = 0
		this.aItems = []
		this.iNextUp = 0
		this.iMaxAsync = 5
		this.fnUrlCallBack = null
		this.ajaxTimeout = 30000
		this.stopping = false
	}
	//********************************************************************************
	go() {
		if (this.stopping)
			return

		if (this.iActive < this.iMaxAsync) {
			if (this.iNextUp < this.aItems.length) {
				var iItem = this.iNextUp
				this.iNextUp++
				this.iActive++
				var oItem = this.aItems[iItem]
				cCommon.writeConsole("go: item " + iItem)

				this.loadData(oItem, iItem) //this is async so will come back immediately
				this.go()
			}
		}
		else
			cCommon.writeConsole("waiting..")
	}

	//********************************************************************************
	loadData(poItem, piIndex) {
		if (this.stopping)
			return
		var oParent = this

		var sUrl = this.fnUrlCallBack(poItem)
		poItem.url = sUrl

		cDebug.write(sUrl)
		return $.ajax({
			url: sUrl,
			dataType: "json",
			async: true,
			success: function (psResult) { oParent.OnJSON(psResult, piIndex, sUrl) },
			error: function () { oParent.OnJSONError(piIndex, sUrl) },
			timeout: this.ajaxTimeout
		})
	}

	//********************************************************************************
	OnJSONError(piIndex, psUrl) {
		if (this.stopping)
			return
		cCommon.writeConsole("OnJSONerror: (" + piIndex + ") " + psUrl)

		var oData = new cRemoteData()
		oData.oItem = this.aItems[piIndex]

		bean.fire(this, "onJSONError", oData)
		this.iActive--
		this.go()
	}

	//********************************************************************************
	OnJSON(poJson, piIndex, psUrl) {
		if (this.stopping)
			return
		cCommon.writeConsole("OnJSON: (" + piIndex + ") " + psUrl)

		var oData = new cRemoteData()
		oData.oItem = this.aItems[piIndex]
		oData.oJson = poJson

		bean.fire(this, "onJSON", oData)
		this.iActive--
		this.go()
	}
}
