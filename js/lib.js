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

	var buildTemplate = this.buildTemplate;
	buildTemplate({},'');
	
	NS.lif = function(f){return L.isFunction(f) ? f : function(){}; };
	NS.life = function(f, p1, p2, p3, p4, p5, p6, p7){
		f = NS.lif(f); f(p1, p2, p3, p4, p5, p6, p7);
	};
	NS.Item = SysNS.Item;
	NS.ItemList = SysNS.ItemList;
	
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
		'about': function(){
			return WS+'about/AboutWidget/';
		},
		'go': function(url){
			Brick.Page.reload(url);
		}
	};	
	
	var Manager = function(modname, callback){
		NS.manager = this;
		
		Manager.superclass.constructor.call(this, '{C#MODNAME}', callback);
	};
	YAHOO.extend(Manager, Brick.mod.catalog.Manager, {
		
	});
	NS.manager = null;
	
	NS.initManager = function(callback){
		Brick.mod.catalog.initManager('{C#MODNAME}', callback, {
			'language': LNG
		});
	};
	
};