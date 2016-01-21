/*
 @package Abricos
 @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: '{C#MODNAME}', files: ['lib-manager.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this,
        SYS = Brick.mod.sys;

    NS.CartBillingWidget = Y.Base.create('cartBillingWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            this.set('waiting', true);
            Brick.use('eshopcart', 'billing', function(){
                this.set('waiting', false);
                this.viewWidget = new Brick.mod.eshopcart.BillingWidget(this.template.gel('view'));
            }, this);
        },
        destructor: function(){
            if (this.viewWidget){
                this.viewWidget.destroy();
            }
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'billing'}
        }
    });

    NS.CartConfigWidget = Y.Base.create('cartBillingWidget', SYS.AppWidget, [], {
        onInitAppWidget: function(err, appInstance, options){
            this.set('waiting', true);
            Brick.use('eshopcart', 'config', function(){
                this.set('waiting', false);
                this.viewWidget = new Brick.mod.eshopcart.ConfigWidget(this.template.gel('view'));
            }, this);
        },
        destructor: function(){
            if (this.viewWidget){
                this.viewWidget.destroy();
            }
        }
    }, {
        ATTRS: {
            component: {value: COMPONENT},
            templateBlockName: {value: 'config'}
        }
    });

};