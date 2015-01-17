(function () {
  'use strict';
  if($('#manage-person').length === 0) return;
 
  $( '#follow-up-date' ).datepicker();
  var urlParams = {},
      person = {},
      visitors = [],
      firstName = document.querySelector('#first-name'),
      lastName = document.querySelector('#last-name'),
      description = document.querySelector('#description'),
      adult = document.querySelector('#adult'),
      active = document.querySelector('#active'),
      baptized = document.querySelector('#baptized'),
      saved = document.querySelector('#saved'),
      member = document.querySelector('#member'),
      visitor = document.querySelector('#visitor'),
      street1 = document.querySelector('#street1'),
      street2 = document.querySelector('#street2'),
      city = document.querySelector('#city'),
      zip = document.querySelector('#zip'),
      primaryPhone = document.querySelector('#primary-phone'),
      secondaryPhone = document.querySelector('#secondary-phone'),
      stateSelect = document.querySelector('#state'),
      updateBtn = document.querySelector('#update'),
      cancelBtn = document.querySelector('#cancel'),
      deleteBtn = document.querySelector('#delete'),
      addFollowUpBtn = document.querySelector('#add-follow-up'),
      editFollowUpBtn = document.querySelector('#edit-follow-up'),
      deleteFollowUpBtn = document.querySelector('#d-follow-up'),
      addClearBtn = document.querySelector('#add-clear'),
      addCopyBtn = document.querySelector('#add-copy'),
      addCloseBtn = document.querySelector('#add-close'),
      closeBtn = document.querySelector('#close'),
      dialog = $('.dialog-form').dialog({
        autoOpen: false,
        height: 400,
        width: 510,
        modal: true
      }),
      followUpId = dialog[0].querySelector('#follow-up-id'),
      followUpType = dialog[0].querySelector('#follow-up-type'),
      followUpDate = dialog[0].querySelector('#follow-up-date'),
      followUpVisitors = dialog[0].querySelector('#follow-up-visitors'),
      followUpComments = dialog[0].querySelector('#follow-up-comments'),
      $dialogTitle = $('.ui-dialog-title').text('Edit Follow Up'),
      followUpIdSequence = -1,
      noChangesMade = true,
      mapPanel = document.querySelector('#map-panel'),
      gMapsImgUrl = '//maps.googleapis.com/maps/api/staticmap?zoom=11&size=400x400&markers=color:red%7Clabel:A|',
      gMapsUrl = 'https://www.google.com/maps/place/',
      followUpTypeData = {
	1: "Phone Call",
	2: "Visit",
	3: "Communication Card",
	4: "Entered in The City"
      };

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
  
  function populateStates(states) {
    var $select = $('#state');
    $.each(states,function(ind, state) {
        $select.append('<option value=' + state.abbreviation + '>' + state.name + '</option>');
    });
    $select.val('OH');
  }
  
  function populateTypes() {
    var $select = $('#follow-up-type');
    $.each(followUpTypeData,function(typeCd, type) {
        $select.append('<option value=' + typeCd + '>' + type + '</option>');
    });
    $select.val('2');
  }
 
  function populateForm(p) {
    firstName.value = p.first_name;
    lastName.value = p.last_name;
    description.value = p.description;
    adult.checked = p.adult;
    active.checked = p.active;
    baptized.checked = p.baptized;
    saved.checked = p.saved;
    member.checked = p.member;
    visitor.checked = p.visitor;

    street1.value = p.street1;
    street2.value = p.street2;
    city.value = p.city;
    zip.value = p.zip;
    if(p.state)
      stateSelect.value = p.state;
    
    email.value = p.email;
    primaryPhone.value = p.primary_phone;
    secondaryPhone.value = p.secondary_phone;
    
    updateMap(p);
    processFollowUps(p.follow_ups);
  }
  
  function updateMap(p) {
    var addr = getAddressString(p);
    // Don't generate a map if the string is just 'OH' since that is the default
    if(addr === 'OH' || addr === '')
      mapPanel.innerHTML = '';
    else
      mapPanel.innerHTML = '<a href="'+gMapsUrl+addr+'" target="_blank"><img border="0" src="'+gMapsImgUrl+addr+'" /></a>';
  }

  function onUpdateClick() {
    var p = {
      id: person.id,
      first_name: $.trim(firstName.value),
      last_name: $.trim(lastName.value),
      description: $.trim(description.value),
      adult: adult.checked,
      active: active.checked,
      baptized: baptized.checked,
      saved: saved.checked,
      member: member.checked,
      visitor: visitor.checked,
      street1: $.trim(street1.value),
      street2: $.trim(street2.value),
      city: $.trim(city.value),
      zip: $.trim(zip.value),
      state: $.trim(stateSelect.value),
      email: $.trim(email.value),
      primary_phone: $.trim(primaryPhone.value),
      secondary_phone: $.trim(secondaryPhone.value),
      follow_ups: getFollowUps()
    };
    
    if(validateUpdate(p)) {
      savePerson(p);
    }
  }
  
  function getFollowUps() {
    var followUps = [];
    $('#follow-up-table tbody tr').each(function(ind, row) {
      followUps.push({
	id: row.getAttribute('follow_up_id'),
	typeCd: row.children[0].getAttribute('typeCd') || '',
	date: row.children[1].innerHTML || '',
	comments: row.children[3].innerHTML || '',
	visitorsIds: row.children[2].getAttribute('visitorsIds').split(',') || '',
	visitors: row.children[2].innerHTML.split(', ') || ''
      });
    });
    return followUps;
  }

  function validateUpdate(p) {
    var msg = '',
        firstNameSpecified = !!p.first_name,
        lastNameSpecified = !!p.last_name,
        descriptionSpecified = !!p.description;
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
    noChangesMade = true;
  }

  updateBtn.addEventListener('click', onUpdateClick);
  cancelBtn.addEventListener('click', onCancelClick);
  deleteBtn.addEventListener('click', onDeleteClick);

  function loadStates() {
    $.ajax({
      type: 'GET',
      url: 'ajax/states.json'
    })
    .done(function(msg) {
      if($.isArray(msg)) {
	populateStates(msg);
      } else {
	var data = JSON.parse(msg);
	populateStates(data);
      }
      populateTypes();
      loadPerson();
      loadVisitors();
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error loading states");
    });
  }
  
  function loadVisitors() {
    $.ajax({
      type: 'GET',
      url: 'ajax/get_visitors.php'
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        visitors = data.people;
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error loading visitors");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error loading visitors");
    });
  }
  
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

  function savePerson(p) {
    $.ajax({
      type: 'POST',
      url: 'ajax/save_person.php',
      data: { person: JSON.stringify(p) }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
	updateMap(p);
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
  
  function deleteFollowUp(id) {
    $.ajax({
      type: 'POST',
      url: 'ajax/delete_follow_up.php',
      data: { id: id }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        $('#follow-up-table tr[follow_up_id='+id+']').remove();
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error deleting Follow Up");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error deleting Follow Up");
    });
  }
  
  function getDisplayName(person, withComma) {
    if(person === null) return '';
    
    var display = '';
    if(person.first_name || person.last_name) {
      if(person.last_name) display += person.last_name;
      
      if(person.last_name && person.first_name) {
        if(withComma === true)
          display += ", " + person.first_name;
        else
          display = person.first_name + ' ' + display;
      } else if(person.first_name) {
        display += person.first_name + ' ';
      }
    } else {
      display = person.description;
    }
    return display;
  }
  
  function clearFollowUpForm() {
    followUpType.value = '2';
    followUpDate.value = '';
    followUpComments.value = '';
    
    var inputs = followUpVisitors.querySelectorAll('input');
    for(var i=0; i<inputs.length; i++)
      inputs[i].checked = false;
  }
  
  function saveFollowUp() {
    var date = $.trim(followUpDate.value),
        type = $.trim(followUpType.value),
        comments = $.trim(followUpComments.value),
        visitors = [],
        visitorsIds = [];
    if(date === '' && comments === '') {
      $().toastmessage('showErrorToast', "Must specify either Date or Comments");
      return false;
    }
    if(comments.length > 5000) {
      $().toastmessage('showErrorToast', "Comments cannot exceed 5000 characters");
      return false;
    }
    
    var inputs = followUpVisitors.querySelectorAll('input');
    for(var i=0; i<inputs.length; i++) {
      if(!inputs[i].checked) continue;
      visitors.push($.trim(inputs[i].previousSibling.innerHTML));
      visitorsIds.push(inputs[i].getAttribute('personid'));
    }
    
    return {
      id: ($dialogTitle.text().indexOf('Edit') === -1) ? genFollowUpId() : followUpId.value,
      date: date,
      typeCd: type,
      type: followUpType.selectedOptions[0].text,
      comments: comments,
      visitors: visitors,
      visitorsIds: visitorsIds
    };
  }
  
  function processFollowUps(followUps) {
    detachClickListeners();
    $('#follow-up-table tbody tr').remove();
    for(var i=0; i<followUps.length; i++) {
      appendFollowUp(followUps[i]);
    }
    attachClickListeners();
  }
  
  function appendFollowUp(followUp) {
    if(!followUp.type && followUp.typeCd)
      followUp.type = followUpTypeData[followUp.typeCd] || '';
    $('#follow-up-table > tbody:last').append(
      '<tr follow_up_id="'+followUp.id+'">' +
        '<td typeCd="'+followUp.typeCd+'">'+followUp.type+'</td>' +
        '<td class="follow-up-table-date-col">'+followUp.date+'</td>' +
        '<td visitorsIds="'+followUp.visitorsIds.join(',')+'">'+followUp.visitors.join(', ')+'</td>' +
        '<td class="follow-up-table-comments-col">'+followUp.comments+'</td>' +
        '<td class="follow-up-table-button-col"><button class="edit-follow-up"><i class="fa fa-edit"></i></button><button class="delete-follow-up"><i class="fa fa-minus-circle"></i></button></td>' +
      '</tr>');
  }
  
  function updateFollowUpRow(followUp) {
    var children = $('#follow-up-table tr[follow_up_id='+followUp.id+']').children();
    children[0].setAttribute('typeCd', followUp.typeCd);
    children[0].innerHTML = followUp.type;
    children[1].innerHTML = followUp.date;
    children[2].innerHTML = followUp.visitors.join(', ');
    children[2].setAttribute('visitorsIds', followUp.visitorsIds.join(','));
    children[3].innerHTML = followUp.comments;
  }
  
  function setVisitors() {
    if(followUpVisitors.innerHTML.trim() === '') {
      var v;
      for(var i=0; i<visitors.length; i++) {
        v = visitors[i];
        followUpVisitors.innerHTML += 
          '<div class="check-field">' +
            '<label for="follow-up-by-'+v.id+'">'+getDisplayName(v)+'</label>' +
            '<input type="checkbox" personid="'+v.id+'" id="follow-up-by-'+v.id+'"/>' +
          '</div>';
      }
    }
  }
  
  function addFollowUp() {
    dialog.dialog('open');
    clearFollowUpForm();
    setVisitors();
    $dialogTitle.text('Add Follow Up');
  }
  function closeFollowUp() {
    dialog.dialog('close');
  }
  function doAddFollowUp() {
    var followUp = saveFollowUp();
    if(followUp === false) return false;
    appendFollowUp(followUp);
    $('button.edit-follow-up:last').on('click', onEditFollowUpClick);
    $('button.delete-follow-up:last').on('click', onDeleteFollowUpClick);
    noChangesMade = false;
    return followUp;
  }
  function doEditFollowUp() {
    var followUp = saveFollowUp();
    if(followUp === false) return false;
    updateFollowUpRow(followUp);
    noChangesMade = false;
    return followUp;
  }
  function addCopy() {
    if($dialogTitle.text().indexOf('Edit') === -1)
      doAddFollowUp();
    else
      doEditFollowUp();
  }
  function addClear() {
    var res;
    if($dialogTitle.text().indexOf('Edit') === -1)
      res = doAddFollowUp();
    else
      res = doEditFollowUp();
    
    if(res)
      clearFollowUpForm();
  }
  function addClose() {
    var res;
    if($dialogTitle.text().indexOf('Edit') === -1)
      res = doAddFollowUp();
    else
      res = doEditFollowUp();
    
    if(res)
      dialog.dialog('close');
  }
  
  function onEditFollowUpClick(e) {
    dialog.dialog('open');
    setVisitors();
    var row = e.currentTarget.parentElement.parentElement;
    
    followUpType.value = row.children[0].getAttribute('typeCd') || '';
    followUpDate.value = row.children[1].innerHTML || '';
    followUpComments.value = row.children[3].innerHTML || '';
    followUpId.value = row.getAttribute('follow_up_id');
    
    var visitorIdsString = row.children[2].getAttribute('visitorsIds') || '';
    var visitorIds = visitorIdsString.split(',');
    var inputs = followUpVisitors.querySelectorAll('input');
    for(var i=0; i<inputs.length; i++) {
      inputs[i].checked = visitorIds.indexOf(inputs[i].getAttribute('personid')) >= 0;
    }
    
    $dialogTitle.text('Edit Follow Up');
  }
  
  function onDeleteFollowUpClick(e) {
    if(confirm("Are you sure you would like to PERMANENTLY delete this Follow Up?")) {
      var id = e.currentTarget.parentElement.parentElement.getAttribute('follow_up_id');
      deleteFollowUp(id);
    }
  }
  
  function getAddressString(p) {
    var addr = '';
    addr += p.street1 || '';
    addr += ' ';
    addr += p.street2 || '';
    addr += ' ';
    addr += p.city || '';
    addr += ' ';
    addr += p.state || '';
    addr += ' ';
    addr += p.zip || '';
    
    return addr.trim();
  }
  
  function onClickLink(e) {
    if(!(noChangesMade || confirm("If you continue you will lose any unsaved changes. Continue?"))) {
      e.preventDefault();
    }
  }
  
  function attachClickListeners() {
    $('#attendance-nav').on('click', onClickLink);
    $('#reports-nav').on('click', onClickLink);
    $('button.edit-follow-up').on('click', onEditFollowUpClick);
    $('button.delete-follow-up').on('click', onDeleteFollowUpClick);
  }
  
  function detachClickListeners() {
    $('#attendance-nav').off('click', onClickLink);
    $('#reports-nav').off('click', onClickLink);
    $('button.edit-follow-up').off('click', onEditFollowUpClick);
    $('button.delete-follow-up').off('click', onDeleteFollowUpClick);
  }  
  
  function genFollowUpId() {
    return --followUpIdSequence;
  }
  
  addFollowUpBtn.addEventListener('click', addFollowUp);
  addCopyBtn.addEventListener('click', addCopy);
  addClearBtn.addEventListener('click', addClear);
  addCloseBtn.addEventListener('click', addClose);
  closeBtn.addEventListener('click', closeFollowUp);

  checkLoginStatus(loadStates);
})();