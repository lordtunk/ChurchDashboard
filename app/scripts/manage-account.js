(function () {
	'use strict';
 
	var updateBtn = document.querySelector('#update'),
		usernameField = document.querySelector('#username'),
		homepageField = document.querySelector('#homepage'),
		oldPasswordField = document.querySelector('#password'),
		newPasswordField = document.querySelector('#new-password'),
		confirmPasswordField = document.querySelector('#confirm-password');
		
	function populateForm() {
		/* jshint ignore:start */
		if(!user) return;
		usernameField.value = user.username;
		if(user.homepage)
			homepageField.value = user.homepage;
		/* jshint ignore:end */
	} 
  
	function update() {
		var msg = '';
		
		var username = $.trim(usernameField.value);
		var homepage = $.trim(homepageField.value);
		var oldPassword = $.trim(oldPasswordField.value);
		var newPassword = $.trim(newPasswordField.value);
		var confirmPassword = $.trim(confirmPasswordField.value);
		
		var account = {
			username: username,
			homepage: homepage
		};
		
		if(username === '')
			msg += 'Username cannot be blank<br />';
		else if(username.length > 100)
			msg += 'Username cannot exceed 100 characters<br />';
		
		if((oldPassword+newPassword+confirmPassword) !== '') {
			if(oldPassword === '' || newPassword === '' || confirmPassword === '') {
				msg += 'To change your password all fields must be filled in<br />';
			} else if(newPassword != confirmPassword) {
				msg += 'New Password and Confirm Password must match<br />';
			} else {
				/* jshint ignore:start */
				account.oldPassword = sha256_digest(oldPassword);
				account.newPassword = sha256_digest(newPassword);
				/* jshint ignore:end */
			}
		}
		if(msg) {
			$().toastmessage('showErrorToast', msg);
			return;
		}
		
		doUpdate(account);
	}
  
	function doUpdate(account) {
		$('.manage-account-form').mask('Saving...');
		$.ajax({
			type: 'POST',
			url: 'ajax/manage-account.php',
			data: { user: JSON.stringify(account) }
		})
		.done(function(msg) {
			$('.manage-account-form').unmask();
			var data = JSON.parse(msg);
			if(data.success) {
				$().toastmessage('showSuccessToast', "Save successful");
				oldPasswordField.value = '';
				newPasswordField.value = '';
				confirmPasswordField.value = '';
			} else {
				if(data.error === 1) {
					logout();
				} else if(data.exception) {
					$().toastmessage('showErrorToast', data.exception);
				} else {
					$().toastmessage('showErrorToast', "Error updating account");
				}
			}
		})
		.fail(function() {
			$('.manage-account-form').unmask();
			$().toastmessage('showErrorToast', "Error updating account");
		});
	}
  
	updateBtn.addEventListener('click', update);
	
	populateForm();
})();
