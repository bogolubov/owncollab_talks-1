if(App.namespace){App.namespace('Action.Listmenu', function(App) {

    /**
     * @namespace App.Action.Listmenu
     */
    var _ = {

        /**
         * Enable imitation of Auto Update messages
         * @var boolean autoUpdateOn
         */
        autoUpdateOn: false, //location.hostname != "owncloud.loc",

        /**
         * Auto Update delay microseconds
         * @var number timerPeriod
         */
        timerPeriod: 5000,

        /**
         * @var Timer|null timerUpdateInstance
         */
        timerUpdateInstance: null
    };


    /**
     * @namespace App.Action.Listmenu.init
     */
    _.init = function () {
        var listmenu = jQuery('.listmenu li');
        var r_messages = jQuery('#r_messages');
        var goto = Util.Cookie('goto_message');

        Util.Cookie('goto_message', false);

        listmenu.click(function(event) {

            var id, menu = event.target;

            r_messages[0].style.display = 'block';
            listmenu.each(function(i,item){item.style.fontWeight='normal'});
            menu.style.fontWeight='bold';

            if(id = menu.getAttribute('data-id')) {

                // Get messages from server side
                _.postChildrenMessage(id);
            }
        });

        if(Util.isNum(goto))
            jQuery("ul.listmenu>li[data-id="+goto+"]").click();
        else
            jQuery(listmenu[0]).click();
    };

    /**
     * @namespace App.Action.Listmenu.getChildrenMessage
     * @param parent_id
     */
    _.postChildrenMessage = function(parent_id) {
        var sendData = {
            parent_id: parent_id
        };

        // show loader ico
        App.query('.loader_min').style.display = 'block';

        App.Action.Api.request('message_children',function(response) {

            //console.log(response);
            if(Util.isObj(response)) {

                //App.requesttoken = response.requesttoken;
                //console.log(response);
                if(response['attachedfiles'])
                    App.inject('#dynamic-attachedfiles', response['attachedfiles']);
                else
                    App.inject('#dynamic-attachedfiles', '');

                if(response.error){
                    return;
                }

                if(response['messageslist']) {

                    // added HTML messages content
                    App.inject('#r_messages', response['messageslist']);
                    App.Action.Edit.submitFormReplyEvent();

                    // hide loader ico
                    App.query('.loader_min').style.display = 'none';

                    // Init Auto Update imitation. If autoUpdate is enable
                    if(_.autoUpdateOn) _.autoUpdateMessages();
                }
            }

        }, sendData);

    };


    _.autoUpdateMessages = function () {

        var parent_id = jQuery('#message_parent>.item_msg').attr('data-link');

        if (_.timerUpdateInstance)
            _.timerUpdateInstance.abort();

        _.timerUpdateInstance = new Timer(parseInt(_.timerPeriod), 0);

        _.timerUpdateInstance.addEventListener(Timer.PROGRESS, function (progress) {
            console.log(parent_id);
            if(parent_id)
                jQuery("ul.listmenu>li[data-id=" + parent_id + "]").click();
        });

        if (parent_id)
            _.timerUpdateInstance.start();
    };


    _.get = function(){

    };

    return _;
})}