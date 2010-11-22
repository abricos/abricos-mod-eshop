/*
* @version $Id$
* @copyright Copyright (C) 2008 Abricos All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Обработчик интерфейса размещенного на страницах сайта
var Component = new Brick.Component();
Component.requires = {
	yahoo: ['dom'],
	mod:[{name: 'sys', files: ['wait.js']}]
};
Component.entryPoint = function(){
	
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var NS = this.namespace, 
		API = NS.API,
		F = Brick.Component.API.fire,
		FF = Brick.Component.API.fireFunction;
	
(function(){

	var LW = Brick.widget.LayWait;
	
	var gel = function(id){
		return L.isString(id) ? Dom.get(id) : id;
	};
	var gval = function(id){
		var el = gel(id);
		if (L.isNull(el)){ return ''; }
		return el.value;
	};
	var sval = function(id, val){
		var el = gel(id);
		if (L.isNull(el)){ return; }
		el.value = val;
	};
	var find = function(el, className, cnt){
		if (Dom.hasClass(el, className)){ return el; }
		cnt = (cnt || 0)+1;
		if (L.isNull(el) || el.parentNode == document.body || cnt > 30){
			return false;
		}
		return find(el.parentNode, className);
	};
	var parseid = function(el, pfx){
        var trim = YAHOO.lang.trim;
		pfx = trim(pfx.toUpperCase());
        var arr = Dom.getAttribute(el, "className").toUpperCase().split(' ');
        for (var i=0;i<arr.length;i++){
        	var arr1 = trim(arr[i]).split('-');
        	if (arr1.length == 2 && arr1[0] == pfx){
        		return arr1[1]*1; 
        	}
        }
        return 0;
	};
	
	var getFElCl = function(className){
		var els = Dom.getElementsByClassName(className);
		return els.length > 0 ? els[0] : null;
	};
	
	/**
	 * Прикрутить событие на клик всех кнопок, чтобы отловить клики 
	 * "Добавить в корзину", "Показать корзину" и т.п.  
	 */
	API.adminUIInit = function(elId){
		var container = Dom.get(elId) || document.body;
		if (L.isNull(container)){ return; }
		
		var onClick = function(el){

			var fel;
			
			if (fel=find(el, 'btn-product-append')){
				var lw = new LW(fel.parentNode, true);
				
				var catalogid = parseid(fel, 'catalogid');
				
				FF('eshop', 'cart', function(){
					Brick.ff('catalog', 'element', function(){
						lw.hide();
						Brick.mod.catalog.API.elementAppend(catalogid, 0, 'eshop');
					});

				});

			}else {
				return false;
			}
			return true;
		};
		
		E.on(container, 'click', function(e){
			var el = E.getTarget(e);
			if (onClick(el)){ E.preventDefault(e); }
		});
	};

})();
};
