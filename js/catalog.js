/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
		{name: 'catalog', files: ['catalogexplore.js', 'catalogview.js', 'elementlist.js']},
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
			this.treeWidget = null;
			this.catViewWidget = null;
			this.elementListWidget = null;
			this.subCatalogEditorWidget = null;
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
			if (!L.isNull(this.catViewWidget)){
				this.catViewWidget.destroy();
			}
			if (!L.isNull(this.elementListWidget)){
				this.elementListWidget.destroy();
			}
			
			CatalogManagerWidget.superclass.destroy.call(this);
		},
		_onLoadManager: function(man){
			this.manager = man;
			this.elHide('loading');
			this.treeWidget = new NSCat.CatalogTreeWidget(this.gel('explore'), man.catalogList);
			this.treeWidget.selectedItemEvent.subscribe(this.onSelectedCatalogItem, this, true);
			
			this.showCatalogViewWidget(0);
		},
		onSelectedCatalogItem: function(evt, prms){
			var cat = prms[0];
			this.showCatalogViewWidget(cat.id);
		},
		showCatalogViewWidget: function(catid){
			this.elShow('colloading');
			this.elHide('colview');
			var __self = this;
			this.manager.catalogLoad(catid, function(cat, elList){
				__self._onLoadCatalogDetail(cat, elList);
			}, {'elementlist': true});
		},
		_onLoadCatalogDetail: function(cat, elList){
			this.elHide('colloading');
			this.elShow('colview');

			var __self = this;
			if (L.isNull(this.catViewWidget)){
				this.catViewWidget = new NSCat.CatalogViewWidget(this.gel('catview'), this.manager, cat, {
					'addElementClick': function(){
						__self.elementListWidget.showNewEditor();
					},
					'addCatalogClick': function(){
						__self.showSubCatalogEditorWidget();
					}
				});
			}else{
				this.catViewWidget.setCatalog(cat);
			}

			if (L.isNull(this.elementListWidget)){
				this.elementListWidget = new NSCat.ElementListWidget(this.gel('ellist'), this.manager, elList);
			}else{
				this.elementListWidget.setList(elList);
			}
		},
		showSubCatalogEditorWidget: function(){
			if (!L.isNull(this.subCatalogEditorWidget)){ return; }
			
			var pid = this.catViewWidget.cat.id;
			
			var cat = new NSCat.CatalogItem({'pid': pid});
			var __self = this;
			
			this.subCatalogEditorWidget = new NSCat.CatalogEditorWidget(this.gel('subcatedit'), this.manager, cat, {
				'onCancelClick': function(){
					__self.closeSubCatalogEditorWidget();
				},
				'onSaveCallback': function(cat){
					__self.closeSubCatalogEditorWidget();
					__self.showCatalogViewWidget(cat.id);
				}
			});
		},
		closeSubCatalogEditorWidget: function(){
			if (L.isNull(this.subCatalogEditorWidget)){ return; }
			this.subCatalogEditorWidget.destroy();
			
			this.subCatalogEditorWidget = null;
		}
	});
	NS.CatalogManagerWidget = CatalogManagerWidget;
};