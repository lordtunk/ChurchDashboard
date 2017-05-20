!function(){"use strict";function s(){n&&($("#username").val(n.username),$("#is-site-admin").attr("checked","1"==n.is_site_admin),$("#is-user-admin").attr("checked","1"==n.is_user_admin))}function e(s){var e="";return 0===s.username.length&&(e+="Username cannot be blank<br />"),s.username.length>50&&(e+="Username cannot exceed 50 characters<br />"),n||(0===s.password.length&&(e+="Password cannot be blank<br />"),s.password.length>50&&(e+="Password cannot exceed 50 characters<br />"),s.password!=s.confirm_password&&(e+="Passwords do not match<br />")),e?($().toastmessage("showErrorToast",e),!1):!0}function r(){var s=$("#is-site-admin"),r={username:$.trim($("#username").val()),is_user_admin:$("#is-user-admin").is(":checked"),homepage:"index.php"};s.length>0&&(r.is_site_admin=s.is(":checked")),n?r.id=n.id:(r.password=$("#password").val(),r.confirm_password=$("#confirm-password").val()),e(r)&&(n||(r.password=sha256_digest(r.password),r.confirm_password=sha256_digest(r.confirm_password)),$.ajax({type:"POST",url:"ajax/manage_user.php",data:{user:JSON.stringify(r)}}).done(function(s){var e=JSON.parse(s);e.success?n?$().toastmessage("showSuccessToast","Save successful"):window.location="index.php":1===e.error?logout():2===e.error?window.location="attendance.php":$().toastmessage("showErrorToast",e.msg||"Error creating user")}).fail(function(){$().toastmessage("showErrorToast","Error creating user")}))}function a(){$.ajax({type:"POST",url:"ajax/reset_password.php",data:{id:n.id}}).done(function(s){var e=JSON.parse(s);e.success?($().toastmessage("showSuccessToast","Password reset successful"),$("#reset-password-text").text("Password was set to: "+e.password)):1===e.error?logout():2===e.error?window.location="attendance.php":$().toastmessage("showErrorToast",e.msg||"Error resetting password")}).fail(function(){$().toastmessage("showErrorToast","Error resetting password")})}function o(){confirm("Are you sure you want to delete this user account? This cannot be undone")&&t()}function t(){$.ajax({type:"POST",url:"ajax/delete_user.php",data:{id:n.id}}).done(function(s){var e=JSON.parse(s);e.success?window.location="list-users.php":1===e.error?logout():2===e.error?window.location="attendance.php":$().toastmessage("showErrorToast",e.msg||"Error deleting user")}).fail(function(){$().toastmessage("showErrorToast","Error deleting user")})}var n=usr;s(),document.getElementById("save").addEventListener("click",r),$("#delete").on("click",o),$("#reset-password").on("click",a)}();