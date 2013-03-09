/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
		{name: 'catalog', files: ['board.js']},
		{name: '{C#MODNAME}', files: ['lib.js']}
	]
};
Component.entryPoint = function(NS){
	
	var Dom = YAHOO.util.Dom,
		L = YAHOO.lang,
		LNG = this.language,
		buildTemplate = this.buildTemplate;
	
	var NSCat = Brick.mod.catalog;
	
	var CatalogManagerWidget = function(container){
		CatalogManagerWidget.superclass.constructor.call(this, container, {
			'buildTemplate': buildTemplate, 'tnames': 'widget' 
		});
	};
	YAHOO.extend(CatalogManagerWidget, Brick.mod.widget.Widget, {
		init: function(){
			this.wsMenuItem = 'catalog'; // использует wspace.js
		},
		onLoad: function(catid){
			var __self = this;
			NS.initManager(function(){
				__self._onLoadManager();
			});
		},
		_onLoadManager: function(){
			this.widget = new NSCat.BoardWidget('{C#MODNAME}', this.gel('widget'));
		}
	});
	NS.CatalogManagerWidget = CatalogManagerWidget;
};