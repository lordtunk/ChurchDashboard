(function () {
    'use strict';
	// Do this so that we only have to ignore this line for jshint
	var editUser = usr;		// jshint ignore:line
	
	function populateForm() {
		if(!editUser) return;
		
		$('#username').val(editUser.username);
		$('#is-site-admin').attr('checked', editUser.is_site_admin == "1");
		$('#is-user-admin').attr('checked', editUser.is_user_admin == "1");
	}
    
    function validateUser(user) {
        var msg = '';
		
        if(user.username.length === 0) {
            msg += 'Username cannot be blank<br />';
        }
        if(user.username.length > 50) {
            msg += 'Username cannot exceed 50 characters<br />';
        }
		if(!editUser) {
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
		
		if(editUser) {
			user.id = editUser.id;
		} else {
			user.password = $('#password').val();
			user.confirm_password = $('#confirm-password').val();
		}
        if(!validateUser(user)) return;
		
		if(!editUser) {
			/* jshint ignore:start */
			user.password = sha256_digest(user.password);
			user.confirm_password = sha256_digest(user.confirm_password);
			/* jshint ignore:end */
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
    }
	
	function resetPassword() {
		$.ajax({
          type: 'POST',
          url: 'ajax/reset_password.php',
          data: { id: editUser.id }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
				$().toastmessage('showSuccessToast', "Password reset successful");
				$('#reset-password-text').text('Password was set to: '+data.password);
            } else {
                if (data.error === 1) {
                    logout();
                } else if(data.error === 2) {
                    window.location = 'attendance.php';
                } else {
                    $().toastmessage('showErrorToast', data.msg || "Error resetting password");
                }
            }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error resetting password");
        });
	}
	
	function onDeleteClick() {
		if(confirm('Are you sure you want to delete this user account? This cannot be undone')) {
			deleteUser();
		}
	}
	
	function deleteUser() {
		$.ajax({
          type: 'POST',
          url: 'ajax/delete_user.php',
          data: { id: editUser.id }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
				window.location = 'list-users.php';
            } else {
                if (data.error === 1) {
                    logout();
                } else if(data.error === 2) {
                    window.location = 'attendance.php';
                } else {
                    $().toastmessage('showErrorToast', data.msg || "Error deleting user");
                }
            }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error deleting user");
        });
	}
	
	populateForm();
    document.getElementById('save').addEventListener('click', saveUser);
	// Use jQuery because the buttons might not exist
	$('#delete').on('click', onDeleteClick);
	$('#reset-password').on('click', resetPassword);
})();
