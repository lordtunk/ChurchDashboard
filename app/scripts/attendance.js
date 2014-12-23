(function () {
  'use strict';
  
  if($('#attendance').length === 0) return;
 
  $( "#attendance-date" ).datepicker();
  var attendanceDate = document.querySelector('#attendance-date'),
      adultTotalAttendance = document.querySelector('#adult-total-attendance'),
      adultFirstServiceAttendance = document.querySelector('#adult-first-service-attendance'),
      adultSecondServiceAttendance = document.querySelector('#adult-second-service-attendance'),
      kidTotalAttendance = document.querySelector('#kid-total-attendance'),
      kidFirstServiceAttendance = document.querySelector('#kid-first-service-attendance'),
      kidSecondServiceAttendance = document.querySelector('#kid-second-service-attendance'),
      activeTrue = document.querySelector('#active-true'),
      activeFalse = document.querySelector('#active-false'),
      updateBtn = document.querySelector('#update'),
      cancelBtn = document.querySelector('#cancel'),
      selectDateBtn = document.querySelector('#go-arrow'),
      addAdultBtn = document.querySelector('#add-adult'),
      addKidBtn = document.querySelector('#add-kid'),
      attendanceDateDisplay = document.querySelector('#attendance-date-display'),
      people = [],
      adultTotalAttendanceCount = 0,
      adultFirstServiceAttendanceCount = 0,
      adultSecondServiceAttendanceCount = 0,
      kidTotalAttendanceCount = 0,
      kidFirstServiceAttendanceCount = 0,
      kidSecondServiceAttendanceCount = 0,
      idSequence = 0,
      personIdSequence = -1,
      prevAttendanceDate,
      currAttendanceDate,
      noChangesMade = true,
      scrollAnimationMs = 1000,
      dialog = $( "#dialog-form" ).dialog({
        autoOpen: false,
        height: 400,
        width: 450,
        modal: true
      });

  function update() {
    try {
      $('.attendance-form').mask('Loading...');
      setTimeout(doUpdate, 50);
    } catch(e) {
      $('.attendance-form').unmask();
    }
  }
  
  function doUpdate() {
    var updatedPeople = [],
        i, j, rows, personId, display, displayField, attendanceDateFound, 
        first, second, p;
    rows = $('#adult-attendance-table > tbody:last').children();
    for(i=0; i<rows.length; i++) {
        // Only update modified rows
        if(rows[i].getAttribute('modified') === null) continue;
 
        personId = rows[i].getAttribute('personId');
        displayField = rows[i].querySelector('[name=name_description]');
        first = isAttendingFirstService(personId);
        second = isAttendingSecondService(personId);
        
        // An input field should be found for added people
        if(displayField) {
          display = $.trim(displayField.value);
          // If the display field is empty then do not save the person
          if(display === '')
            continue;
        } else {
          display = undefined;
        }
        
        if(personId.indexOf('-') === -1) {
          // Update the cached person information
          p = getPerson(personId);
          if(p) {
            attendanceDateFound = false;
            for(j=0; j<p.attendance.length; j++) {
              if(p.attendance[j].date === currAttendanceDate) {
                p.attendance[j].first = first;
                p.attendance[j].second = second;
                attendanceDateFound = true;
              }
            }
            if(!attendanceDateFound) {
              p.attendance.push({
                date: currAttendanceDate,
                first: first,
                second: second
              });
            }
          }
        }
        updatedPeople.push({
          id: personId,
          adult: true,
          display: display,
          attendanceDate: currAttendanceDate,
          first: first,
          second: second
        });
      }
      rows = $('#kid-attendance-table > tbody:last').children();
      for(i=0; i<rows.length; i++) {
        // Only update modified rows
        if(rows[i].getAttribute('modified') === null) continue;
 
        personId = rows[i].getAttribute('personId');
        displayField = rows[i].querySelector('[name=name_description]');

        // An input field should be found for added people
        if(displayField) {
          display = $.trim(displayField.value);
          // If the display field is empty then do not save the person
          if(!display)
            continue;
        } else {
          display = undefined;
        }
        
        updatedPeople.push({
          id: personId,
          adult: false,
          display: display,
          attendanceDate: currAttendanceDate,
          first: isAttendingFirstService(personId),
          second: isAttendingSecondService(personId)
        });
      }
      if(updatedPeople.length > 0) {
        savePeople(updatedPeople);
      } else {
        $('.attendance-form').unmask();
        $().toastmessage('showWarningToast', "No attendance was modified");
      }
  }

  function cancel() {
    if(noChangesMade || confirm("Clear out any unsaved changes?"))
      reset();
  }

  function reset() {
    noChangesMade = true;
    // This will restore the table data back to its original state
    $('#adult-attendance-table > tbody:last').children().remove();
    $('#kid-attendance-table > tbody:last').children().remove();
    processPeople(people);
  }

  function addAdult() {
    addAdultBtn.blur();
    noChangesMade = false;
    var person = {
      id: genPersonid(),
      adult: true
    };
    var row = buildNewPersonRow(person, currAttendanceDate);
    $('#adult-attendance-table > tbody:last').append(row);
    $('[personid='+person.id+'] input:checkbox').on('change', updateAttendance);
    $('#adult-attendance-table-container').animate({ scrollTop: $('#adult-attendance-table-container')[0].scrollHeight}, scrollAnimationMs);
  }

  function addKid() {
    addKidBtn.blur();
    noChangesMade = false;
    var person = {
      id: genPersonid(),
      adult: false
    };
    var row = buildNewPersonRow(person, currAttendanceDate);
    $('#kid-attendance-table > tbody:last').append(row);
    $('[personid='+person.id+'] input:checkbox').on('change', updateAttendance);
    $('#kid-attendance-table-container').animate({ scrollTop: $('#kid-attendance-table-container')[0].scrollHeight}, scrollAnimationMs);
  }

  function setAttendanceDate() {
    var curr = new Date(); // get current date
    var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
    var sunday = new Date(curr.setDate(first));
    attendanceDate.value = getDateString(sunday);
    currAttendanceDate = attendanceDate.value;
    prevAttendanceDate = sunday;
    
    attendanceDateDisplay.innerHTML = attendanceDate.value;
  }
  
  function onSelectAttendanceDate() {
    if(noChangesMade || confirm("If you change the date you will lose any unsaved changes. Continue?")) {
      prevAttendanceDate = new Date(attendanceDate.value);
      currAttendanceDate = attendanceDate.value;
      reset();
    } else {
      attendanceDate.value = getDateString(prevAttendanceDate);
    }
    attendanceDateDisplay.innerHTML = attendanceDate.value;
  }

  function onClickTopBottom(e) {
    var containerId = '#'+((e.target.id.indexOf('adult') == -1) ? 'kid' : 'adult') + '-attendance-table-container',
        container = $(containerId),
        pos = (e.target.id.indexOf('top') == -1) ?
                    container[0].scrollHeight
                    : 0;

    container.stop().animate({ scrollTop: pos }, scrollAnimationMs, 'swing', function() {
      $('<style></style>').appendTo($(document.body)).remove();
    });
  }

  function processPeople(data) {
    detachLinkClickListeners();
    $('.attendance-table-attendance-col input:checkbox').off('change');
    adultTotalAttendanceCount = 0;
    adultFirstServiceAttendanceCount = 0;
    adultSecondServiceAttendanceCount = 0;
    kidTotalAttendanceCount = 0;
    kidFirstServiceAttendanceCount = 0;
    kidSecondServiceAttendanceCount = 0;
    people = data;
    var dt = currAttendanceDate;
    var adultRows = '', kidRows = '';
    for(var i=0; i<people.length; i++) {
      if(people[i].active == activeTrue.checked) {
        if(people[i].adult)
          adultRows += buildPersonRow(people[i], dt);
        else
          kidRows += buildPersonRow(people[i], dt);
      }
    }
    setAttendance(adultTotalAttendanceCount, adultFirstServiceAttendanceCount, adultSecondServiceAttendanceCount, kidTotalAttendanceCount, kidFirstServiceAttendanceCount, kidSecondServiceAttendanceCount);
    $('#adult-attendance-table > tbody:last').append(adultRows);
    $('#kid-attendance-table > tbody:last').append(kidRows);
    $('.attendance-table-attendance-col input:checkbox').on('change', updateAttendance);
    attachLinkClickListeners();
  }

  function buildPersonRow(person, dt) {
    var firstChecked = '',
        secondChecked = '',
        display = '',
        firstId = genId(),
        secondId = genId(),
        ind;
    if((ind = getDateIndex(person, dt)) != -1) {
      firstChecked = person.attendance[ind].first ? 'checked' : '';
      secondChecked = person.attendance[ind].second ? 'checked' : '';
    }

    display = getDisplayName(person);
    display = '<a class="person_name" href="manage-person.html?id='+person.id+'">'+display+'</a>';

    if(person.adult) {
      if(firstChecked) ++adultFirstServiceAttendanceCount;
      if(secondChecked) ++adultSecondServiceAttendanceCount;
      if(firstChecked || secondChecked) ++adultTotalAttendanceCount;
    } else {
      if(firstChecked) ++kidFirstServiceAttendanceCount;
      if(secondChecked) ++kidSecondServiceAttendanceCount;
      if(firstChecked || secondChecked) ++kidTotalAttendanceCount;
    }
    return    '<tr adult="'+person.adult+'" personId="'+person.id+'"><td data-th="Name">'+
              '<button class="attendance-history-button"><i class="fa fa-archive" /></button>'+display+'</td>'+
              '<td class="attendance-table-attendance-col" service="first" data-th="First?">'+
              '<label for="'+firstId+'"><input id="'+firstId+'" type="checkbox" '+firstChecked+'/></label></td>'+
              '<td class="attendance-table-attendance-col" service="second" data-th="Second?">'+
              '<label for="'+secondId+'"><input id="'+secondId+'" type="checkbox" '+secondChecked+'/></label></td></tr>';
  }

  function buildNewPersonRow(person, dt) {
    var firstId = genId(),
        secondId = genId();
    return    '<tr adult="'+person.adult+'" personId="'+person.id+'" modified="true"><td data-th="Name">'+
              '<input name="name_description" type="text" placeholder="Last, First or Description" /></td>'+
              '<td class="attendance-table-attendance-col" service="first" data-th="First?">'+
              '<label for="'+firstId+'"><input id="'+firstId+'" type="checkbox" /></label></td>'+
              '<td class="attendance-table-attendance-col" service="second" data-th="Second?">'+
              '<label for="'+secondId+'"><input id="'+secondId+'" type="checkbox" /></label></td></tr>';
  }
  
  function updateNewPeople(data) {
    noChangesMade = true;
    if(!data || data.length === 0) return;
    var personRows = [],
        lastAdultInd = 0,
        lastKidInd = 0,
        i,j,person,newPerson,inserted,pEl;
    // Remove any 'new person' rows from the table
    $('[personid^=-]').remove();
    
    // Create rows for them
    for(i=0; i<data.length; i++) {
      people.push(data[i]);
      personRows.push(buildPersonRow(data[i], currAttendanceDate));
    }
    
    // Add them to the appropriate person table
    for(i=0; i<data.length; i++) {
      newPerson = data[i];
      inserted = false;
      for(j=0; j<people.length; j++) {
        person = people[j];
        
        if(person.adult === true)
          lastAdultInd = j;
        else
          lastKidInd = j;
        
        // Only compare the two if they are both adults or both kids
        if(person.adult !== newPerson.adult) continue;
 
        if(personCompareTo(newPerson, person) === -1) {
          $(personRows[i]).insertBefore('[personid='+person.id+']');
          people.splice(j, 0, newPerson);
          inserted = true;
          break;
        }
      }
      // If the person was not inserted into the table in the loop then add to the end
      if(inserted === false) {
        if(newPerson.adult === true) {
          $('#adult-attendance-table > tbody:last').append(personRows[i]);
          people.splice(lastAdultInd+1, 0, newPerson);
        } else {
          $('#kid-attendance-table > tbody:last').append(personRows[i]);
          people.splice(lastKidInd+1, 0, newPerson);
        }
      }
      
      // Attach listeners to inserted rows
      pEl = $('[personid='+newPerson.id+']');
      pEl.find('a.person_name').on('click', onClickLink);
      pEl.find('button.attendance-history-button').on('click', onClickAttendanceHistoryButton);
      // FIX ATTENDANCE UPDATE ISSUE. Attendance counts get messed up when toggling checkbox
      // after saving after adding a new person
      pEl.find('input:checkbox').on('change', updateAttendance);
    }
  }

  function updateAttendance(e) {
    var me = e.target,
        personId = me.parentElement.parentElement.parentElement.getAttribute('personId'),
        adult = me.parentElement.parentElement.parentElement.getAttribute('adult');

    noChangesMade = false;
    me.parentElement.parentElement.parentElement.setAttribute('modified', true);
    if(adult == 'true') {
      if(me.parentElement.parentElement.getAttribute('service') === 'first') {
        if(me.checked) {
          ++adultFirstServiceAttendanceCount;

          if(!isAttendingSecondService(personId))
            ++adultTotalAttendanceCount;
        } else {
          --adultFirstServiceAttendanceCount;

          if(!isAttendingSecondService(personId))
            --adultTotalAttendanceCount;
        }
        adultFirstServiceAttendance.innerHTML = adultFirstServiceAttendanceCount;
      } else if(me.parentElement.parentElement.getAttribute('service') === 'second') {
        if(me.checked) {
          ++adultSecondServiceAttendanceCount;

          if(!isAttendingFirstService(personId))
            ++adultTotalAttendanceCount;
        } else {
          --adultSecondServiceAttendanceCount;

          if(!isAttendingFirstService(personId))
            --adultTotalAttendanceCount;
        }
        adultSecondServiceAttendance.innerHTML = adultSecondServiceAttendanceCount;
      }

      adultTotalAttendance.innerHTML = adultTotalAttendanceCount;
    } else {
      if(me.parentElement.parentElement.getAttribute('service') === 'first') {
        if(me.checked) {
          ++kidFirstServiceAttendanceCount;

          if(!isAttendingSecondService(personId))
            ++kidTotalAttendanceCount;
        } else {
          --kidFirstServiceAttendanceCount;

          if(!isAttendingSecondService(personId))
            --kidTotalAttendanceCount;
        }
        kidFirstServiceAttendance.innerHTML = kidFirstServiceAttendanceCount;
      } else if(me.parentElement.parentElement.getAttribute('service') === 'second') {
        if(me.checked) {
          ++kidSecondServiceAttendanceCount;

          if(!isAttendingFirstService(personId))
            ++kidTotalAttendanceCount;
        } else {
          --kidSecondServiceAttendanceCount;

          if(!isAttendingFirstService(personId))
            --kidTotalAttendanceCount;
        }
        kidSecondServiceAttendance.innerHTML = kidSecondServiceAttendanceCount;
      }

      kidTotalAttendance.innerHTML = kidTotalAttendanceCount;
    }
  }
  
  function showPersonAttendance(personId) {
    var p = getPerson(personId);
    if(!p) return;
    
    $('#person-attendance-history-table > tbody:last').children().remove();
 
    dialog.dialog("open");
    dialog[0].querySelector('#person-name').innerHTML = getDisplayName(p, false);
    var rows = '', a;
    for(var i=0; i<p.attendance.length; i++) {
      rows += buildAttendanceRow(p.attendance[i]);
    }
    $('#person-attendance-history-table > tbody:last').append(rows);
  }
  
  function buildAttendanceRow(a) {
    var firstChecked = a.first ? 'checked' : '';
    var secondChecked = a.second ? 'checked' : '';
    return    '<tr><td data-th="Date">'+a.date+'</td>'+
              '<td class="attendance-table-attendance-col" service="first" data-th="First?">'+
              '<input type="checkbox" '+firstChecked+' disabled/></td>'+
              '<td class="attendance-table-attendance-col" service="second" data-th="Second?">'+
              '<input type="checkbox" '+secondChecked+' disabled/></td></tr>';
  }

  function setAttendance(totalAdult, firstAdult, secondAdult, totalKid, firstKid, secondKid) {
    adultTotalAttendanceCount = totalAdult;
    adultFirstServiceAttendanceCount = firstAdult;
    adultSecondServiceAttendanceCount = secondAdult;
    
    adultTotalAttendance.innerHTML = totalAdult;
    adultFirstServiceAttendance.innerHTML = firstAdult;
    adultSecondServiceAttendance.innerHTML = secondAdult;

    kidTotalAttendanceCount = totalKid;
    kidFirstServiceAttendanceCount = firstKid;
    kidSecondServiceAttendanceCount = secondKid;

    kidTotalAttendance.innerHTML = totalKid;
    kidFirstServiceAttendance.innerHTML = firstKid;
    kidSecondServiceAttendance.innerHTML = secondKid;
  }

  function loadPeople() {
    $('.attendance-form').mask('Loading...');
    $.ajax({
      type: 'GET',
      url: 'ajax/get_attendance.php'
    })
    .done(function(msg) {
      $('.attendance-form').unmask();
      var data = JSON.parse(msg);
      if(data.success) {
        processPeople(data.people);
        if(data.scroll_to_id && data.scroll_to_id >= 0) {
          var scrollTo = $('[personid='+data.scroll_to_id+']')[0];
          if(scrollTo) {
            var adult = scrollTo.getAttribute('adult') == 'true' ? 'adult' : 'kid',
                containerId = '#'+adult+'-attendance-table-container',
                screenOff = $(containerId).offset().top,
                scrollOff = scrollTo.offsetTop;
            $('body').animate({ scrollTop: screenOff }, 300);
            $(containerId).animate({ scrollTop: scrollOff }, scrollAnimationMs);
          }
        }
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error loading");
        }
      }
    })
    .fail(function() {
      $('.attendance-form').unmask();
      $().toastmessage('showErrorToast', "Error loading");
    });
  }

  function savePeople(newPeople) {
    $.ajax({
      type: 'POST',
      url: 'ajax/save_people.php',
      data: { people: JSON.stringify(newPeople) }
    })
    .done(function( msg ) {
      $('.attendance-form').unmask();
      var data = JSON.parse(msg);
      if(data.success) {
        updateNewPeople(data.people);
        //people = data.people;
        //reset();
        $().toastmessage('showSuccessToast', "Save successful");
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error saving");
        }
      }
    });
  }

  function isAttendingFirstService(id) {
    return $('[personId='+id+'] input:checkbox:first')[0].checked;
  }

  function isAttendingSecondService(id) {
    return $('[personId='+id+'] input:checkbox:last')[0].checked;
  }
  
  function personCompareTo(p1, p2) {
    if(p1 == p2) return 0;
    if(p1 === null) return -1;
    if(p2 === null) return 1;
    
    var p1Display = getDisplayName(p1, true), 
        p2Display = getDisplayName(p2, true);
    if(p1.first_name || p1.last_name) {
      if(p2.first_name || p2.last_name) {
        if(p1Display === p2Display) return 0;
        if(p1Display.toLowerCase() < p2Display.toLowerCase()) return -1;
        if(p1Display.toLowerCase() > p2Display.toLowerCase()) return 1;
      } else {
        return -1;
      }
    } else if(p2.first_name || p2.last_name) {
      return 1;
    } else {
      if(p1Display === p2Display) return 0;
      if(p1Display.toLowerCase() < p2Display.toLowerCase()) return -1;
      if(p1Display.toLowerCase() > p2Display.toLowerCase()) return 1;
    }
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

  function getDateIndex(person, dt) {
    for(var i=0; i<person.attendance.length; i++) {
      if(person.attendance[i].date === dt) return i;
    }
    return -1;
  }

  function getDateString(dt) {
    var curr_date = dt.getDate();
    var curr_month = dt.getMonth();
    var curr_year = dt.getFullYear();
    
    curr_date = (curr_date < 10) ? '0'+curr_date : ''+curr_date;
    curr_month++;
    curr_month = (curr_month < 10) ? '0'+curr_month : ''+curr_month;
    
    return curr_month + "/" + curr_date + "/" + curr_year;
  }
  
  function getPerson(id) {
    for(var i=0; i<people.length; i++) {
      if(people[i].id === id) return people[i];
    }
    return null;
  }

  function genId() {
    return 'id-'+(++idSequence);
  }

  function genPersonid() {
    return --personIdSequence;
  }
  
  function attachLinkClickListeners() {
    $('#attendance-nav').on('click', onClickLink);
    $('#reports-nav').on('click', onClickLink);
    $('a.person_name').on('click', onClickLink);
    $('button.attendance-history-button').on('click', onClickAttendanceHistoryButton);
  }
  
  function detachLinkClickListeners() {
    $('#attendance-nav').off('click', onClickLink);
    $('#reports-nav').off('click', onClickLink);
    $('a.person_name').off('click', onClickLink);
    $('button.attendance-history-button').off('click', onClickAttendanceHistoryButton);
  }
  
  function onClickAttendanceHistoryButton() {
    var personId = this.parentElement.parentElement.getAttribute('personId');
    showPersonAttendance(personId);
  }
  
  function onClickLink(e) {
    if(!(noChangesMade || confirm("If you continue you will lose any unsaved changes. Continue?"))) {
      e.preventDefault();
    }
  }

  setAttendanceDate();

  updateBtn.addEventListener('click', update);
  cancelBtn.addEventListener('click', cancel);
  selectDateBtn.addEventListener('click', onSelectAttendanceDate);
  addAdultBtn.addEventListener('click', addAdult);
  addKidBtn.addEventListener('click', addKid);

  $('.top-bottom-links a').on('click', onClickTopBottom);
  

  checkLoginStatus(loadPeople);
})();
