/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
		{name: 'catalog', files: ['catalog.js','eltype.js']},
		{name: '{C#MODNAME}', files: ['lib.js']}
	]
};
Component.entryPoint = function(NS){
	
	var Dom = YAHOO.util.Dom,
		L = YAHOO.lang,
		LNG = this.language,
		buildTemplate = this.buildTemplate;
	
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
			new Brick.mod.catalog.API.showManagerWidget({
				'container': this.gel('widget'), 
				'mmPrefix': 'eshop'
			});
		}
	});
	NS.CatalogManagerWidget = CatalogManagerWidget;
};