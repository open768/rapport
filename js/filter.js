'use strict';
var cFilterFunctions={
	selector:null,
	parent_selector:null,
	attr:null,
	id:"filter",
	
	//**********************************************************************************
	filter_box: function(psContainerID, psCaption, psSelector, psAttr, psParentSelector){
		var oThis=this;
		
		this.selector = psSelector;
		this.attr = psAttr;
		this.parent_selector = psParentSelector;
		
		var sHTML = '<form action="#">' +
			'<div class="mdl-textfield mdl-js-textfield">' +
				'<input class="mdl-textfield__input" type="text" id="' + this.id + '" disabled>' +
				'<label class="mdl-textfield__label" for="' + this.id + '">' + psCaption + '...</label>' +
			'</div>' +
		'</form>';
		var oDiv = $("#"+psContainerID);
		oDiv.empty();
		oDiv.append(sHTML);
		
		this.setOnKeyUp();			
	},
	
	//**********************************************************************************
	setOnKeyUp: function(){
		var oThis=this;
		$("#" + this.id ).prop( "disabled", false );
		$("#" + this.id ).keyup( 
			function(poEvent){
				oThis.onFilterKeyUp(poEvent);
			}
		);
	},
	
	//**********************************************************************************
	onFilterKeyUp: function( poEvent){
		//look through divs with selector
		var oThis = this;
		var aSelected = $(oThis.selector);
		var sInput = $("#filter").val().toLowerCase();
		//iterate
		aSelected.each(
			function(index){
				//set up variables
				var oParent=$(this).closest(oThis.parent_selector);
				var oHider = oParent;
				var oTRParent = $(this).closest("TR");					
				if (oTRParent.length > 0){
					 if (oTRParent.parents().length > oParent.parents().length) //element is in a table
						oHider = oTRParent;
				}
				
				//check minimum length of search expression
				if (sInput.length < 3){	//must be at least 3 chars
					oHider.show();
					return;
				}

				//perform search
				var oEl = $(this);
				var sSearch;
				if (oThis.attr == "")
					sSearch = oEl[0].innerText;
				else
					sSearch = oEl.attr(oThis.attr); 
				
				if (sSearch) { //skip selected that dont have the desired attribute
					sSearch = sSearch.toLowerCase();
					if ( sSearch.indexOf(sInput) == -1)	////check the attribute for a match
						oHider.hide();
					else{
						oHider.show();
						oParent.show();
					}
				}
			}
		);
	}
}