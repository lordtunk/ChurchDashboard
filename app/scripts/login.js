/* jshint ignore:start */
function checkLoginStatus(cb, preventRedirect) {
    $.ajax({
        type: 'POST',
        url: 'ajax/check_login.php'
    })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                if (cb) cb();
            } else if(preventRedirect !== true) {
                window.location = 'login.php?url='+encodeURIComponent(window.location);
            }
        });
}

function logout() {
    if (confirm('If you have unsaved changes logging out will cause you to lose them. Continue?')) {
        $.ajax({
            type: 'POST',
            url: 'ajax/logout.php'
        })
            .done(function() {
                window.location = 'login.php';
            });
    }
}
/* jshint ignore:end */
(function() {
    'use strict';

    var loginBtn = document.querySelector('#login-btn');
    var usernameField = document.querySelector('#username');
    var passwordField = document.querySelector('#password');

    function onLoginClick() {
        var username = usernameField.value;
        var password = passwordField.value;
        if (validateLogin(username, password)) {
            login(username, $.md5(password));
        }
    }

    function validateLogin(username, password) {
        var msg = '';
        if (!$.trim(username)) {
            msg += 'Username cannot be blank<br />';
        }
        if (!password) {
            msg += 'Password cannot be blank<br />';
        }
        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }
    
    function getUrlParameter(sParam)
    {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) 
        {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] == sParam) 
            {
                return decodeURIComponent(sParameterName[1]);
            }
        }
        return null;
    }  

    function login(username, password) {
        $.ajax({
            type: 'POST',
            url: 'ajax/login.php',
            data: {
                username: username,
                password: password
            }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                var url = getUrlParameter('url');
                if(url) {
                    window.location = url;
                } else {
                    window.location = 'attendance.php';
                }
            } else {
                $().toastmessage('showErrorToast', 'Username or password is incorrect');
            }
        });
    }

    if (loginBtn) {
        loginBtn.addEventListener('click', onLoginClick);
        $(document).keypress(function(e) {
            if (e.which == 13) {
                onLoginClick();
            }
        });
    }
})();