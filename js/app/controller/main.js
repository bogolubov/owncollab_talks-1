/**
 * Controller main.js
 */

(function($, OC, app){

    // using depending on the base application
    var o = app.controller.main;

    o.construct = function(hash) {
        //app.api('getproject', onProjectLoaded, {hash:hash});

        $(document).ready(onDocumentLoaded);

    };

    function onProjectLoaded(res){

        console.log(res);

    }

    function onDocumentLoaded(){

        // todo: Код для /action
        $('#hello').click(function () {
            alert('Hello from your script file');
        });

        // todo: Код для /action
        $('#echo').click(function () {
            var url = OC.generateUrl('/apps/'+app.name+'/echo');
            var data = {
                echo: $('#echo-content').val()
            };
            $.post(url, data).success(function (response) {
                $('#echo-result').text(response.echo);
            });
        });

    }

    o.loader = function(name){};

})(jQuery, OC, app);
