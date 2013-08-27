/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['dom']

};
Component.entryPoint = function(NS){

	var Dom = YAHOO.util.Dom,
		L = YAHOO.lang;
	
	var autocompleteInit = function(input, container){
	    var ds = new YAHOO.util.XHRDataSource('/ajax/eshop/js_search/');
	    ds.connMethodPost = true;  
	    ds.responseSchema = {recordDelim:"\n", fieldDelim: "\t"};
	    ds.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
	    ds.maxCacheEntries = 60;

		var oAC = new YAHOO.widget.AutoComplete(input, container, ds);
		oAC.delimChar = [",",";"]; // Enable comma and semi-colon delimiters
	};

	var isSearLineInit = false;
	NS.API.searchLineInit = function(){
		if (isSearLineInit){ return; }
		isSearLineInit = true;

		var elCont = Dom.getElementsByClassName('aw-eshop-searchline');
		
		if (elCont.length == 0){ return; }
		
		for (var i=0;i<elCont.length;i++){
			var elInput = Dom.getElementsByClassName('sinput', 'input', elCont[i])[0],
				elLoading = Dom.getElementsByClassName('loading', '', elCont[i])[0],
				elAc = Dom.getElementsByClassName('autocompletecont', '', elCont[i])[0];

			if (!L.isValue(elInput) || !L.isValue(elAc)){ continue; }
			
			Dom.setStyle(elLoading, 'display', '');
			
			Brick.ff('eshop', 'autocomplete', function(){
				autocompleteInit(elInput, elAc);
				Dom.setStyle(elLoading, 'display', 'none');
			});
		}
	};
	
};