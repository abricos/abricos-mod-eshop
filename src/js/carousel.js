/*!
 * Copyright 2014 Alexander Kuzmin <roosit@abricos.org>
 * Licensed under the MIT license
 */

var Component = new Brick.Component();
Component.requires = {
    mod: [
//         {name: 'carousel', files: ['carousel.js']},
        {name: '{C#MODNAME}', files: ['lib.js']}
    ]
};
Component.entryPoint = function(NS){

    var Y = Brick.YUI,
        COMPONENT = this;

    NS.ProductCarouselWidget = Y.Base.create('productCarouselWidget', NS.AppWidget, [
    ], {
        onInitAppWidget: function(err, appInstance, options){
            /*
            if (this.get('groupList')){
                this.onLoadGroupList();
            } else {
                this.set('waiting', true);
                this.get('appInstance').groupList(function(err, result){
                    this.set('waiting', false);
                    if (!err){
                        this.set('groupList', result.groupList);
                    }
                    this.onLoadGroupList();
                }, this);
            }
            /**/
        }
    }, {
        ATTRS: {
            component: {
                value: COMPONENT
            },
            templateBlockName: {
                value: 'widget'
            }
        }
    });
};