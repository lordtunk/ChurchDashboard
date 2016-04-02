(function() {
    'use strict';

    // A janky way to ensure that this code only runs on the Attendance
    // page. This is needed because the js minifier brings in all code
    // files. If the minifier process could be modified to generate a
    // minified version of only the needed files for each page or if
    // each page could be made to be an object that would probably
    // be ideal
    if ($('#attendance').length === 0) return;

    $('#attendance-date').datepicker({
        dateFormat: 'm/d/yy'
    });
    var attendanceDate = document.querySelector('#attendance-date'),
        activeTrue = document.querySelector('#active-true'),
        activeFalse = document.querySelector('#active-false'),
        adultTrue = document.querySelector('#adults-true'),
        adultFalse = document.querySelector('#adults-false'),
        updateBtn = document.querySelector('#update'),
        cancelBtn = document.querySelector('#cancel'),
        exportBtn = document.querySelector('#export'),
        serviceLabel1Field = document.querySelector('#service-label-1'),
        serviceLabel2Field = document.querySelector('#service-label-2'),
        campusField = document.querySelector('#campus'),
        currentLabel1 = -1,
        currentLabel2 = -1,
        currentCampus = -1,
        selectDateBtn = document.querySelector('#go-arrow'),
        addPersonBtn = document.querySelector('#add-person'),
        attendanceDateDisplay = document.querySelector('#attendance-date-display'),
        visitorsFirstServiceField = document.querySelector('#visitors-first-service'),
        visitorsSecondServiceField = document.querySelector('#visitors-second-service'),
        visitorsFirstCount = 0,
        visitorsSecondCount = 0,
        originalVisitorsFirstCount = 0,
        originalVisitorsSecondCount = 0,
        otherVisitorsFirstCount = 0,
        otherVisitorsSecondCount = 0,
        people = [],
        idSequence = 0,
        personIdSequence = -1,
        prevAttendanceDate,
        currAttendanceDate,
        noChangesMade = true,
        scrollAnimationMs = 1000,
        dialog = $('.dialog-form').dialog({
            autoOpen: false,
            height: 450,
            width: 450,
            modal: true
        }),
        months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ],
        options = {},
        tempId = -1,
        isAdults = true,
        originalTotals,
        totals = {},
        totalsEls = {
            total_first_count: document.querySelector('#total-first-count'),
            total_second_count: document.querySelector('#total-second-count'),
            total_total_count: document.querySelector('#total-total-count'),
            
            adult_first_count: document.querySelector('#adult-first-count'),
            adult_second_count: document.querySelector('#adult-second-count'),
            adult_total_count: document.querySelector('#adult-total-count'),
            
            kid_first_count: document.querySelector('#kid-first-count'),
            kid_second_count: document.querySelector('#kid-second-count'),
            kid_total_count: document.querySelector('#kid-total-count')
        };

    function exportAttendance() {
        window.location = 'ajax/export_attendance.php';
    }
    
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

    function update() {
        try {
            if(refreshTotals()) {
                $('.attendance-form').mask('Loading...');
                setTimeout(doUpdate, 50);
            }
        } catch (e) {
            $('.attendance-form').unmask();
        }
    }

    function doUpdate() {
        var updatedPeople = [],
            i, rows, personId, display, displayField,
            first, second, p;
        rows = $('#attendance-table > tbody:last').children();
        for (i = 0; i < rows.length; i++) {
            // Only update modified rows
            if (rows[i].getAttribute('modified') === null) continue;
            
            personId = rows[i].getAttribute('personId');
            displayField = rows[i].querySelector('[name=name_description]');
            first = isAttendingFirstService(personId);
            second = isAttendingSecondService(personId);

            // An input field should be found for added people
            if (displayField) {
                display = $.trim(displayField.value);
                // If the display field is empty then do not save the person
                if (display === '')
                    continue;
            } else {
                display = undefined;
            }

            if (personId.indexOf('-') === -1) {
                // Update the cached person information
                p = getPerson(personId);
                if (p) {
                    p.first = first;
                    p.second = second;
                }
            }
            updatedPeople.push({
                id: personId,
                adult: isAdults,
                display: display,
                attendanceDate: currAttendanceDate,
                first: first,
                second: second
            });
        }
        if (updatedPeople.length > 0 || visitorsFirstCount != originalVisitorsFirstCount || visitorsSecondCount != originalVisitorsSecondCount) {
            savePeople(updatedPeople);
        } else {
            $('.attendance-form').unmask();
            $().toastmessage('showWarningToast', "No attendance was modified");
        }
    }

    function cancel() {
        if (noChangesMade || confirm("Clear out any unsaved changes?"))
            reset();
    }

    function reset() {
        clear();
        processPeople(people);
    }
    
    function clear() {
        noChangesMade = true;
        $('#attendance-table > tbody:last').children().remove();
    }

    function addPerson() {
        addPersonBtn.blur();
        noChangesMade = false;
        var person = {
            id: genPersonid(),
            adult: true
        };
        var row = buildNewPersonRow(person, currAttendanceDate);
        $('#attendance-table > tbody:last').append(row);
        $('[personid=' + person.id + '] input:checkbox').on('change', updateAttendance);
        $('#attendance-table-container').animate({
            scrollTop: $('#attendance-table-container')[0].scrollHeight
        }, scrollAnimationMs);
        $('.attendance-table-attendance-col[service=second]').css('display', (currentLabel2) ? '' : 'none');
    }

    function setAttendanceDate(dt) {
        if(!dt) {
            var curr = new Date(); // get current date
            var first = curr.getDate() - curr.getDay(); // First day is the day of the month - the day of the week
            dt = new Date(curr.setDate(first));
        }
        $('#attendance-date').datepicker("setDate", dt);
        //attendanceDate.value = getDateString(sunday);
        currAttendanceDate = attendanceDate.value;
        prevAttendanceDate = dt;
        
        attendanceDateDisplay.innerHTML = getDateString(dt);
    }
    function getDateString(date) {
        return months[date.getMonth()]+' '+date.getDate()+', '+date.getFullYear();
    }

    function onSelectAttendanceDate() {
        if (noChangesMade || confirm("If you change the date you will lose any unsaved changes. Continue?")) {            
            if(serviceLabel1Field.value == serviceLabel2Field.value) {
                $().toastmessage('showErrorToast', "First and Second Service cannot be the same");
                return;
            }
            prevAttendanceDate = new Date(attendanceDate.value);
            currAttendanceDate = attendanceDate.value;
            clear();
            loadPeople(false);
        } else {
            $('#attendance-date').datepicker("setDate", prevAttendanceDate);
            //attendanceDate.value = getDateString(prevAttendanceDate);
        }
        attendanceDateDisplay.innerHTML = getDateString($('#attendance-date').datepicker("getDate"));
    }

    function onClickTopBottom(e) {
        var containerId = '#attendance-table-container',
            container = $(containerId),
            pos = (e.target.id.indexOf('top') == -1) ?
                container[0].scrollHeight : 0;

        container.stop().animate({
            scrollTop: pos
        }, scrollAnimationMs, 'swing', function() {
            $('<style></style>').appendTo($(document.body)).remove();
        });
    }

    function processPeople(data) {
        clearNavigation();
        detachLinkClickListeners();
        $('.attendance-table-attendance-col input:checkbox').off('change');
        clearAttendance();
        people = data;
        var dt = currAttendanceDate;
        var rows = '';
        for (var i = 0; i < people.length; i++) {
            if (people[i].active == activeTrue.checked) {
                rows += buildPersonRow(people[i], dt);
            }
        }
        setAttendance(totals);
        $('#attendance-table > tbody:last').append(rows);
        $('.attendance-table-attendance-col input:checkbox').on('change', updateAttendance);
        attachLinkClickListeners();
    }

    function buildPersonRow(person) {
        appendNavigationOption(person.id, person.last_name, person.adult);
        var firstChecked = person.first ? 'checked' : '',
            secondChecked = person.second ? 'checked' : '',
            display = '',
            secondServiceCol = '';

        display = '<a class="person_name" href="manage-person.html?id=' + person.id + '">' + person.display + '</a>';
        if(currentLabel2) {
            tempId = genId();
            secondServiceCol = '<td class="attendance-table-attendance-col" service="second" data-th="Second?"><label for="' + tempId + '"><input id="' + tempId + '" type="checkbox" ' + secondChecked + '/></label></td>';
        }
        tempId = genId();
        return '<tr adult="' + person.adult + '" personId="' + person.id + '"><td data-th="Name">' +
            '<button class="attendance-history-button"><i class="fa fa-archive" /></button>' + display + '</td>' +
            '<td class="attendance-table-attendance-col" service="first" data-th="First?">' +
            '<label for="' + tempId + '"><input id="' + tempId + '" type="checkbox" ' + firstChecked + '/></label></td>' +
            secondServiceCol + '</tr>';
            //'<td class="attendance-table-attendance-col" service="second" data-th="Second?">' + secondServiceCol + '</td></tr>';
    }

    function buildNewPersonRow(person) {
        var secondServiceCol = '';
        if(currentLabel2) {
            tempId = genId();
            secondServiceCol = '<td class="attendance-table-attendance-col" service="second" data-th="Second?"><label for="' + tempId + '"><input id="' + tempId + '" type="checkbox" /></label></td>';
        }
        tempId = genId();
        return '<tr adult="' + person.adult + '" personId="' + person.id + '" modified="true"><td data-th="Name">' +
            '<input name="name_description" type="text" placeholder="Last, First or Description" /></td>' +
            '<td class="attendance-table-attendance-col" service="first" data-th="First?">' +
            '<label for="' + tempId + '"><input id="' + tempId + '" type="checkbox" /></label></td>' +
            secondServiceCol + '</tr>';
            //'<td class="attendance-table-attendance-col" service="second" data-th="Second?">' + secondServiceCol + '</td></tr>';
    }

    function updateNewPeople(data) {
        noChangesMade = true;
        if (!data || data.length === 0) return;
        var personRows = [],
            lastPersonInd = 0,
            i, j, person, newPerson, inserted, pEl;
        // Remove any 'new person' rows from the table
        $('[personid^=-]').remove();

        // Create rows for them
        for (i = 0; i < data.length; i++) {
            people.push(data[i]);
            personRows.push(buildPersonRow(data[i], currAttendanceDate));
        }

        // Add them to the appropriate person table
        for (i = 0; i < data.length; i++) {
            newPerson = data[i];
            inserted = false;
            for (j = 0; j < people.length; j++) {
                person = people[j];
                lastPersonInd = j;

                if (personCompareTo(newPerson, person) === -1) {
                    $(personRows[i]).insertBefore('[personid=' + person.id + ']');
                    people.splice(j, 0, newPerson);
                    inserted = true;
                    break;
                }
            }
            // If the person was not inserted into the table in the loop then add to the end
            if (inserted === false) {
                $('#attendance-table > tbody:last').append(personRows[i]);
                people.splice(lastPersonInd + 1, 0, newPerson);
            }

            // Attach listeners to inserted rows
            pEl = $('[personid=' + newPerson.id + ']');
            pEl.find('a.person_name').on('click', onClickLink);
            //pEl.find('button.attendance-history-button').on('click', onClickAttendanceHistoryButton);
            // FIX ATTENDANCE UPDATE ISSUE. Attendance counts get messed up when toggling checkbox
            // after saving after adding a new person
            pEl.find('input:checkbox').on('change', updateAttendance);
        }
    }

    function updateAttendance(e) {
        var me = e.target,
            personId = me.parentElement.parentElement.parentElement.getAttribute('personId'),
            isFirst = me.parentElement.parentElement.getAttribute('service') === 'first',
            totalName = (isAdults ? 'adult' : 'kid') + '_total_count',
            firstSecondName = (isAdults ? 'adult' : 'kid') + '_' + (isFirst ? 'first' : 'second') + '_count',
            isAttendingOtherService = (!currentLabel2) ? function() { return false; } : (isFirst ? isAttendingSecondService : isAttendingFirstService);

        noChangesMade = false;
        me.parentElement.parentElement.parentElement.setAttribute('modified', true);
        if (me.checked) {
            totals[firstSecondName]++;

            if (!isAttendingOtherService(personId))
                totals[totalName]++;
        } else {
            totals[firstSecondName]--;

            if (!isAttendingOtherService(personId))
                totals[totalName]--;
        }
        updateVisitorAttendance();
    }
    
    function updateVisitorAttendance() {
        if(isAdults) {
            totalsEls.adult_first_count.innerHTML = totals.adult_first_count + visitorsFirstCount;
            totalsEls.adult_second_count.innerHTML = totals.adult_second_count + visitorsSecondCount;
            totalsEls.adult_total_count.innerHTML = totals.adult_total_count + visitorsFirstCount + visitorsSecondCount;
                     
            totalsEls.kid_first_count.innerHTML = totals.kid_first_count + otherVisitorsFirstCount;
            totalsEls.kid_second_count.innerHTML = totals.kid_second_count + otherVisitorsSecondCount;
            totalsEls.kid_total_count.innerHTML = totals.kid_total_count + otherVisitorsFirstCount + otherVisitorsSecondCount;
        } else {     
            totalsEls.adult_first_count.innerHTML = totals.adult_first_count + otherVisitorsFirstCount;
            totalsEls.adult_second_count.innerHTML = totals.adult_second_count + otherVisitorsSecondCount;
            totalsEls.adult_total_count.innerHTML = totals.adult_total_count + otherVisitorsFirstCount + otherVisitorsSecondCount;
                     
            totalsEls.kid_first_count.innerHTML = totals.kid_first_count + visitorsFirstCount;
            totalsEls.kid_second_count.innerHTML = totals.kid_second_count + visitorsSecondCount;
            totalsEls.kid_total_count.innerHTML = totals.kid_total_count + visitorsFirstCount + visitorsSecondCount;
        }
        totals.total_first_count = totals.adult_first_count + totals.kid_first_count;
        totals.total_second_count = totals.adult_second_count + totals.kid_second_count;
        totals.total_total_count = totals.adult_total_count + totals.kid_total_count;
        
        totalsEls.total_first_count.innerHTML = totals.total_first_count + visitorsFirstCount + otherVisitorsFirstCount;
        totalsEls.total_second_count.innerHTML = totals.total_second_count + visitorsSecondCount + otherVisitorsSecondCount;
        totalsEls.total_total_count.innerHTML = totals.total_total_count + visitorsFirstCount + visitorsSecondCount + otherVisitorsFirstCount + otherVisitorsSecondCount;
    }
    
    function refreshTotals() {
        var visitorsFirst = parseInt(visitorsFirstServiceField.value || 0),
            visitorsSecond = parseInt(visitorsSecondServiceField.value || 0),
            msg = '';
        if(isNaN(visitorsFirst) || isNaN(visitorsSecond))
            msg += 'Visitor count must be a number';
        if(visitorsFirst < 0 || visitorsSecond < 0)
            msg += 'Number of visitors cannot be negative';
        if(msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        visitorsFirstCount = visitorsFirst;
        visitorsSecondCount = visitorsSecond;
        updateVisitorAttendance();
        return true;
    }

    function showPersonAttendance(p) {
        $('#person-attendance-history-table > tbody:last').children().remove();

        dialog.dialog('open');
        dialog[0].querySelector('#person-name').innerHTML = getDisplayName(p, false);
        var rows = '';
        for (var i = 0; i < p.attendance.length; i++) {
            rows += buildAttendanceRow(p.attendance[i]);
        }
        $('#person-attendance-history-table > tbody:last').append(rows);
    }

    function buildAttendanceRow(a) {
        var firstChecked = a.first ? 'checked' : '';
        var secondServiceCol = '';
        if(currentLabel2) {
            secondServiceCol = '<td class="attendance-table-attendance-col" service="second" data-th="Second?"><input type="checkbox" ' + (a.second ? 'checked' : '') + ' disabled/></td>';
        }
        return '<tr><td data-th="Date">' + a.date + '</td>' +
            '<td class="attendance-table-attendance-col" service="first" data-th="First?">' +
            '<input type="checkbox" ' + firstChecked + ' disabled/></td>' +
            //'' +
            secondServiceCol+
            '</tr>';
            //'</td></tr>';
    }
    
    function sanitizeAttendance() {
        for(var t in totals) {
            if(totals.hasOwnProperty(t)) {
                totals[t] = parseInt(totals[t]);
            }
        }
    }
    
    function clearAttendance() {
        visitorsFirstServiceField.value = visitorsFirstCount = originalVisitorsFirstCount;
        visitorsSecondServiceField.value = visitorsSecondCount = originalVisitorsSecondCount;
        
        totals = jQuery.extend({}, originalTotals);
        for(var t in totals) {
            if(totals.hasOwnProperty(t)) {
                totalsEls[t].innerHTML = totals[t];
            }
        }
        
        refreshTotals();
    }
    
    function setAttendance(ts) {
        totals.total_first_count = totals.adult_first_count + totals.kid_first_count;
        totals.total_second_count = totals.adult_second_count + totals.kid_second_count;
        totals.total_total_count = totals.adult_total_count + totals.kid_total_count;
        
        for(var t in ts) {
            if(ts.hasOwnProperty(t)) {
                totals[t] = ts[t];
                totalsEls[t].innerHTML = ts[t];
            }
        }
    }
    
    
    
    function loadPersonAttendance(personId, cb) {
        $('.attendance-form').mask('Loading...');
        $.ajax({
            type: 'GET',
            url: 'ajax/get_person_attendance.php',
            data: {
                campus: currentCampus,
                label1: currentLabel1,
                label2: currentLabel2,
                id: personId
            }
        })
            .done(function(msg) {
                $('.attendance-form').unmask();
                var data = JSON.parse(msg);
                if (data.success) {
                    showPersonAttendance(data.person);
                    if(currentLabel2) {
                        $('.attendance-table-attendance-col[service=second]').css('display', '');
                    } else {
                        $('.attendance-table-attendance-col[service=second]').css('display', 'none');
                    }
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        $().toastmessage('showErrorToast', "Error loading person's attendance history");
                    }
                }
            })
            .fail(function() {
                $('.attendance-form').unmask();
                $().toastmessage('showErrorToast', "Error loading person's attendance history");
            });
    }
    
    function loadServiceOptions() {
        $('.attendance-form').mask('Loading...');
        $.ajax({
            type: 'POST',
            url: 'ajax/get_service_options.php'
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success && data.options) {
                options = data.options;
                populateTypes();
                loadPeople();
            } else {
                if (data.error === 1) {
                    logout();
                } else {
                    $().toastmessage('showErrorToast', "Error loading settings");
                }
            }
        })
        .fail(function() {
            $('.attendance-form').unmask();
            $().toastmessage('showErrorToast', "Error loading settings");
        });
    }

    function loadPeople(isDefault) {
        if(typeof isDefault === 'undefined') {
            isDefault = true;
        }
        if(!isDefault) {
            $('.attendance-form').mask('Loading...');
        }
        
        $.ajax({
            type: 'POST',
            url: 'ajax/get_attendance.php',
            data: {
                date: attendanceDate.value,
                active: activeTrue.checked,
                adult: adultTrue.checked,
                campus: campusField.value,
                label1: serviceLabel1Field.value,
                label2: serviceLabel2Field.value,
                isDefaultLoad: isDefault
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    originalVisitorsSecondCount = 0;
                    otherVisitorsSecondCount = 0;
                    
                    if(data.attendance_dt)
                        setAttendanceDate(new Date(data.attendance_dt));
                    if(typeof data.attendance_adults !== "undefined") {
                        var isLoadAdults = data.attendance_adults == "true";
                        adultTrue.checked = isLoadAdults;                            
                        adultFalse.checked = !isLoadAdults;                            
                    }
                    if(typeof data.attendance_active !== "undefined"){
                        var isLoadActive = data.attendance_active == "true";
                        activeTrue.checked = isLoadActive;                            
                        activeFalse.checked = !isLoadActive;                            
                    }
                    if(typeof data.campus !== "undefined") {
                        campusField.value = data.campus;
                    }
                    if(typeof data.label1 !== "undefined") {
                        serviceLabel1Field.value = data.label1;
                    }
                    if(typeof data.label2 !== "undefined") {
                        serviceLabel2Field.value = data.label2;
                    }
                    isAdults = adultTrue.checked;
                    currentCampus = campusField.value;
                    currentLabel1 = serviceLabel1Field.value;
                    currentLabel2 = serviceLabel2Field.value;
                    var secondService = $('#service-label-2').val();
                    
                    if(isAdults) {
                        originalVisitorsFirstCount = visitorsFirstCount = parseInt(data.visitors1.adult_visitors);
                        otherVisitorsFirstCount = parseInt(data.visitors1.kid_visitors);
                        if(secondService) {
                            originalVisitorsSecondCount = visitorsSecondCount = parseInt(data.visitors2.adult_visitors);
                            otherVisitorsSecondCount = parseInt(data.visitors2.kid_visitors);
                        }
                    } else {
                        originalVisitorsFirstCount = visitorsFirstCount = parseInt(data.visitors1.kid_visitors);
                        otherVisitorsFirstCount = parseInt(data.visitors1.adult_visitors);
                        if(secondService) {
                            originalVisitorsSecondCount = visitorsSecondCount = parseInt(data.visitors2.kid_visitors);
                            otherVisitorsSecondCount = parseInt(data.visitors2.adult_visitors);
                        }
                    }
                    //originalVisitorsFirstCount = visitorsFirstCount = parseInt(isAdults ? data.visitors1.adult_visitors : data.visitors1.kid_visitors);
                    //originalVisitorsSecondCount = visitorsSecondCount = parseInt(isAdults ? data.visitors2.adult_visitors : data.visitors2.kid_visitors);
                    totals = data.totals;
                    sanitizeAttendance();
                    originalTotals = jQuery.extend({}, totals);
                    processPeople(data.people);
                    refreshTotals();
                    $('#name-table-header').text(isAdults ? 'Adults' : 'Kids');
                    var firstServiceText = options.service_labels[$('#service-label-1').val()];
                    $('.first-service-header').text(firstServiceText);
                    $('#first-service-total-header').text(firstServiceText+' Attendance');
                    $('#visitors-first-service-label').text(firstServiceText+' Visitors');
                    
                    if(secondService) {
                        $('.second-service-header').text(options.service_labels[secondService]);
                        $('#second-service-total-header').text(options.service_labels[secondService] + ' Attendance');
                        $('.second-service-header').css('display', '');
                        $('#first-service-total-row').css('display', '');
                        $('#second-service-total-row').css('display', '');
                        $('.attendance-table-attendance-col[service=second]').css('display', '');
                        $('#visitors-second-service-label').css('display', '').text(options.service_labels[secondService]+' Visitors');
                        $('#visitors-second-service').css('display', '');
                    } else {
                        $('.second-service-header').css('display', 'none');
                        $('#first-service-total-row').css('display', 'none');
                        $('#second-service-total-row').css('display', 'none');
                        $('.attendance-table-attendance-col[service=second]').css('display', 'none');
                        $('#visitors-second-service-label').css('display', 'none');
                        $('#visitors-second-service').css('display', 'none');
                    }
                    $('.campus-text').text(options.campuses[$('#campus').val()]);
                    if (data.scroll_to_id && data.scroll_to_id >= 0) {
                        var scrollTo = $('[personid=' + data.scroll_to_id + ']')[0];
                        if (scrollTo) {
                            var containerId = '#attendance-table-container',
                                screenOff = $(containerId).offset().top,
                                scrollOff = scrollTo.offsetTop;
                            $('body').animate({
                                scrollTop: screenOff
                            }, 300);
                            $(containerId).animate({
                                scrollTop: scrollOff
                            }, scrollAnimationMs);
                        }
                    }
                    $('.attendance-form').unmask();
                } else {
                    $('.attendance-form').unmask();
                    if (data.error === 1) {
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
            data: {
                people: JSON.stringify(newPeople),
                campus: currentCampus,
                label1: currentLabel1,
                label2: currentLabel2,
                visitors1: visitorsFirstCount,
                visitors2: visitorsSecondCount,
                adult: isAdults,
                date: currAttendanceDate
            }
        })
            .done(function(msg) {
                $('.attendance-form').unmask();
                var data = JSON.parse(msg);
                if (data.success) {
                    originalTotals = jQuery.extend({}, totals);
                    updateNewPeople(data.people);
                    $('.attendance-table-attendance-col[service=second]').css('display', (currentLabel2) ? '' : 'none');
                    //people = data.people;
                    //reset();
                    $().toastmessage('showSuccessToast', "Save successful");
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        $().toastmessage('showErrorToast', "Error saving");
                    }
                }
            });
    }

    function isAttendingFirstService(id) {
        return $('[personId=' + id + '] input:checkbox:first')[0].checked;
    }

    function isAttendingSecondService(id) {
        return $('[personId=' + id + '] input:checkbox:last')[0].checked;
    }

    function personCompareTo(p1, p2) {
        if (p1 == p2) return 0;
        if (p1 === null) return -1;
        if (p2 === null) return 1;

        var p1Display = getDisplayName(p1, true),
            p2Display = getDisplayName(p2, true);
        if (p1.first_name || p1.last_name) {
            if (p2.first_name || p2.last_name) {
                if (p1Display == p2Display) return 0;
                if (p1Display.toLowerCase() < p2Display.toLowerCase()) return -1;
                if (p1Display.toLowerCase() > p2Display.toLowerCase()) return 1;
            } else {
                return -1;
            }
        } else if (p2.first_name || p2.last_name) {
            return 1;
        } else {
            if (p1Display == p2Display) return 0;
            if (p1Display.toLowerCase() < p2Display.toLowerCase()) return -1;
            if (p1Display.toLowerCase() > p2Display.toLowerCase()) return 1;
        }
    }

    function getDisplayName(person, withComma) {
        if (person === null) return '';

        var display = '';
        if (person.first_name || person.last_name) {
            if (person.last_name) display += person.last_name;

            if (person.last_name && person.first_name) {
                if (withComma === true)
                    display += ", " + person.first_name;
                else
                    display = person.first_name + ' ' + display;
            } else if (person.first_name) {
                display += person.first_name + ' ';
            }
        } else {
            display = person.description;
        }
        return display;
    }

    function getPerson(id) {
        for (var i = 0; i < people.length; i++) {
            if (people[i].id === id) return people[i];
        }
        return null;
    }

    function genId() {
        return 'id-' + (++idSequence);
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
        var personId = this.parentElement.parentElement.getAttribute('personId'); // jshint ignore:line
        loadPersonAttendance(personId);
    }

    function clearNavigation() {
        document.querySelector('#jump-to').innerHTML = '<option>--Jump To Letter--</option>';
    }

    function appendNavigationOption(id, lastName) {
        if (!lastName) return;
        var letter = lastName.substr(0, 1).toUpperCase();
        if ($('#jump-to option:contains("' + letter + '")').length > 0) return;
        var s = document.querySelector('#jump-to');
        var o = document.createElement('option');
        o.setAttribute('scroll_to_id', id);
        o.innerHTML = letter;
        s.appendChild(o);
    }

    function jumpTo(e) {
        var o = e.target.options[e.target.options.selectedIndex];
        if (!o) return;

        var scrollTo = $('[personid=' + o.getAttribute('scroll_to_id') + ']')[0];
        if (!scrollTo) return;
        var containerId = '#attendance-table-container',
            screenOff = $(containerId).offset().top,
            scrollOff = scrollTo.offsetTop;
        $('body').animate({
            scrollTop: screenOff
        }, 300);
        $(containerId).animate({
            scrollTop: scrollOff
        }, scrollAnimationMs);
    }

    function onClickLink(e) {
        if (!(noChangesMade || confirm("If you continue you will lose any unsaved changes. Continue?"))) {
            e.preventDefault();
        }
    }

    setAttendanceDate();

    updateBtn.addEventListener('click', update);
    cancelBtn.addEventListener('click', cancel);
    exportBtn.addEventListener('click', exportAttendance);
    selectDateBtn.addEventListener('click', onSelectAttendanceDate);
    addPersonBtn.addEventListener('click', addPerson);
    //$('#visitors').on('change', updateVisitorAttendance);
    $('.jump-to').on('change', jumpTo);
    $('.navigation-links a').on('click', onClickTopBottom);
    $('#refresh-visitors').on('click', refreshTotals);
    
    checkLoginStatus(loadServiceOptions);
})();
