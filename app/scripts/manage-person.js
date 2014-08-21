(function () {
  'use strict';
  if($('#manage-person').length === 0) return;
 
  var urlParams = {},
      person = {},
      firstName = document.querySelector('#first-name'),
      lastName = document.querySelector('#last-name'),
      description = document.querySelector('#description'),
      adult = document.querySelector('#adult'),
      active = document.querySelector('#active'),
      updateBtn = document.querySelector('#update'),
      cancelBtn = document.querySelector('#cancel'),
      deleteBtn = document.querySelector('#delete');

  (window.onpopstate = function () {
      var match,
          pl     = /\+/g,  // Regex for replacing addition symbol with a space
          search = /([^&=]+)=?([^&]*)/g,
          decode = function (s) { return decodeURIComponent(s.replace(pl, " ")); },
          query  = window.location.search.substring(1);

      urlParams = {};
      while ((match = search.exec(query)) !== null)
        urlParams[decode(match[1])] = decode(match[2]);
  }).call();

  // Redirect the user to the attendance page if no
  // id was specified in the url parameters
  if(!urlParams.id) window.location = 'attendance.html';

  function populateForm(person) {
    firstName.value = person.first_name;
    lastName.value = person.last_name;
    description.value = person.description;
    adult.checked = person.adult;
    active.checked = person.active;
  }

  function onUpdateClick() {
    var first_name = $.trim(firstName.value),
        last_name = $.trim(lastName.value),
        descr = $.trim(description.value);
    if(validateUpdate(first_name, last_name, descr)) {
      savePerson({
        id: person.id,
        first_name: first_name,
        last_name: last_name,
        description: descr,
        adult: adult.checked,
        active: active.checked
      });
    }
  }

  function validateUpdate(first_name, last_name, descr) {
    var msg = '',
        firstNameSpecified = !!first_name,
        lastNameSpecified = !!last_name,
        descriptionSpecified = !!descr;
    if(!firstNameSpecified && lastNameSpecified) {
      msg += 'First Name cannot be blank if Last Name is specified<br />';
    } else if(firstNameSpecified && !lastNameSpecified) {
      msg += 'Last Name cannot be blank if First Name is specified<br />';
    } else if(!firstNameSpecified && !lastNameSpecified && !descriptionSpecified) {
      msg += 'Must specify either First and Last Name or Description<br />';
    }
    if(msg) {
      $().toastmessage('showErrorToast', msg);
      return false;
    }
    return true;
  }

  function onDeleteClick() {
    if(confirm("Deleting someone also deletes their attendance history. Continue?")) {
      deletePerson();
    }
  }

  function onCancelClick() {
    populateForm(person);
  }

  updateBtn.addEventListener('click', onUpdateClick);
  cancelBtn.addEventListener('click', onCancelClick);
  deleteBtn.addEventListener('click', onDeleteClick);

  function loadPerson() {
    $.ajax({
      type: 'GET',
      url: 'ajax/get_person.php',
      data: { id: urlParams.id }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        person = data.person;
        populateForm(data.person);
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error loading");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error loading person");
    });
  }

  function savePerson(person) {
    $.ajax({
      type: 'POST',
      url: 'ajax/save_person.php',
      data: { person: JSON.stringify(person) }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        $().toastmessage('showSuccessToast', "Save successful");
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error saving person");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error saving person");
    });
  }

  function deletePerson() {
    $.ajax({
      type: 'POST',
      url: 'ajax/delete_person.php',
      data: { id: person.id }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        window.location = 'attendance.html';
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error deleting person");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error deleting person");
    });
  }

  checkLoginStatus(loadPerson);
})();