!function(){"use strict";function e(){var e=$("#follow-up-frequency");$.each(kt,function(t,o){e.append("<option value="+t+">"+o+"</option>")}),e.val("")}function t(){var e=$("#follow-up-type");$.each(bt,function(t,o){e.append("<option value="+t+">"+o+"</option>")}),e.val("3"),H()}function o(){var e=$.trim(_t.value);""===e?$().toastmessage("showErrorToast","Must enter Name"):r(e)}function n(){var e=$.trim(_t.value);""===e?$().toastmessage("showErrorToast","Must enter Name"):i(e)}function r(e){$.ajax({type:"POST",url:"ajax/create_person.php",data:{person_display:e}}).done(function(e){var t=JSON.parse(e);t.success?f(t.person_id,t.person_name):1===t.error?logout():$().toastmessage("showErrorToast","Error loading visitors")}).fail(function(){$().toastmessage("showErrorToast","Error loading visitors")})}function i(e){$.ajax({type:"GET",url:"ajax/search.php",data:{search:e}}).done(function(e){var t=JSON.parse(e);t.success?_(t.people):1===t.error?logout():$().toastmessage("showErrorToast","Error loading visitors")}).fail(function(){$().toastmessage("showErrorToast","Error loading visitors")})}function c(){$.ajax({type:"GET",url:"ajax/get_visitors.php"}).done(function(o){var n=JSON.parse(o);n.success?(F=n.people,k(),t(),e(),a()):1===n.error?logout():$().toastmessage("showErrorToast","Error loading visitors")}).fail(function(){$().toastmessage("showErrorToast","Error loading visitors")})}function a(){$.ajax({type:"POST",url:"ajax/get_follow_ups.php",data:{date:R.value}}).done(function(e){var t=JSON.parse(e);t.success?y(t.follow_ups):1===t.error?logout():$().toastmessage("showErrorToast","Error loading visitors")}).fail(function(){$().toastmessage("showErrorToast","Error loading visitors")})}function s(e,t,o){$.ajax({type:"POST",url:"ajax/save_follow_up.php",data:{follow_up:JSON.stringify(e)}}).done(function(n){var r=JSON.parse(n);r.success?(e.id=r.follow_up_id,t.call(this,e,o),$().toastmessage("showSuccessToast","Save successful")):1===r.error?logout():r.warning?$().toastmessage("showErrorToast",r.warning):$().toastmessage("showErrorToast","Error saving follow up")}).fail(function(){$().toastmessage("showErrorToast","Error saving follow up")})}function l(e,t){$.ajax({type:"POST",url:"ajax/delete_follow_up.php",data:{id:e,personId:t}}).done(function(t){var o=JSON.parse(t);o.success?$("#follow-up-table tr[follow_up_id="+e+"]").remove():1===o.error?logout():o.warning?$().toastmessage("showErrorToast",o.warning):$().toastmessage("showErrorToast","Error deleting Follow Up")}).fail(function(){$().toastmessage("showErrorToast","Error deleting Follow Up")})}function u(e,t){if(null===e)return"";var o="";return e.first_name||e.last_name?(e.last_name&&(o+=e.last_name),e.last_name&&e.first_name?t===!0?o+=", "+e.first_name:o=e.first_name+" "+o:e.first_name&&(o+=e.first_name+" ")):o=e.description,o}function d(){D.text("Add Follow Up"),Y.innerHTML="(Select a person)",Y.setAttribute("personid",""),Y.setAttribute("person_name",""),z.value="2",B.value="",V.value="",Q.checked=!1,W.checked=!1,X.checked=!1,Z.checked=!1,et.checked=!1,tt.checked=!1,ot.checked=!1,nt.checked=!1,rt.checked=!1,it.checked=!1,ct.checked=!1,ut.value="",st.checked=!1,B.disabled=!1;for(var e=lt.querySelectorAll("input"),t=0;t<e.length;t++)e[t].checked=!1;H()}function m(){var e=$.trim(Y.getAttribute("person_name")),t=$.trim(Y.getAttribute("personid")),o=$.trim(B.value),n=$.trim(z.value),r=$.trim(ut.value),i={frequency:V.value,commitment_christ:!1,recommitment_christ:!1,commitment_tithe:!1,commitment_ministry:!1,commitment_baptism:!1,info_next:!1,info_gkids:!1,info_ggroups:!1,info_gteams:!1,info_member:!1,info_visit:!1},c=[],a=[],s="",l="";(""===t||0>t)&&(s+="Must select a person<br />"),""!==r||1!=n&&2!=n||(s+="Must specify comments<br />"),""!==o||st.checked||(s+="Must specify a date or mark it unknown<br />"),r.length>5e3&&(s+="Comments cannot exceed 5000 characters<br />");for(var u=lt.querySelectorAll("input"),d=0;d<u.length;d++)u[d].checked&&(c.push($.trim(u[d].nextSibling.innerHTML)),a.push(u[d].getAttribute("personid")));return 0===a.length&&(s+="Must specify a visitor<br />"),s?($().toastmessage("showErrorToast",s),!1):(3==n&&(i={frequency:V.value,commitment_christ:Q.checked,recommitment_christ:W.checked,commitment_tithe:X.checked,commitment_ministry:Z.checked,commitment_baptism:et.checked,info_next:tt.checked,info_gkids:ot.checked,info_ggroups:nt.checked,info_gteams:rt.checked,info_member:it.checked,info_visit:ct.checked}),{id:M()?J():G.value,date:o,name:e,personId:t,spouseId:l,typeCd:n,type:z.selectedOptions[0].text,comments:r,communication_card_options:i,visitors:c,visitorsIds:a})}function p(e){var t=e.currentTarget.parentElement.parentElement,o=t.getAttribute("person_id"),n=t.children[0].getAttribute("person_name");f(o,n)}function f(e,t){L(),Y.innerHTML='<a class="person_name" href="manage-person.html?id='+e+'">'+t+"</a>",Y.setAttribute("personid",e),Y.setAttribute("person_name",t)}function h(e){var t=e.currentTarget.parentElement.parentElement,o=t.getAttribute("person_id");window.location="manage-person.html?id="+o}function _(e){$("a.person_name").off("click",p),$("button.search-button").off("click",h),$("#search-table tbody tr").remove();for(var t=0;t<e.length;t++)g(e[t]);$("a.person_name").on("click",p),$("button.search-button").on("click",h)}function g(e){var t=u(e);$("#search-table > tbody:last").append('<tr person_id="'+e.id+'"><td data-th="Name" person_name="'+t+'"><a class="person_name" href="javascript:void(0);">'+t+'</a></td><td data-th="Address">'+v(e)+'</td><td data-th="" class="search-table-button-col"><button class="search-button btn btn-xs btn-info">Manage</button></td></tr>')}function v(e){var t="";return t+=e.street1||"",e.street1&&(t+="<br />"),t+=e.street2||"",e.street2&&(t+="<br />"),t+=e.city||"",e.city&&e.state&&(t+=","),t+=" ",t+=e.state||"",t+=" ",t+=e.zip||"",t.trim()}function y(e){P(),$("#follow-up-table tbody tr").remove();for(var t=0;t<e.length;t++)w(e[t]);N()}function w(e){!e.type&&e.typeCd&&(e.type=bt[e.typeCd]||""),e.date=e.date||"",e.name=e.name||"";var t,o=e.name;e.personId>=0&&(t='<a class="person_name" href="manage-person.html?id='+e.personId+'">'+o+"</a>");var n=[];for(var r in e.communication_card_options)e.communication_card_options.hasOwnProperty(r)&&e.communication_card_options[r]===!0&&n.push(r);$("#follow-up-table > tbody:last").append('<tr class="'+("1"==e.communication_card_options.frequency?"first-time-visitor":"")+'" follow_up_id="'+e.id+'" communication_card_options="'+n.join(",")+'" frequency="'+e.communication_card_options.frequency+'"><td data-th="Name" personid="'+e.personId+'" person_name="'+o+'">'+t+'</td><td data-th="Type" typeCd="'+e.typeCd+'">'+e.type+'</td><td data-th="Date" class="follow-up-table-date-col">'+e.date+'</td><td data-th="By" visitorsIds="'+e.visitorsIds.join(",")+'">'+e.visitors.join(", ")+'</td><td data-th="Comments" class="follow-up-table-comments-col">'+e.comments+'</td><td data-th="" class="follow-up-table-button-col"><button class="edit-follow-up btn btn-xs btn-default"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></button><button class="delete-follow-up btn btn-xs btn-default"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></button></td></tr>')}function b(e){var t=$("#follow-up-table tr[follow_up_id="+e.id+"]"),o=t.children(),n=[];t[0].setAttribute("class","1"==e.communication_card_options.frequency?"first-time-visitor":"");for(var r in e.communication_card_options)e.communication_card_options.hasOwnProperty(r)&&e.communication_card_options[r]===!0&&n.push(r);t[0].setAttribute("frequency",e.communication_card_options.frequency),t[0].setAttribute("communication_card_options",n.join(",")),o[0].setAttribute("personid",e.personId),o[0].setAttribute("person_name",e.name),o[0].innerHTML='<a class="person_name" href="manage-person.html?id='+e.personId+'">'+e.name+"</a>",o[1].setAttribute("typeCd",e.typeCd),o[1].innerHTML=e.type,o[2].innerHTML=e.date,o[3].innerHTML=e.visitors.join(", "),o[4].setAttribute("visitorsIds",e.visitorsIds.join(",")),o[4].innerHTML=e.comments}function k(){if(""===lt.innerHTML.trim())for(var e,t=0;t<F.length;t++)e=F[t],lt.innerHTML+='<div class="check-field"><input type="checkbox" personid="'+e.id+'" id="follow-up-by-'+e.id+'"/><label for="follow-up-by-'+e.id+'">'+u(e)+"</label></div>"}function E(e,t){w(e),$("button.edit-follow-up:last").on("click",A),$("button.delete-follow-up:last").on("click",O),t===!0&&d()}function S(e,t){b(e),t===!0&&d()}function T(){var e=m();return e===!1?!1:(s(e,M()?E:S,!1),void 0)}function q(){var e=m();return e===!1?!1:(s(e,M()?E:S,!0),void 0)}function A(e){var t,o=e.currentTarget.parentElement.parentElement,n=o.children[2].innerHTML||"",r=(o.getAttribute("communication_card_options")||"").split(","),i={commitment_christ:!1,recommitment_christ:!1,commitment_tithe:!1,commitment_ministry:!1,commitment_baptism:!1,info_next:!1,info_gkids:!1,info_ggroups:!1,info_gteams:!1,info_member:!1,info_visit:!1};for(t=0;t<r.length;t++)i[r[t]]=!0;Y.setAttribute("personid",o.children[0].getAttribute("personid")||""),Y.setAttribute("person_name",o.children[0].getAttribute("person_name")||""),Y.innerHTML=o.children[0].innerHTML||"",z.value=o.children[1].getAttribute("typeCd")||"",B.value=n,V.value=o.getAttribute("frequency")||"",Q.checked=i.commitment_christ,W.checked=i.recommitment_christ,X.checked=i.commitment_tithe,Z.checked=i.commitment_ministry,et.checked=i.commitment_baptism,tt.checked=i.info_next,ot.checked=i.info_gkids,nt.checked=i.info_ggroups,rt.checked=i.info_gteams,it.checked=i.info_member,ct.checked=i.info_visit,ut.value=o.children[4].innerHTML||"",G.value=o.getAttribute("follow_up_id"),st.checked=""===n,B.disabled=st.checked;var c=o.children[3].getAttribute("visitorsIds")||"",a=c.split(","),s=lt.querySelectorAll("input");for(t=0;t<s.length;t++)s[t].checked=a.indexOf(s[t].getAttribute("personid"))>=0;H(),D.text("Edit Follow Up");var l=$(".follow-ups-form").offset().top;$("body").animate({scrollTop:l},200),$(".follow-ups-form").effect("highlight",{},1200)}function x(){vt.dialog("open")}function L(){vt.dialog("close")}function M(){return-1===D.text().indexOf("Edit")}function j(e){U||confirm("If you continue you will lose any unsaved changes. Continue?")||e.preventDefault()}function C(){confirm("If you continue you will lose any unsaved changes. Continue?")&&d()}function O(e){if(confirm("Are you sure you would like to PERMANENTLY delete this Follow Up?")){var t=e.currentTarget.parentElement.parentElement,o=t.getAttribute("follow_up_id"),n=t.children[0].getAttribute("personid");l(o,n)}}function I(e){B.disabled=e.target.checked,B.value=""}function H(){$("#follow-up-frequency-container").css("display",3==z.value?"inherit":"none"),K.style.display=3==z.value?"inherit":"none"}function N(){$("#attendance-nav").on("click",j),$("#reports-nav").on("click",j),$("button.edit-follow-up").on("click",A),$("button.delete-follow-up").on("click",O)}function P(){$("#attendance-nav").off("click",j),$("#reports-nav").off("click",j),$("button.edit-follow-up").off("click",A),$("button.delete-follow-up").off("click",O)}function J(){return--wt}$("#follow-up-date").datepicker(),$("#follow-ups-for-date").datepicker({dateFormat:"m/d/yy"}),$("#follow-ups-for-date").datepicker("setDate",new Date);var F=[],U=!0,D=$("#follow-ups-form-title"),G=document.querySelector("#follow-up-id"),Y=document.querySelector("#follow-up-person"),z=document.querySelector("#follow-up-type"),B=document.querySelector("#follow-up-date"),R=document.querySelector("#follow-ups-for-date"),V=document.querySelector("#follow-up-frequency"),K=document.querySelector(".communication-card-options"),Q=document.querySelector("#follow-up-commitment-christ"),W=document.querySelector("#follow-up-recommitment-christ"),X=document.querySelector("#follow-up-commitment-tithe"),Z=document.querySelector("#follow-up-commitment-ministry"),et=document.querySelector("#follow-up-commitment-baptism"),tt=document.querySelector("#follow-up-info-next"),ot=document.querySelector("#follow-up-info-gkids"),nt=document.querySelector("#follow-up-info-ggroups"),rt=document.querySelector("#follow-up-info-gteams"),it=document.querySelector("#follow-up-info-member"),ct=document.querySelector("#follow-up-info-visit"),at=document.querySelector("#get-follow-ups"),st=document.querySelector("#unknown-date"),lt=document.querySelector("#follow-up-visitors"),ut=document.querySelector("#follow-up-comments"),dt=document.querySelector("#add-clear"),mt=document.querySelector("#add-copy"),pt=document.querySelector("#clear"),ft=document.querySelector("#add-new-person"),ht=document.querySelector("#search"),_t=document.querySelector("#search-name"),gt=document.querySelector("#close"),vt=$("#search-form").dialog({autoOpen:!1,height:430,width:510,modal:!0}),yt=document.querySelector("#select-person-btn"),wt=-1,bt={1:"Phone Call",2:"Visit",3:"Communication Card",4:"Entered in The City",5:"Thank You Card Sent"},kt={1:"1st Time",2:"2nd Time",3:"Often",4:"Member","":"--None Provided--"};mt.addEventListener("click",T),dt.addEventListener("click",q),pt.addEventListener("click",C),yt.addEventListener("click",x),ft.addEventListener("click",o),ht.addEventListener("click",n),_t.addEventListener("keydown",function(e){13==e.keyCode&&n()}),gt.addEventListener("click",L),at.addEventListener("click",a),$("#unknown-date").on("change",I),$("#follow-up-type").on("change",H),d(),checkLoginStatus(c)}();