(function() {
    'use strict';

    $('#follow-up-date').datepicker();
    var urlParams = {},
        firstName = document.querySelector('#first-name'),
        lastName = document.querySelector('#last-name'),
        description = document.querySelector('#description'),
        firstRecordedVisit = document.querySelector('#first-recorded-visit'),
        attenderStatus = document.querySelector('#attender-status'),
        adult = document.querySelector('#adult'),
        active = document.querySelector('#active'),
        baptized = document.querySelector('#baptized'),
        saved = document.querySelector('#saved'),
        //member = document.querySelector('#member'),
        visitor = document.querySelector('#visitor'),
        assignedAgent = document.querySelector('#assigned-agent'),
        startingPoint = document.querySelector('#starting-point-notified'),
        street1 = document.querySelector('#street1'),
        street2 = document.querySelector('#street2'),
        city = document.querySelector('#city'),
        zip = document.querySelector('#zip'),
        email = document.querySelector('#email'),
        primaryPhone = document.querySelector('#primary-phone'),
        secondaryPhone = document.querySelector('#secondary-phone'),
        primaryPhoneType = document.querySelector('#primary-phone-type'),
        secondaryPhoneType = document.querySelector('#secondary-phone-type'),
        stateSelect = document.querySelector('#state'),
        commitmentChrist = document.querySelector('#commitment-christ'),
        recommitmentChrist = document.querySelector('#recommitment-christ'),
        commitmentTithe = document.querySelector('#commitment-tithe'),
        commitmentMinistry = document.querySelector('#commitment-ministry'),
        commitmentBaptism = document.querySelector('#commitment-baptism'),
        infoNext = document.querySelector('#info-next'),
        infoGKids = document.querySelector('#info-gkids'),
        infoGGroups = document.querySelector('#info-ggroups'),
        infoGTeams = document.querySelector('#info-gteams'),
        infoMember = document.querySelector('#info-member'),
        infoVisit = document.querySelector('#info-visit'),
        updateBtn = document.querySelector('#update'),
        cancelBtn = document.querySelector('#cancel'),
        deleteBtn = document.querySelector('#delete'),
        addFollowUpBtn = document.querySelector('#add-follow-up'),
        copyAddressToSpouseBtn = document.querySelector('#copy-address-to-spouse'),
        
        relationshipDialog = $('.manage-person-relationship-form').dialog({
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
        relationshipId = relationshipDialog[0].querySelector('#relationship-id'),
        relationshipType = relationshipDialog[0].querySelector('#relationship-type'),
        relationshipPersonName = relationshipDialog[0].querySelector('#relationship-person-name'),
        addRelationshipBtn = document.querySelector('#add-relationship'),
        addCloseRelationshipBtn = document.querySelector('#add-close-relationship'),
        closeRelationshipBtn = document.querySelector('#close-relationship'),
        relationshipRelation = document.querySelector('#relationship-relation'),
        addToSpouseContainerRelationship = relationshipDialog[0].querySelector('#add-to-spouse-container-relationship'),
        addToSpouseRelationship = relationshipDialog[0].querySelector('#add-to-spouse-relationship'),
        
        selectPersonDialog = $('.select-person-form').dialog({
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
        addNewPersonBtn = document.querySelector('#add-new-person'),
        searchBtn = document.querySelector('#search'),
        searchField = document.querySelector('#search-name'),
        closeSelectPersonBtn = selectPersonDialog[0].querySelector('#close-select-person'),
        selectPersonBtn = document.querySelector('#select-person-btn'),
        relationshipIdSequence = -1,
        
        addClearBtn = document.querySelector('#add-clear'),
        addCopyBtn = document.querySelector('#add-copy'),
        addCloseBtn = document.querySelector('#add-close'),
        closeBtn = document.querySelector('#close'),
        dialog = $('.manage-person-follow-up-form').dialog({
            autoOpen: false,
            height: 410,
            width: 350,
            modal: true,
            open: function() {
                $("body").css({ overflow: 'hidden' });
            },
            beforeClose: function() {
                $("body").css({ overflow: 'inherit' });
            }
        }),
        addToSpouseContainerFollowUp = dialog[0].querySelector('#add-to-spouse-container-follow-up'),
        addToSpouseFollowUp = dialog[0].querySelector('#add-to-spouse-follow-up'),
        followUpId = dialog[0].querySelector('#follow-up-id'),
        followUpType = dialog[0].querySelector('#follow-up-type'),
        followUpDate = dialog[0].querySelector('#follow-up-date'),
        unknownDate = document.querySelector('#manage-unknown-date'),
        followUpVisitors = dialog[0].querySelector('#follow-up-visitors'),
        followUpAttendanceFrequency = dialog[0].querySelector('#follow-up-frequency'),
        communicationCardOptions = dialog[0].querySelector('.communication-card-options'),
        followUpCommitmentChrist = dialog[0].querySelector('#follow-up-commitment-christ'),
        followUpRecommitmentChrist = dialog[0].querySelector('#follow-up-recommitment-christ'),
        followUpCommitmentTithe = dialog[0].querySelector('#follow-up-commitment-tithe'),
        followUpCommitmentMinistry = dialog[0].querySelector('#follow-up-commitment-ministry'),
        followUpCommitmentBaptism = dialog[0].querySelector('#follow-up-commitment-baptism'),
        followUpInfoNext = dialog[0].querySelector('#follow-up-info-next'),
        followUpInfoGKids = dialog[0].querySelector('#follow-up-info-gkids'),
        followUpInfoGGroups = dialog[0].querySelector('#follow-up-info-ggroups'),
        followUpInfoGTeams = dialog[0].querySelector('#follow-up-info-gteams'),
        followUpInfoMember = dialog[0].querySelector('#follow-up-info-member'),
        followUpInfoVisit = dialog[0].querySelector('#follow-up-info-visit'),
        followUpComments = dialog[0].querySelector('#follow-up-comments'),
        followUpIdSequence = -1,
        noChangesMade = true,
        phoneNumberRegex = /^((\(?([2-9][0-8][0-9])\))|([2-9][0-8][0-9]))?[-. ]?([2-9][0-9]{2})[-. ]?([0-9]{4})$/,
        mapPanel = document.querySelector('#map-panel'),
        gMapsImgUrl = '//maps.googleapis.com/maps/api/staticmap?zoom=11&size=400x400&markers=color:red%7Clabel:A|',
        gMapsUrl = 'https://www.google.com/maps/place/',
        followUpTypeData = {
            1: "Phone Call",
            2: "Visit",
            3: "Communication Card",
            4: "Entered in The City",
            5: "Thank You Card Sent"
        },
        relationshipTypeData = {
            1: 'Spouse',
            2: 'Child',
            3: 'Parent'
        },
        attenderStatusData = {
            1: 'Member',
            2: 'Regular',
            3: 'Irregular'
        },
        followUpAttendanceFrequencyData = {
            1: "1st Time",
            2: "2nd Time",
            3: "Often",
            4: "Member",
            "": "--None Provided--"
        };
		
	/* jshint ignore:start */
	if(apiKey)
		gMapsImgUrl += '&key='+apiKey;
	/* jshint ignore:end */

    (window.onpopstate = function() {
        var match,
            pl = /\+/g, // Regex for replacing addition symbol with a space
            search = /([^&=]+)=?([^&]*)/g,
            decode = function(s) {
                return decodeURIComponent(s.replace(pl, " "));
            },
            query = window.location.search.substring(1);

        urlParams = {};
        while ((match = search.exec(query)) !== null)
            urlParams[decode(match[1])] = decode(match[2]);
    }).call();

    // Redirect the user to the attendance page if no
    // id was specified in the url parameters
    if (!urlParams.id) window.location = 'attendance.php';

    function populateStates(states) {
        var $select = $('#state');
        $.each(states, function(ind, state) {
            $select.append('<option value=' + state.abbreviation + '>' + state.name + '</option>');
        });
        $select.val('OH');
    }
    
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
        $select.val('2');
        
        $select = $('#relationship-type');
        $.each(relationshipTypeData, function(typeCd, type) {
            $select.append('<option value=' + typeCd + '>' + type + '</option>');
        });
        $select.val('1');
        
        $select = $('#attender-status');
        $.each(attenderStatusData, function(typeCd, type) {
            $select.append('<option value=' + typeCd + '>' + type + '</option>');
        });
        $select.val('3');
    }

    function populateForm(p) {
        firstName.value = p.first_name;
        lastName.value = p.last_name;
        description.value = p.description;
        firstRecordedVisit.innerHTML = p.first_attendance_dt || '<span style="font-style: italic;">(None)</span>';
		attenderStatus.value = p.attender_status;
        adult.checked = p.adult;
        active.checked = p.active;
        baptized.checked = p.baptized;
        saved.checked = p.saved;
        //member.checked = p.member;
        visitor.checked = p.visitor;
        assignedAgent.checked = p.assigned_agent;
        startingPoint.checked = p.starting_point_notified;
        
        street1.value = p.street1;
        street2.value = p.street2;
        city.value = p.city;
        zip.value = p.zip;
        if (p.state)
            stateSelect.value = p.state;

        email.value = p.email;
        primaryPhone.value = formatPhoneNumber(p.primary_phone);
        primaryPhoneType.value = p.primary_phone_type;
        secondaryPhone.value = formatPhoneNumber(p.secondary_phone);
        secondaryPhoneType.value = p.secondary_phone_type;

        populateCommunicationCardOptions(p);
		populateCampuses(p.campuses);

        updateMap(p);
        processFollowUps(p.follow_ups);
        processRelationships(p.relationships);

        setCopyAddressBtnVisibility();
    }
    
    function populateCommunicationCardOptions(p) {
        commitmentChrist.checked = p.commitment_christ;
        recommitmentChrist.checked = p.recommitment_christ;
        commitmentTithe.checked = p.commitment_tithe;
        commitmentMinistry.checked = p.commitment_ministry;
        commitmentBaptism.checked = p.commitment_baptism;

        infoNext.checked = p.info_next;
        infoGKids.checked = p.info_gkids;
        infoGGroups.checked = p.info_ggroups;
        infoGTeams.checked = p.info_gteams;
        infoMember.checked = p.info_member;
        infoVisit.checked = p.info_visit;
    }
	
	function populateCampuses(campuses) {
		$('.campuses input').prop('checked', false);
		for(var i=0; i<campuses.length; i++) {
			$('.campuses input[campusid='+campuses[i]+']').prop('checked', true);
		}
	}

    function updateMap(p) {
        var addr = getAddressString(p);
        // Don't generate a map if the string is just 'OH' since that is the default
        if (addr === 'OH' || addr === '')
            mapPanel.innerHTML = '';
        else
            mapPanel.innerHTML = '<a href="' + gMapsUrl + addr + '" target="_blank"><img border="0" src="' + gMapsImgUrl + addr + '" /></a>';
    }

    function onUpdateClick() {
		var campusEls = $('.campuses input:checked');
		var campuses = [];
		for(var i=0; i<campusEls.length; i++) {
			campuses.push(campusEls[i].getAttribute('campusid'));
		}
		
        var p = {
            id: person.id,	// jshint ignore:line
            first_name: $.trim(firstName.value),
            last_name: $.trim(lastName.value),
            description: $.trim(description.value),
            attender_status: attenderStatus.value,
            adult: adult.checked,
            active: active.checked,
            baptized: baptized.checked,
            saved: saved.checked,
            //member: member.checked,
            visitor: visitor.checked,
            assigned_agent: assignedAgent.checked,
            street1: $.trim(street1.value),
            street2: $.trim(street2.value),
            city: $.trim(city.value),
            zip: $.trim(zip.value),
            state: $.trim(stateSelect.value),
            email: $.trim(email.value),
            primary_phone: $.trim(primaryPhone.value),
            primary_phone_type: primaryPhoneType.value,
            secondary_phone: $.trim(secondaryPhone.value),
            secondary_phone_type: secondaryPhoneType.value,
            commitment_christ: commitmentChrist.checked,
            recommitment_christ: recommitmentChrist.checked,
            commitment_tithe: commitmentTithe.checked,
            commitment_ministry: commitmentMinistry.checked,
            commitment_baptism: commitmentBaptism.checked,
            info_next: infoNext.checked,
            info_gkids: infoGKids.checked,
            info_ggroups: infoGGroups.checked,
            info_gteams: infoGTeams.checked,
            info_member: infoMember.checked,
            info_visit: infoVisit.checked,
			campuses: campuses
        };
        
        if($.trim(getAddressString(p)) === 'OH')
            p.state = '';

        if (validateUpdate(p)) {
            savePerson(p);
        }
    }

    function getFollowUps() {
        var followUps = [];
        $('#follow-up-table tbody tr').each(function(ind, row) {
            var typeCd = row.children[0].getAttribute('typeCd') || '';
            followUps.push({
                id: row.getAttribute('follow_up_id'),
                typeCd: typeCd,
                type: followUpTypeData[typeCd],
                date: row.children[1].innerHTML || '',
                comments: row.children[3].innerHTML || '',
                visitorsIds: row.children[2].getAttribute('visitorsIds').split(',') || '',
                visitors: row.children[2].innerHTML.split(', ') || ''
            });
        });
        return followUps;
    }
    
    function getRelationships() {
        var relationships = [];
        $('#relationship-table tbody tr').each(function(ind, row) {
            var typeCd = row.children[0].getAttribute('typeCd') || '';
            relationships.push({
                id: row.getAttribute('relationship_id'),
                typeCd: typeCd,
                type: relationshipTypeData[typeCd],
                person_id: person.id,	// jshint ignore:line
                relation_id: row.children[1].getAttribute('relationid'),
                name: row.children[1].getAttribute('relationname')
            });
        });
        return relationships;
    }

    function validateUpdate(p) {
        var msg = '',
            firstNameSpecified = !! p.first_name,
            lastNameSpecified = !! p.last_name,
            descriptionSpecified = !! p.description;
        if (!firstNameSpecified && lastNameSpecified)
            msg += 'First Name cannot be blank if Last Name is specified<br />';
        else if (firstNameSpecified && !lastNameSpecified)
            msg += 'Last Name cannot be blank if First Name is specified<br />';
        else if (!firstNameSpecified && !lastNameSpecified && !descriptionSpecified)
            msg += 'Must specify either First and Last Name or Description<br />';

        if (p.first_name.length > 50)
            msg += 'First Name cannot exceed 50 characters<br />';
        if (p.last_name.length > 50)
            msg += 'Last Name cannot exceed 50 characters<br />';
        if (p.description.length > 250)
            msg += 'Description cannot exceed 250 characters<br />';
        if (p.email.length > 100)
            msg += 'Email cannot exceed 100 characters<br />';
        if (!email.checkValidity())
            msg += 'Email is not valid<br />';
        if (p.primary_phone.length > 15)
            msg += 'Primary Phone cannot exceed 15 characters<br />';
        else if (p.primary_phone.length > 0 && !phoneNumberRegex.test(p.primary_phone))
            msg += 'Primary Phone is not a valid phone number format<br />';
        if (p.secondary_phone.length > 15)
            msg += 'Secondary Phone cannot exceed 15 characters<br />';
        else if (p.secondary_phone.length > 0 && !phoneNumberRegex.test(p.secondary_phone))
            msg += 'Secondary Phone is not a valid phone number format<br />';
        if (p.street1.length > 100)
            msg += 'Street 1 cannot exceed 100 characters<br />';
        if (p.street2.length > 100)
            msg += 'Street 2 cannot exceed 100 characters<br />';
        if (p.city.length > 100)
            msg += 'City cannot exceed 100 characters<br />';
        if (p.zip.length > 5)
            msg += 'Zip Code cannot exceed 5 characters<br />';
        if (p.street1.length > 100)
            msg += 'Street 1 cannot exceed 100 characters<br />';

        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }

    function onDeleteClick() {
        if (confirm("Deleting someone also deletes their attendance history. Continue?")) {
            deletePerson();
        }
    }

    function onCancelClick() {
        if (confirm("If you continue you will lose any unsaved changes. Continue?")) {
            populateForm(person);	// jshint ignore:line
            noChangesMade = true;
        }
    }

    updateBtn.addEventListener('click', onUpdateClick);
    cancelBtn.addEventListener('click', onCancelClick);
    deleteBtn.addEventListener('click', onDeleteClick);
    
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

    function loadStates() {
        $.ajax({
            type: 'GET',
            url: 'ajax/states.json'
        })
            .done(function(msg) {
                if ($.isArray(msg)) {
                    populateStates(msg);
                } else {
                    var data = JSON.parse(msg);
                    populateStates(data);
                }

                //loadPerson();
                //loadVisitors();
            })
            .fail(function() {
                $().toastmessage('showErrorToast', "Error loading states");
            });
    }

    function savePerson(p) {
        $.ajax({
            type: 'POST',
            url: 'ajax/save_person.php',
            data: {
                person: JSON.stringify(p)
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    p.follow_ups = getFollowUps();
                    p.relationships = getRelationships();
                    person = p;		// jshint ignore:line
                    noChangesMade = true;
                    updateMap(p);
                    $().toastmessage('showSuccessToast', "Save successful");
                } else {
                    if (data.error === 1) {
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
            data: {
                id: person.id	// jshint ignore:line
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    window.location = 'attendance.php';
                } else {
                    if (data.error === 1) {
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
    
    function saveFollowUp(f, cb) {
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
                    convertTrueFalse(data.communication_card_options[0]);
                    populateCommunicationCardOptions(data.communication_card_options[0]);
                    cb.call(this, f);
                    $().toastmessage('showSuccessToast', "Follow up save successful");
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

    function deleteFollowUp(id) {
        $.ajax({
            type: 'POST',
            url: 'ajax/delete_follow_up.php',
            data: {
                id: id,
                personId: person.id		// jshint ignore:line
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    $('#follow-up-table tr[follow_up_id=' + id + ']').remove();
                    convertTrueFalse(data.communication_card_options[0]);
                    populateCommunicationCardOptions(data.communication_card_options[0]);
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        if(data.warning)
                            $().toastmessage('showErrorToast', data.warning);
                        else
                            $().toastmessage('showErrorToast', "Error deleting follow up");
                    }
                }
            })
            .fail(function() {
                $().toastmessage('showErrorToast', "Error deleting follow up");
            });
    }
    
    function convertTrueFalse(p) {
        for(var prop in p) {
            if(p.hasOwnProperty(prop)) {
                p[prop] = p[prop] === "true";
            }
        }
    }
    
    function saveRelationship(r, cb) {
        $.ajax({
            type: 'POST',
            url: 'ajax/save_relationship.php',
            data: {
                relationship: JSON.stringify(r)
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    r.id = data.relationship_id;
                    cb.call(this, r);
                    setCopyAddressBtnVisibility();
                    $().toastmessage('showSuccessToast', "Relationship save successful");
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        var message = "Error saving relationship";
                        if(data.msg)
                            message = data.msg;
                        $().toastmessage('showErrorToast', message);
                    }
                }
            })
            .fail(function() {
                $().toastmessage('showErrorToast', "Error saving relationship");
            });
    }
    
    function deleteRelationship(id) {
        $.ajax({
            type: 'POST',
            url: 'ajax/delete_relationship.php',
            data: {
                id: id
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    $('#relationship-table tr[relationship_id=' + id + ']').remove();
                    setCopyAddressBtnVisibility();
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        $().toastmessage('showErrorToast', "Error deleting relationship");
                    }
                }
            })
            .fail(function() {
                $().toastmessage('showErrorToast', "Error deleting relationship");
            });
    }

    function copyAddress() {
        $.ajax({
            type: 'POST',
            url: 'ajax/copy_address_to_spouse.php',
            data: {
                personId: person.id		// jshint ignore:line
            }
        })
        .done(function(msg) {
            var data = JSON.parse(msg);
            if (data.success) {
                $().toastmessage('showSuccessToast', "Address copied successful");
            } else {
                if (data.error === 1) {
                    logout();
                } else {
                    $().toastmessage('showErrorToast', "Error copying address");
                }
            }
        })
        .fail(function() {
            $().toastmessage('showErrorToast', "Error copying address");
        });
    }

    function setCopyAddressBtnVisibility() {
        copyAddressToSpouseBtn.style.display = $('#relationship-table td[typecd=1]').length > 0 ? '' : 'none';
    }
    
    
    function onSelectPerson(e) {
        var row = e.currentTarget.parentElement.parentElement;

        var id = row.getAttribute('person_id');
        var name = row.children[0].getAttribute('person_name');
        doSelectPerson(id, name);
    }

    function doSelectPerson(id, name) {
        closeSelectPerson();
        relationshipRelation.innerHTML = '<a class="person_name" href="manage-person.php?id=' + id + '">' + name + '</a>';
        relationshipRelation.setAttribute('relationid', id);
        relationshipRelation.setAttribute('person_name', name);
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
            '<tr person_id="' + p.id + '">' +
            '<td data-th="Name" person_name="' + name + '"><a class="person_name" href="javascript:void(0);">' + name + '</a></td>' +
            '<td data-th="Address">' + getAddress(p) + '</td>' +
            '<td data-th="" class="search-table-button-col"><button class="search-button btn btn-xs btn-info">Manage</button></td>' +
            '</tr>');
    }
    
    function openSelectPerson() {
        selectPersonDialog.dialog('open');
    }

    function closeSelectPerson() {
        selectPersonDialog.dialog('close');
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
        followUpType.value = '2';
        followUpDate.value = '';
        followUpDate.disabled = false;
        unknownDate.checked = false;
        followUpComments.value = '';
        
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
        
        onFollowUpTypeChange();
        
        var spouse = $('#relationship-table td[typecd=1]');
        addToSpouseContainerFollowUp.style.display = (spouse.length > 0) ? 'inherit' : 'none';
        addToSpouseFollowUp.checked = spouse.length > 0;

        var inputs = followUpVisitors.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++)
            inputs[i].checked = false;
    }

    function buildFollowUp() {
        var date = $.trim(followUpDate.value),
            type = $.trim(followUpType.value),
            comments = $.trim(followUpComments.value),
            visitors = [],
            visitorsIds = [],
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
                info_visit: false
            },
            msg = '',
            spouseId = '';

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
        
        var spouse = $('#relationship-table td[typecd=1]');
        if(spouse.length > 0 && addToSpouseFollowUp.checked && dialog.dialog('option', 'title').indexOf('Edit') === -1) {
            spouseId = spouse.next()[0].getAttribute('relationid');
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
                info_visit: followUpInfoVisit.checked
            };
        }

        return {
            id: (dialog.dialog('option', 'title').indexOf('Edit') === -1) ? genFollowUpId() : followUpId.value,
            personId: person.id,		// jshint ignore:line
            spouseId: spouseId,
            date: date,
            typeCd: type,
            type: followUpType.selectedOptions[0].text,
            comments: comments,
            visitors: visitors,
            visitorsIds: visitorsIds,
            communication_card_options: communication_card_options
        };
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
        var options = [];
        for(var o in followUp.communication_card_options) {
            if(followUp.communication_card_options.hasOwnProperty(o) && followUp.communication_card_options[o] === true)
                options.push(o);
        }
        $('#follow-up-table > tbody:last').append(
            '<tr follow_up_id="' + followUp.id + '" communication_card_options="' + options.join(',') + '" frequency="' + followUp.communication_card_options.frequency + '">' +
            '<td data-th="Type" typeCd="' + followUp.typeCd + '">' + followUp.type + '</td>' +
            '<td data-th="Date" class="follow-up-table-date-col">' + followUp.date + '</td>' +
            '<td data-th="By" visitorsIds="' + followUp.visitorsIds.join(',') + '">' + followUp.visitors.join(', ') + '</td>' +
            '<td data-th="Comments" class="follow-up-table-comments-col">' + followUp.comments + '</td>' +
            '<td data-th="" class="follow-up-table-button-col"><button class="edit-follow-up"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button><button class="delete-follow-up"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></button></td>' +
            '</tr>');
    }

    function updateFollowUpRow(followUp) {
        var row = $('#follow-up-table tr[follow_up_id=' + followUp.id + ']'),
            children = row.children(),
            options = [];
        
        for(var o in followUp.communication_card_options) {
            if(followUp.communication_card_options.hasOwnProperty(o) && followUp.communication_card_options[o] === true)
                options.push(o);
        }
        row[0].setAttribute('frequency', followUp.communication_card_options.frequency);
        row[0].setAttribute('communication_card_options', options.join(','));
        children[0].setAttribute('typeCd', followUp.typeCd);
        children[0].innerHTML = followUp.type;
        children[1].innerHTML = followUp.date;
        children[2].innerHTML = followUp.visitors.join(', ');
        children[2].setAttribute('visitorsIds', followUp.visitorsIds.join(','));
        children[3].innerHTML = followUp.comments;
    }

    function processRelationships(relationships) {
        detachClickListeners();
        $('#relationship-table tbody tr').remove();
        for (var i = 0; i < relationships.length; i++) {
            appendRelationship(relationships[i]);
        }
        attachClickListeners();
    }

    function appendRelationship(relationship) {
        if (!relationship.type && relationship.typeCd)
            relationship.type = relationshipTypeData[relationship.typeCd] || '';
        $('#relationship-table > tbody:last').append(
            '<tr relationship_id="' + relationship.id + '">' +
            '<td data-th="Type" typeCd="' + relationship.typeCd + '">' + relationship.type + '</td>' +
            '<td data-th="Name" relationId="'+relationship.relation_id+'" relationname="' + relationship.name + '"><a class="person_name" href="manage-person.php?id=' + relationship.relation_id + '">' + relationship.name + '</a></td>' +
            '<td data-th="" class="relationship-table-button-col"><button class="edit-relationship"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button><button class="delete-relationship"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></button></td>' +
            '</tr>');
    }

    function updateRelationshipRow(relationship) {
        var children = $('#relationship-table tr[relationship_id=' + relationship.id + ']').children();
        children[0].setAttribute('typeCd', relationship.typeCd);
        children[0].innerHTML = relationship.type;
        children[1].innerHTML = relationship.name;
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
    
    function addRelationship() {
        relationshipDialog.dialog('open');
        clearRelationshipForm();
        relationshipDialog.dialog('option', 'title', 'Add Relationship');
        relationshipPersonName.innerHTML = getDisplayName(person);	// jshint ignore:line
    }

    function closeRelationship() {
        relationshipDialog.dialog('close');
    }
    
    function clearRelationshipForm() {
        relationshipType.value = '1';
        relationshipRelation.innerHTML = '(Select a person)';
        relationshipRelation.setAttribute('relationid', '');
        relationshipRelation.setAttribute('person_name', '');
        addToSpouseContainerRelationship.style.display = 'none';
    }

    function doAddRelationship() {
        var relationship = buildRelationship();
        if (relationship === false) return false;
        
        saveRelationship(relationship, function(r) {
            appendRelationship(r);
            $('button.edit-relationship:last').on('click', onEditRelationshipClick);
            $('button.delete-relationship:last').on('click', onDeleteRelationshipClick);
            relationshipDialog.dialog('close');
        });
    }

    function doEditRelationship() {
        var relationship = buildRelationship();
        if (relationship === false) return false;
        
        saveRelationship(relationship, function(r) {
            updateRelationshipRow(r);
            relationshipDialog.dialog('close');
        });
    }
    
    function addCloseRelationship() {
        if (relationshipDialog.dialog('option', 'title').indexOf('Edit') === -1)
            doAddRelationship();
        else
            doEditRelationship();
    }
    
    function buildRelationship() {
        var type = $.trim(relationshipType.value),
            relationId = relationshipRelation.getAttribute('relationid'),
            msg = '',
            spouseId = '';

        if (!relationId) {
            msg += 'Must select a person<br />';
        } else if(relationId == person.id) {	// jshint ignore:line
            msg += 'Person cannot have a relationship with themself<br />';            
        }
        
        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        
        var spouse = $('#relationship-table td[typecd=1]');
        if(spouse.length > 0 && type === '2' && addToSpouseRelationship.checked) {
            spouseId = spouse.next()[0].getAttribute('relationid');
        }
        
        return {
            id: (relationshipDialog.dialog('option', 'title').indexOf('Edit') === -1) ? genRelationshipId() : relationshipId.value,
            spouseId: spouseId,
            typeCd: type,
            type: relationshipType.selectedOptions[0].text,
            person_id: person.id,		// jshint ignore:line
            relation_id: relationId,
            name: relationshipRelation.getAttribute('person_name')
        };
    }

    function onEditRelationshipClick(e) {
        relationshipDialog.dialog('open');
        var row = e.currentTarget.parentElement.parentElement;

        relationshipType.value = row.children[0].getAttribute('typeCd') || '';
        relationshipId.value = row.getAttribute('relationship_id');
        doSelectPerson(row.children[1].getAttribute('relationid'), row.children[1].getAttribute('relationname'));
        relationshipDialog.dialog('option', 'title', 'Edit Relationship');
    }

    function onDeleteRelationshipClick(e) {
        if (confirm("Are you sure you would like to PERMANENTLY delete this Relationship?")) {
            var id = e.currentTarget.parentElement.parentElement.getAttribute('relationship_id');
            deleteRelationship(id);
        }
    }
    
    function addFollowUp() {
        dialog.dialog('open');
        clearFollowUpForm();
        setVisitors();
        dialog.dialog('option', 'title', 'Add Follow Up');
    }

    function closeFollowUp() {
        dialog.dialog('close');
    }

    function doAddFollowUp(cb) {
        var followUp = buildFollowUp();
        if (followUp === false) return false;
        saveFollowUp(followUp, function(f) {
            appendFollowUp(f);
            $('button.edit-follow-up:last').on('click', onEditFollowUpClick);
            $('button.delete-follow-up:last').on('click', onDeleteFollowUpClick);
            noChangesMade = false;
            cb.call(this);
        });
    }

    function doEditFollowUp(cb) {
        var followUp = buildFollowUp();
        if (followUp === false) return false;
        saveFollowUp(followUp, function(f) {
            updateFollowUpRow(f);
            noChangesMade = false;
            cb.call(this);
        });
    }

    function addCopy() {
        if (dialog.dialog('option', 'title').indexOf('Edit') === -1)
            doAddFollowUp();
        else
            doEditFollowUp();
    }

    function addClear() {
        if (dialog.dialog('option', 'title').indexOf('Edit') === -1)
            doAddFollowUp(clearFollowUpForm);
        else
            doEditFollowUp(clearFollowUpForm);
    }

    function addClose() {
        if (dialog.dialog('option', 'title').indexOf('Edit') === -1)
            doAddFollowUp(closeFollowUp);
        else
            doEditFollowUp(closeFollowUp);
    }

    function onEditFollowUpClick(e) {
        dialog.dialog('open');
        addToSpouseContainerFollowUp.style.display = 'none';
        setVisitors();
        var row = e.currentTarget.parentElement.parentElement,
            date = row.children[1].innerHTML || '',
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

        followUpAttendanceFrequency.value = row.getAttribute('frequency') || '';
        followUpType.value = row.children[0].getAttribute('typeCd') || '';
        followUpDate.value = date;
        followUpComments.value = row.children[3].innerHTML || '';
        followUpId.value = row.getAttribute('follow_up_id');
        unknownDate.checked = date === '';
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
        followUpDate.disabled = unknownDate.checked;

        var visitorIdsString = row.children[2].getAttribute('visitorsIds') || '';
        var visitorIds = visitorIdsString.split(',');
        var inputs = followUpVisitors.querySelectorAll('input');
        for (i = 0; i < inputs.length; i++) {
            inputs[i].checked = visitorIds.indexOf(inputs[i].getAttribute('personid')) >= 0;
        }
        onFollowUpTypeChange();
        
        dialog.dialog('option', 'title', 'Edit Follow Up');
    }

    function onDeleteFollowUpClick(e) {
        if (confirm("Are you sure you would like to PERMANENTLY delete this Follow Up?")) {
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

    function onChangeUnknownDate(e) {
        followUpDate.disabled = e.currentTarget.checked;
        followUpDate.value = '';
    }
    
    function onRelationshipTypeChange() {
        var type = $.trim(relationshipType.value);
        
        // If changing to child then show the 'Add to spouse' checkbox if there is a spouse
        if(type === '2') {
            var spouse = $('#relationship-table td[typecd=1]');
            addToSpouseContainerRelationship.style.display = (spouse.length > 0) ? 'inherit' : 'none';
            addToSpouseRelationship.checked = true;
        } else {
            addToSpouseContainerRelationship.style.display = 'none';
        }
    }
    
    function onFollowUpTypeChange() {
        $('#follow-up-frequency-container').css('display', (followUpType.value == 3) ? 'inherit' : 'none');
        communicationCardOptions.style.display = (followUpType.value == 3) ? 'inherit' : 'none';
    }

    function onClickLink(e) {
        if (!(noChangesMade || confirm("If you continue you will lose any unsaved changes. Continue?"))) {
            e.preventDefault();
        }
    }

    function attachClickListeners() {
        $('#attendance-nav').on('click', onClickLink);
        $('#reports-nav').on('click', onClickLink);
        $('button.edit-follow-up').on('click', onEditFollowUpClick);
        $('button.delete-follow-up').on('click', onDeleteFollowUpClick);
        $('button.edit-relationship').on('click', onEditRelationshipClick);
        $('button.delete-relationship').on('click', onDeleteRelationshipClick);
    }

    function detachClickListeners() {
        $('#attendance-nav').off('click', onClickLink);
        $('#reports-nav').off('click', onClickLink);
        $('button.edit-follow-up').off('click', onEditFollowUpClick);
        $('button.delete-follow-up').off('click', onDeleteFollowUpClick);
        $('button.edit-relationship').off('click', onEditRelationshipClick);
        $('button.delete-relationship').off('click', onDeleteRelationshipClick);
    }

    function genFollowUpId() {
        return followUpIdSequence--;
    }
    
    function genRelationshipId() {
        return relationshipIdSequence--;
    }

    // Format the phone number if there is no formatting already and if the number
    // is either 7 or 10 characters.
    function formatPhoneNumber(phone) {
        phone = phone || '';
        if (phone.match(/\D/g, '') === null) {
            var tmp = phone.replace(/\D/g);
            if (tmp.length === 7) {
                phone = tmp.substr(0, 3) + '-' + tmp.substr(3);
            } else if (tmp.length === 10) {
                phone = '(' + tmp.substr(0, 3) + ') ' + tmp.substr(3, 3) + '-' + tmp.substr(6);
            }
        }
        return phone;
    }

    addFollowUpBtn.addEventListener('click', addFollowUp);
    addCopyBtn.addEventListener('click', addCopy);
    addClearBtn.addEventListener('click', addClear);
    addCloseBtn.addEventListener('click', addClose);
    closeBtn.addEventListener('click', closeFollowUp);
    copyAddressToSpouseBtn.addEventListener('click', copyAddress);
    $('#follow-up-type').on('change', onFollowUpTypeChange);
    
    addRelationshipBtn.addEventListener('click', addRelationship);
    addCloseRelationshipBtn.addEventListener('click', addCloseRelationship);
    closeRelationshipBtn.addEventListener('click', closeRelationship);
    $('#relationship-type').on('change', onRelationshipTypeChange);
    
    selectPersonBtn.addEventListener('click', openSelectPerson);
    addNewPersonBtn.addEventListener('click', addNewPerson);
    searchBtn.addEventListener('click', search);
    searchField.addEventListener('keydown', function(e) {
        if (e.keyCode == 13) {
            search();
        }
    });
    closeSelectPersonBtn.addEventListener('click', closeSelectPerson);
    
    $('#manage-unknown-date').on('change', onChangeUnknownDate);

    loadStates();
	populateTypes();
	populateAttendanceFrequency();
	populateForm(person);	// jshint ignore:line
})();
