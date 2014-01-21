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
	
	var isSearLineInit = false;
	NS.API.searchLineInit = function(){
		if (isSearLineInit){ return; }
		isSearLineInit = true;

		var elCont = Dom.getElementsByClassName('aw-eshop-searchline');
		
		if (elCont.length == 0){ return; }
		
		for (var i=0;i<elCont.length;i++){
			var elInput = Dom.getElementsByClassName('sinput', 'input', elCont[i])[0],
				elForm = Dom.getElementsByClassName('sform', 'form', elCont[i])[0],
				elLoading = Dom.getElementsByClassName('loading', '', elCont[i])[0],
				elAc = Dom.getElementsByClassName('autocompletecont', '', elCont[i])[0],
				elFValue = Dom.getElementsByClassName('sfilter', 'select', elCont[i])[0],
				elFField = Dom.getElementsByClassName('sfilterfield', 'input', elCont[i])[0];

			if (!L.isValue(elInput) || !L.isValue(elAc)){ continue; }
			
			Dom.setStyle(elLoading, 'display', '');
			
			Brick.ff('eshop', 'autocomplete', function(){
				Dom.setStyle(elLoading, 'display', 'none');
				
				var ds = new YAHOO.util.XHRDataSource('/ajax/eshop/js_search/');
			    ds.connMethodPost = true;  
			    ds.responseSchema = {recordDelim:"\n", fieldDelim: "\t"};
			    ds.responseType = YAHOO.util.XHRDataSource.TYPE_TEXT;
			    ds.maxCacheEntries = 60;

				var oAC = new YAHOO.widget.AutoComplete(elInput, elAc, ds);
				oAC.animSpeed = 0.1;
				oAC.minQueryLength = 2;
				oAC.animHoriz = false;
				oAC.animVert = false;
				oAC.maxResultsDisplayed = 20; 

				if (L.isValue(elFField) && L.isValue(elFValue)){
					oAC.generateRequest = function(q){
						return "eff="+ encodeURIComponent(elFField.value)
							+"&ef="+encodeURIComponent(elFValue.value)+"&query="+q;
					};
				}
				
				oAC.itemSelectEvent.subscribe(function(sType, aArgs){
					if (L.isValue(elForm) && L.isFunction(elForm.submit)){
						elForm.submit();
					}
				});				
				
			});
		}
	};
	
};