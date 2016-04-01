function onPasteInput() {
	// Delay reading the input because paste event fires just before paste occurs
	setTimeout(readInput, 100);
}
function readInput() {
	var val = $('#input')[0].value;
	if(val) {
		try {
			var input = JSON.parse(val);
			storeData(val);
		} catch(e) { console.log(e); }
	}
}
function storeData(data) {
	if(typeof(data) == 'object')
		data = JSON.stringify(data);
	localStorage.setItem('formData', data);
	$('#userCopied').css('display', 'block');
}

function pasteUser() {
	//var data = localStorage.getItem('formData');
	//if(data) {
		//chrome.tabs.query({active: true, url: 'https://guidechurch.onthecity.org/admin/users*'}, function(tabs) {
			//if(tabs.length > 0) {
				//chrome.runtime.sendMessage({ tabId: tabs[0].id, formData: data }, function(response) {
					//console.log(response)
				//});
			//} else {
				//console.log('Guide Church City admin tab is not open');
			//}
		//});
	//}

    chrome.tabs.getSelected(null, function(tab) {
        chrome.runtime.sendMessage({ tabId: tab.id, method: "setText" }, function(response) {
            console.log(response)
        });
    });
}

function copyInput() {
    chrome.tabs.getSelected(null, function(tab) {
        chrome.runtime.sendMessage({ tabId: tab.id, method: "getText" }, function(response) {
            console.log(response)
        });
        //chrome.tabs.sendRequest(tab.id, {method: "getText"}, function(response) {
            //if(response && response.method=="getText"){
                //console.log(response.data);
            //} else {
                //console.log('no response');
                //debugger;
            //}
        //});
    });
}

$(function() {
    $('#read').on('click', readInput);
    $('#copy').on('click', copyInput);
	$('#paste').on('click', pasteUser);
	$('#input').on('paste', onPasteInput);
});
