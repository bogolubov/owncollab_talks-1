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

        if($appPath === '/begin') {

            // init visual editor
            $("#message-body").trumbowyg();
        }


    }

    return _;

})}
