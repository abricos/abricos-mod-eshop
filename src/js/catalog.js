/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['resize'],
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
	
	var CatalogManagerWidget = function(container, cfg){
		CatalogManagerWidget.superclass.constructor.call(this, container, {
			'buildTemplate': buildTemplate, 'tnames': 'widget' 
		}, cfg);
	};
	YAHOO.extend(CatalogManagerWidget, Brick.mod.widget.Widget, {
		init: function(cfg){
			this.wsMenuItem = 'catalog'; // использует wspace.js
			this.manager = null;
			this.cfg = cfg;
			this.viewWidget = null;
		},
		destroy: function(){
			if (!L.isNull(this.viewWidget)){
				this.viewWidget.destroy();
			}
			CatalogManagerWidget.superclass.destroy.call(this);
		},
		onLoad: function(cfg){
			var __self = this;
			NS.initManager(function(man){
				__self._onLoadManager(man);
			});
		},
		_onLoadManager: function(man){
			this.manager = man;
			this.elHide('loading');
			this.viewWidget = new NSCat.CatalogManagerWidget(this.gel('view'), man, this.cfg);
		}
	});
	NS.CatalogManagerWidget = CatalogManagerWidget;
	
	
	
	var CatalogEditorWidget = function(container, cfg){
		CatalogEditorWidget.superclass.constructor.call(this, container, cfg);
	};
	YAHOO.extend(CatalogEditorWidget, CatalogManagerWidget, {
		_onLoadManager: function(man){
			this.manager = man;
			var __self = this, cfg = this.cfg;
			
			man.catalogLoad(cfg['catid'], function(cat){
				__self.elHide('loading');
				__self.viewWidget = new NSCat.CatalogEditorWidget(__self.gel('view'), man, cat);
			});
		}
	});
	NS.CatalogEditorWidget = CatalogEditorWidget;
	
	var CatalogManagerDialog = function(cfg){
		this.manCfg = L.merge({
			'refreshAfterClose': false
		}, cfg || {});
		
		CatalogManagerDialog.superclass.constructor.call(this, {
			'resize': true,
			fixedcenter: true,
			'width': '1010px',
			'height': '600px'
		});
	};
	YAHOO.extend(CatalogManagerDialog, Brick.widget.Dialog, {
		initTemplate: function(){
			return buildTemplate(this, 'dialog').replace('dialog');
		},
		onLoad: function(){
			this.viewWidget = new NS.CatalogManagerWidget(this._TM.getEl('dialog.widget'), this.manCfg); 
		},
		onClose: function(){
			var cfg = this.manCfg;
			if (cfg['refreshAfterClose']){
				Brick.Page.reload();
			}
		}
	});
	NS.CatalogManagerDialog = CatalogManagerDialog;
	
	
	var CatalogEditorWdget = function(container, cfg){
		CatalogEditorWdget.superclass.constructor.call(this, container, {
			'buildTemplate': buildTemplate, 'tnames': 'editorwidget' 
		}, cfg);
	};
	YAHOO.extend(CatalogEditorWdget, Brick.mod.widget.Widget, {
		init: function(cfg){
			this.manager = null;
			this.cfg = cfg;
			this.viewWidget = null;
		},
		destroy: function(){
			if (!L.isNull(this.viewWidget)){
				this.viewWidget.destroy();
			}
			CatalogEditorWdget.superclass.destroy.call(this);
		},
		onLoad: function(cfg){
			var __self = this;
			NS.initManager(function(man){
				man.catalogLoad(cfg['catid'], function(cat){
					__self._onLoadManager(man, cat);
				});
			});
		},
		_onLoadManager: function(man, cat){
			this.manager = man;
			this.elHide('loading');
			this.viewWidget = new NSCat.CatalogEditorWidget(this.gel('view'), man, cat, this.cfg);
		}
	});
	NS.CatalogEditorWdget = CatalogEditorWdget;
	
	
	var CatalogEditorDialog = function(cfg){
		this.manCfg = L.merge({
			'refreshAfterClose': false
		}, cfg || {});
		
		CatalogEditorDialog.superclass.constructor.call(this, {
			'resize': true,
			fixedcenter: true,
			'width': '800px',
			'height': '500px'
		});
	};
	YAHOO.extend(CatalogEditorDialog, Brick.widget.Dialog, {
		initTemplate: function(){
			return buildTemplate(this, 'editordialog').replace('editordialog');
		},
		onLoad: function(){
			var __self = this, cfg = this.manCfg;
			this.viewWidget = new NS.CatalogEditorWdget(this._TM.getEl('editordialog.widget'), {
				'catid': cfg['catid'],
				'onCancelClick': function(){
					Brick.console('oncancel');
					__self.close();
				},
				'onSaveCallback': function(){
					__self.close();
					if (cfg['refreshAfterClose']){
						Brick.Page.reload();
					}
				}
			}); 
		}
	});
	NS.CatalogEditorDialog = CatalogEditorDialog;

};