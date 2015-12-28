// AJAX Nominatim Call
// 16.04.2011
// Gerit Wissing

function GetXmlHttpObject() {
    try {
        // Firefox. Opera, Sarafi
        try {  // get firefox to do cross domain ajax
            netscape.security.PrivilegeManager.enablePrivilege("UniversalBrowserRead");
            netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
        } catch (err) {
            //alert("Error initializing XMLHttpRequest.\n"+err); // show error
        }
        xmlHttp = new XMLHttpRequest(); // instantiate it regardless of security
    }
    catch(err) {
        // Internet Explorer
        try {
            xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
        } catch(err) {
            xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
    }
    return xmlHttp;
}

function ajax(url, callback, infotext) {
    var xmlhttp = GetXmlHttpObject();
    //alert(url);
    if (xmlhttp) {
        //alert('doit');
        xmlhttp.open("GET", url, true);
        xmlhttp.onreadystatechange=function(){
            //alert('readyState = ' + xmlhttp.readyState + ' / status = ' + xmlhttp.status);
            if ( xmlhttp.readyState == 4  ) {
                //alert(xmlhttp.responseText);
                callback(xmlhttp, infotext);
            }
        }
        xmlhttp.send(null);
    }
}

function nominatim(searchtext) {
    var url='./api/nominatim.php?q='+searchtext;
    ajax(url, nominatim_callback, infotext=searchtext);
}

function nominatim_callback(xmlHttp, infotext) {
    if ( xmlHttp.status == 0 ) {
        alert('"'+infotext+' not found.');
    } else if ( xmlHttp.status == 200 ) {
        if ( xmlHttp.responseXML.getElementsByTagName('place')[0] ) {   // is one place returned?
            addSearchResults(xmlHttp);
        } else {
            alert('"'+infotext+'" not found.');
        }
    }
}


// in addition to the CC-BY-SA of the wiki feel free to use the following source for any purpose without restrictions (PD)
// credits and additions appreciated: http://wiki.openstreetmap.org/wiki/User:Stephankn

function checkJOSM(version){
   alert(version.application + " uses protocol version " + version.protocolversion.major + "." + version.protocolversion.minor);
   // do something useful, maybe showing edit button
}

function getJOSMVersion() {
    var url = "http://127.0.0.1:8111/version";
    var useFallback = false;
    // currently FF3.5, Safari 4 and IE8 implement CORS
    if (XMLHttpRequest) {
       var request = new XMLHttpRequest();
       if ("withCredentials" in request) {
          request.open('GET', url, true);
          request.onreadystatechange = function(){
             if (request.readyState != 4) {
                return;
             }
             if (request.status == 200) {
                checkJOSM(eval('(' + request.responseText + ')'));
             }
          };
          request.send();
       }
       else if (XDomainRequest) {
          var xdr = new XDomainRequest();
          try {
             xdr.open("get", url);
             xdr.onload = function(){
                checkJOSM(eval('(' + xdr.responseText + ')'));
             };
             xdr.send();
          } catch (e) {
             useFallback = true;
          }
       } else {
          useFallback = true;
       }
    }
    else {
       // no XMLHttpRequest available
       useFallback = true;
    }

    if (useFallback) {
       // Use legacy jsonp call
       var s = document.createElement('script');
       s.src = url + '?jsonp=checkJOSM';
       s.type = 'text/javascript';

       if (document.getElementsByTagName('head').length > 0) {
          document.getElementsByTagName('head')[0].appendChild(s);
       }
    }
}

function josm_call() {
    var left    = x2lon( map.getExtent().left   ).toFixed(5);
    var right   = x2lon( map.getExtent().right  ).toFixed(5);
    var top    = y2lat( map.getExtent().top    ).toFixed(5);
    var bottom  = y2lat( map.getExtent().bottom ).toFixed(5);
    var baseUrl = 'http://127.0.0.1:8111/load_and_zoom?left='+left+'&right='+right+'&top='+top+'&bottom='+bottom;
    // IE 9 + localhost ajax GEHT NICHT, daher Fallback:
    //window.open (baseUrl);
    document.getElementById('josm_call_iframe').src=baseUrl;
}
