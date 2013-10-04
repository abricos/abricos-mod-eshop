/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[
        {name: '{C#MODNAME}', files: ['catalog.js']}
	]
};
Component.entryPoint = function(NS){
	
	var Dom = YAHOO.util.Dom,
		L = YAHOO.lang,
		R = NS.roles,
		buildTemplate = this.buildTemplate;
	
	// Точка входа работы приложения
	
	var TeamAppWidget = function(container, teamid){
		TeamAppWidget.superclass.constructor.call(this, container, {
			'buildTemplate': buildTemplate, 'tnames': 'widget' 
		}, teamid);
	};
	YAHOO.extend(TeamAppWidget, Brick.mod.widget.Widget, {
		init: function(teamid){
			this.wsList = [];
			this.wsMenuItem = 'events'; // использует wspace.js
		},
		onLoad: function(teamid){
			this.catalogWidget = new NS.CatalogManagerWidget(this.gel('view'), {
				'teamid': teamid
			});
		},
		destroy: function(){
			this.eventListWidget.destroy();
			TeamAppWidget.superclass.destroy.call(this);
		},
	});
	NS.TeamAppWidget = TeamAppWidget;

};