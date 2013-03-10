/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
		{name: 'catalog', files: ['catalogexplore.js']},
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
			this.treeWidget = null;
			this.catItemViewWidget = null;
			this.manager = null;
		},
		onLoad: function(catid){
			var __self = this;
			NS.initManager(function(man){
				__self._onLoadManager(man);
			});
		},
		destroy: function(){
			if (!L.isNull(this.treeWidget)){
				this.treeWidget.destroy();
			}
			if (!L.isNull(this.catItemViewWidget)){
				this.catItemViewWidget.destroy();
			}
			CatalogManagerWidget.superclass.destroy.call(this);
		},
		_onLoadManager: function(man){
			this.manager = man;
			this.elHide('loading');
			this.treeWidget = new NSCat.CatalogTreeWidget(this.gel('explore'), man.catalogList, {
				'rootItem': {
					'title': 'Каталог товаров'
				}
			});
			this.treeWidget.selectedItemEvent.subscribe(this.onSelectedCatalogItem, this, true);
			
			this.showCatalogViewWidget(0);
		},
		onSelectedCatalogItem: function(evt, prms){
			var cat = prms[0];
			this.showCatalogViewWidget(cat.id);
		},
		showCatalogViewWidget: function(catid){
			if (!L.isNull(this.catItemViewWidget)){
				this.catItemViewWidget.destroy();
			}
			this.elShow('itemloading');
			var __self = this;
			this.manager.catalogLoad(catid, function(){
				__self.elHide('itemloading');
			});
		}
	});
	NS.CatalogManagerWidget = CatalogManagerWidget;
};