/**
 * buckeyeAlert library
 * Ohio State University - Interactive Communications
 * http://ucom.osu.edu
 */

function buckeyeAlert(settings) {
    if(typeof(settings)==='undefined') settings = new Object();
    if(typeof(settings.element_id)==='undefined') settings.element_id = "buckeye_alert";
    if(typeof(settings.url)==='undefined') settings.url = "//www.osu.edu/feeds/emergency-alert.rss";
    if(typeof(settings.callback)==='undefined') settings.callback = function () { };
    if(typeof(settings.displayType)==='undefined') settings.display = "block";

    var container = document.getElementById(settings.element_id);
    container.setAttribute("aria-live", "polite");

    if(window.XDomainRequest){ // For IE

        var xdr = new XDomainRequest();
        xdr.open("GET", settings.url);
        xdr.onprogress = function () { };
        xdr.ontimeout = function () { };
        xdr.onerror = function () { };
        xdr.onload = function() {
            var response;
            if (window.DOMParser) {
                var parser = new window.DOMParser();
                response = parser.parseFromString(xdr.responseText, "text/xml");
            } else {
                response = new ActiveXObject("Microsoft.XMLDOM");
                response.async = false;
                response.loadXML(xdr.responseText);
            }
            displayResponse(response);
        }
        setTimeout(function () {xdr.send();}, 0);

    } else {
        var xmlhttp = new XMLHttpRequest();

        xmlhttp.onreadystatechange = function() {
            if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                displayResponse(xmlhttp.responseXML);
            }
        }

        xmlhttp.open("GET", settings.url, true);
        xmlhttp.send();
    }

    function displayResponse(response) {
        var items = response.getElementsByTagName("item");

        if (items.length) {
            var heading = document.createElement("h2");
            heading.className = "osu-semantic";
            heading.innerHTML = "Emergency alert message";

            var message = document.createElement("div");
            message.className = settings.messageClass;
            message.setAttribute("id", "buckeye_alert_msg");

            for (var i=0; i<items.length; i++) {
                var description = items[i].getElementsByTagName("description")[0];
                var thisMessage = description.textContent || description.text;
                if (thisMessage != 'undefined') {
                    message.innerHTML += thisMessage;
                }
            }

            var container = document.getElementById(settings.element_id);
            container.style.display = settings.display;
            container.removeAttribute("hidden");

            if (container.childNodes[0]) {
                container.insertBefore(message, container.childNodes[0]);
            } else {
                container.appendChild(message);
            }
            container.insertBefore(heading, container.childNodes[0]);

            settings.callback();
        }
    }
}
