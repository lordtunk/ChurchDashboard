(function() {
    'use strict';
    if ($('.follow-ups-form').length === 0) return;

    $('#follow-up-date').datepicker();
    var visitors = [],
        noChangesMade = true,
        $formTitle = $('#follow-ups-form-title'),
        followUpId = document.querySelector('#follow-up-id'),
        followUpPerson = document.querySelector('#follow-up-person'),
        followUpType = document.querySelector('#follow-up-type'),
        followUpDate = document.querySelector('#follow-up-date'),
        unknownDate = document.querySelector('#unknown-date'),
        followUpVisitors = document.querySelector('#follow-up-visitors'),
        followUpComments = document.querySelector('#follow-up-comments'),
        addClearBtn = document.querySelector('#add-clear'),
        addCopyBtn = document.querySelector('#add-copy'),
        clearBtn = document.querySelector('#clear'),
        addNewPersonBtn = document.querySelector('#add-new-person'),
        searchBtn = document.querySelector('#search'),
        searchField = document.querySelector('#search-name'),
        closeBtn = document.querySelector('#close'),
        dialog = $('.dialog-form').dialog({
            autoOpen: false,
            height: 400,
            width: 510,
            modal: true
        }),
        scrollAnimationMs = 1000,
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
        $.each(followUpTypeData, function(typeCd, type) {
            $select.append('<option value=' + typeCd + '>' + type + '</option>');
        });
        $select.val('2');
    }

    function addNewPerson() {
        var text = $.trim(searchField.value);
        if (text === '')
            $().toastmessage('showErrorToast', "Must enter Name");
        else
            doAddNewPerson(text);
    }

    function search() {
        var text = $.trim(searchField.value);
        if (text === '')
            $().toastmessage('showErrorToast', "Must enter Name");
        else
            doSearch(text);
    }

    function doAddNewPerson(text) {
        $.ajax({
            type: 'POST',
            url: 'ajax/create_person.php',
            data: {
                person_display: text
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    doSelectPerson(data.person_id, data.person_name);
                } else {
                    if (data.error === 1) {
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

    function doSearch(text) {
        $.ajax({
            type: 'GET',
            url: 'ajax/search.php',
            data: {
                search: text
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    processSearchResults(data.people);
                } else {
                    if (data.error === 1) {
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

    function loadVisitors() {
        $.ajax({
            type: 'GET',
            url: 'ajax/get_visitors.php'
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    visitors = data.people;
                    setVisitors();
                    populateTypes();
                    loadFollowUps();
                } else {
                    if (data.error === 1) {
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
                if (data.success) {
                    processFollowUps(data.follow_ups);
                } else {
                    if (data.error === 1) {
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
            data: {
                follow_up: JSON.stringify(f)
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    f.id = data.follow_up_id;
                    cb.call(this, f, clear);
                    $().toastmessage('showSuccessToast', "Save successful");
                } else {
                    if (data.error === 1) {
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
            data: {
                id: id
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    $('#follow-up-table tr[follow_up_id=' + id + ']').remove();
                } else {
                    if (data.error === 1) {
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

    function clearFollowUpForm() {
        $formTitle.text('Add Follow Up');
        followUpPerson.innerHTML = '(Select a person)';
        followUpPerson.setAttribute('personid', '');
        followUpPerson.setAttribute('person_name', '');
        followUpType.value = '2';
        followUpDate.value = '';
        followUpComments.value = '';
        unknownDate.checked = false;
        followUpDate.disabled = false;

        var inputs = followUpVisitors.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++)
            inputs[i].checked = false;
    }

    function saveFollowUp() {
        var name = $.trim(followUpPerson.getAttribute('person_name')),
            personId = $.trim(followUpPerson.getAttribute('personid')),
            date = $.trim(followUpDate.value),
            type = $.trim(followUpType.value),
            comments = $.trim(followUpComments.value),
            visitors = [],
            visitorsIds = [],
            msg = '';

        if (personId === '' || personId < 0) {
            msg += 'Must select a person<br />';
        }
        if (comments === '' && (type == 1 || type == 2)) {
            msg += 'Must specify comments<br />';
        }
        if (date === '' && !unknownDate.checked) {
            msg += 'Must specify a date or mark it unknown<br />';
        }
        if (comments.length > 5000) {
            msg += 'Comments cannot exceed 5000 characters<br />';
        }

        var inputs = followUpVisitors.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++) {
            if (!inputs[i].checked) continue;
            visitors.push($.trim(inputs[i].nextSibling.innerHTML));
            visitorsIds.push(inputs[i].getAttribute('personid'));
        }
        
        if (visitorsIds.length === 0) {
            msg += 'Must specify a visitor<br />';
        }
        
        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
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

    function onSelectPerson(e) {
        var row = e.currentTarget.parentElement.parentElement;

        var id = row.getAttribute('person_id');
        var name = row.children[0].getAttribute('person_name');
        doSelectPerson(id, name);
    }

    function doSelectPerson(id, name) {
        close();
        followUpPerson.innerHTML = '<a class="person_name" href="manage-person.html?id=' + id + '">' + name + '</a>';
        followUpPerson.setAttribute('personid', id);
        followUpPerson.setAttribute('person_name', name);
    }

    function onManagePerson(e) {
        var row = e.currentTarget.parentElement.parentElement;
        var id = row.getAttribute('person_id');
        window.location = 'manage-person.html?id=' + id;
    }

    function processSearchResults(results) {
        $('a.person_name').off('click', onSelectPerson);
        $('button.search-button').off('click', onManagePerson);

        $('#search-table tbody tr').remove();
        for (var i = 0; i < results.length; i++) {
            appendPerson(results[i]);
        }

        $('a.person_name').on('click', onSelectPerson);
        $('button.search-button').on('click', onManagePerson);
    }

    function appendPerson(p) {
        var name = getDisplayName(p);
        $('#search-table > tbody:last').append(
            '<tr person_id="' + p.id + '">' +
            '<td data-th="Name" person_name="' + name + '"><a class="person_name" href="javascript:void(0);">' + name + '</a></td>' +
            '<td data-th="Address">' + getAddress(p) + '</td>' +
            '<td data-th="" class="search-table-button-col"><button class="search-button button--blue-x-small">Manage</button></td>' +
            '</tr>');
    }

    function getAddress(p) {
        var addr = '';
        addr += p.street1 || '';
        if (p.street1)
            addr += '<br />';
        addr += p.street2 || '';
        if (p.street2)
            addr += '<br />';
        addr += p.city || '';
        if (p.city && p.state)
            addr += ',';

        addr += ' ';
        addr += p.state || '';
        addr += ' ';
        addr += p.zip || '';

        return addr.trim();
    }

    function processFollowUps(followUps) {
        detachClickListeners();
        $('#follow-up-table tbody tr').remove();
        for (var i = 0; i < followUps.length; i++) {
            appendFollowUp(followUps[i]);
        }
        attachClickListeners();
    }

    function appendFollowUp(followUp) {
        if (!followUp.type && followUp.typeCd)
            followUp.type = followUpTypeData[followUp.typeCd] || '';
        followUp.date = followUp.date || '';
        followUp.name = followUp.name || '';

        var name = followUp.name,
            display;
        if (followUp.personId >= 0) {
            display = '<a class="person_name" href="manage-person.html?id=' + followUp.personId + '">' + name + '</a>';
        }
        $('#follow-up-table > tbody:last').append(
            '<tr follow_up_id="' + followUp.id + '">' +
            '<td data-th="Name" personid="' + followUp.personId + '" person_name="' + name + '">' + display + '</td>' +
            '<td data-th="Type" typeCd="' + followUp.typeCd + '">' + followUp.type + '</td>' +
            '<td data-th="Date" class="follow-up-table-date-col">' + followUp.date + '</td>' +
            '<td data-th="By" visitorsIds="' + followUp.visitorsIds.join(',') + '">' + followUp.visitors.join(', ') + '</td>' +
            '<td data-th="Comments" class="follow-up-table-comments-col">' + followUp.comments + '</td>' +
            '<td data-th="" class="follow-up-table-button-col"><button class="edit-follow-up"><i class="fa fa-edit"></i></button><button class="delete-follow-up"><i class="fa fa-minus-circle"></i></button></td>' +
            '</tr>');
    }

    function updateFollowUpRow(followUp) {
        var children = $('#follow-up-table tr[follow_up_id=' + followUp.id + ']').children();
        children[0].setAttribute('personid', followUp.personId);
        children[0].setAttribute('person_name', followUp.name);
        children[0].innerHTML = '<a class="person_name" href="manage-person.html?id=' + followUp.personId + '">' + followUp.name + '</a>';
        children[1].setAttribute('typeCd', followUp.typeCd);
        children[1].innerHTML = followUp.type;
        children[2].innerHTML = followUp.date;
        children[3].innerHTML = followUp.visitors.join(', ');
        children[4].setAttribute('visitorsIds', followUp.visitorsIds.join(','));
        children[4].innerHTML = followUp.comments;
    }

    function setVisitors() {
        if (followUpVisitors.innerHTML.trim() === '') {
            var v;
            for (var i = 0; i < visitors.length; i++) {
                v = visitors[i];
                followUpVisitors.innerHTML +=
                    '<div class="check-field">' +
                    '<input type="checkbox" personid="' + v.id + '" id="follow-up-by-' + v.id + '"/>' +
                    '<label for="follow-up-by-' + v.id + '">' + getDisplayName(v) + '</label>' +
                    '</div>';
            }
        }
    }

    function doAddFollowUp(followUp, clear) {
        appendFollowUp(followUp);
        $('button.edit-follow-up:last').on('click', onEditFollowUpClick);
        $('button.delete-follow-up:last').on('click', onDeleteFollowUpClick);

        if (clear === true)
            clearFollowUpForm();
    }

    function doEditFollowUp(followUp, clear) {
        updateFollowUpRow(followUp);

        if (clear === true)
            clearFollowUpForm();
    }

    function addCopy() {
        var followUp = saveFollowUp();
        if (followUp === false) return false;
        saveFollowUpEntry(followUp, isAdd() ? doAddFollowUp : doEditFollowUp, false);
    }

    function addClear() {
        var followUp = saveFollowUp();
        if (followUp === false) return false;
        saveFollowUpEntry(followUp, isAdd() ? doAddFollowUp : doEditFollowUp, true);
    }

    function onEditFollowUpClick(e) {
        var row = e.currentTarget.parentElement.parentElement,
            date = row.children[2].innerHTML || '';

        followUpPerson.setAttribute('personid', row.children[0].getAttribute('personid') || '');
        followUpPerson.setAttribute('person_name', row.children[0].getAttribute('person_name') || '');
        followUpPerson.innerHTML = row.children[0].innerHTML || '';
        followUpType.value = row.children[1].getAttribute('typeCd') || '';
        followUpDate.value = date;
        followUpComments.value = row.children[4].innerHTML || '';
        followUpId.value = row.getAttribute('follow_up_id');
        unknownDate.checked = date === '';
        followUpDate.disabled = unknownDate.checked;

        var visitorIdsString = row.children[3].getAttribute('visitorsIds') || '';
        var visitorIds = visitorIdsString.split(',');
        var inputs = followUpVisitors.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++) {
            inputs[i].checked = visitorIds.indexOf(inputs[i].getAttribute('personid')) >= 0;
        }

        $formTitle.text('Edit Follow Up');
        var screenOff = $('.follow-ups-form').offset().top;
        $('body').animate({
            scrollTop: screenOff
        }, 200);
        $('.follow-ups-form').effect('highlight', {}, 1200);
    }

    function isAdd() {
        return $formTitle.text().indexOf('Edit') === -1;
    }

    function openSelectPerson() {
        dialog.dialog('open');
    }

    function close() {
        dialog.dialog('close');
    }

    function onClickLink(e) {
        if (!(noChangesMade || confirm("If you continue you will lose any unsaved changes. Continue?"))) {
            e.preventDefault();
        }
    }

    function onDeleteFollowUpClick(e) {
        if (confirm("Are you sure you would like to PERMANENTLY delete this Follow Up?")) {
            var id = e.currentTarget.parentElement.parentElement.getAttribute('follow_up_id');
            deleteFollowUp(id);
        }
    }

    function onChangeUnknownDate(e) {
        followUpDate.disabled = e.target.checked;
        followUpDate.value = '';
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

    function onClickTopBottom(e) {
        var container = $('#search-table-container'),
            pos = (e.target.id.indexOf('top') == -1) ?
                container[0].scrollHeight : 0;

        container.stop().animate({
            scrollTop: pos
        }, scrollAnimationMs, 'swing', function() {
            $('<style></style>').appendTo($(document.body)).remove();
        });
    }
    $('.navigation-links a').on('click', onClickTopBottom);

    addCopyBtn.addEventListener('click', addCopy);
    addClearBtn.addEventListener('click', addClear);
    clearBtn.addEventListener('click', clearFollowUpForm);
    selectPersonBtn.addEventListener('click', openSelectPerson);
    addNewPersonBtn.addEventListener('click', addNewPerson);
    searchBtn.addEventListener('click', search);
    searchField.addEventListener('keydown', function(e) {
        if (e.keyCode == 13) {
            search();
        }
    });
    closeBtn.addEventListener('click', close);
    $('#unknown-date').on('change', onChangeUnknownDate);

    clearFollowUpForm();
    checkLoginStatus(loadVisitors);
})();