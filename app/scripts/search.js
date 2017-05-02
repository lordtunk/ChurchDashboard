(function () {
  'use strict';
 
  var searchBtn = document.querySelector('#search'),
      searchField = document.querySelector('#search-name'),
      searchByField = document.querySelector('#search-by'),
      scrollAnimationMs = 1000,
	  searchBy = {
		1: 'Name',
		2: 'Address'
	  },
	  states = {};
	  
	function populateTypes() {
		var $select = $('#search-by');
        $.each(searchBy, function(typeCd, type) {
            $select.append('<option value=' + typeCd + '>' + type + '</option>');
        });
        $select.val('1');
		
		$select = $('#state');
        $.each(states, function(ind, state) {
            $select.append('<option value=' + state.abbreviation + '>' + state.name + '</option>');
        });
        $select.val('OH');
	}
	 
	function search() {
		var searchBy = parseInt(searchByField.value);
		if(searchBy === 1) {
			var text = $.trim(searchField.value);
			if(text === '') {
			  $().toastmessage('showErrorToast', 'Must enter Name');
			  return;
			}
		  doSearch(text, null);
		} else if(searchBy === 2) {
			var street1 = $.trim($('#street1').val());
			var street2 = $.trim($('#street2').val());
			var city = $.trim($('#city').val());
			var state = $.trim($('#state').val());
			var zip = $.trim($('#zip').val());
			
			if(street1 === '' && street2 === '' && city === '' && state === '' && zip === '') {
				$().toastmessage('showErrorToast', 'Must enter address');
				return;
			}
			doSearch(null, {
				street1: street1,
				street2: street2,
				city: city,
				state: state,
				zip: zip
			});
		}
	}
  
  function doSearch(text, address) {
    $('.search-form').mask('Loading...');
	var data = {};
	if(text)
		data.search = text;
	else
		data.address = JSON.stringify(address);
    $.ajax({
      type: 'POST',
      url: 'ajax/search.php',
      data: data
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
	  $('.search-form').unmask();
      if(data.success) {
        processSearchResults(data.people);
      } else {
        if(data.error === 1) {
          logout();
        } else {
          $().toastmessage('showErrorToast', "Error searching people");
        }
      }
    })
    .fail(function() {
      $().toastmessage('showErrorToast', "Error searching people");
    });
  }
  
  
    function loadStates() {
        $.ajax({
            type: 'GET',
            url: 'ajax/states.json'
        })
		.done(function(msg) {
			if ($.isArray(msg)) {
				states = msg;
			} else {
				var data = JSON.parse(msg);
				states = data;
			}
			populateTypes();
		})
		.fail(function() {
			$().toastmessage('showErrorToast', "Error loading states");
		});
    }
  
  function getDisplayName(person, withComma) {
    if(person === null) return '';
    
    var display = '';
    if(person.first_name || person.last_name) {
      if(person.last_name) display += person.last_name;
      
      if(person.last_name && person.first_name) {
        if(withComma === true)
          display += ", " + person.first_name;
        else
          display = person.first_name + ' ' + display;
      } else if(person.first_name) {
        display += person.first_name + ' ';
      }
    } else {
      display = person.description;
    }
    return display;
  }
  
  function processSearchResults(results) {
    $('#search-table tbody tr').remove();
    for(var i=0; i<results.length; i++) {
      appendPerson(results[i]);
    }
  }
  
  function appendPerson(p) {
    var name = getDisplayName(p);
    $('#search-table > tbody:last').append(
      '<tr person_id="'+p.id+'">' +
	'<td data-th="Name" person_name="'+name+'"><a class="person_name" href="manage-person.php?id='+p.id+'">'+name+'</a></td>' +
        '<td data-th="Email">'+p.email+'</td>'+
	'<td data-th="Address">'+getAddress(p)+'</td>'+
      '</tr>');
  }
  
  function getAddress(p) {
    var addr = '';
    addr += p.street1 || '';
    if(p.street1)
      addr += '<br />';
    addr += p.street2 || '';
    if(p.street2)
      addr += '<br />';
    addr += p.city || '';
    if(p.city && p.state)
      addr += ',';
    
    addr += ' ';
    addr += p.state || '';
    addr += ' ';
    addr += p.zip || '';
    
    return addr.trim();
  }
  
	function onSearchByChange() {
		var searchBy = parseInt(searchByField.value);
    
		$('#search-by-address').css('display', (searchBy === 2) ? '' : 'none');
		$('#search-by-name').css('display', (searchBy === 1) ? '' : 'none');
	}
  
  function onClickTopBottom(e) {
    var container = $('#search-table-container'),
        pos = (e.target.id.indexOf('top') == -1) ?
                    container[0].scrollHeight
                    : 0;

    container.stop().animate({ scrollTop: pos }, scrollAnimationMs, 'swing', function() {
      $('<style></style>').appendTo($(document.body)).remove();
    });
  }
  $('.navigation-links a').on('click', onClickTopBottom);
  
  searchByField.addEventListener('change', onSearchByChange);
  searchBtn.addEventListener('click', search);
  searchField.addEventListener('keydown', function(e) {
    if(e.keyCode==13){
      search();
    }
  });
  
  loadStates();
})();
