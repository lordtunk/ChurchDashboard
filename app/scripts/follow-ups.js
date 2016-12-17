(function() {
    'use strict';

    $('#follow-up-date').datepicker();
    $('#follow-ups-for-date').datepicker({
        dateFormat: 'm/d/yy'
    });
    $('#follow-ups-for-date').datepicker("setDate", new Date());
    var noChangesMade = true,
        $formTitle = $('#follow-ups-form-title'),
        followUpId = document.querySelector('#follow-up-id'),
        followUpPerson = document.querySelector('#follow-up-person'),
        followUpType = document.querySelector('#follow-up-type'),
        followUpDate = document.querySelector('#follow-up-date'),
        followUpsForDate = document.querySelector('#follow-ups-for-date'),
        followUpAttendanceFrequency = document.querySelector('#follow-up-frequency'),
        communicationCardOptions = document.querySelector('.communication-card-options'),
        followUpCommitmentChrist = document.querySelector('#follow-up-commitment-christ'),
        followUpRecommitmentChrist = document.querySelector('#follow-up-recommitment-christ'),
        followUpCommitmentTithe = document.querySelector('#follow-up-commitment-tithe'),
        followUpCommitmentMinistry = document.querySelector('#follow-up-commitment-ministry'),
        followUpCommitmentBaptism = document.querySelector('#follow-up-commitment-baptism'),
        followUpInfoNext = document.querySelector('#follow-up-info-next'),
        followUpInfoGKids = document.querySelector('#follow-up-info-gkids'),
        followUpInfoGGroups = document.querySelector('#follow-up-info-ggroups'),
        followUpInfoGTeams = document.querySelector('#follow-up-info-gteams'),
        followUpInfoMember = document.querySelector('#follow-up-info-member'),
        followUpInfoVisit = document.querySelector('#follow-up-info-visit'),
        followUpInfoGrowth = document.querySelector('#follow-up-info-growth'),
        getFollowUpsBtn = document.querySelector('#get-follow-ups'),
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
        dialog = $('#search-form').dialog({
            autoOpen: false,
            height: 400,
            width: 350,
            modal: true,
            open: function() {
                $("body").css({ overflow: 'hidden' });
            },
            beforeClose: function() {
                $("body").css({ overflow: 'inherit' });
            }
        }),
        selectPersonBtn = document.querySelector('#select-person-btn'),
        followUpIdSequence = -1,
        followUpTypeData = {
            1: "Phone Call",
            2: "Visit",
            3: "Communication Card",
            4: "Entered in The City",
            5: "Thank You Card Sent"
        },
        followUpAttendanceFrequencyData = {
            1: "1st Time",
            2: "2nd Time",
            3: "Often",
            4: "Member",
            "": "--None Provided--"
        };
    
    function populateAttendanceFrequency() {
        var $select = $('#follow-up-frequency');
        $.each(followUpAttendanceFrequencyData, function(frequencyCd, frequency) {
            $select.append('<option value=' + frequencyCd + '>' + frequency + '</option>');
        });
        $select.val('');
    }
    
    function populateTypes() {
        var $select = $('#follow-up-type');
        $.each(followUpTypeData, function(typeCd, type) {
            $select.append('<option value=' + typeCd + '>' + type + '</option>');
        });
        $select.val('3');
        onFollowUpTypeChange();
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

    function loadFollowUps() {
        $.ajax({
            type: 'POST',
            url: 'ajax/get_follow_ups.php',
            data: {
                date: followUpsForDate.value
            }
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
					
					if(data.spouse_follow_up_id) {
						f.id = data.spouse_follow_up_id;
						f.personId = data.spouse_id;
						f.name = data.spouse_name;
						cb.call(this, f, clear);
					}
                    $().toastmessage('showSuccessToast', "Save successful");
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        if(data.warning)
                            $().toastmessage('showErrorToast', data.warning);
                        else
                            $().toastmessage('showErrorToast', "Error saving follow up");
                    }
                }
            })
            .fail(function() {
                $().toastmessage('showErrorToast', "Error saving follow up");
            });
    }

    function deleteFollowUp(id, personId) {
        $.ajax({
            type: 'POST',
            url: 'ajax/delete_follow_up.php',
            data: {
                id: id,
                personId: personId
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
                        if(data.warning)
                            $().toastmessage('showErrorToast', data.warning);
                        else
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
        followUpType.value = '3';
        followUpDate.value = '';
		
		$('#add-to-spouse')[0].checked = false;
		$('#add-to-spouse').css('display', 'none');
		$('#add-to-spouse-label').css('display', 'none');

        followUpAttendanceFrequency.value = '';
        
        followUpCommitmentChrist.checked = false;
        followUpRecommitmentChrist.checked = false;
        followUpCommitmentTithe.checked = false;
        followUpCommitmentMinistry.checked = false;
        followUpCommitmentBaptism.checked = false;

        followUpInfoNext.checked = false;
        followUpInfoGKids.checked = false;
        followUpInfoGGroups.checked = false;
        followUpInfoGTeams.checked = false;
        followUpInfoMember.checked = false;
        followUpInfoVisit.checked = false;
		followUpInfoGrowth.checked = false;
        
        followUpComments.value = '';
        unknownDate.checked = false;
        followUpDate.disabled = false;

        var inputs = followUpVisitors.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++)
            inputs[i].checked = false;
        
        onFollowUpTypeChange();
    }

    function saveFollowUp() {
        var name = $.trim(followUpPerson.getAttribute('person_name')),
            personId = $.trim(followUpPerson.getAttribute('personid')),
            date = $.trim(followUpDate.value),
            type = $.trim(followUpType.value),
            comments = $.trim(followUpComments.value),
            communication_card_options = {
                frequency: followUpAttendanceFrequency.value,
                commitment_christ: false,
                recommitment_christ: false,
                commitment_tithe: false,
                commitment_ministry: false,
                commitment_baptism: false,
                info_next: false,
                info_gkids: false,
                info_ggroups: false,
                info_gteams: false,
                info_member: false,
                info_visit: false,
                info_growth: false
            },
            visitors = [],
            visitorsIds = [],
            msg = '',
            spouseId = '';

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
        
        
        if(type == 3) {
            communication_card_options = {
                frequency: followUpAttendanceFrequency.value,
                commitment_christ: followUpCommitmentChrist.checked,
                recommitment_christ: followUpRecommitmentChrist.checked,
                commitment_tithe: followUpCommitmentTithe.checked,
                commitment_ministry: followUpCommitmentMinistry.checked,
                commitment_baptism: followUpCommitmentBaptism.checked,
                info_next: followUpInfoNext.checked,
                info_gkids: followUpInfoGKids.checked,
                info_ggroups: followUpInfoGGroups.checked,
                info_gteams: followUpInfoGTeams.checked,
                info_member: followUpInfoMember.checked,
                info_visit: followUpInfoVisit.checked,
                info_growth: followUpInfoGrowth.checked
            };
        }

        return {
            id: isAdd() ? genFollowUpId() : followUpId.value,
			add_to_spouse: $('#add-to-spouse')[0].checked,
            date: date,
            name: name,
            personId: personId,
            spouseId: spouseId,
            typeCd: type,
            type: followUpType.selectedOptions[0].text,
            comments: comments,
            communication_card_options: communication_card_options,
            visitors: visitors,
            visitorsIds: visitorsIds
        };
    }

    function onSelectPerson(e) {
        var row = e.currentTarget.parentElement.parentElement;

        var id = row.getAttribute('person_id');
		var hasSpouse = row.getAttribute('has_spouse');
        var name = row.children[0].getAttribute('person_name');
        doSelectPerson(id, name, hasSpouse);
    }

    function doSelectPerson(id, name, hasSpouse) {
        close();
        followUpPerson.innerHTML = '<a class="person_name" href="manage-person.php?id=' + id + '">' + name + '</a>';
        followUpPerson.setAttribute('personid', id);
        followUpPerson.setAttribute('person_name', name);
		
		$('#add-to-spouse').css('display', (hasSpouse) ? '' : 'none');
		$('#add-to-spouse-label').css('display', (hasSpouse) ? '' : 'none');
		$('#add-to-spouse').prop( "checked", hasSpouse );
    }

    function onManagePerson(e) {
        var row = e.currentTarget.parentElement.parentElement;
        var id = row.getAttribute('person_id');
        window.location = 'manage-person.php?id=' + id;
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
            '<tr person_id="' + p.id + '" has_spouse="'+(!!p.has_spouse)+'">' +
            '<td data-th="Name" person_name="' + name + '"><a class="person_name" href="javascript:void(0);">' + name + '</a></td>' +
            '<td data-th="Address">' + getAddress(p) + '</td>' +
            '<td data-th="" class="search-table-button-col"><button class="search-button btn btn-xs btn-info">Manage</button></td>' +
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
            display = '<a class="person_name" href="manage-person.php?id=' + followUp.personId + '">' + name + '</a>';
        }
        var options = [];
        for(var o in followUp.communication_card_options) {
            if(followUp.communication_card_options.hasOwnProperty(o) && followUp.communication_card_options[o] === true)
                options.push(o);
        }
        $('#follow-up-table > tbody:last').append(
            '<tr class="' + (followUp.communication_card_options.frequency=='1' ? 'first-time-visitor' : '') + '" follow_up_id="' + followUp.id + '" communication_card_options="' + options.join(',') + '" frequency="' + followUp.communication_card_options.frequency + '">' +
            '<td data-th="Name" personid="' + followUp.personId + '" person_name="' + name + '">' + display + '</td>' +
            '<td data-th="Type" typeCd="' + followUp.typeCd + '">' + followUp.type + '</td>' +
            '<td data-th="Date" class="follow-up-table-date-col">' + followUp.date + '</td>' +
            '<td data-th="By" visitorsIds="' + followUp.visitorsIds.join(',') + '">' + followUp.visitors.join(', ') + '</td>' +
            '<td data-th="Comments" class="follow-up-table-comments-col">' + followUp.comments + '</td>' +
            '<td data-th="" class="follow-up-table-button-col"><button class="edit-follow-up btn btn-xs btn-default"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button><button class="delete-follow-up btn btn-xs btn-default"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></button></td>' +
            '</tr>');
    }

    function updateFollowUpRow(followUp) {
        var row = $('#follow-up-table tr[follow_up_id=' + followUp.id + ']'),
            children = row.children(),
            options = [];
        row[0].setAttribute('class', followUp.communication_card_options.frequency=='1' ? 'first-time-visitor' : '');
        for(var o in followUp.communication_card_options) {
            if(followUp.communication_card_options.hasOwnProperty(o) && followUp.communication_card_options[o] === true)
                options.push(o);
        }
        row[0].setAttribute('frequency', followUp.communication_card_options.frequency);
        row[0].setAttribute('communication_card_options', options.join(','));
        children[0].setAttribute('personid', followUp.personId);
        children[0].setAttribute('person_name', followUp.name);
        children[0].innerHTML = '<a class="person_name" href="manage-person.php?id=' + followUp.personId + '">' + followUp.name + '</a>';
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
            for (var i = 0; i < visitors.length; i++) {		// jshint ignore:line
                v = visitors[i];							// jshint ignore:line
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
            date = row.children[2].innerHTML || '',
            optionsArr = (row.getAttribute('communication_card_options') || '').split(','),
            options = {
                commitment_christ: false,
                recommitment_christ: false,
                commitment_tithe: false,
                commitment_ministry: false,
                commitment_baptism: false,
                info_next: false,
                info_gkids: false,
                info_ggroups: false,
                info_gteams: false,
                info_member: false,
                info_visit: false
            }, i;
        for(i=0; i<optionsArr.length; i++) {
            options[optionsArr[i]] = true;
        }
		
		$('#add-to-spouse').css('display', 'none');
		$('#add-to-spouse-label').css('display', 'none');
		
        followUpPerson.setAttribute('personid', row.children[0].getAttribute('personid') || '');
        followUpPerson.setAttribute('person_name', row.children[0].getAttribute('person_name') || '');
        followUpPerson.innerHTML = row.children[0].innerHTML || '';
        followUpType.value = row.children[1].getAttribute('typeCd') || '';
        followUpDate.value = date;
        
        followUpAttendanceFrequency.value = row.getAttribute('frequency') || '';
        followUpCommitmentChrist.checked = options.commitment_christ;
        followUpRecommitmentChrist.checked = options.recommitment_christ;
        followUpCommitmentTithe.checked = options.commitment_tithe;
        followUpCommitmentMinistry.checked = options.commitment_ministry;
        followUpCommitmentBaptism.checked = options.commitment_baptism;

        followUpInfoNext.checked = options.info_next;
        followUpInfoGKids.checked = options.info_gkids;
        followUpInfoGGroups.checked = options.info_ggroups;
        followUpInfoGTeams.checked = options.info_gteams;
        followUpInfoMember.checked = options.info_member;
        followUpInfoVisit.checked = options.info_visit;
        followUpInfoGrowth.checked = options.info_growth;
        
        followUpComments.value = row.children[4].innerHTML || '';
        followUpId.value = row.getAttribute('follow_up_id');
        unknownDate.checked = date === '';
        followUpDate.disabled = unknownDate.checked;

        var visitorIdsString = row.children[3].getAttribute('visitorsIds') || '';
        var visitorIds = visitorIdsString.split(',');
        var inputs = followUpVisitors.querySelectorAll('input');
        for (i = 0; i < inputs.length; i++) {
            inputs[i].checked = visitorIds.indexOf(inputs[i].getAttribute('personid')) >= 0;
        }

        onFollowUpTypeChange();
        
        $formTitle.text('Edit Follow Up');
        var screenOff = $('.follow-ups-form').offset().top;
        $('body').animate({
            scrollTop: screenOff
        }, 200);
        $('.follow-ups-form').effect('highlight', {}, 1200);
    }

    function openSelectPerson() {
        dialog.dialog('open');
    }

    function close() {
        dialog.dialog('close');
    }

    function isAdd() {
        return $formTitle.text().indexOf('Edit') === -1;
    }

    function onClickLink(e) {
        if (!(noChangesMade || confirm("If you continue you will lose any unsaved changes. Continue?"))) {
            e.preventDefault();
        }
    }
    
    function onClearClick() {
        if (confirm("If you continue you will lose any unsaved changes. Continue?")) {
            clearFollowUpForm();
        }
    }

    function onDeleteFollowUpClick(e) {
        if (confirm("Are you sure you would like to PERMANENTLY delete this Follow Up?")) {
            var row = e.currentTarget.parentElement.parentElement,
                id = row.getAttribute('follow_up_id'),
                personId = row.children[0].getAttribute('personid');
            
            deleteFollowUp(id, personId);
        }
    }

    function onChangeUnknownDate(e) {
        followUpDate.disabled = e.target.checked;
        followUpDate.value = '';
    }
    
    function onFollowUpTypeChange() {
        $('#follow-up-frequency-container').css('display', (followUpType.value == 3) ? 'inherit' : 'none');
        communicationCardOptions.style.display = (followUpType.value == 3) ? 'inherit' : 'none';
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

    addCopyBtn.addEventListener('click', addCopy);
    addClearBtn.addEventListener('click', addClear);
    clearBtn.addEventListener('click', onClearClick);
    selectPersonBtn.addEventListener('click', openSelectPerson);
    addNewPersonBtn.addEventListener('click', addNewPerson);
    searchBtn.addEventListener('click', search);
    searchField.addEventListener('keydown', function(e) {
        if (e.keyCode == 13) {
            search();
        }
    });
    closeBtn.addEventListener('click', close);
    getFollowUpsBtn.addEventListener('click', loadFollowUps);
    $('#unknown-date').on('change', onChangeUnknownDate);
    $('#follow-up-type').on('change', onFollowUpTypeChange);
    
    clearFollowUpForm();
    
	setVisitors();
	populateTypes();
	populateAttendanceFrequency();
	
	processFollowUps(followUps);	// jshint ignore:line
})();
