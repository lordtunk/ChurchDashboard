chrome.extension.onRequest.addListener(
    function(request, sender, sendResponse) {
        if(request && request.method == "copyPerson") {
            var p = {
                firstName: getValue('first-name'),
                lastName: getValue('last-name'),
                email: getValue('email'),
                phone1: getValue('primary-phone'),
                phoneType1: getPhoneType('primary-phone-type'),
                phone2: getValue('secondary-phone'),
                phoneType2: getPhoneType('secondary-phone-type'),
                street1: getValue('street1'),
                street2: getValue('street2'),
                city: getValue('city'),
                state: getValue('state'),
                zip: getValue('zip')
            };
            
            sendResponse({data: p, method: "copyPerson"});
        }
    }
);

function getValue(fieldName) {
    var f = document.getElementById(fieldName);
    if(f)
        return f.value;
    return '';
}

function getPhoneType(fieldName) {
    var phoneType = getValue(fieldName);
    if(phoneType == '1')
        return 'Home';
    if(phoneType == '2')
        return 'Mobile';
    if(phoneType == '3')
        return 'Work';
    return '';
}
