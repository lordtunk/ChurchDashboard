(function() {
    'use strict';

    if ($('#attendance').length === 0) return;

    //$('#attendance-date').datepicker();
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
        selectDateBtn = document.querySelector('#go-arrow'),
        addPersonBtn = document.querySelector('#add-person'),
        attendanceDateDisplay = document.querySelector('#attendance-date-display'),
        //visitors = document.querySelector('#visitors'),
        people = [],
        idSequence = 0,
        personIdSequence = -1,
        prevAttendanceDate,
        currAttendanceDate,
        noChangesMade = true,
        scrollAnimationMs = 1000,
        dialog = $('.dialog-form').dialog({
            autoOpen: false,
            height: 400,
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

    function update() {
        try {
            $('.attendance-form').mask('Loading...');
            setTimeout(doUpdate, 50);
        } catch (e) {
            $('.attendance-form').unmask();
        }
    }

    function doUpdate() {
        var updatedPeople = [],
            i, j, rows, personId, display, displayField, attendanceDateFound,
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
                    attendanceDateFound = false;
                    for (j = 0; j < p.attendance.length; j++) {
                        if (p.attendance[j].date === currAttendanceDate) {
                            p.attendance[j].first = first;
                            p.attendance[j].second = second;
                            attendanceDateFound = true;
                        }
                    }
                    if (!attendanceDateFound) {
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
                adult: isAdults,
                display: display,
                attendanceDate: currAttendanceDate,
                first: first,
                second: second
            });
        }
        if (updatedPeople.length > 0) {
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

    function buildPersonRow(person, dt) {
        appendNavigationOption(person.id, person.last_name, person.adult);
        var firstChecked = '',
            secondChecked = '',
            display = '',
            firstId = genId(),
            secondId = genId(),
            ind;
        if ((ind = getDateIndex(person, dt)) != -1) {
            firstChecked = person.attendance[ind].first ? 'checked' : '';
            secondChecked = person.attendance[ind].second ? 'checked' : '';
        }

        display = getDisplayName(person);
        display = '<a class="person_name" href="manage-person.html?id=' + person.id + '">' + display + '</a>';
        
        return '<tr adult="' + person.adult + '" personId="' + person.id + '"><td data-th="Name">' +
            '<button class="attendance-history-button"><i class="fa fa-archive" /></button>' + display + '</td>' +
            '<td class="attendance-table-attendance-col" service="first" data-th="First?">' +
            '<label for="' + firstId + '"><input id="' + firstId + '" type="checkbox" ' + firstChecked + '/></label></td>' +
            '<td class="attendance-table-attendance-col" service="second" data-th="Second?">' +
            '<label for="' + secondId + '"><input id="' + secondId + '" type="checkbox" ' + secondChecked + '/></label></td></tr>';
    }

    function buildNewPersonRow(person) {
        var firstId = genId(),
            secondId = genId();
        return '<tr adult="' + person.adult + '" personId="' + person.id + '" modified="true"><td data-th="Name">' +
            '<input name="name_description" type="text" placeholder="Last, First or Description" /></td>' +
            '<td class="attendance-table-attendance-col" service="first" data-th="First?">' +
            '<label for="' + firstId + '"><input id="' + firstId + '" type="checkbox" /></label></td>' +
            '<td class="attendance-table-attendance-col" service="second" data-th="Second?">' +
            '<label for="' + secondId + '"><input id="' + secondId + '" type="checkbox" /></label></td></tr>';
    }

    function updateNewPeople(data) {
        noChangesMade = true;
        if (!data || data.length === 0) return;
        var personRows = [],
            lastAdultInd = 0,
            lastKidInd = 0,
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

                if (person.adult === true)
                    lastAdultInd = j;
                else
                    lastKidInd = j;

                // Only compare the two if they are both adults or both kids
                if (person.adult !== newPerson.adult) continue;

                if (personCompareTo(newPerson, person) === -1) {
                    $(personRows[i]).insertBefore('[personid=' + person.id + ']');
                    people.splice(j, 0, newPerson);
                    inserted = true;
                    break;
                }
            }
            // If the person was not inserted into the table in the loop then add to the end
            if (inserted === false) {
                if (newPerson.adult === true) {
                    $('#adult-attendance-table > tbody:last').append(personRows[i]);
                    people.splice(lastAdultInd + 1, 0, newPerson);
                } else {
                    $('#kid-attendance-table > tbody:last').append(personRows[i]);
                    people.splice(lastKidInd + 1, 0, newPerson);
                }
            }

            // Attach listeners to inserted rows
            pEl = $('[personid=' + newPerson.id + ']');
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
            isFirst = me.parentElement.parentElement.getAttribute('service') === 'first',
            totalName = (isAdults ? 'adult' : 'kid') + '_total_count',
            firstSecondName = (isAdults ? 'adult' : 'kid') + '_' + (isFirst ? 'first' : 'second') + '_count',
            isAttendingOtherService = isFirst ? isAttendingSecondService : isAttendingFirstService;

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
        totalsEls[firstSecondName].innerHTML = totals[firstSecondName];
        totalsEls[totalName].innerHTML = totals[totalName];
        
        totals.total_first_count = totals.adult_first_count + totals.kid_first_count;
        totals.total_second_count = totals.adult_second_count + totals.kid_second_count;
        totals.total_total_count = totals.adult_total_count + totals.kid_total_count;
        
        totalsEls.total_first_count.innerHTML = totals.total_first_count;
        totalsEls.total_second_count.innerHTML = totals.total_second_count;
        totalsEls.total_total_count.innerHTML = totals.total_total_count;
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
        var secondChecked = a.second ? 'checked' : '';
        return '<tr><td data-th="Date">' + a.date + '</td>' +
            '<td class="attendance-table-attendance-col" service="first" data-th="First?">' +
            '<input type="checkbox" ' + firstChecked + ' disabled/></td>' +
            '<td class="attendance-table-attendance-col" service="second" data-th="Second?">' +
            '<input type="checkbox" ' + secondChecked + ' disabled/></td></tr>';
    }
    
    function sanitizeAttendance() {
        for(var t in totals) {
            if(totals.hasOwnProperty(t)) {
                totals[t] = parseInt(totals[t]);
            }
        }
    }
    
    function clearAttendance() {
        totals = jQuery.extend({}, originalTotals);
        for(var t in totals) {
            if(totals.hasOwnProperty(t)) {
                totalsEls[t].innerHTML = totals[t];
            }
        }
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
    
    function loadPersonAttendance(personId) {
        $('.attendance-form').mask('Loading...');
        $.ajax({
            type: 'GET',
            url: 'ajax/get_person_attendance.php',
            data: {
                id: personId
            }
        })
            .done(function(msg) {
                $('.attendance-form').unmask();
                var data = JSON.parse(msg);
                if (data.success) {
                    showPersonAttendance(data.person);
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

    function loadPeople(isDefault) {
        if(typeof isDefault === 'undefined')
            isDefault = true;
        $('.attendance-form').mask('Loading...');
        $.ajax({
            type: 'POST',
            url: 'ajax/get_attendance.php',
            data: {
                date: attendanceDate.value,
                active: activeTrue.checked,
                adult: adultTrue.checked,
                isDefaultLoad: isDefault
            }
        })
            .done(function(msg) {
                $('.attendance-form').unmask();
                var data = JSON.parse(msg);
                if (data.success) {
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
                    isAdults = adultTrue.checked;
                    $('#name-table-header').text(isAdults ? 'Adults' : 'Kids');
                    totals = data.totals;
                    sanitizeAttendance();
                    originalTotals = jQuery.extend({}, totals);
                    processPeople(data.people);
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
                } else {
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
                people: JSON.stringify(newPeople)
            }
        })
            .done(function(msg) {
                $('.attendance-form').unmask();
                var data = JSON.parse(msg);
                if (data.success) {
                    originalTotals = jQuery.extend({}, totals);
                    updateNewPeople(data.people);
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
                if (p1Display === p2Display) return 0;
                if (p1Display.toLowerCase() < p2Display.toLowerCase()) return -1;
                if (p1Display.toLowerCase() > p2Display.toLowerCase()) return 1;
            } else {
                return -1;
            }
        } else if (p2.first_name || p2.last_name) {
            return 1;
        } else {
            if (p1Display === p2Display) return 0;
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

    function getDateIndex(person, dt) {
        for (var i = 0; i < person.attendance.length; i++) {
            if (person.attendance[i].date === dt) return i;
        }
        return -1;
    }

//    function getDateString(dt) {
//        var curr_date = dt.getDate();
//        var curr_month = dt.getMonth();
//        var curr_year = dt.getFullYear();
//
//        curr_date = (curr_date < 10) ? '0' + curr_date : '' + curr_date;
//        curr_month++;
//        curr_month = (curr_month < 10) ? '0' + curr_month : '' + curr_month;
//
//        return curr_month + "/" + curr_date + "/" + curr_year;
//    }

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
        var containerId = '#jump-to';
        var letter = lastName.substr(0, 1).toUpperCase();
        if ($(containerId + ' option:contains("' + letter + '")').length > 0) return;
        var s = document.querySelector(containerId);
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


    checkLoginStatus(loadPeople);
})();
