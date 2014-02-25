/*
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = {
	mod:[{name: 'user', files: ['permission.js']}]
};
Component.entryPoint = function(NS){
	
	var BP = Brick.Permission;

	NS.roles = {
		load: function(callback){
			BP.load(function(){
				NS.roles['isView'] = BP.check('{C#MODNAME}', '10') == 1;
				NS.roles['isWrite'] = BP.check('{C#MODNAME}', '30') == 1;
				NS.roles['isOperator'] = BP.check('{C#MODNAME}', '40') == 1;
				NS.roles['isModerator'] = BP.check('{C#MODNAME}', '45') == 1;
				NS.roles['isAdmin'] = BP.check('{C#MODNAME}', '50') == 1;
				callback();
			});
		}
	};
};