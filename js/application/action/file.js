if(App.namespace){App.namespace('Action.File', function(App){
    /**
     * @namespace App.Action.File
     */
    var _ = {};

    /**
     *
     * @namespace App.Action.File.__
     */
    _.__ = function(){};


    _.clickUpload = function(){




        /*
        document.getElementById("uploadBtn").onchange = function () {
            document.getElementById("uploadFile").value = this.value;
            var file = document.getElementById('uploadBtn').files[0];
            $('#uploadimg').show();
            uploadFile(file, function(response){
                try {
                    var r = JSON.parse(response);
                    var file = r[0];
                    $(".uploadedfiles ul").append('<li><div class="thumbnail" style="background-image: url('+file.icon+')"></div><div class="name">'+file.name+'</div><div class="size">'+sizeRoundedString(file.size)+'</div><div class="clear"></div><input type="hidden" name="upload-files[]" value="'+file.id+'"></li>');

                }catch (e){}
            });
            $('#uploadimg').hide();
        };
        */
    };

    /**
     * @namespace App.Action.File.uploadFile
     * @param myFile
     * @param callback
     * @returns {boolean}
     */
    _.uploadFile = function (myFile, callback) {

        var fd = new FormData();
        var folderName = '', d = new Date();
        var d1 = ''+d.getFullYear();
        var d2 = ''+(d.getMonth() + 1);
        var d3 = ''+d.getDate();
        if(d2.length === 1) d2 = '0' + d2;
        if(d3.length === 1) d3 = '0' + d3;
        folderName = d1 +'-'+ d2 +'-'+ d3;

        fd.append('files[]', myFile);
        fd.append('dir', '/');
        fd.append('file_directory', 'Talks/' + folderName);

        jQuery.ajax({
            url: "/index.php/apps/files/ajax/upload.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            success: function(response) {
                if(typeof callback === 'function')
                    callback.call({}, response);
            },
            error: function(jqXHR, textStatus, errorMessage) {
                console.error('Error on upload file >>> ', textStatus, errorMessage);
            }
        });
    };

    /**
     * @namespace App.Action.File.fileListSourceData

    _.fileListSourceData = []; */

    /**
     * @namespace App.Action.File.selectFilesData
     */
    _.selectFilesData = {};
    /**
     * @namespace App.Action.File.selectShareFiles
     */
    _.selectShareFiles = function(parentElement) {

        jQuery('input[type=checkbox]', parentElement).change(function(event) {
            var target = event['target'];
            if(target.checked) {
                var info = {};
                info['name'] = target.getAttribute('data-name');
                info['path'] = target.getAttribute('data-path');
                info['id'] = target.getAttribute('data-id');
                info['parentid'] = target.getAttribute('data-parentid');
                _.selectFilesData[info['id']] = info;
            } else {
                var _id = target.getAttribute('data-id');
                if(_.selectFilesData[_id] !== undefined)
                    delete _.selectFilesData[_id];
            }
        });

    };


        return _;
})}