/**
* @version $Id$
* @package Abricos
* @copyright Copyright (C) 2010 Abricos. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	yahoo: ['animation','datasource','dragdrop','resize'],
	mod:[{name: 'sys', files: ['data.js', 'form.js', 'container.js', 'wait.js']}]
};
Component.entryPoint = function(){
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var NS = this.namespace,
		API = NS.API,
		TMG = this.template;

	var LW = Brick.widget.LayWait;

	if (!NS.data){
		NS.data = new Brick.util.data.byid.DataSet('eshop');
	}
	var DATA = NS.data;
	
(function(){
	
	var cartInfo = null;
	
	// Получить информацию по корзине (кол-во товаров и сумма)
	API.getCartInfo = function(callback){
		if (!L.isFunction(callback)){ return; }
		
		if (!L.isNull(cartInfo)){
			callback(cartInfo);
		}else{
			Brick.ajax('eshop',{
				'data': {'do': 'cartinfo'},
				'event': function(response){
					cartInfo = response.data;
					callback(cartInfo);
				}
			});
		}
	};
	
	// Положить товар в корзину, вернуть кол-во и сумму в корзине 
	API.productAddToCart = function(productid, count, callback){
		if (!L.isNull(DATA.get('cart'))){
			DATA.get('cart').clear();
		}
		
		var __callback = function(response){
			if (!L.isFunction(callback)){
				return;
			}
			cartInfo = response.data; 
			callback(cartInfo);
		};
		
		Brick.ajax('eshop',{
			'data': {
				'do': 'prodtocart',
				'productid': productid,
				'quantity': count
			},
			'event': __callback
		});
	};
})();

	API.formatPrice = function(price, notSpecSpice){
		var fprice = YAHOO.util.Number.format(price, {
			decimalPlaces: 2,
			thousandsSeparator: notSpecSpice ? ' ' : '&nbsp;',
			suffix: ''
		}); 
		return fprice; 
	};
	
	API.cartPanelShow = function(){
		if (!L.isNull(NS.CartPanel.instance)){ return; }
		return new NS.CartPanel();
	};
	
	// если orderid > 0, то используется таблица уже сформированного заказа
	var CartWidget = function(readonly, orderid){
		this.init(readonly, orderid);
	};
	CartWidget.prototype = {
		init: function(readonly, orderid){
			this.readonly = readonly;
			this.orderid = orderid*1 || 0;
		},
		initTemplate: function(){
			var TM = TMG.build('widget,table,row,rowwait,tablero,rowro,rowwaitro,rowsum,rowdsc,rowsumdsc,rowsumro,rowdscro,rowsumdscro'), 
				T = TM.data, TId = TM.idManager;
			this._TM = TM; this._T = T; this._TId = TId;
			
			return T['widget'];
		}, 
		onLoad: function(){
			this.element = this._TM.getEl('widget.id');
			this.tables = {
				'cart': DATA.get('cart', true),
				'discount': DATA.get('discount', true)
			};
			this.tables['cart'].getRows({'orderid': this.orderid});
			DATA.onComplete.subscribe(this.dsEvents, this, true);
			DATA.onStart.subscribe(this.dsEvents, this, true);
			if (DATA.isFill(this.tables)){
				this.renderData();
			}else{
				this.renderWait();
			}
		},
		refresh: function(){
			DATA.get('cart').clear();
			DATA.request();
		},
		destroy: function(){
			DATA.onComplete.unsubscribe(this.dsEvents);
			DATA.onStart.unsubscribe(this.dsEvents);
		},
		dsEvents: function(type, args){
			if (!args[0].checkWithParam('cart', {'orderid': this.orderid})){ return; }
			
			if (type == 'onStart'){
				this.renderWait();
			}else{
				this.renderData();
			}
		},
		renderData: function(){
			var TM = this._TM, T = this._T, TId = this._TId;
			var lst = "", flag = false, pfx = this.readonly ? 'ro' : '';
			var allsum = 0 ;
			DATA.get('cart').getRows({'orderid': this.orderid}).foreach(function(row){
				var di = row.cell,
					sum = di['pc']*di['qty'];
				allsum += sum;
				lst += TM.replace('row'+pfx, {
					'id': di['id'],
					'sel': flag? 'td_over' : '',
					'title': di['tl'],
					'price': API.formatPrice(di['pc']),
					'count': di['qty'],
					'summ': API.formatPrice(sum)
				});
				flag = flag? false : true;
			});
			
			lst += TM.replace('rowsum'+pfx, {
				'amount': API.formatPrice(allsum)
			});

			var itogsum = allsum;
			
			// применение скидок
			DATA.get('discount').getRows().foreach(function(row){
				var di = row.cell,
					fsum = di['fsm']*1,
					esum = di['esm']*1,
					prc = di['prc']*1;

				if (di['dsb']*1>0 || di['tp']*1 == 1){ return; }
				
				if (allsum < fsum || allsum >= esum){ return; }
				
				var discount = prc < 1 ? 
						di['pc']*1 :
						(di['pc']*1/100)*allsum;

				itogsum -= discount;
				
				lst += TM.replace('rowdsc'+pfx, {
					'id': di['id'],
					'tl': di['tl'],
					'discount': API.formatPrice(discount)
				});
			});

			lst += TM.replace('rowsumdsc'+pfx, {
				'finalprice': API.formatPrice(itogsum)
			});
			
			TM.getEl('widget.table').innerHTML = TM.replace('table'+pfx, {'rows': lst});
		},
		renderWait: function(){
			var TM = this._TM, T = this._T, TId = this._TId,
				pfx = this.readonly ? 'ro' : '';
			TM.getEl('widget.table').innerHTML = TM.replace('table'+pfx, {
				'rows': T['rowwait'+pfx]
			});
		},
		onClick: function(el){
			var TId = this._TId;
			
			var prefix = el.id.replace(/([0-9]+$)/, '');
			var numid = el.id.replace(prefix, "");
			
			switch(prefix){
			case (this._TId['row']['remove']+'-'):
				this.removeProduct(numid);
				return true;
			}

			return false;
		},
		removeProduct: function(productid){
			var table = DATA.get('cart'); 
			table.getRows({'orderid': this.orderid}).getById(productid).remove();
			table.applyChanges();
			DATA.request();
		},
		recalc: function(){
			var TId = this._TId;
			DATA.get('cart').getRows({'orderid': this.orderid}).foreach(function(row){
				var di = row.cell;
				row.update({
					'qty': Dom.get(TId['row']['qty']+'-'+di['id']).value
				});
			});
			DATA.get('cart').applyChanges();
			DATA.request();
		}
	};
	NS.CartWidget = CartWidget;
	
	var CartPanel = function(){
		CartPanel.instance = this;
		CartPanel.superclass.constructor.call(this, {
			fixedcenter: true, width: '600px', zindex: '1000', resize: true
		});
	};
	CartPanel.instance = null;
	
	YAHOO.extend(CartPanel, Brick.widget.Panel, {
		initTemplate: function(){
		
			var TM = TMG.build('panel'), T = TM.data, TId = TM.idManager;
			this._TM = TM; this._T = T; this._TId = TId;
			
			this.cartWidget = new CartWidget();
			
			return TM.replace('panel', {
				'widget': this.cartWidget.initTemplate()
			});
		}, 
		onLoad: function(){
			this.cartWidget.onLoad();
			DATA.request();
		},
		destroy: function(){
			this.cartWidget.destroy();
			
			CartPanel.superclass.destroy.call(this);
			CartPanel.instance = null;
		},
		onClick: function(el){
			var tp = this._TId['panel'];
			switch(el.id){
			case tp['brecalc']: this.cartWidget.recalc(); return true;
			case tp['bcheckout']: this.checkout(); return true;
			case tp['bclose']: this.close(); return true;
			}
			return this.cartWidget.onClick(el);
		},
		checkout: function(){
			
			var lw = new LW(this._TM.getEl('panel.buttons'), true);
			Brick.ff('eshop', 'order', function(){
				lw.hide();
				API.showOrderPanel();
			});
		}
	});
	
	NS.CartPanel = CartPanel;
	
(function(){
	
	var ProductCartMoveAnim = function(elFrom, elTo, callback){
		try{
			this.init(elFrom, elTo, callback);
		}catch(ex){
			// вдруг ошибка, а callback нужно то выполнить
			if (L.isFunction(callback)){callback(false);}
		}
	};
	ProductCartMoveAnim.prototype = {
		init: function(elFrom, elTo, callback){
			elFrom = Dom.get(elFrom);
			elTo = Dom.get(elTo);
			
			var doCallback = function(isOk){if (L.isFunction(callback)){callback(isOk);}};
			
			if (L.isNull(elFrom) || L.isNull(elTo)){
				doCallback(false);
				return; 
			}
			
			var TM = TMG.build('pcimove');
			
			var rg = Dom.getRegion(elFrom);

			var out = 5;
			var div = document.createElement('div');
			div.innerHTML = TM.replace('pcimove', {
				'w': rg['width']+out*2, 
				'h': rg['height']+out*2, 
				'top': rg['top']-out,
				'left': rg['left']-out
			});
			var el = div.childNodes[0];

			el.appendChild(elFrom.cloneNode(true));
			document.body.appendChild(el);
			
			var rg = Dom.getRegion(elTo);
			
			var attributes = {
				points: { to: [rg.left, rg.top] }
		    };
		    var anim = new YAHOO.util.Motion(el, attributes, 1, YAHOO.util.Easing.backIn);
		    anim.animate();
		    anim.onComplete.subscribe(function(){
		    	el.parentNode.removeChild(el);
		    	doCallback(true);
		    });
		}
	};
	NS.ProductCartMoveAnim = ProductCartMoveAnim;
})();	
};
