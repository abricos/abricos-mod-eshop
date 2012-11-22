/*
* @version $Id$
* @copyright Copyright (C) 2008 Abricos All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Обработчик интерфейса размещенного на страницах сайта
var Component = new Brick.Component();
Component.requires = {
	yahoo: ['dom'],
	mod:[{name: 'sys', files: ['wait.js']}]
};
Component.entryPoint = function(NS){
	
	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;
	
	var API = NS.API,
		F = Brick.Component.API.fire,
		FF = Brick.Component.API.fireFunction,
		TMG = this.template;
	
	var LW = Brick.widget.LayWait;
	
	var gel = function(id){
		return L.isString(id) ? Dom.get(id) : id;
	};
	var gval = function(id){
		var el = gel(id);
		if (L.isNull(el)){ return ''; }
		return el.value;
	};
	var sval = function(id, val){
		var el = gel(id);
		if (L.isNull(el)){ return; }
		el.value = val;
	};
	var find = function(el, className, cnt){
		if (Dom.hasClass(el, className)){ return el; }
		cnt = (cnt || 0)+1;
		if (L.isNull(el) || el.parentNode == document.body || cnt > 30){
			return false;
		}
		return find(el.parentNode, className);
	};
	var parseid = function(el, pfx){
        var trim = YAHOO.lang.trim;
		pfx = trim(pfx.toUpperCase());
        var arr = Dom.getAttribute(el, "className").toUpperCase().split(' ');
        for (var i=0;i<arr.length;i++){
        	var arr1 = trim(arr[i]).split('-');
        	if (arr1.length == 2 && arr1[0] == pfx){
        		return arr1[1]*1; 
        	}
        }
        return 0;
	};
	
	var getFElCl = function(className){
		var els = Dom.getElementsByClassName(className);
		return els.length > 0 ? els[0] : null;
	};
	
	var isInitButtons = false;
	
	/**
	 * Прикрутить событие на клик всех кнопок, чтобы отловить клики 
	 * "Добавить в корзину", "Показать корзину" и т.п.  
	 */
	API.buyButtonsInit = function(elId){
		if (isInitButtons){ return; }
		
		isInitButtons = true;
		var container = document.body;
		if (L.isNull(container)){ return; }
		
		var onClick = function(el){

			var fel;
			
			if (fel=find(el, 'btn-show-cart')){
				var lw = new LW(fel.parentNode, true);
				
				FF('eshop', 'cart', function(){
					lw.hide();
					API.cartPanelShow();
				});

			}else if (fel=find(el, 'btn-show-order')){
				
				var lw = new LW(fel.parentNode, true);
				
				FF('eshop', 'order', function(){
					lw.hide();
					API.showOrderPanel();
				});
				
			}else if (fel=find(el, 'btn-product-buy')){
				var productid = parseid(fel, 'product');
				
				var txtBuyCount = getFElCl('txt-product-bcount-'+productid);
				var count = L.isNull(txtBuyCount) ? 1 : gval(txtBuyCount)*1;
				if (count < 1){ return; }
				
				var lw = new LW(fel.parentNode, true);
				FF('eshop', 'cart', function(){
					API.productAddToCart(productid, count, function(cartinfo){
						lw.hide();
						
						// произошло чтото непоправимое
						if (L.isNull(cartinfo)){
							// TODO: необходимо доработать систему оповещения ошибок пользователю
							alert('Error in eshop/ui.js');
							return;
						}

						// забросить товар в корзину - почти баскетбольно-анимированный бросок фотки
						var img = getFElCl('img-product-'+productid);
						var to = Dom.get('basket');
						var cartPanel = NS.CartPanel.instance; 
						if (!L.isNull(cartPanel)){
							to = cartPanel.body;
						}

						var pcma = new NS.ProductCartMoveAnim(img, to, function(){
							// После того, как товар улетел в корзину, необходимо обновить 
							// информацию в ней (даже если их несколько на странице)
							var els = Dom.getElementsByClassName('eshop-cart-count');
							for (var n in els){
								els[n].innerHTML = cartinfo['qty'];
							}
							var els = Dom.getElementsByClassName('eshop-cart-summ');
							for (var n in els){
								els[n].innerHTML = API.formatPrice(cartinfo['sum']);
							}
							if (!L.isNull(cartPanel)){
								cartPanel.cartWidget.refresh();
							}
						});
						
					});
				});
				
			}else {
				return false;
			}
			return true;
		};
		
		E.on(container, 'click', function(e){
			var el = E.getTarget(e);
			if (onClick(el)){ E.preventDefault(e); }
		});
	};
	
	API.showBigPhoto = function(cfg) {
		cfg = L.merge({
			'w': 200,
			'h': 200
		}, cfg || {});
		var pid = cfg.fid, tmb = cfg.el;
	    var p = gel("bigPhoto");
	    gel("loading").style.display = "block";
	    p.className = "big loading_bg";
	    p.onload = function () {gel("loading").style.display = "none";p.className = "big";};
	    p.src=tmb.src.replace("w_40-h_40","w_"+cfg['w']+"-h_"+cfg['h']+"");
	    gel("tmb" + pidCurrent).className = "tmbsmsm";
	    tmb.className = "currentPhoto";
	    pidCurrent = pid;
	};
	
	var viewBigImage = function(fel, fid){
		
		var lw = new LW(fel.parentNode, true);

		Brick.ff('sys', 'container', function(){
			
			var scrBig = '/filemanager/i/'+fid+'/';
			
			var imgBig = document.createElement('img');
			imgBig.onload = function(){
				
				lw.hide();
				var w = imgBig.width*1,
					h = imgBig.height*1;
				
				if (w == 0 || h == 0){ return; }
				w += 20; h += 50;
				
				var pnl = new YAHOO.widget.Panel("wait", { 
					'width': w+'px', 'height': h+'px',
					close: true, 
					draggable:false, 
					zindex:4000,
					modal:false,
					visible:false,
					overflow: true
				});
				pnl.setHeader('Image');
				pnl.setBody('<img src="'+scrBig+'" />');
				pnl.render(document.body);
				pnl.center();
				pnl.show();
				E.on(pnl.element, 'click', function(){
					pnl.hide();
					pnl.destroy();
				});
			};
			imgBig.src = scrBig;
		});	

	};
	

	API.showMaxPhoto = function(tmb) {
		var a = tmb.src.split('/');
		for (var i=0;i<a.length;i++){
			if (a[i] == 'i' && i < a.length-1){
				viewBigImage(tmb, a[i+1]);
			}
		}
	};
};
