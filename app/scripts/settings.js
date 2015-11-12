(function () {
    'use strict';
    if($('.settings-form').length === 0) return;
    
    var startingPointEmailField = document.querySelector('#starting-point-emails');
    
    function validateSettings() {
        var msg = '';
        if($.trim(startingPointEmailField.value).length > 500) {
            msg += 'Email(s) cannot exceed 500 characters';
        }
        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }

    function updateSettings() {
        validateSettings();
        $.ajax({
          type: 'POST',
          url: 'ajax/save_settings.php',
          data: {
              startingPointEmails: startingPointEmailField.value
          }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                $().toastmessage('showSuccessToast', "Settings updated successfully");
            } else {
                if (data.error === 1) {
                    logout();
                } else {
                    $().toastmessage('showErrorToast', "Error updating settings");
                }
            }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error updating settings");
        });
    }
    
    function loadSettings() {
        $.ajax({
          type: 'POST',
          url: 'ajax/get_settings.php'
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                startingPointEmailField.value = data.starting_point_emails;
            } else {
                if (data.error === 1) {
                    logout();
                } else {
                    $().toastmessage('showErrorToast', "Error loading settings");
                }
            }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error loading settings");
        });
    }


    document.getElementById('update').addEventListener('click', updateSettings);
    
    checkLoginStatus(loadSettings);
})();
