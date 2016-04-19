(function() {
    'use strict';

    $("#from-date").datepicker();
    $("#to-date").datepicker();

    var toDateField = document.querySelector('#to-date'),
        fromDateField = document.querySelector('#from-date'),
        runBtn = document.querySelector('#go-arrow'),
        genMapBtn = document.querySelector('#gen-map'),
        scrollAnimationMs = 1000,
        mapPanel = document.querySelector('#address-map-panel'),
        mapLegend = document.querySelector('#map-legend'),
        gMapsImgUrl = '//maps.googleapis.com/maps/api/staticmap?zoom=11&size=500x500',
        gMapsUrl = 'https://www.google.com/maps/place/',
        gMapsMarker = '&markers=color:red%7Clabel:',
        gMapsLabels = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];

    function onRunClick() {
        if (validateDates(fromDateField.value, toDateField.value)) {
            var params;
            params = {
                fromDate: fromDateField.value,
                toDate: toDateField.value,
                active: $('#active').is(':checked'),
                not_visited: $('#not-visited').is(':checked'),
                ty_card_not_sent: false,
                signed_up_for_baptism: false,
                baptized: false,
                interested_in_gkids: false,
                interested_in_next: false,
                interested_in_ggroups: false,
                interested_in_gteams: false,
                interested_in_joining: false,
                would_like_visit: $('#would-like-visit').is(':checked'),
                no_agent: false,
                commitment_christ: false,
                recommitment_christ: false,
                commitment_tithe: false,
                commitment_ministry: false,
                attendance_frequency: false
            };
            doSearch(params);
        }
    }

    function validateDates(fDate, tDate) {
        var msg = '';

        if (!isDate(fDate, true)) {
            msg += 'From Date must be a valid date<br />';
        }
        if (!isDate(tDate, true)) {
            msg += 'To Date must be a valid date<br />';
        }

        if (msg) {
            $().toastmessage('showErrorToast', msg);
            return false;
        }
        return true;
    }

    function doSearch(params) {
        $('.address-view-form').mask('Loading...');
        $.ajax({
            type: 'POST',
            url: 'ajax/get_report.php',
            data: {
                type: 4,
                params: JSON.stringify(params)
            }
        })
            .done(function(msg) {
                var data = JSON.parse(msg);
                if (data.success) {
                    processSearchResults(data.people);
                    $('.address-view-form').unmask();
                } else {
                    if (data.error === 1) {
                        logout();
                    } else {
                        $().toastmessage('showErrorToast', 'Error searching people');
                    }
                }
            })
            .fail(function() {
                $().toastmessage('showErrorToast', 'Error searching people');
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

    function processSearchResults(results) {
        $('#address-view-table tbody tr').remove();
        for (var i = 0; i < results.length; i++) {
            appendPerson(results[i]);
        }
    }

    function appendPerson(p) {
        var name = getDisplayName(p);
        $('#address-view-table > tbody:last').append(
            '<tr person_id="' + p.id + '">' +
            '<td data-th="Name" person_name="' + name + '"><a class="person_name" href="manage-person.php?id=' + p.id + '">' + name + '</a></td>' +
            '<td data-th="Address">' + getAddress(p) + '</td>' +
            '<td data-th="Visit" class="checkbox-table-col"><label for="visit-checkbox-' + p.id + '"><input id="visit-checkbox-' + p.id + '" class="visit-checkbox" type="checkbox" address="' + getAddressString(p) + '" /></label></td>' +
            '</tr>');
    }

    function updateMap() {
        var checkboxes = $('.visit-checkbox:checked'),
            url = gMapsImgUrl,
            addr, name, addrStr;
        if (checkboxes.length > gMapsLabels.length) {
            $().toastmessage('showErrorToast', "Can't show more than " + gMapsLabels.length + " people at once.");
            return;
        }
        mapLegend.innerHTML = '';
        for (var i = 0; i < checkboxes.length; i++) {
            addrStr = checkboxes[i].getAttribute('address');
            // Don't generate a map if the string is just 'OH' since that is the default
            if (addrStr === 'OH' || addrStr === '') continue;
            url += gMapsMarker + gMapsLabels[i] + '|' + addrStr;
            addr = checkboxes[i].parentElement.parentElement.previousElementSibling.innerHTML;
            name = checkboxes[i].parentElement.parentElement.previousElementSibling.previousElementSibling.getAttribute('person_name');
            mapLegend.innerHTML += ' <div class="map-legend-item"><span style="font-weight: bold;">' + gMapsLabels[i] + '</span> : ' + name + '<br /><a href="' + gMapsUrl + addr + '" target="_blank">' + addr + '</a></div>';
        }
        if (mapLegend.innerHTML !== '') {
            mapPanel.innerHTML = '<img border="0" src="' + url + '" />';
            $('#map-legend')[0].style.setProperty('display', 'block');
            $('#map-note')[0].style.setProperty('display', 'block');
        } else {
            mapPanel.innerHTML = '';
            $('#map-legend')[0].style.setProperty('display', 'none');
            $('#map-note')[0].style.setProperty('display', 'none');
            $().toastmessage('showWarningToast', 'Must select at least 1 person with an address');
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

    function isDate(txtDate, allowBlank) {
        var currVal = txtDate;
        if (currVal === '')
            return !!allowBlank;

        //Declare Regex
        var rxDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
        var dtArray = currVal.match(rxDatePattern); // is format OK?

        if (dtArray === null)
            return false;

        //Checks for mm/dd/yyyy format.
        var dtMonth = dtArray[1];
        var dtDay = dtArray[3];
        var dtYear = dtArray[5];

        if (dtMonth < 1 || dtMonth > 12)
            return false;
        else if (dtDay < 1 || dtDay > 31)
            return false;
        else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
            return false;
        else if (dtMonth == 2) {
            var isleap = (dtYear % 4 === 0 && (dtYear % 100 !== 0 || dtYear % 400 === 0));
            if (dtDay > 29 || (dtDay == 29 && !isleap))
                return false;
        }
        return true;
    }

    function onClickTopBottom(e) {
        var container = $('#address-view-table-container'),
            pos = (e.target.id.indexOf('top') == -1) ?
                container[0].scrollHeight : 0;

        container.stop().animate({
            scrollTop: pos
        }, scrollAnimationMs, 'swing', function() {
            $('<style></style>').appendTo($(document.body)).remove();
        });
    }
    $('.navigation-links a').on('click', onClickTopBottom);

    runBtn.addEventListener('click', onRunClick);
    genMapBtn.addEventListener('click', updateMap);
})();
