'use strict';
var cFilterFunctions={
	selector:null,
	parent_selector:null,
	attr:null,
	id:"filter",
	
	//**********************************************************************************
	filter_box: function(psCaption, psSelector, psAttr, psParentSelector){
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
		document.writeln(sHTML);
		
		$( function(){oThis.setOnKeyUp()});			
	},
	
	//**********************************************************************************
	setOnKeyUp: function(psID){
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
		//look through divs with selectmenu
		var oThis = this;
		var aSelected = $(oThis.selector);
		var sInput = $("#filter").val().toLowerCase();
		//iterate
		aSelected.each(
			function(index){
				
				var oEl = $(this);
				var sAttr = oEl.attr(oThis.attr); 
				if (sAttr) { //skip selected that dont have the desired attribute
					
					var oParent=$(this).closest(oThis.parent_selector);
					var oTRParent = $(this).closest("TR");					
					var oHider = oParent;
					if (oTRParent.length > 0){
						 if (oTRParent.parents().length > oParent.parents().length) //element is in a table
							oHider = oTRParent;
					}
					
					
					if (sInput.length < 3){	//must be at least 3 chars
						oHider.show();
					}else{
						sAttr = sAttr.toLowerCase();
						if ( sAttr.indexOf(sInput) == -1)	////check the attribute for a match
							oHider.hide();
						else{
							oHider.show();
							oParent.show();
						}
					}
				}
			}
		);
	}
}