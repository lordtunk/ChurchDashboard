(function () {
    'use strict';
	
	function populateForm() {
		/* jshint ignore:start */
		if(!editUser) return;
		
		$('#username').val(editUser.username);
		$('#is-site-admin').attr('checked', editUser.is_site_admin == "1");
		$('#is-user-admin').attr('checked', editUser.is_user_admin == "1");
		/* jshint ignore:end */
	}
    
    function validateUser(user) {
        var msg = '';
		
        if(user.username.length === 0) {
            msg += 'Username cannot be blank<br />';
        }
        if(user.username.length > 50) {
            msg += 'Username cannot exceed 50 characters<br />';
        }
		if(!editUser) {	// jshint ignore:line
			if(user.password.length === 0) {
				msg += 'Password cannot be blank<br />';
			}
			if(user.password.length > 50) {
				msg += 'Password cannot exceed 50 characters<br />';
			}
			if(user.password != user.confirm_password) {
				msg += 'Passwords do not match<br />';
			}
		}
        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }
    
    function saveUser() {
		var isSiteAdmin = $('#is-site-admin');
		var user = {
			username: $.trim($('#username').val()),
			is_user_admin: $('#is-user-admin').is(':checked'),
			homepage: 'index.php'
		};
		if(isSiteAdmin.length > 0)
			user.is_site_admin = isSiteAdmin.is(':checked');
		
		if(editUser) {	// jshint ignore:line
			user.id = editUser.id; // jshint ignore:line
		} else {
			user.password = $('#password').val();
			user.confirm_password = $('#confirm-password').val();
		}
        if(!validateUser(user)) return;
		
		/* jshint ignore:start */
		if(!editUser) {
			user.password = sha256_digest(user.password);
			user.confirm_password = sha256_digest(user.confirm_password);
			
		}
		
        $.ajax({
          type: 'POST',
          url: 'ajax/manage_user.php',
          data: { user: JSON.stringify(user) }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
				if(editUser) {
					$().toastmessage('showSuccessToast', "Save successful");
				} else {
					window.location = 'index.php';
				}
            } else {
                if (data.error === 1) {
                    logout();
                } else if(data.error === 2) {
                    window.location = 'attendance.php';
                } else {
                    $().toastmessage('showErrorToast', data.msg || "Error creating user");
                }
            }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error creating user");
        });
		/* jshint ignore:end */
    }
	populateForm();
    document.getElementById('save').addEventListener('click', saveUser);
})();
