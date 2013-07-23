/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = { 
	mod:[
        {name: 'catalog', files: ['lib.js']},
        {name: '{C#MODNAME}', files: ['roles.js']}
	]		
};
Component.entryPoint = function(NS){

	var L = YAHOO.lang,
		R = NS.roles;
	
	var SysNS = Brick.mod.sys;
	var LNG = this.language;
	var NSCat = Brick.mod.catalog;

	var buildTemplate = this.buildTemplate;
	buildTemplate({},'');
	
	NS.lif = function(f){return L.isFunction(f) ? f : function(){}; };
	NS.life = function(f, p1, p2, p3, p4, p5, p6, p7){
		f = NS.lif(f); f(p1, p2, p3, p4, p5, p6, p7);
	};
	NS.Item = SysNS.Item;
	NS.ItemList = SysNS.ItemList;
	
	NS.ORDERSTATUS = {
		'NEW': 0,
		'EXEC': 1,
		'ARCHIVE': 2,
		'REMOVED': 3
	};
	
	var CatalogItem = function(manager, d){
		CatalogItem.superclass.constructor.call(this, manager, d);
	};
	YAHOO.extend(CatalogItem, NSCat.CatalogItem, {
		update: function(d){
			this._urlCache = null;
			CatalogItem.superclass.update.call(this, d);
		},
		url: function(){
			if (!L.isNull(this._urlCache)){ return this._urlCache; }
			var url = "/eshop/", pline = this.getPathLine();
			for (var i=1;i<pline.length;i++){
				url += pline[i].name+'/';
			}
			
			this._urlCache = url;
			return url;
		}
	});
	NS.CatalogItem = CatalogItem;
	
	var Element = function(manager, d){
		Element.superclass.constructor.call(this, manager, d);
	};
	YAHOO.extend(Element, NSCat.Element, {
		update: function(d){
			this._urlCache = null;
			Element.superclass.update.call(this, d);
		},
		url: function(){
			if (!L.isNull(this._urlCache)){ return this._urlCache; }
			
			var cat = this.manager.catalogList.find(this.catid);
			
			this._urlCache = cat.url() + 'product_'+this.id;;
			return this._urlCache;
		}
	});
	NS.Element = Element;

	
	var WS = "#app={C#MODNAMEURI}/wspace/ws/";
	NS.navigator = {
		'home': function(){ return WS; },
		'catalogman': function(catid){
			var link = WS+'catalog/CatalogManagerWidget/';
			if (catid && catid*1>0){
				link += catid+'/';
			}
			return link;
		},
		'billing': function(){
			return WS+'billing/BillingWidget/';
		},
		'config': function(){
			return WS+'manager/ConfigWidget/';
		},
		'cartbilling': function(){
			return WS+'eshopcart/CartBillingWidget/';
		},
		'cartconfig': function(){
			return WS+'eshopcart/CartConfigWidget/';
		},
		'about': function(){
			return WS+'about/AboutWidget/';
		},
		'go': function(url){
			Brick.Page.reload(url);
		}
	};	
	
	
	NS.manager = null;
	
	NS.initManager = function(callback){
		NSCat.initManager('{C#MODNAME}', callback, {
			'CatalogItemClass': NS.CatalogItem,
			'ElementClass': NS.Element,
			'language': LNG
		});
	};
	
};