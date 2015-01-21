(function () {
  'use strict';

  if($('#reports').length === 0) return;
  
  $( "#from-date" ).datepicker();
  $( "#to-date" ).datepicker();
  
  var toDateField = document.querySelector('#to-date'),
      fromDateField = document.querySelector('#from-date'),
      firstDateField = document.querySelector('#first-date'),
      lastDateField = document.querySelector('#last-date'),
      reportTypeField = document.querySelector('#report-type'),
      runBtn = document.querySelector('#go-arrow'),
      scrollAnimationMs = 300;

  function populateForm(first_dt, last_dt) {
    firstDateField.innerHTML = first_dt ? first_dt : 'N/A';
    lastDateField.innerHTML = last_dt ? last_dt : 'N/A';

    var d = new Date();
    fromDateField.value = $.datepicker.formatDate('mm/dd/yy', new Date(d.getFullYear(), d.getMonth(), 1, 0, 0, 0, 0));
    toDateField.value = $.datepicker.formatDate('mm/dd/yy', d);
  }
  
  function onReportTypeChange() {
    var reportType = parseInt(reportTypeField.value);
    
    $('#report-type-container')[0].style.setProperty('display', (reportType === 3) ? 'inline-block' : 'block');
    $('#from-to-dates')[0].style.setProperty('display', (reportType === 3) ? 'none' : 'inline-block');
    if(reportType === 3) {
      fromDateField.value = '';
      toDateField.value = '';
    }
      
    $('#communication-card-header')[0].style.setProperty('display', (reportType === 4) ? 'inherit' : 'none');
    $('#follow-up-options')[0].style.setProperty('display', (reportType === 4) ? 'inline-block' : 'none');
    $('#follow-up-options-spacer')[0].style.setProperty('display', (reportType === 4) ? 'inherit' : 'none');
  }

  function onRunClick() {
    var reportType = parseInt(reportTypeField.value);
    if(validateDates(fromDateField.value, toDateField.value, (reportType === 1 || reportType === 2))) {
      hideAllReportContainers();
      var params;
      switch(reportType) {
        case 1:
          $('#attendance-by-date-container')[0].style.setProperty('display', 'inherit');
	  params = {
	    fromDate: fromDateField.value,
	    toDate: toDateField.value
	  };
          break;
        case 2:
          $('#attendance-by-person-container')[0].style.setProperty('display', 'inherit');
	  params = {
	    fromDate: fromDateField.value,
	    toDate: toDateField.value
	  };
          break;
        case 3:
          $('#attendance-by-mia-container')[0].style.setProperty('display', 'inherit');
          break;
	case 4:
          $('#follow-up-container')[0].style.setProperty('display', 'inherit');
	  params = {
	    fromDate: fromDateField.value,
	    toDate: toDateField.value,
	    not_visited: $('#not-visited').is(':checked'),
	    ty_card_not_sent: $('#ty-card-not-sent').is(':checked'),
	    signed_up_for_baptism: $('#signed-up-for-baptism').is(':checked'),
	    baptized: $('#baptized').is(':checked'),
	    interested_in_gkids: $('#interested-in-gkids').is(':checked'),
	    interested_in_next: $('#interested-in-next').is(':checked'),
	    interested_in_ggroups: $('#interested-in-ggroups').is(':checked'),
	    interested_in_gteams: $('#interested-in-gteams').is(':checked'),
	    interested_in_joining: $('#interested-in-joining').is(':checked'),
	    would_like_visit: $('#would-like-visit').is(':checked'),
	    no_agent: $('#no-agent').is(':checked')
	  };
          break;
      }
      loadReport(reportType, params);
    }
  }

  function hideAllReportContainers() {
    $('#attendance-by-person-container')[0].style.setProperty('display', 'none');
    $('#attendance-by-date-container')[0].style.setProperty('display', 'none');
    $('#attendance-by-mia-container')[0].style.setProperty('display', 'none');
    $('#follow-up-container')[0].style.setProperty('display', 'none');
  }

  function validateDates(fDate, tDate, requireDate) {
    var msg = '';
    
    if(!isDate(fDate, true)) {
      msg += 'From Date must be a valid date<br />';
    }
    if(!isDate(tDate, true)) {
      msg += 'To Date must be a valid date<br />';
    }
    
    if(msg) {
      $().toastmessage('showErrorToast', msg);
      return false;
    } else if(requireDate && !fDate && !tDate) {
      $().toastmessage('showErrorToast', 'From Date or To Date must be specified');
      return false;
    }
    return true;
  }

  function populateAttendanceByDate(totals, aggregates) {
    $('#attendance-date-table > tbody:last').empty();
    $('#attendance-date-aggregates-table > tbody:last').empty();
    var totalRows= '';
    for(var i=0; i<totals.length; i++) {
      totalRows += buildTotalRow(totals[i]);
    }
    $('#attendance-date-table > tbody:last').append(totalRows);
    $('#attendance-date-aggregates-table > tbody:last').append(buildAggregateRows(aggregates));
  }

  function buildTotalRow(total) {
    return    '<tr><td data-th="Date">'+total.Attendance_dt+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Total">'+
              total.Total_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="First">'+
              total.First_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Second">'+
              total.Second_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="First Adult">'+
              total.First_Service_Adult_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Second Adult">'+
              total.Second_Service_Adult_Attendance+'</td>'+
              '</tr>';
  }

  function buildAggregateRows(aggregates) {
    var rows = '';
    var aggregate = aggregates[0];
    rows +=   '<tr><td data-th="Aggregate">Average</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Average Total">'+
              aggregate.Avg_Total_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Average First">'+
              aggregate.Avg_First_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Average Second">'+
              aggregate.Avg_Second_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Average First Adult">'+
              aggregate.Avg_First_Service_Adult_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Average Second Adult">'+
              aggregate.Avg_Second_Service_Adult_Attendance+'</td>'+
              '</tr>';
    rows +=   '<tr><td data-th="Aggregate">Max</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Max Total">'+
              aggregate.Max_Total_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Max First">'+
              aggregate.Max_First_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Max Second">'+
              aggregate.Max_Second_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Max First Adult">'+
              aggregate.Max_First_Service_Adult_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Max Second Adult">'+
              aggregate.Max_Second_Service_Adult_Attendance+'</td>'+
              '</tr>';
    rows +=   '<tr><td data-th="Aggregate">Min</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Min Total">'+
              aggregate.Min_Total_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Min First">'+
              aggregate.Min_First_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Min Second">'+
              aggregate.Min_Second_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Min First Adult">'+
              aggregate.Min_First_Service_Adult_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Min Second Adult">'+
              aggregate.Min_Second_Service_Adult_Attendance+'</td>'+
              '</tr>';
    return rows;
  }

  function populateAttendanceByPerson(people) {
    $('#adult-attendance-table > tbody:last').empty();
    var rows= '';
    for(var i=0; i<people.length; i++) {
      if(people[i].adult == 'true')
        rows += buildPersonRow(people[i]);
    }
    $('#adult-attendance-table > tbody:last').append(rows);
  }
  
  function populateAttendanceByMia(people) {
    $('#mia-attendance-table > tbody:last').empty();
    var rows= '';
    for(var i=0; i<people.length; i++) {
      if(people[i].adult == 'true')
        rows += buildMiaRow(people[i]);
    }
    $('#mia-attendance-table > tbody:last').append(rows);
  }
  
  function populateFollowUps(people) {
    $('#follow-up-table > tbody:last').empty();
    var rows= '';
    for(var i=0; i<people.length; i++) {
      rows += buildFollowUpRow(people[i]);
    }
    $('#follow-up-table > tbody:last').append(rows);
  }
  
  function buildMiaRow(person) {
    var display = '';

    if(person.first_name || person.last_name) {
      if(person.first_name) display += person.first_name + ' ';
      if(person.last_name) display += person.last_name;
    } else {
      display = person.description;
    }

    display = '<a class="person_name" href="manage-person.html?id='+person.id+'">'+display+'</a>';

    return '<tr adult="'+person.adult+'" personId="'+person.id+'"><td data-th="Name">'+display+'</td></tr>';
  }

  function buildPersonRow(person) {
    var display = '';

    if(person.first_name || person.last_name) {
      if(person.first_name) display += person.first_name + ' ';
      if(person.last_name) display += person.last_name;
    } else {
      display = person.description;
    }

    display = '<a class="person_name" href="manage-person.html?id='+person.id+'">'+display+'</a>';

    return    '<tr adult="'+person.adult+'" personId="'+person.id+'"><td data-th="Name">'+display+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="First">'+
              person.First_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Second">'+
              person.Second_Service_Attendance+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Total">'+
              person.Total_Attendance+'</td></tr>';
  }
  
  function buildFollowUpRow(person) {
    var display = '', phone = person.primary_phone || '';

    if(person.first_name || person.last_name) {
      if(person.first_name) display += person.first_name + ' ';
      if(person.last_name) display += person.last_name;
    } else {
      display = person.description;
    }
    
    display = '<a class="person_name" href="manage-person.html?id='+person.id+'">'+display+'</a>';

    phone = formatPhoneNumber(phone);

    return    '<tr personId="'+person.id+'"><td data-th="Name">'+display+'</td>'+
              '<td class="checkbox-table-col" data-th="Visited?"><input type="checkbox" disabled '+
              (person.visited === 'true' ? 'checked ' : '')+'/></td>'+
              '<td class="checkbox-table-col" data-th="Phone Number">'+
              phone+'</td>'+
              '<td data-th="Thank You<br />Card Sent">'+
              (person.ty_card_date || '')+'</td>'+
	      '<td data-th="Communication Card<br />Received">'+
              (person.communication_card_date || '')+'</td></tr>';
  }

  function onClickTopBottom(e) {
    var containerId = '#'+e.target.parentElement.previousElementSibling.id;
    var pos = (e.target.id.indexOf('top') == -1) ?
                    $(containerId)[0].scrollHeight
                    : 0;
    $(containerId).animate({ scrollTop: pos }, scrollAnimationMs, 'swing', function() {
      $('<style></style>').appendTo($(document.body)).remove();
    });
  }

  function loadReport(reportType, params) {
    $('.reports-form').mask('Loading...');
    $.ajax({
      type: 'POST',
      url: 'ajax/get_report.php',
      data: {
        type: reportType,
        params: JSON.stringify(params)
      }
    })
    .done(function(msg) {
      $('.reports-form').unmask();
      var data = JSON.parse(msg);
      if(data.success) {
        switch(reportType) {
          case 1:
            populateAttendanceByDate(data.totals, data.aggregates);
            break;
          case 2:
            populateAttendanceByPerson(data.people);
            break;
          case 3:
            populateAttendanceByMia(data.people);
            break;
	  case 4:
	    populateFollowUps(data.people);
	    break;
        }
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error loading report");
        }
      }
    })
    .fail(function() {
      $('.reports-form').unmask();
      $().toastmessage('showErrorToast', "Error loading report");
    });
  }

  function loadFirstLastServiceDates() {
    $('.reports-form').mask('Loading...');
    $.ajax({
      type: 'GET',
      url: 'ajax/get_first_last_service_dates.php'
    })
    .done(function(msg) {
      $('.reports-form').unmask();
      var data = JSON.parse(msg);
      if(data.success) {
        populateForm(data.first_dt, data.last_dt);
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error loading data");
        }
      }
    })
    .fail(function() {
      $('.reports-form').unmask();
      $().toastmessage('showErrorToast', "Error loading data");
    });
  }

  function isDate(txtDate, allowBlank) {
    var currVal = txtDate;
    if(currVal === '')
      return !!allowBlank;

    //Declare Regex
    var rxDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
    var dtArray = currVal.match(rxDatePattern); // is format OK?

    if (dtArray === null)
      return false;

    //Checks for mm/dd/yyyy format.
    var dtMonth = dtArray[1];
    var dtDay= dtArray[3];
    var dtYear = dtArray[5];

    if (dtMonth < 1 || dtMonth > 12)
        return false;
    else if (dtDay < 1 || dtDay> 31)
        return false;
    else if ((dtMonth==4 || dtMonth==6 || dtMonth==9 || dtMonth==11) && dtDay ==31)
        return false;
    else if (dtMonth == 2)
    {
      var isleap = (dtYear % 4 === 0 && (dtYear % 100 !== 0 || dtYear % 400 === 0));
      if (dtDay> 29 || (dtDay ==29 && !isleap))
            return false;
    }
    return true;
  }
  
  function formatPhoneNumber(phone) {
    phone = phone || '';
    if(phone.match(/\D/g,'') == null) {
      var tmp = phone.replace(/\D/g);
      if(tmp.length === 7) {
	phone = tmp.substr(0,3) + '-' + tmp.substr(3);
      } else if(tmp.length === 10) {
	phone = '(' + tmp.substr(0,3) + ') ' + tmp.substr(3,3) + '-' + tmp.substr(6);
      }
    }
    return phone;
  }
  
  reportTypeField.value = '1';
  
  runBtn.addEventListener('click', onRunClick);
  reportTypeField.addEventListener('change', onReportTypeChange);
  $('.top-bottom-links a').on('click', onClickTopBottom);
  
  checkLoginStatus(loadFirstLastServiceDates);
})();