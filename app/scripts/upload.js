(function () {
    'use strict';
    if($('.upload-form').length === 0) return;
    
    var ticket_id = '',
        access_token = '',
        ticket_uri = '',
        upload_link = '',
        complete_uri = '',
        fileSize = 0,
        fileType = 'video/mp4',
        fileName = 'Temp',
        progress = document.querySelector('.percent'),
        cancel = false;

    function parseFile (file) {
        var chunkSize  = 64 * 1024; // bytes
        var offset     = 0;
        var self       = this; // we need a reference to the current object
        var block      = null;
        var start      = 0;
        prepUpload();
        function onRead (evt) {
            if (evt.target.error == null) {
                start = offset;
                offset += evt.target.result.length;

                if (offset >= fileSize) {
                    $('#percent').text('100%');
                    $('#status').text('Done!');
                    return;
                } else {
                    var percent = ((offset/fileSize)*100).toFixed(2);
                    $('#percent').text(percent+'%');
                    $('#percent').css('width', percent+'%')
                }
                if(cancel === true) {
                    $('#status').text('Cancelled');
                    hideProgressBar();
                } else {
                    var cb = function() {
                        block(offset, chunkSize, file);
                    };
                    // If the first read
                    if(start === 0)
                        uploadFirstChunk(evt.target.result, cb);
                    else
                        uploadChunk(start, offset, evt.target.result, cb);
                }
            } else {
                console.log("Read error: " + evt.target.error);
                return;
            }
        }

        block = function (_offset, length, _file) {
            var r = new FileReader();
            var blob = _file.slice(_offset, length + _offset);
            r.onload = onRead;
            r.readAsText(blob);
        };

        block(offset, chunkSize, file);
    }

    function upload () {
        var f = $('#files')[0];
        if(f.files.length === 0) return;
        fileSize = f.files[0].size;
        fileType = f.files[0].type;
        fileName = $('#file-name').text();
        
        $('#upload-btn')[0].style.setProperty('display', 'none');
        generateTicket(fileSize, function() {
            parseFile(f.files[0]);
        });
    }

    function cancelUpload () {
        cancel = true;
        $('#status').text('Cancelling...');
    }

    function prepUpload () {
        cancel = false;
        document.getElementById('progress-bar').className = 'loading';
        document.getElementById('status').className = 'loading';
        $('#cancel-btn')[0].style.setProperty('display', 'inline-block');
        $('#percent').text('0%');
        $('#status').text('Uploading...');
        $('#percent').css('width', '0%');
        $("#upload-settings *").attr("disabled", "disabled").off('click');
    }

    function hideProgressBar () {
        $('#upload-btn')[0].style.setProperty('display', 'inline-block');
        $('#cancel-btn')[0].style.setProperty('display', 'none');
        $("#upload-settings *").attr("disabled", false).on('click');
        setTimeout(function() { 
            document.getElementById('progress-bar').className=''; 
            document.getElementById('status').className='';
        }, 2000);
    }
    
    function uploadFirstChunk (data, length, cb) {
        var me = this;
        $.ajax({
          type: 'PUT',
          url: upload_link,
          contentType: fileType || 'application/octet-stream',
          processData: false,
          data: data,
          beforeSend: function( xhr ) {
              //xhr.setRequestHeader( 'X-File-Name', fileName );
              //xhr.setRequestHeader( 'Authorization', 'Bearer '+access_token );
              //xhr.setRequestHeader( 'Content-Length', fileSize );
              xhr.setRequestHeader( 'Content-Type', fileType );
          }
        })
        .done(function(msg) {
            console.log(msg);
            verifyUpload(length, cb);
        })
        .fail(function(xhr, status, error) {
            console.log(status, error);
            $().toastmessage('showErrorToast', status+": Error verifying the upload: "+error);
        });
    }
    
    function uploadChunk (startByte, endByte, data, cb) {
        var me = this;
        $.ajax({
          type: 'PUT',
          url: upload_link,
          contentType: fileType || 'application/octet-stream',
          processData: false,
          data: data,
          beforeSend: function( xhr ) {
              //xhr.setRequestHeader( 'Authorization', 'Bearer '+access_token );
              xhr.setRequestHeader( 'Content-Range', 'bytes '+startByte+'-'+endByte+'/'+fileSize );
          }
        })
        .done(function(msg) {
            console.log(msg);
            verifyUpload(endByte-startByte, cb);
        })
        .fail(function(xhr, status, error) {
            console.log(status, error);
            $().toastmessage('showErrorToast', status+": Error verifying the upload: "+error);
        });
    }
    
    function generateTicket (size, cb) {
        var me = this;
        $('.upload-form').mask("Connecting to Vimeo...");
        $.ajax({
          type: 'POST',
          url: 'ajax/generate_ticket.php',
          data: { size: size }
        })
        .done(function(msg) {
          $('.upload-form').unmask();
          var data = JSON.parse(msg);
          if(data.success) {
              ticket_uri = data.ticket_uri;
              ticket_id = data.ticket_id;
              upload_link = data.upload_link;
              complete_uri = data.complete_uri;
              access_token = data.access_token;
              cb.call(me);
          } else {
            if(data.msg) {
              $().toastmessage('showErrorToast', data.msg);
            } else {
              $().toastmessage('showErrorToast', "Error establishing connection to Vimeo");
            }
          }
        })
        .fail(function() {
              $('.upload-form').unmask();
          $().toastmessage('showErrorToast', "Error searching people");
        });
    }
    
    function verifyUpload (offset, cb) {
        var me = this;
        $.ajax({
          type: 'PUT',
          url: upload_link,
          beforeSend: function( xhr ) {
              //xhr.setRequestHeader( 'Authorization', 'Bearer '+access_token );
              //xhr.setRequestHeader( 'Content-Length', '0' );
              xhr.setRequestHeader( 'Content-Range', 'bytes */*' );
          }
        })
        .done(function(msg) {
             console.log(msg);
            // Verify that the Content-Range is from 0-offset
            cb.call(me);
        })
        .fail(function(xhr, status, error) {
            console.log(status, error);
            $().toastmessage('showErrorToast', status+": Error verifying the upload: "+error);
        });
    }

    function finishUpload () {
        $.ajax({
          url: upload_link,
          type: 'DELETE',
          beforeSend: function( xhr ) {
            xhr.setRequestHeader( 'Authorization', 'Bearer '+access_token );
          }
        }).success(function() {
            $('#status').text('Successfully finished uploading the video');
        }).failure(function() {
            $('#status').text('Error finishing the upload video process');
        });
    }

    document.getElementById('upload-btn').addEventListener('click', upload);
    document.getElementById('cancel-btn').addEventListener('click', cancelUpload);
    
    checkLoginStatus();
})();
