'use strict';
function common_onListChange(){
	//stop all remote activity
	if (typeof cRemote != 'undefined')
		cRemote.stopping = true;
	document.location.href=$(this).val();
}
