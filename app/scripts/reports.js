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
      serviceLabel1Field = document.querySelector('#service-label-1'),
      serviceLabel2Field = document.querySelector('#service-label-2'),
      campusField = document.querySelector('#campus'),
      runBtn = document.querySelector('#go-arrow'),
	  emailField = document.querySelector('#email'),
	  emailBtn = document.querySelector('#email-summary'),
	  runParams = null,
      options = {},
      currentLabel1 = -1,
      currentLabel2 = -1,
      currentCampus = -1,
      temp = '',
      attenderStatusData = {
          1: 'Member',
          2: 'Regular',
          3: 'Irregular'
      },
      scrollAnimationMs = 300;

    function populateTypes() {
        var $select = $('#service-label-1'),
            $select2 = $('#service-label-2');
        $.each(options.service_labels, function(typeCd, type) {
            $select.append('<option value="' + typeCd + '">' + type + '</option>');
            $select2.append('<option value="' + typeCd + '">' + type + '</option>');
        });
        $select2.append('<option value="">--None--</option>');
        $select.val(options.default_first_service_label);
        $select2.val(options.default_second_service_label);
        
        $select = $('#campus');
        $.each(options.campuses, function(typeCd, type) {
            $select.append('<option value="' + typeCd + '">' + type + '</option>');
        });
        $select.val(options.default_campus);
    }
    
  function populateForm(first_dt, last_dt) {
    firstDateField.innerHTML = first_dt ? first_dt : 'N/A';
    lastDateField.innerHTML = last_dt ? last_dt : 'N/A';

    var d = new Date();
    fromDateField.value = $.datepicker.formatDate('mm/dd/yy', new Date(d.getFullYear(), d.getMonth(), 1, 0, 0, 0, 0));
    toDateField.value = $.datepicker.formatDate('mm/dd/yy', d);
  }
  
  function onReportTypeChange() {
    var reportType = parseInt(reportTypeField.value);
    
    $('#from-to-dates')[0].style.setProperty('display', (reportType === 3 || reportType === 5) ? 'none' : '');
    $('.service-label-container').css('display', (reportType === 3 || reportType === 4 || reportType === 5) ? 'none' : '');
    if(reportType === 3 || reportType === 4 || reportType === 5) {
      fromDateField.value = '';
      toDateField.value = '';
    }
    $('#first-last-dates')[0].style.setProperty('display', (reportType === 4) ? 'none' : 'inherit');
    $('#communication-card-header')[0].style.setProperty('display', (reportType === 4) ? 'inherit' : 'none');
    $('#follow-up-options')[0].style.setProperty('display', (reportType === 4) ? 'inline-block' : 'none');
    $('#follow-up-options-spacer')[0].style.setProperty('display', (reportType === 4) ? 'inherit' : 'none');
  }

  function onRunClick() {
    var reportType = parseInt(reportTypeField.value);
    if((reportType === 1 || reportType === 2) && serviceLabel1Field.value == serviceLabel2Field.value) {
        $().toastmessage('showErrorToast', "First and Second Service cannot be the same");
        return;
    }
    if(validateDates(fromDateField.value, toDateField.value, (reportType === 1 || reportType === 2))) {
      hideAllReportContainers();
      currentCampus = campusField.value;
      currentLabel1 = serviceLabel1Field.value;
      currentLabel2 = '';
      switch(reportType) {
        case 1:
          $('#attendance-by-date-container')[0].style.setProperty('display', 'inherit');
          runParams = {
            fromDate: fromDateField.value,
            toDate: toDateField.value,
            campus: campusField.value,
            label1: serviceLabel1Field.value,
            label2: serviceLabel2Field.value
          };
          currentLabel2 = serviceLabel2Field.value;
          if(currentLabel2) {
            $('.service-header').css('display', '');
            $('.first-service-header').text(options.service_labels[currentLabel1]);
            $('.second-service-header').text(options.service_labels[currentLabel2]);
          } else {
              $('.service-header').css('display', 'none');
          }

          break;
        case 2:
          $('#attendance-by-person-container')[0].style.setProperty('display', 'inherit');
          runParams = {
            fromDate: fromDateField.value,
            toDate: toDateField.value,
            campus: campusField.value,
            label1: serviceLabel1Field.value,
            label2: serviceLabel2Field.value
          };
          currentLabel2 = serviceLabel2Field.value;
          if(currentLabel2) {
            $('.service-header').css('display', '');
            $('.first-service-header').text(options.service_labels[currentLabel1]);
            $('.second-service-header').text(options.service_labels[currentLabel2]);
          } else {
              $('.service-header').css('display', 'none');
          }
          break;
        case 3:
          $('#attendance-by-mia-container')[0].style.setProperty('display', 'inherit');
          runParams = {
            fromDate: '',
            toDate: '',
            campus: campusField.value
          };
          break;
	   case 4:
          $('#follow-up-container')[0].style.setProperty('display', 'inherit');
          runParams = buildParameters();
          runParams.campus = campusField.value;
          break;
        case 5:
          $('#people-by-attender-status-container')[0].style.setProperty('display', 'inherit');
          runParams = {
            fromDate: '',
            toDate: '',
            campus: campusField.value
          };
      }
      loadReport(reportType, runParams);
    }
  }
  
  function buildParameters() {
	  return {
		fromDate: fromDateField.value,
		toDate: toDateField.value,
		active: $('#active').is(':checked'),
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
		no_agent: $('#no-agent').is(':checked'),
		commitment_christ: $('#commitment-christ').is(':checked'),
		recommitment_christ: $('#recommitment-christ').is(':checked'),
		commitment_tithe: $('#commitment-tithe').is(':checked'),
		commitment_ministry: $('#commitment-ministry').is(':checked'),
		attendance_frequency: $('#first-time-visitor').is(':checked')
	  };
  }

  function hideAllReportContainers() {
    $('#attendance-by-person-container')[0].style.setProperty('display', 'none');
    $('#attendance-by-date-container')[0].style.setProperty('display', 'none');
    $('#attendance-by-mia-container')[0].style.setProperty('display', 'none');
    $('#follow-up-container')[0].style.setProperty('display', 'none');
    $('#people-by-attender-status-container')[0].style.setProperty('display', 'none');
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
    temp = (currentLabel2 === '') ? 
            '' : 
            '<td class="report-attendance-table-attendance-col" data-th="First">'+
            total.First_Service_Attendance+'</td>'+
            '<td class="report-attendance-table-attendance-col" data-th="Second">'+
            total.Second_Service_Attendance+'</td>';
    return    '<tr><td data-th="Date">'+total.Attendance_dt+'</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Total">'+
              total.Total_Attendance+'</td>'+
              temp +
              '</tr>';
  }

  function buildAggregateRows(aggregate) {
    var rows = '';
    //var aggregate = aggregates[0];
    temp = (currentLabel2 === '') ? 
            '' : 
            '<td class="report-attendance-table-attendance-col" data-th="Average First">'+
            aggregate.Avg_First_Service_Attendance+'</td>'+
            '<td class="report-attendance-table-attendance-col" data-th="Average Second">'+
            aggregate.Avg_Second_Service_Attendance+'</td>';
    rows +=   '<tr><td data-th="Aggregate">Average</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Average Total">'+
              aggregate.Avg_Total_Attendance+'</td>'+
              temp +
              '</tr>';
    temp = (currentLabel2 === '') ? 
            '' : 
            '<td class="report-attendance-table-attendance-col" data-th="Max First">'+
            aggregate.Max_First_Service_Attendance+'</td>'+
            '<td class="report-attendance-table-attendance-col" data-th="Max Second">'+
            aggregate.Max_Second_Service_Attendance+'</td>';
    rows +=   '<tr><td data-th="Aggregate">Max</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Max Total">'+
              aggregate.Max_Total_Attendance+'</td>'+
              temp +
              '</tr>';
    temp = (currentLabel2 === '') ? 
            '' : 
            '<td class="report-attendance-table-attendance-col" data-th="Min First">'+
            aggregate.Min_First_Service_Attendance+'</td>'+
            '<td class="report-attendance-table-attendance-col" data-th="Min Second">'+
            aggregate.Min_Second_Service_Attendance+'</td>';
    rows +=   '<tr><td data-th="Aggregate">Min</td>'+
              '<td class="report-attendance-table-attendance-col" data-th="Min Total">'+
              aggregate.Min_Total_Attendance+'</td>'+
              temp +
              '</tr>';
    return rows;
  }

  function populateAttendanceByPerson(people) {
    $('#adult-attendance-table > tbody:last').empty();
    var rows= '';
    for(var i=0; i<people.length; i++) {
      rows += buildPersonRow(people[i]);
    }
    $('#adult-attendance-table > tbody:last').append(rows);
  }
  
  function populateAttendanceByMia(people) {
    $('#mia-attendance-table > tbody:last').empty();
    var rows= '';
    for(var i=0; i<people.length; i++) {
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
    
    
  function populatePeopleByAttenderStatus(people) {
    $('#people-by-attender-status-table > tbody:last').empty();
    if(people.length === 0) return;
    var rows= '';
    var currentStatus = '';
    for(var i=0; i<people.length; i++) {
      if(people[i].adult == 'true') {
        if(people[i].attender_status != currentStatus) {
            rows += buildHeaderRow(attenderStatusData[people[i].attender_status]);
            currentStatus = people[i].attender_status;
        }
        rows += buildMiaRow(people[i]);
      }
    }
    $('#people-by-attender-status-table > tbody:last').append(rows);
  }
  
  function buildHeaderRow(headerText) {
      return '<tr class="row-header"><td>'+headerText+'</td></tr>';
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


    //display = '<a class="person_name" href="manage-person.html?id='+person.id+'">'+display+'</a>';
    temp = (currentLabel2 === '') ? 
            '' : 
            '<td class="report-attendance-table-attendance-col" data-th="First">'+
            person.First_Service_Attendance+'</td>'+
            '<td class="report-attendance-table-attendance-col" data-th="Second">'+
            person.Second_Service_Attendance+'</td>';
    return    '<tr personId="'+person.id+'"><td data-th="Name"><a class="person_name" href="manage-person.html?id='+person.id+'">'+person.display+'</a></td>'+
              temp+
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
    var containerId = '#'+e.target.parentElement.parentElement.parentElement.previousElementSibling.id;
    var pos = (e.target.id.indexOf('top') == -1) ?
                    $(containerId)[0].scrollHeight
                    : 0;
    $(containerId).animate({ scrollTop: pos }, scrollAnimationMs, 'swing', function() {
      $('<style></style>').appendTo($(document.body)).remove();
    });
  }
      
    function loadServiceOptions() {
        $.ajax({
            type: 'POST',
            url: 'ajax/get_service_options.php'
        })
        .done(function(msg) {
            $('.reports-form').unmask();
            var data = JSON.parse(msg);
            if (data.success && data.options) {
                options = data.options;
                populateTypes();
            } else {
                if (data.error === 1) {
                    logout();
                } else {
                    $().toastmessage('showErrorToast', "Error loading settings");
                }
            }
        })
        .fail(function() {
            $('.reports-form').unmask();
            $().toastmessage('showErrorToast', "Error loading settings");
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
          case 5:
            populatePeopleByAttenderStatus(data.people);
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
      var data = JSON.parse(msg);
      if(data.success) {
        populateForm(data.first_dt, data.last_dt);
        loadServiceOptions();
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
    if(phone.match(/\D/g,'') === null) {
      var tmp = phone.replace(/\D/g);
      if(tmp.length === 7) {
	phone = tmp.substr(0,3) + '-' + tmp.substr(3);
      } else if(tmp.length === 10) {
	phone = '(' + tmp.substr(0,3) + ') ' + tmp.substr(3,3) + '-' + tmp.substr(6);
      }
    }
    return phone;
  }
  
  function onEmailClick() {
	  var email = $.trim(emailField.value);
	  if(!email || !emailField.checkValidity()) {
		  $().toastmessage('showErrorToast', "Must specify a valid email");
		  return;
	  }
	  $('.reports-form').mask('Sending...');
	  $.ajax({
		  type: 'POST',
		  url: 'ajax/email_follow_up_summary.php',
		  data: {
			email: email,
			params: JSON.stringify(runParams)
		  }
		})
		.done(function(msg) {
		  $('.reports-form').unmask();
		  var data = JSON.parse(msg);
		  if(data.success) {
			$().toastmessage('showSuccessToast', "Summary sent successfully");
		  } else {
			if(data.error === 1) {
			  logout();
			} else {
			  $().toastmessage('showErrorToast', "Error sending summary");
			}
		  }
		})
		.fail(function() {
		  $('.reports-form').unmask();
		  $().toastmessage('showErrorToast', "Error sending summary");
		});
  }
  
  function toggleSelection() {
      var checkboxes = $('#include-all-checkboxes input[type=checkbox]');
      if($('#toggle-check').is(':checked')) {
          checkboxes.each(function(ind, el) { 
              el.checked = true; 
          });
      } else {
          checkboxes.each(function(ind, el) { 
              el.checked = false; 
          });
      }
  }
  
  reportTypeField.value = '1';
  
  runBtn.addEventListener('click', onRunClick);
  emailBtn.addEventListener('click', onEmailClick);
  reportTypeField.addEventListener('change', onReportTypeChange);
  $('.top-bottom-links a').on('click', onClickTopBottom);
  $('#toggle-check').on('change', toggleSelection);
  
  checkLoginStatus(loadFirstLastServiceDates);
})();
