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

  function onRunClick() {
    if(validateDates(fromDateField.value, toDateField.value)) {
      var reportType = parseInt(reportTypeField.value);
      hideAllReportContainers();
      switch(reportType) {
        case 1:
          $('#attendance-by-date-container')[0].style.setProperty('display', 'inherit');
          break;
        case 2:
          $('#attendance-by-person-container')[0].style.setProperty('display', 'inherit');
          break;
      }
      loadReport(reportType, fromDateField.value, toDateField.value);
    }
  }

  function hideAllReportContainers() {
    $('#attendance-by-person-container')[0].style.setProperty('display', 'none');
    $('#attendance-by-date-container')[0].style.setProperty('display', 'none');
  }

  function validateDates(fDate, tDate) {
    var msg = '';
    
    if(!isDate(fDate)) {
      msg += 'From Date must be a valid date<br />';
    }
    if(!isDate(tDate)) {
      msg += 'To Date must be a valid date<br />';
    }
    
    if(msg) {
      $().toastmessage('showErrorToast', msg);
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
    var adultRows= '';
    for(var i=0; i<people.length; i++) {
      if(people[i].adult == 'true')
        adultRows += buildPersonRow(people[i]);
//       else
//         kidRows += buildPersonRow(people[i], dt);
    }
    $('#adult-attendance-table > tbody:last').append(adultRows);
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

  function onClickTopBottom(e) {
    var containerId = '#attendance-by-'+((e.target.id.indexOf('date') == -1) ? 'person' : 'date') + '-table-container';
    var pos = (e.target.id.indexOf('top') == -1) ?
                    $(containerId)[0].scrollHeight
                    : 0;
    $(containerId).animate({ scrollTop: pos }, scrollAnimationMs, 'swing', function() {
      $('<style></style>').appendTo($(document.body)).remove();
    });
  }

  function loadReport(reportType, fromDate, toDate) {
    $.ajax({
      type: 'GET',
      url: 'ajax/get_report.php',
      data: {
        type: reportType,
        fromDate: fromDate,
        toDate: toDate
      }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
        switch(reportType) {
          case 1:
            populateAttendanceByDate(data.totals, data.aggregates);
            break;
          case 2:
            populateAttendanceByPerson(data.people);
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
      $().toastmessage('showErrorToast', "Error loading report");
    });
  }

  function loadFirstLastServiceDates() {
    $.ajax({
      type: 'GET',
      url: 'ajax/get_first_last_service_dates.php'
    })
    .done(function(msg) {
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
      $().toastmessage('showErrorToast', "Error loading data");
    });
  }

  function isDate(txtDate) {
    var currVal = txtDate;
    if(currVal === '')
      return false;

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
  runBtn.addEventListener('click', onRunClick);
  $('.top-bottom-links a').on('click', onClickTopBottom);
  
  checkLoginStatus(loadFirstLastServiceDates);
})();