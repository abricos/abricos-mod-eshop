/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = { 
	mod:[
        {name: 'widget', files: ['notice.js']},
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
		'catalogman': function(){
			return WS+'catalog/CatalogManagerWidget/';
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

	var Manager = function (callback){
		this.init(callback);
	};
	Manager.prototype = {
		init: function(callback){
			NS.manager = this;
			
			this.users = Brick.mod.uprofile.viewer.users;
			
			var __self = this;
			R.load(function(){
				NS.life(callback, __self);
			});
		},
		ajax: function(data, callback){
			data = data || {};

			Brick.ajax('{C#MODNAME}', {
				'data': data,
				'event': function(request){
					NS.life(callback, request.data);
				}
			});
		}
	};
	NS.manager = null;
	
	NS.initManager = function(callback){
		if (L.isNull(NS.manager)){
			NS.manager = new Manager(callback);
		}else{
			NS.life(callback, NS.manager);
		}
	};
	
};