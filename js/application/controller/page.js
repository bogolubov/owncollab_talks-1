if(App.namespace){App.namespace('Controller.Page', function(App){

    /**
     * @namespace App.Controller.Page
     */
    var _ = {}
        , node = {}
        ;
            
    _.construct = function(){
        App.domLoaded(afterDOMLoaded);


    };

    function afterDOMLoaded () {

        // routing settings
        $appPath = App.getAppPath();
        if($appPath === '/' || $appPath === '/begin') {

            // init visual editor
            $("textarea[name=message]").trumbowyg();

            App.Action.Edit.init();
        }



    }

    return _;

})}
