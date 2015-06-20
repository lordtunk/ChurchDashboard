(function () {
    'use strict';
    if($('.upload-form').length === 0) return;
    
    var hups = $('#files').hup({ read_method: 'readAsText', type: 'PUT' }),
        hup = hups[0],
        ticket_id = '',
        access_token = '';
    hup.input.on('fileListLoaded', function(event, data) {
            console.log(data);
            progress.style.width = '0%';
            progress.textContent = '0%';
            document.getElementById('progress_bar').className = 'loading';
        }).on('fileTypeError', function(event, data) {
            console.log(data);
        }).on('fileSizeError', function(event, data) {
            console.log(data);
        }).on('fileReadProgress', function(event, data) {
            var percentLoaded = Math.round((data.progress * 100) * 100) / 100;
            if (percentLoaded < 100) {
                progress.style.width = percentLoaded + '%';
                progress.textContent = percentLoaded + '%';
            }
        }).on('fileReadFinished', function(event, data) {
            console.log(data);
            // Ensure that the progress bar displays 100% at the end.
            progress.style.width = '100%';
            progress.textContent = '100%';
            setTimeout(function() { document.getElementById('progress_bar').className=''; }, 2000);
        }).on('fileReadAll', function(event, data) {
            console.log(data);
        }).on('fileUploadProgress', function(event, data) {
            console.log(data);
        }).on('fileUploadPause', function(event, data) {
            console.log(data);
        }).on('fileUploadResume', function(event, data) {
            console.log(data);
        }).on('fileUploadFinished', function(event, data) {
            console.log(data);
            // Ensure that the progress bar displays 100% at the end.
            progress.style.width = '100%';
            progress.textContent = '100%';
            setTimeout(function() { document.getElementById('progress_bar').className=''; }, 2000);
        }).on('fileUploadAll', function(event, data) {
            console.log(data);
        });
    

    
    var progress = document.querySelector('.percent');

    function abortRead() {
        debugger;
        if(hup.reader)
            hup.reader.abort();
    }
    
    function finishUpload() {
        $.ajax({
          url: "https://api.vimeo.com/users/guidechurch/tickets/"+ticket_id,
          type: 'DELETE',
          beforeSend: function( xhr ) {
            xhr.setRequestHeader( "Authorization", 'Bearer '+access_token );
          }
        }).success(function() {
            console.log('Successfully finished uploading the video');
        }).failure(function() {
            console.log('Error finishing the upload video process');
        });
    }

//    function errorHandler(evt) {
//        switch(evt.target.error.code) {
//          case evt.target.error.NOT_FOUND_ERR:
//            alert('File Not Found!');
//            break;
//          case evt.target.error.NOT_READABLE_ERR:
//            alert('File is not readable');
//            break;
//          case evt.target.error.ABORT_ERR:
//            break; // noop
//          default:
//            alert('An error occurred reading this file.');
//        }
//    }
    
//    function updateProgress(evt) {
//        // evt is an ProgressEvent.
//        if (evt.lengthComputable) {
//          var percentLoaded = Math.round((evt.loaded / evt.total) * 100);
//          //contents += evt.target.result.substring(lastLength, evt.loaded);
//          lastLength = evt.loaded;
//          // Increase the progress bar length.
//          if (percentLoaded < 100) {
//            progress.style.width = percentLoaded + '%';
//            progress.textContent = percentLoaded + '%';
//          }
//        }
//    }

//    function handleFileSelect(evt) {
//        // Reset progress indicator on new file selection.
//        progress.style.width = '0%';
//        progress.textContent = '0%';
//
//        reader = new FileReader();
//        reader.onerror = errorHandler;
//        reader.onprogress = updateProgress;
//        reader.onabort = function(e) {
//          console.log(e);
//          alert('File read cancelled');
//        };
//        reader.onloadstart = function(e) {
//          console.log(e);
//          document.getElementById('progress_bar').className = 'loading';
//        };
//        reader.onload = function(e) {
//          console.log(e, contents);
//          // Ensure that the progress bar displays 100% at the end.
//          progress.style.width = '100%';
//          progress.textContent = '100%';
//          setTimeout(function() { document.getElementById('progress_bar').className=''; }, 2000);
//        };
//        lastLength = 0;
//        contents = '';
//        // Read in the image file as a binary string.
//        reader.readAsText(evt.target.files[0]);
//        //reader.readAsBinaryString(evt.target.files[0]);
//    }
    
    document.getElementById('abort-btn').addEventListener('click', abortRead);
    
    checkLoginStatus();
})();
