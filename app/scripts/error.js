(function() {
    var errorMsg = '';
    function displayMessage(msg) {
        var el = document.getElementById('error-container'),
            haveMsg = !!msg;
        if(el) {
            document.getElementById('error-msg').innerHTML = haveMsg ? msg : '';
            el.style.display = haveMsg ? '' : 'none';
        } else if(haveMsg) {
            alert(msg);
        }
    }
    function emailError() {
        $.ajax({
            type: 'POST',
            url: 'ajax/email_error.php',
            data: {
                msg: errorMsg
            }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                displayMessage(errorMsg + '<br /><br /> Email sent');
            } else {
                displayMessage(errorMsg + '<br /><br /> Error sending email');
            }
        })
        .fail(function() {
            displayMessage(errorMsg + '<br /><br /> Error sending email');
        });
    }
    window.onerror = function (message, file, line, col, error) {
        $('.masked').unmask();
        message = error || message || '';
        file = file || '';
        line = line || '';
        col = col || '';
        
        errorMsg = 'Message: ' + message + '<br />File: ' + file + '<br />Line: ' + line + '<br />Column: ' + col;
        
        displayMessage(errorMsg);
    };
    
    document.querySelector('#email-error').addEventListener('click', emailError);
})();