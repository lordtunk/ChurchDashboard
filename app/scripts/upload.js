(function () {
    'use strict';
    if($('.upload-form').length === 0) return;
    
    var access_token = '',
        video_name = '',
        email_address = '',
        video_uri = '',
        uploading = false;
//        video_name = 'Test',
//        email_address = 'stevvensa.550@gmail.com',
//        video_uri = 'https://api.vimeo.com/videos/134261442';
    
    /**
     * Called when files are dropped on to the drop target. For each file,
     * uploads the content to Drive & displays the results when complete.
     */
    function handleFileSelect(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        if(uploading) return;
        if(!validateFields()) return;
        
        var files = evt.dataTransfer.files; // FileList object.
        
        email = $('#email').val();
        video_name = $('#file-name').val();
        $("#upload-settings *").attr("disabled", "disabled").off('click');
        
        
        function callback() {
            uploading = true;
            var upgrade_to_1080 = document.getElementById("upgrade_to_1080").value;
            document.getElementById('progress-bar').className = 'loading';
            
            // Clear the results div
            var node = document.getElementById('results');
            while (node.hasChildNodes()) node.removeChild(node.firstChild);
            // Rest the progress bar
            updateProgress(0);

            var uploader = new MediaUploader({
                file: files[0],
                token: access_token,
                upgrade_to_1080: upgrade_to_1080,
                onError: function(data) {
                    uploading = false;
                    hideProgressBar();
                    var errorResponse = JSON.parse(data);
                    message = errorResponse.error;
                    var element = document.createElement("div");
                    element.setAttribute('class', "alert alert-danger");
                    element.appendChild(document.createTextNode(message));
                    document.getElementById('results').appendChild(element);
                    
                    email('3');
                    $("#upload-settings *").attr("disabled", false).on('click');
                },
                onProgress: function(data) {
                    updateProgress(data.loaded / data.total);
                },
                onComplete: function(videoId) {
                    uploading = false;
                    hideProgressBar();
                    var url = 'https://vimeo.com/'+videoId;
                    video_uri = 'https://api.vimeo.com/videos/'+videoId;
                    
                    var a = document.createElement('a');
                    a.appendChild(document.createTextNode(url));
                    a.setAttribute('href',url);
                    var element = document.createElement("div");
                    element.setAttribute('class', "alert alert-success");
                    element.appendChild(a);

                    document.getElementById('results').appendChild(element);
                    
                    updateTitle();
                }
            });
            uploader.upload();
        }
        
        checkQuota(files[0].size, callback);
    }
    
    function validateFields() {
        var msg = '';
        if(!document.getElementById('email').validity.valid)
            msg += 'Email address is invalid <br />';
        if(!$('#file-name').val())
            msg += 'File name cannot be empty';
        if(msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }
    
    /**
     * Dragover handler to set the drop effect.
     */
    function handleDragOver(evt) {
        evt.stopPropagation();
        evt.preventDefault();
        evt.dataTransfer.dropEffect = (uploading) ? 'none' : 'copy'; 
    }
    /**
     * Wire up drag & drop listeners once page loads
     */
    document.addEventListener('DOMContentLoaded', function () {
        var dropZone = document.getElementById('drop_zone');
        dropZone.addEventListener('dragover', handleDragOver, false);
        dropZone.addEventListener('drop', handleFileSelect, false);
    });
    ;
    /**
     * Updat progress bar.
     */
    function updateProgress(progress) {
        progress = Math.floor(progress * 100);
        $('#percent').text(progress+'%');
        $('#percent').css('width', progress+'%');
    }
    
    function hideProgressBar () {
        $('#percent').text('0%');
        $('#percent').css('width', '0%')
        setTimeout(function() { 
            document.getElementById('progress-bar').className='';
        }, 2000);
    }
    
    function updateStatus(status) {
        $('#status').text(status);
    }
    
    function checkQuota (size, cb) {
        var me = this;
        $('.upload-form').mask("Connecting to Vimeo...");
        $.ajax({
          type: 'POST',
          url: 'ajax/check_quota.php',
          data: { size: size }
        })
        .done(function(msg) {
          $('.upload-form').unmask();
          var data = JSON.parse(msg);
          if(data.success) {
              access_token = data.access_token;
              cb.call(me);
          } else {
            if(data.msg) {
              $().toastmessage('showErrorToast', data.msg);
            } else {
              $().toastmessage('showErrorToast', "Error checking Vimeo upload quota");
            }
          }
        })
        .fail(function() {
              $('.upload-form').unmask();
          $().toastmessage('showErrorToast', "Error checking Vimeo upload quota");
        });
    }
    
    function checkConversionStatus () {
        var me = this;
        $.ajax({
          type: 'GET',
          url: video_uri,
          beforeSend: function( xhr ) {
            xhr.setRequestHeader( 'Authorization', 'Bearer '+access_token );
          }
        })
        .done(function(data) {
          updateStatus('Upload complete! Video conversion status: '+data.status);
          if(data.status !== "available") {
              setTimeout(checkConversionStatus, 5000);
          } else {
              updateStatus('Upload complete! Video conversion complete!');
              email('1');
          }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error checking the video conversion status");
            email('3');
            $("#upload-settings *").attr("disabled", false).on('click');
        });
    }

    function updateTitle () {
        var me = this;
        $.ajax({
          type: 'PATCH',
          url: video_uri,
          beforeSend: function( xhr ) {
            xhr.setRequestHeader( 'Authorization', 'Bearer '+access_token );
          },
          data: {
              name: video_name
          }
        })
        .done(function(data) {
          checkConversionStatus();
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error checking the video conversion status");
            email('4');
            $("#upload-settings *").attr("disabled", false).on('click');
        });
    }
    
    function email (status) {
        status = status || '2';
        $.ajax({
          type: 'POST',
          url: 'ajax/send_email.php',
          data: { email: email_address, video_status: status }
        })
        .done(function(data) {
            $().toastmessage('showErrorToast', "Error sending status email");
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error sending status email");
        });
    }

    //document.getElementById('upload-btn').addEventListener('click', upload);
    //document.getElementById('cancel-btn').addEventListener('click', cancelUpload);
    
    checkLoginStatus();
    //checkQuota(0, updateTitle);
})();
