(function () {
  'use strict';
  if($('.follow-ups-form').length === 0) return;
 
  $( '#follow-up-date' ).datepicker();
  var visitors = [],
      noChangesMade = true,
      $formTitle = $('#follow-ups-form-title'),
      followUpId = document.querySelector('#follow-up-id'),
      followUpPerson = document.querySelector('#follow-up-person'),
      followUpType = document.querySelector('#follow-up-type'),
      followUpDate = document.querySelector('#follow-up-date'),
      followUpVisitors = document.querySelector('#follow-up-visitors'),
      followUpComments = document.querySelector('#follow-up-comments'),
      addClearBtn = document.querySelector('#add-clear'),
      addCopyBtn = document.querySelector('#add-copy'),
      clearBtn = document.querySelector('#clear'),
      dialog = $('.dialog-form').dialog({
        autoOpen: false,
        height: 400,
        width: 510,
        modal: true
      }),
      selectPersonBtn = document.querySelector('#select-person-btn'),
      followUpIdSequence = -1,
      followUpTypeData = {
	1: "Phone Call",
	2: "Visit",
	3: "Communication Card",
	4: "Entered in The City",
	5: "Thank You Card Sent"
      };
  
  function populateTypes() {
    var $select = $('#follow-up-type');
    $.each(followUpTypeData,function(typeCd, type) {
        $select.append('<option value=' + typeCd + '>' + type + '</option>');
    });
    $select.val('2');
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
  
  function loadVisitors() {
    $.ajax({
      type: 'GET',
      url: 'ajax/get_visitors.php'
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        visitors = data.people;
	setVisitors();
	populateTypes();
	loadFollowUps();
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
  
  function loadFollowUps() {
    $.ajax({
      type: 'GET',
      url: 'ajax/get_follow_ups.php'
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        processFollowUps(data.follow_ups);
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
  
  function saveFollowUpEntry(f, cb, clear) {
    $.ajax({
      type: 'POST',
      url: 'ajax/save_follow_up.php',
      data: { follow_up: JSON.stringify(f) }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
	f.id = data.follow_up_id;
	cb.call(this, f, clear);
        $().toastmessage('showSuccessToast', "Save successful");
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error saving follow up");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error saving follow up");
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
    $formTitle.text('Add Follow Up');
    followUpPerson.innerHTML = '';
    followUpPerson.setAttribute('personid', '');
    followUpType.value = '2';
    followUpDate.value = '';
    followUpComments.value = '';
    
    var inputs = followUpVisitors.querySelectorAll('input');
    for(var i=0; i<inputs.length; i++)
      inputs[i].checked = false;
  }
  
  function saveFollowUp() {
    var name = $.trim(followUpPerson.innerHTML),
	personId = $.trim(followUpPerson.getAttribute('personid')),
	date = $.trim(followUpDate.value),
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
      id: isAdd() ? genFollowUpId() : followUpId.value,
      date: date,
      name: name,
      personId: personId,
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
    followUp.date = followUp.date || '';
    followUp.name = followUp.name || '';
    
    var name = followUp.name;
    if(followUp.personId >= 0) {
      name = '<a class="person_name" href="manage-person.html?id='+followUp.personId+'">'+name+'</a>';
    }
    $('#follow-up-table > tbody:last').append(
      '<tr follow_up_id="'+followUp.id+'">' +
	'<td data-th="Name" personid="'+followUp.personId+'">'+name+'</td>' +
        '<td data-th="Type" typeCd="'+followUp.typeCd+'">'+followUp.type+'</td>' +
        '<td data-th="Date" class="follow-up-table-date-col">'+followUp.date+'</td>' +
        '<td data-th="By" visitorsIds="'+followUp.visitorsIds.join(',')+'">'+followUp.visitors.join(', ')+'</td>' +
        '<td data-th="Comments" class="follow-up-table-comments-col">'+followUp.comments+'</td>' +
        '<td data-th="" class="follow-up-table-button-col"><button class="edit-follow-up"><i class="fa fa-edit"></i></button><button class="delete-follow-up"><i class="fa fa-minus-circle"></i></button></td>' +
      '</tr>');
  }
  
  function updateFollowUpRow(followUp) {
    var children = $('#follow-up-table tr[follow_up_id='+followUp.id+']').children();
    children[0].setAttribute('personid', followUp.personId);
    children[0].innerHTML = followUp.name;
    children[1].setAttribute('typeCd', followUp.typeCd);
    children[1].innerHTML = followUp.type;
    children[2].innerHTML = followUp.date;
    children[3].innerHTML = followUp.visitors.join(', ');
    children[4].setAttribute('visitorsIds', followUp.visitorsIds.join(','));
    children[4].innerHTML = followUp.comments;
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
  
  function doAddFollowUp(followUp, clear) {
    appendFollowUp(followUp);
    $('button.edit-follow-up:last').on('click', onEditFollowUpClick);
    $('button.delete-follow-up:last').on('click', onDeleteFollowUpClick);
    
    if(clear === true)
      clearFollowUpForm();
  }
  function doEditFollowUp(followUp, clear) {
    updateFollowUpRow(followUp);
    
    if(clear === true)
      clearFollowUpForm();
  }
  function addCopy() {
    var followUp = saveFollowUp();
    if(followUp === false) return false;
    saveFollowUpEntry(followUp, isAdd() ? doAddFollowUp : doEditFollowUp, false);
  }
  function addClear() {
    var followUp = saveFollowUp();
    if(followUp === false) return false;
    saveFollowUpEntry(followUp, isAdd() ? doAddFollowUp : doEditFollowUp, true);
  }
  
  function onEditFollowUpClick(e) {
    var row = e.currentTarget.parentElement.parentElement;
    
    followUpPerson.setAttribute('personid', row.children[0].getAttribute('personid') || '');
    followUpPerson.innerHTML = row.children[0].innerHTML || '';
    followUpType.value = row.children[1].getAttribute('typeCd') || '';
    followUpDate.value = row.children[2].innerHTML || '';
    followUpComments.value = row.children[4].innerHTML || '';
    followUpId.value = row.getAttribute('follow_up_id');
    
    var visitorIdsString = row.children[3].getAttribute('visitorsIds') || '';
    var visitorIds = visitorIdsString.split(',');
    var inputs = followUpVisitors.querySelectorAll('input');
    for(var i=0; i<inputs.length; i++) {
      inputs[i].checked = visitorIds.indexOf(inputs[i].getAttribute('personid')) >= 0;
    }
    
    $formTitle.text('Edit Follow Up');
  }
  
  function isAdd() {
    return $formTitle.text().indexOf('Edit') === -1;
  }
  
  function openSelectPerson() {
    dialog.dialog('open');
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
  
  function onDeleteFollowUpClick(e) {
    if(confirm("Are you sure you would like to PERMANENTLY delete this Follow Up?")) {
      var id = e.currentTarget.parentElement.parentElement.getAttribute('follow_up_id');
      deleteFollowUp(id);
    }
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
  
  addCopyBtn.addEventListener('click', addCopy);
  addClearBtn.addEventListener('click', addClear);
  clearBtn.addEventListener('click', clearFollowUpForm);
  selectPersonBtn.addEventListener('click', openSelectPerson);
  
  checkLoginStatus(loadVisitors);
})();
