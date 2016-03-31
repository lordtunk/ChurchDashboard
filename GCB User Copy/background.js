chrome.runtime.onMessage.addListener(function(request, sender, sendResponse) {
	if(request.formData) {
		var f = JSON.parse(request.formData);
		var code = "";
		code += buildSetter('offline_user_first', f.firstName);
		code += buildSetter('offline_user_last', f.lastName);
		code += buildSetter('offline_user_email', f.email);
		code += buildSetter('birthdate_input', f.birthdate);
		//code += buildSetter('offline_user_gender', f.gender || 'Male');
		code += addPhoneNumbers(f.homePhoneNumber, f.cellPhoneNumber);
		code += showAddressFields();
		code += buildSetter('offline_user_address_attributes__street', f.street);
		code += buildSetter('offline_user_address_attributes__city', f.city);
		code += buildSetter('offline_user_address_attributes__state', f.state);
		code += buildSetter('offline_user_address_attributes__zipcode', f.zip);
		//code += "}";
		chrome.tabs.executeScript(request.tabId, {
			code: code
		});
	} else if(request.method == "getText"){
        var tabId = request.tabId;
        chrome.tabs.executeScript(tabId, { file: "content.js" }, function() {
            chrome.tabs.sendRequest(tabId, {}, function(results) {
              doStuffWithDom(results);
              localStorage.setItem('formData', JSON.stringify(results));
              //sendResponse(results);
            });
         });
    }  else if(request.method == "setText"){
        var data = localStorage.getItem('formData');
        console.log('my data: ', JSON.parse(data));
    } else {
        console.log('no request');
    }
});

// Regex-pattern to check URLs against. 
// It matches URLs like: http[s]://[...]stackoverflow.com[...]
var managePersonUrlRegex = /.*manage-person\.html.*/;
var cityUrlRegex = /.*guidechurch.onthecity.org\/admin\/users*.*/;

// A function to use as callback
function doStuffWithDom(domContent) {
    console.log('I received the following DOM content:\n', domContent);
}

// When the browser-action button is clicked...
chrome.browserAction.onClicked.addListener(function (tab) {
    // ...check the URL of the active tab against our pattern and...
    if (managePersonUrlRegex.test(tab.url)) {
        chrome.tabs.executeScript(tab.id, { file: "content.js" }, function() {
            chrome.tabs.sendRequest(tab.id, { method: 'copyPerson' }, function(results) {
              if(results.method == 'copyPerson')
                localStorage.setItem('formData', JSON.stringify(results.data));
            });
        });
    } else if(cityUrlRegex.test(tab.url)) {
        var f = JSON.parse(localStorage.getItem('formData'));
		var code = "";
		code += buildSetter('offline_user_first', f.firstName);
		code += buildSetter('offline_user_last', f.lastName);
		code += buildSetter('offline_user_email', f.email);
		code += buildSetter('birthdate_input', f.birthdate);
		//code += buildSetter('offline_user_gender', f.gender || 'Male');
        code += buildSetter('offline_user_primary_phone', f.phone1);
        code += buildSetter('offline_user_primary_phone_type', f.phoneType1);
        code += buildSetter('offline_user_secondary_phone', f.phone2);
        code += buildSetter('offline_user_secondary_phone_type', f.phoneType2);
		code += showAddressFields();
		code += buildSetter('offline_user_address_attributes__street', f.street1);
        code += buildSetter('offline_user_address_attributes__street2', f.street2);
		code += buildSetter('offline_user_address_attributes__city', f.city);
		code += buildSetter('offline_user_address_attributes__state', f.state);
		code += buildSetter('offline_user_address_attributes__zipcode', f.zip);
        console.log(code);
		chrome.tabs.executeScript(tab.id, {
			code: code
		});
    }
});


function buildSetter(field, value) {
	value = value || '';
	return "f = document.getElementById('"+field+"'); if(f) { f.value = '"+value+"'; }";
}
function addPhoneNumbers(home, cell) {
	var code = "";
	if(home && cell) {
		code += buildSetter('offline_user_primary_phone', home);
		code += buildSetter('offline_user_primary_phone_type', 'Home');
		code += buildSetter('offline_user_secondary_phone', cell);
		code += buildSetter('offline_user_secondary_phone_type', 'Mobile');
	} else if(home) {
		code += buildSetter('offline_user_primary_phone', home);
		code += buildSetter('offline_user_primary_phone_type', 'Home');
	} else if(cell) {
		code += buildSetter('offline_user_primary_phone', cell);
		code += buildSetter('offline_user_primary_phone_type', 'Mobile');
	}
	return code;
}
function showAddressFields() {
	var code = "";
	code += "if(!document.getElementById('addresses_box')) {";
	code += "var links = document.getElementsByTagName('a');";
	code += "for(var i=0; i<links.length; i++) {";
	code += "if(links[i].innerHTML == '+ add a new address') links[i].click();";
	code += "}";
	code += "}";
	return code;
}
