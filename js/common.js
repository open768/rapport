'use strict'
/* globals cRemote */

// eslint-disable-next-line no-unused-vars
function common_onListChange(){
	//stop all remote activity
	if (typeof cRemote != 'undefined')
		cRemote.stopping = true
	window.stop()
	document.location.href=$(this).val()
}
