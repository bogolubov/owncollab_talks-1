if(App.namespace){App.namespace('Action.Edit', function(App){
    /**
     * @namespace App.Action.Edit
     */
    var _ = {};

    /**
     * @namespace App.Action.Edit.init
     */
    _.init = function(){

        jQuery("textarea[name=message]").trumbowyg();

        _.checkSubscribersEvent();
        _.submitFormEvent();
    };

    _.submitFormEvent = function(){



    };

    /**
     * @namespace App.Action.Edit.checkSubscribersEvent
     */
    _.checkSubscribersEvent = function(){

        jQuery('.talk-subscribers input[type=checkbox]').click(function(event){

            var target = event.target;
            var name = target.name;
            var value = target.value;
            var email = target.getAttribute('data-email');
            var isChecked = event.target.checked;
            var isUser = !!target.getAttribute('data-group');

            if(isUser && email.length < 5 && isChecked){
                App.Controller.Page.errorLine("Этому пользователю письо не будет доставленно. В параметрах пользователя '"+value+"' не указан email адрес, или указан не верный email");
            }

            if(!isUser) {
                jQuery('.talk-subscribers input[type=checkbox][data-group='+value+']').each(function(index, item){
                    item.checked = isChecked;
                });
            }

        });
    };

    return _;
})}
