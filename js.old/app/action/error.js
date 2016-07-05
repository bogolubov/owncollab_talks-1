/**
 * Action error.js
 */

(function($, OC, app){

    // elimination of dependence this action object
    if(typeof app.action.error !== 'object')
        app.action.error = {};

    // alias of app.action.error
    var o = app.action.error;

    o.page = function (text){

        var title = 'Application throw error',
            wrapper = '#app-content-wrapper',
            error = '#app-content-error';

        if(text){
            $(wrapper).hide();
            $(error).html('<h1>' + title + '</h1><p>' + text + '</p>').show();
        }else{
            $(wrapper).show();
            $(error).hide();
        }

    };

    o.inline = function (text){

        var title = 'Application throw error',
            errorinline = '#app-content-inline-error';

        if(text){
            $(errorinline)
                .show()
                .html('Application throw error: ' + text).show();
        }else{
            $(errorinline)
                .hide()
                .html("");
        }

    };
    

})(jQuery, OC, app);
