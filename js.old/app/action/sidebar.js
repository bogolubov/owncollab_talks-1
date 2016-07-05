/**
 * Action sidebar.js
 */

(function($, OC, app){

    // elimination of dependence this action object
    if(typeof app.action.sidebar !== 'object') app.action.sidebar = {};

    var o = app.action.sidebar;

    console.log(app);

    o.init = function(){};

    o.set = function(name, value){};

    o.get = function(name){};

})(jQuery, OC, app);
