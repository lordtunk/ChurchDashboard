(function () {
    'use strict';
    if($('.settings-form').length === 0) return;
    
    var startingPointEmailField = document.querySelector('#starting-point-emails'),
        campusesField = document.querySelector('#campuses'),
        serviceLabelsField = document.querySelector('#service-labels'),
        defaultFirstServiceLabelField = document.querySelector('#default-service-label-first'),
        defaultSecondServiceLabelField = document.querySelector('#default-service-label-second'),
        defaultCampusField = document.querySelector('#default-campus');
    
    function validateSettings() {
        var msg = '',
            campuses = $.trim(campusesField.value),
            serviceLabels = $.trim(serviceLabelsField.value);
        if($.trim(startingPointEmailField.value).length > 500) {
            msg += 'Email(s) cannot exceed 500 characters<br />';
        }
        if(campuses.length > 500) {
            msg += 'Campus(es) cannot exceed 500 characters<br />';
        }
        if(!isValidIdLabelFormat(campuses)) {
            msg += 'Campus(es) have incorrect format<br />';
        }
        if(serviceLabels.length > 500) {
            msg += 'Service Label(s) cannot exceed 500 characters<br />';
        }
        if(!isValidIdLabelFormat(serviceLabels)) {
            msg += 'Service Label(s) have incorrect format<br />';
        }
        if(!defaultCampusField.validity.valid) {
            msg += 'Default Campus id must be greater than or equal to 1<br />';
        }
        if(!defaultFirstServiceLabelField.validity.valid) {
            msg += 'Default First Service Label id must be greater than or equal to 1<br />';
        }
        if(!defaultSecondServiceLabelField.validity.valid) {
            msg += 'Default Second Service Label id must be greater than or equal to 1<br />';
        }
        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }
    
    function isValidIdLabelFormat(settingsStr) {
        if(settingsStr.length === 0) return true;
        
        var settings = settingsStr.split(','),
            fields, id;
        for(var i=0; i<settings.length; i++) {
            if(settings[i].indexOf('|') === -1)
                return false;
            fields = settings[i].split('|');
            id = parseInt(fields[0]);
            if(isNaN(id) || id < 1)
                return false;
            if($.trim(fields[1]) === '')
                return false;
        }
        return true;
    }

    function updateSettings() {
        if(!validateSettings()) return;
        
        $.ajax({
          type: 'POST',
          url: 'ajax/save_settings.php',
          data: {
              startingPointEmails: startingPointEmailField.value,
              campuses: campusesField.value,
              serviceLabels: serviceLabelsField.value,
              defaultCampus: defaultCampusField.value,
              defaultFirstServiceLabel: defaultFirstServiceLabelField.value,
              defaultSecondServiceLabel: defaultSecondServiceLabelField.value
          }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                $().toastmessage('showSuccessToast', "Settings updated successfully");
            } else {
                if (data.error === 1) {
                    logout();
                } else if(data.error === 2) {
                    window.location = 'attendance.html';
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
                campusesField.value = data.campuses;
                serviceLabelsField.value = data.service_labels;
                defaultCampusField.value = data.default_campus;
                defaultFirstServiceLabelField.value = data.default_first_service_label;
                defaultSecondServiceLabelField.value = data.default_second_service_label;
            } else {
                if (data.error === 1) {
                    logout();
                } else if(data.error === 2) {
                    window.location = 'attendance.html';
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
