/**
* @version $Id$
* @package Abricos
* @copyright Copyright (C) 2010 Abricos. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[{name: 'sys', files: ['data.js', 'container.js', 'wait.js', 'form.js']}]
};
Component.entryPoint = function(){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var NS = this.namespace,
		API = NS.API,
		TMG = this.template;

	NS.delivery = NS.delivery || {}; 

	if (!NS.data){
		NS.data = new Brick.util.data.byid.DataSet('eshop');
	}
	var DATA = NS.data;
	
	var LW = Brick.widget.LayWait;

	var buildTemplate = function(widget, templates){
		var TM = TMG.build(templates), T = TM.data, TId = TM.idManager;
		widget._TM = TM; 
		widget._T = T; 
		widget._TId = TId;
	};
	
	var ManagerWidget = function(container){
		this.init(container);
	};
	
	ManagerWidget.prototype = {
		init: function(container){
			buildTemplate(this, 'manwidget,table,row,rowwait');
			
			container.innerHTML = this._T['manwidget'];
			
			var tables = {
				'delivery': DATA.get('delivery', true)
			};
			DATA.onStart.subscribe(this.dsComplete, this, true);
			DATA.onComplete.subscribe(this.dsComplete, this, true);
			if (DATA.isFill(this.tables)){
				this.render();
			}else{
				this.renderWait();
			}
		},
		dsComplete: function(type, args){
			if (args[0].checkWithParam('delivery', {})){
				if (type == 'onComplete'){
					this.render(); 
				}else{
					this.renderWait();
				}
			}
		},
		destroy: function(){
			DATA.onComplete.unsubscribe(this.dsComplete);
			DATA.onStart.unsubscribe(this.dsComplete);
		},
		renderWait: function(){
			var TM = this._TM, T = this._T;
			TM.getEl('manwidget.table').innerHTML = TM.replace('table', {'rows': T['rowwait']});
		},
		render: function(){
			this._TM.getEl('manwidget.table').innerHTML = this.buildList(0);
		},
		buildList: function(pid){
			var __self = this, lst = "", TM = this._TM, T = this._T;
			DATA.get('delivery').getRows().filter({'pid': pid}).foreach(function(row){
				var di = row.cell;
				lst += TM.replace('row', {
					'tl': di['tl'],
					'ord': di['ord'],
					'id': di['id'],
					'child': __self.buildList(di['id'])
				});
			});
			
			return lst.length == 0 ? "" : TM.replace('table', {'rows': lst});
		},
		onClick: function(el){
			var TId = this._TId;
			if (el.id == TId['manwidget']['bappend']){
				this.edit(0);
				return true;
			}else if (el.id == TId['manwidget']['bsave']){
				this.save();
				return true;
			}
			
			var prefix = el.id.replace(/([0-9]+$)/, '');
			var numid = el.id.replace(prefix, "");
			
			switch(prefix){
			case (this._TId['row']['add']+'-'):
				this.edit(0, numid);
				return true;
			case (this._TId['row']['edit']+'-'):
				this.edit(numid);
				return true;
			case (this._TId['row']['remove']+'-'):
				this.remove(numid);
				return true;
			}
			
			return false;
		},
		edit: function(id, pid){
			
			id = id*1 || 0;
			pid = pid*1 || 0;
			var table = DATA.get('delivery'),
				rows = table.getRows(),
				row = id == 0 ? table.newRow() : rows.getById(id);
			
			if (id == 0){
				row.update({'pid': pid});
			}
			new EditorPanel(row, function(){
				if (id == 0){ rows.add(row); }
				table.applyChanges();
				DATA.request();
			});
		},
		remove: function(id){
			var table = DATA.get('delivery'),
				rows = table.getRows(),
				row = rows.getById(id);
			
			new RemovePanel(row, function(){
				row.remove();
				table.applyChanges();
				DATA.request();
			});
		}
	};
	NS.delivery.ManagerWidget = ManagerWidget;
	
	API.showDeliveryManagerWidget = function(container){
		var widget = new ManagerWidget(container);
		DATA.request();
		return widget;
	};
	
	var ManagerPanel = function(){
		ManagerPanel.superclass.constructor.call(this, {
			fixedcenter: true, resize: true, 
			width: '600px', height: '400px'
		});
	};
	YAHOO.extend(ManagerPanel, Brick.widget.Panel, {
		initTemplate: function(){
			buildTemplate(this, 'manpanel');
			return this._T['manpanel'];
		},
		onLoad: function(){
			this.widget = new ManagerWidget(this._TM.getEl('manpanel.widget'));
		},
		onClick: function(el){
			return this.widget.onClick(el);
		}
	});
	NS.delivery.ManagerPanel = ManagerPanel;
	
	API.showDeliveryManagerPanel = function(){
		var panel = new ManagerPanel();
		DATA.request();
		return panel;
	};
	
	var EditorPanel = function(row, callback){
		this.row = row;
		this.callback = callback;
		EditorPanel.superclass.constructor.call(this, {
			width: '600px', resize: true
		});
	};
	
	YAHOO.extend(EditorPanel, Brick.widget.Dialog, {
		el: function(name){ return Dom.get(this._TId['editorpanel'][name]); },
		elv: function(name){ return Brick.util.Form.getValue(this.el(name)); },
		setelv: function(name, value){ Brick.util.Form.setValue(this.el(name), value); },
		initTemplate: function(){
			buildTemplate(this, 'editorpanel');
			return this._T['editorpanel'];
		},
		onLoad: function(){
			var di = this.row.cell;
			this.setelv('tl', di['tl']);
			this.setelv('it', di['it']);
			this.setelv('ot', di['ot']);
		},
		onClick: function(el){
			var tp = this._TId['editorpanel'];
			switch(el.id){
			case tp['bcancel']: this.close(); return true;
			case tp['bsave']: this.save(); return true;
			}
			return false;
		},
		save: function(){
			
			this.row.update({
				'tl': this.elv('tl'),
				'it': this.elv('it'),
				'ot': this.elv('ot')
			});
			
			this.callback();
			this.close();
		}
	});
	
	var RemovePanel = function(row, callback){
		this.row = row;
		this.callback = callback;
		RemovePanel.superclass.constructor.call(this, {
			resize: false, width: '400px'
		});
	};
	YAHOO.extend(RemovePanel, Brick.widget.Dialog, {
		initTemplate: function(){
			buildTemplate(this, 'removepanel');
			return this._TM.replace('removepanel', {
				'tl': this.row.cell['tl']
			});
		},
		onClick: function(el){
			var tp = this._TId['removepanel'];
			switch(el.id){
			case tp['bcancel']: this.close(); return true;
			case tp['bremove']: this.remove(); return true;
			}
			return false;
		},
		remove: function(){
			this.callback();
			this.close();
		}
	});


};
