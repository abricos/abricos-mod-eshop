/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
		{name: 'catalog', files: ['catalogmanager.js']},
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
			this.manager = null;
			this.catalogManagerWidget = null;
		},
		destroy: function(){
			if (!L.isNull(this.catalogManagerWidget)){
				this.catalogManagerWidget.destroy();
			}
			CatalogManagerWidget.superclass.destroy.call(this);
		},
		onLoad: function(catid){
			var __self = this;
			NS.initManager(function(man){
				__self._onLoadManager(man);
			});
		},
		_onLoadManager: function(man){
			this.manager = man;
			this.elHide('loading');
			this.catalogManagerWidget = new NSCat.CatalogManagerWidget(this.gel('view'), man);
		}
	});
	NS.CatalogManagerWidget = CatalogManagerWidget;
};