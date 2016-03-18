/**
 * Created by olexiy on 19.02.16.
 */

(function($, OC, app){

    // using depending on the base application
    var o = app.action.buttons;

    o.construct = function() {
        /**
         * First we need to select all the elements necessary for work.
         * But after the DOM is loaded
         */
        var path = window.location.pathname.split( '/' );
        //var lastItem = path[path.length-1];
        var lastItem = path[4];

        switch (lastItem) {
            case 'begin':
            case 'reply':
                $(document).ready(editorInit);
                $(document).ready(linksInit);
                $(document).ready(checkboxInit);
                break;
            case 'mytalks':
                $(document).ready(linksInit);
                break;
            default:
                $(document).ready(menuLinksInit);
                $(document).ready(menuInit);
                $(document).ready(linksInit);
                $(document).ready(checkboxInit);
                $(document).ready(buttonsInit);
                break;
        }
    }

    function menuInit() {
        //alert("menu");
        $(".mark-talk-as").hover(
            function(){
                $(this).find('#mark-talk-as').fadeIn("slow");
            },
            function(){
                $(this).find('#mark-talk-as').fadeOut("fast");
            }
        );
    }

    function linksInit() {
        //alert("links");
        $(".messagerow").click(
            function(){
                var messid;
                messid = $(this).find('#messageid').attr('value');
                window.location = '/index.php/apps/owncollab_talks/read/'+messid;
            }
        );

        var filesresult = 0;
        $("#ajax-showfiles").click(
            function(event) {
                event.preventDefault();

                if (!filesresult) {
                    $('#loadimg').show();
                    app.api('getuserfiles', function (response) {
                        //if (response.requesttoken && response.files instanceof Array) {
                        if (response.requesttoken) {
                            app.requesttoken = response.requesttoken;

                            $("#attach-files").append(response.view);
                                /* response.files.map(function(item){

                                 console.log(item);

                                 var p = document.createElement('p');
                                 p.className = '';
                                 p.innerHTML = 'File: ' + item['file_target'];
                                 document.querySelector('#talk-attachements').appendChild(p);
                                 }); */

                            }
                        //console.log(response);
                    });
                    filesresult = 1;
                    $('#loadimg').hide();
                }
                else {
                    $("#attach-files").html("");
                    filesresult = 0;
                }
            }
        );

        var folderresult = 0;
        'use strict';
        $(document.body).on('click', ".ajax-openfolder", function(event) {
            event.preventDefault();
            var cell = $(this).parents('table').find("#folder-files-"+this.id.substring(7));

            if (!folderresult) {
                app.api('getfolderfiles', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        cell.append(response.view);
                    }
                    console.log(response);
                }, this.id);
                folderresult = 1;
            }
            else {
                cell.html("");
                folderresult = 0;
            }
        })
     }

    function menuLinksInit() {
        $("#MainMenu-begin").click(
            function(event) {
                menuSelect('MainMenu-begin');
                event.preventDefault();

                $("#app-content-wrapper").html("");
                app.api('begintalk', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#app-content-wrapper").append(response.view);
                    }
                    //console.log(response);
                });
            }
        );

        $("#MainMenu-subscribers").click(
            function(event) {
                menuSelect('MainMenu-subscribers');
                event.preventDefault();

                $("#app-content-wrapper").html("");
                app.api('selectSubscribers', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#app-content-wrapper").append(response.view);
                    }
                    //console.log(response);
                });
            }
        );

        $("#MainMenu-mytalks").click(
            function(event) {
                menuSelect('MainMenu-mytalks');
                event.preventDefault();

                $("#app-content-wrapper").html("");
                app.api('mytalks', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#app-content-wrapper").append(response.view);
                    }
                    //console.log(response);
                });
            }
        );

        $("#MainMenu-attachments").click(
            function(event) {
                menuSelect('MainMenu-attachments');
                event.preventDefault();

                $("#app-content-wrapper").html("");
                app.api('attachments', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#app-content-wrapper").append(response.view);
                    }
                    //console.log(response);
                });
            }
        );

        $("#MainMenu-all").click(
            function(event) {
                menuSelect('MainMenu-all');
                event.preventDefault();

                $("#app-content-wrapper").html("");
                app.api('alltalks', function (response) {
                    if (response.requesttoken) {
                        app.requesttoken = response.requesttoken;

                        $("#app-content-wrapper").append(response.view);
                    }
                    //console.log(response);
                });
            }
        );
    }

    function checkboxInit() {
        //alert("checkbox");
        $(function(){
            var checked = null;
            $("input.groupname").click(
                function(){
                    var fieldset = $(this).parent().parent();
                    if ($(this).is(':checked')) {
                        fieldset.find('.group-user input:checkbox').attr('checked', true);
                    }
                    else {
                        fieldset.parent().find('.group-user input:checkbox').attr('checked', false);
                    };
                }
            );
        });

        //Select the same user in different groups
        $(function(){
            $(".group-user input").click(
                function(){
                    var uid = $(this)[0].value;
                    var allusers = $(this).parents('fieldset').parent();
                    //alert(uid);
                    if ($(this).is(':checked')) {
                        allusers.find('#'+uid).attr('checked', true);
                    }
                    else {
                        allusers.find('#'+uid).attr('checked', false);
                    };
                }
            );
        });
    }

    function buttonsInit() {
        //alert("buttons");
        var buttons = document.querySelector('.message-buttons');

        console.log(buttons);
        if(buttons)
            buttons.addEventListener('click', onClickButtons, true);
        return;
    }

    function editorInit() {
        $("#message-body").trumbowyg();
    }

    function onClickButtons(event) {
        var button = event.target;
        var action = button.getAttribute('data-link');

        switch (action) {
            case 'reply':
                var messid = $("#messageId").attr('value');
                window.location = '/index.php/apps/owncollab_talks/reply/'+messid;
                break;
            case 'no-reply':
                break;
            case 'delete-confirm':
                if (confirm("Are You sure You don't want to take part in this Talk any more?")) {
                    var talkid = $("#messageId").attr('value');
                    var userid = $("#userId").attr('value');
                    window.location = '/index.php/apps/owncollab_talks/removeuser/' + talkid + '/' + userid;
                }
                break;
            case 'mark':
                document.getElementById('mark-talk-as').fadeIn("slow");
                break;
            case 'read':
            case 'unread':
            case 'finished':
                var messid = $("#messageId").attr('value');
                window.location = '/index.php/apps/owncollab_talks/mark/'+messid+'/'+action;
                break;
            case 'add-subscribers':
                break;
            default:
                break;
        }

    }

    function menuSelect(select) {
        var menuitems = ['MainMenu-begin', 'MainMenu-subscribers', 'MainMenu-mytalks', 'MainMenu-attachments', 'MainMenu-all'];
        menuitems.forEach(function(item) {
            if (item == select) {
                //var m = $(item);
                var m = document.getElementById(item);
                if (!(m.className == 'active')) {
                    m.className = 'active';
                }
            }
            else {
                var m = document.getElementById(item);
                if (m.className == 'active') {
                    m.className = '';
                }
            }
        })
    }

})(jQuery, OC, app);
