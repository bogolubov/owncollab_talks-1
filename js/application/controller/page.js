if(App.namespace){App.namespace('Controller.Page', function(App){

    /**
     * @namespace App.Controller.Page
     */
    var _ = {}
        , node = {}
        ;
            
    _.construct = function(){

        App.uriPath = App.uriPath();

        App.domLoaded(afterDOMLoaded);

    };

    function afterDOMLoaded () {

        if(['/','/begin'].indexOf(App.uriPath)!==-1) {
            // init visual editor
            $("textarea[name=message]").trumbowyg();
            App.Action.Edit.init();
        }

        if(['/my','/all','/started'].indexOf(App.uriPath)!==-1) {
            // init menu
            App.Action.Listmenu.init();
        }

        if(App.uriPath.search(/\/read\/\d+/i)!==-1) {
            // init
            _.readEvents();
        }

    }

    _.readEvents = function(){
        Linker.search();
        Linker.click('msg_back', function(event){event.preventDefault();window.history.back()});
        Linker.click('msg_reply', function(event){
            event.preventDefault();
            if(event.target.getAttribute('data-ready')==='yes'){
                console.log('send');
            }
            else{
                event.target.textContent = 'Send answer';
                event.target.setAttribute('data-ready','yes');
                jQuery('.read_reply').show();
            }
        });
    };


    return _;

})}
