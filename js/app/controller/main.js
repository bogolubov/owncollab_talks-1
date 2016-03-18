/**
 * Controller main.js
 */

(function($, OC, app){

    // using depending on the base application
    var o = app.controller.main;

    /**
     * Construct call first when this controller run
     */
    o.construct = function() {


        /**
         * First we need to select all the elements necessary for work.
         * But after the DOM is loaded
         */
        $(document).ready(onDocumentLoaded);
        //$(document).ready(buttonsInit);
    };

    function onDocumentLoaded(){

    }

    function buttonsInit() {
        var inc = new Inc(),
            path = '/apps/' + app.name;

    }

})(jQuery, OC, app);
