if(App.namespace){App.namespace('Action.Edit', function(App){
    /**
     * @namespace App.Action.Edit
     */
    var _ = {};

    /**
     * @namespace App.Action.Edit.init
     */
    _.init = function(){

        _.checkSubscribersEvent();


    };

    _.submitForm = function(formName){



    };

    /**
     * @namespace App.Action.Edit.checkSubscribersEvent
     */
    _.checkSubscribersEvent = function(){

        $('.talk-subscribers input[type=checkbox]').click(function(event){

            var target = event.target;
            var name = target.name;
            var value = target.value;
            var isChecked = event.target.checked;
            var isUser = !!target.getAttribute('data-group');




            if(!isUser) {
                $('.talk-subscribers input[type=checkbox][data-group='+value+']').each(function(index, item){
                    item.checked = isChecked;
                });
            }

        });
    };

    return _;
})}
