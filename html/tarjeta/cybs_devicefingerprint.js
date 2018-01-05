/*
<!doctype html> -> Esta etiqueta debe de estar en la página, 
                   donde se define el document type. De
				   lo contraio aparecera un warning de 
				   Active-X
*/

/*
    cybs_devicefingerprint.js

    This file is part of numerocentral and is provided as is.

    print a catalog of customers by nit (tax identifying number) their id and email.
*/

 function cybs_dfprofiler(merchantID,environment)
 {  
 
	if (environment == 'live') {
		var org_id = 'k8vif92e';
	} else {
	    var org_id = '1snn5n9w';
	}
	
	var sessionID = new Date().getTime();

	var paragraphTM = document.createElement("p");
	str = "background:url(https://h.online-metrix.net/fp/clear.png?org_id=" + org_id + "&session_id=" + merchantID + sessionID + "&m=1)";
    
	paragraphTM.styleSheets = str;
	paragraphTM.height = "0";
	paragraphTM.width = "0";
	paragraphTM.hidden = "true";
	
	document.body.appendChild(paragraphTM);
	
	var img = document.createElement("img");
	
	str = "https://h.online-metrix.net/fp/clear.png?org_id=" + org_id + "&session_id=" + merchantID + sessionID + "&m=2";
	img.src = str;
	
	document.body.appendChild(img);
	
	var tmscript = document.createElement("script");
	tmscript.src = "https://h.online-metrix.net/fp/check.js?org_id=" + org_id + "&session_id=" + merchantID + sessionID;
	tmscript.type = "text/javascript";
	
	document.body.appendChild(tmscript);
	
	var objectTM = document.createElement("object");
	/*objectTM.type = "application/x-shockwave-flash";*/
	/*objectTM.classid = "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000";*/
	objectTM.data = "https://h.online-metrix.net/fp/fp.swf?org_id=" + org_id + "&session_id=" + merchantID + sessionID;
	objectTM.width = "1";
	objectTM.height = "1";
	objectTM.id = "thm_fp";
	
	var param = document.createElement("param");
	param.name = "movie";
	param.value = "https://h.online-metrix.net/fp/fp.swf?org_id=" + org_id + "&session_id=" + merchantID + sessionID;
	objectTM.appendChild(param);
	
	document.body.appendChild(objectTM);
	
	return sessionID;
 }
 
 function cybs_dfprofilerRaw(environment)
 {  
 
	if (environment == 'live') {
		var org_id = 'k8vif92e';
	} else {
	    var org_id = '1snn5n9w';
	}
	
	var sessionID = new Date().getTime();

	var paragraphTM = document.createElement("p");
	str = "background:url(https://h.online-metrix.net/fp/clear.png?org_id=" + org_id + "&session_id=" + sessionID + "&m=1)";
    
	paragraphTM.styleSheets = str;
	paragraphTM.height = "0";
	paragraphTM.width = "0";
	paragraphTM.hidden = "true";
	
	document.body.appendChild(paragraphTM);
	
	var img = document.createElement("img");
	
	str = "https://h.online-metrix.net/fp/clear.png?org_id=" + org_id + "&session_id=" + sessionID + "&m=2";
	img.src = str;
	
	document.body.appendChild(img);
	
	var tmscript = document.createElement("script");
	tmscript.src = "https://h.online-metrix.net/fp/check.js?org_id=" + org_id + "&session_id=" +  sessionID;
	tmscript.type = "text/javascript";
	
	document.body.appendChild(tmscript);
	
	var objectTM = document.createElement("object");
	/*objectTM.type = "application/x-shockwave-flash";*/
	/*objectTM.classid = "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000";*/
	objectTM.data = "https://h.online-metrix.net/fp/fp.swf?org_id=" + org_id + "&session_id=" +  sessionID;
	objectTM.width = "1";
	objectTM.height = "1";
	objectTM.id = "thm_fp";
	
	var param = document.createElement("param");
	param.name = "movie";
	param.value = "https://h.online-metrix.net/fp/fp.swf?org_id=" + org_id + "&session_id=" +  sessionID;
	objectTM.appendChild(param);
	
	document.body.appendChild(objectTM);
	
	return sessionID;
 }

