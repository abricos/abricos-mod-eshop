/*
 @package Abricos
 @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

var Component = new Brick.Component();
Component.requires = {
    yui: ['aui-tabview'],
    mod: [
        {name: 'catalog', files: ['typemanager.js', 'currencylist.js']},
        {name: '{C#MODNAME}', files: ['lib-manager.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    var NSCat = Brick.mod.catalog;

    NS.CatalogConfigWidget = Y.Base.create('catalogConfigWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            this.set('waiting', true);

            var __self = this;
            NS.initManager(function(man){
                __self._onLoadManager(man);
            });
        },
        destructor: function(){
            if (this.typeWidget){
                this.typeWidget.destroy();
                this.currencyWidget.destroy();
            }
        },
        _onLoadManager: function(man){
            this.set('waiting', false);

            var tp = this.template;

            new Y.TabView({srcNode: tp.gel('view')}).render();
            this.typeWidget = new NSCat.TypeManagerWidget(tp.gel('typemanager'), man);
            this.currencyWidget = new NSCat.CurrencyListWidget(tp.gel('currency'), man);
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'widget'}
        }
    });
};