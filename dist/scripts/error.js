!function(){function e(e){var r=document.getElementById("error-container"),n=!!e;r?(document.getElementById("error-msg").innerHTML=n?e:"",r.style.display=n?"":"none"):n&&alert(e)}function r(){$.ajax({type:"POST",url:"ajax/email_error.php",data:{msg:n}}).done(function(r){var a=JSON.parse(r);a.success?e(n+"<br /><br /> Email sent"):e(n+"<br /><br /> Error sending email")}).fail(function(){e(n+"<br /><br /> Error sending email")})}var n="",a=document.querySelector("#email-error");window.onerror=function(r,a,o,i,t){$(".masked").unmask(),r=t||r||"",a=a||"",o=o||"",i=i||"",n="Message: "+r+"<br />File: "+a+"<br />Line: "+o+"<br />Column: "+i,e(n)},a&&a.addEventListener("click",r)}();