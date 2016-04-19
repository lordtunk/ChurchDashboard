(function () {
    'use strict';
    
	var followUpTypeData = {
            1: "Phone Call",
            2: "Visit",
            3: "Communication Card",
            4: "Entered in The City",
            5: "Thank You Card Sent"
        };
	function processFollowUps(followUps) {
        $('#follow-up-table tbody tr').remove();
        for (var i = 0; i < followUps.length; i++) {
            appendFollowUp(followUps[i]);
        }
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
            '</tr>');
    }
	
	processFollowUps(followUps);	// jshint ignore:line
})();
