'use strict';
function common_onListChange(){
	//stop all remote activity
	if (typeof cRemote != 'undefined')
		cRemote.stopping = true;
	window.stop();
	document.location.href=$(this).val();
}
