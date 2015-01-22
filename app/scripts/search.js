(function () {
  'use strict';
  if($('.search-form').length === 0) return;
 
  var searchBtn = document.querySelector('#search'),
      searchField = document.querySelector('#search-name'),
      scrollAnimationMs = 1000;
  
  function search() {
    var text = $.trim(searchField.value);
    if(text === '')
      $().toastmessage('showErrorToast', "Must enter Name");
    else
      doSearch(text);
  }
  
  function doSearch(text) {
    $.ajax({
      type: 'GET',
      url: 'ajax/search.php',
      data: { search: text }
    })
    .done(function(msg) {
      var data = JSON.parse(msg);
      if(data.success) {
	processSearchResults(data.people);
      } else {
        if(data.error === 1) {
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
	'<td data-th="Name" person_name="'+name+'"><a class="person_name" href="manage-person.html?id='+p.id+'">'+name+'</a></td>' +
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
  
  searchBtn.addEventListener('click', search);
  searchField.addEventListener('keydown', function(e) {
    if(e.keyCode==13){
      search();
    }
  });
  
  checkLoginStatus();
})();
