/* jshint ignore:start */
function checkLoginStatus(cb) {
  $.ajax({
    type: 'POST',
    url: 'ajax/check_login.php'
  })
  .done(function( msg ) {
    var data = JSON.parse(msg);
    if(data.success) {
      if(cb) cb();
    } else {
      window.location = 'index.html';
    }
  });
}

function logout() {
  if(confirm('If you have unsaved changes logging out will cause you to lose them. Continue?')) {
    $.ajax({
      type: 'POST',
      url: 'ajax/logout.php'
    })
    .done(function() {
      window.location = 'index.html';
    });
  }
}
/* jshint ignore:end */
(function () {
  'use strict';

  var loginBtn = document.querySelector('#login-btn');
  var usernameField = document.querySelector('#username');
  var passwordField = document.querySelector('#password');

  function onLoginClick() {
    var username = usernameField.value;
    var password = passwordField.value;
    if(validateLogin(username, password)) {
      login(username, $.md5(password));
    }
  }

  function validateLogin(username, password) {
    var msg = '';
    if(!$.trim(username)) {
      msg += 'Username cannot be blank<br />';
    }
    if(!password) {
      msg += 'Password cannot be blank<br />';
    }
    if(msg) {
      $().toastmessage('showErrorToast', msg);
      return false;
    }
    return true;
  }

  function login(username, password) {
    $.ajax({
      type: 'POST',
      url: 'ajax/login.php',
      data: { username: username, password: password }
    })
    .done(function( msg ) {
      var data = JSON.parse(msg);
      if(data.success) {
        window.location = 'attendance.html';
      } else {
        $().toastmessage('showErrorToast', 'Username or password is incorrect');
      }
    });
  }

  if(loginBtn) {
    loginBtn.addEventListener('click', onLoginClick);
    $(document).keypress(function(e) {
      if(e.which == 13) {
          onLoginClick();
      }
    });
  }
})();
