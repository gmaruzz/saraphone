/*
   SaraPhone
   Version: MPL 1.1

   The contents of this file are subject to Mozilla Public License Version
   1.1 (the "License"); you may not use this file except in compliance with
   the License. You may obtain a copy of the License at
   http://www.mozilla.org/MPL/

   Software distributed under the License is distributed on an "AS IS" basis,
   WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
   for the specific language governing rights and limitations under the
   License.

   The Original Code is SaraPhone

   The Initial Developer of the Original Code is
   Giovanni Maruzzelli <gmaruzz@opentelecom.it>
   Portions created by the Initial Developer are Copyright (C) 2020
   the Initial Developer. All Rights Reserved.

   SaraPhone gets its name from Giovanni's wife, Sara.

   Author(s):
   Giovanni Maruzzelli <gmaruzz@opentelecom.it>
   Danilo Volpinari
   Luca Mularoni
 */

'use strict';

var cur_call = null;
var ua;
var which_server;
var isAndroid = false;
var isIOS = false;
var isOnMute = false;
var isOnHold = false;
var clicklogin = "no";
var isRecording = false;
var isDnd = false;
var isNoRing = false;
var isAutoAnswer = false;
var isRegistered = false;
var vmail_subscription = false;
var presence_array = new Array();
var incomingsession = null;
var audioElement = document.createElement('audio');
var callTimer;
var oldext = false;
var gotopanel = false;
var isIncomingCall = false;
var isOutboundCall = false;

var dtmf_options = {
  'duration': 100,
  'interToneGap': 100
};

//http://jsfiddle.net/55Kfu/1506/
//https://stackoverflow.com/posts/13194087/revisions
var beep = (function() {
    var ctxClass = window.audioContext || window.AudioContext || window.AudioContext || window.webkitAudioContext
    var ctx = new ctxClass();
    return function(duration, type, finishedCallback) {

        duration = +duration;

        // Only 0-4 are valid types.
        type = (type % 5) || 0;

        if (typeof finishedCallback != "function") {
            finishedCallback = function() {};
        }

        var osc = ctx.createOscillator();

        //osc.type = type;
        osc.type = "sine";

        osc.connect(ctx.destination);
        if (osc.noteOn) osc.noteOn(0); // old browsers
        if (osc.start) osc.start(); // new browsers

        setTimeout(function() {
            if (osc.noteOff) osc.noteOff(0); // old browsers
            if (osc.stop) osc.stop(); // new browsers
            finishedCallback();
        }, duration);

    };
})();

function tempAlert(msg,duration)
{
     var el = document.createElement("div");
     el.setAttribute("style","position:absolute;top:1%;left:1%;background-color:red;foreground-color:black;");
     el.innerHTML = msg;
     setTimeout(function(){
      el.parentNode.removeChild(el);
      location.reload(true);
     },duration);
     document.body.appendChild(el);
    console.error("TEMPALERT");
}



function onCancelled() {
    audioElement.pause();
    console.log('cancelled');
    $("#isIncomingcall").hide();
    $("#isNotIncomingcall").show();
    incomingsession = null;
    var span = document.getElementById('calling');
    $("#calling_input").val("");
    span.innerText = "...";
}

function onTerminated() {
    audioElement.pause();
    console.log('Onterminated');
    $("#signin").hide();
    $("#dial").show();
    $("#incall").hide();
    $("#ext").val("");
    if (cur_call) {
        cur_call.terminate();
        cur_call = null;
        resetOptionsTimer();
    }
    isOnMute = false;

    incomingsession = null;

    var span = document.getElementById('calling');
    $("#calling_input").val("");
    span.innerText = "...";
}

function onTerminated2() {
    console.log('Onterminated2');
    cur_call = null;
    incomingsession = null;
}

function onAccepted() {
    audioElement.pause();

    $("#signin").hide();
    $("#dial").hide();
    $("#incall").show();

    isOnMute = false;
    $("#mutebtn").removeClass('btn-danger').addClass('btn-warning');

}

$("#asknotificationpermission").click(function() {
    if (isIOS) {
        //do nothing
    } else {
        // Let's check if the browser supports notifications
        if (!("Notification" in window)) {
            alert("This browser does not support desktop notification");
        }

        // Otherwise, we need to ask the user for permission
        // Note, Chrome does not implement the permission static property
        // So we have to check for NOT 'denied' instead of 'default'
        else if (Notification.permission !== 'denied') {
            Notification.requestPermission(function(permission) {

                // Whatever the user answers, we make sure we store the information
                if (!('permission' in Notification)) {
                    Notification.permission = permission;
                }

                // If the user is okay, let's create a notification
                if (permission === "granted") {
                    console.log("Notification Permission Granted!");
                    var notification = new Notification("Notification Permission Granted!");
                    $("#asknotificationpermission").hide();
                }
            });
        } else {
            alert(`Permission is ${Notification.permission}`);
        }

    }
});


function notifyMe(msg) {
    if (isIOS) {
        //do nothing
    } else {
        if (Notification.permission === "granted") {
            console.log(msg);
            let img = 'img/notification.png';
            let notification = new Notification('WebPhone', {
                body: msg,
                icon: img
            });
            notification.onclick = function() {
                parent.focus();
                window.focus();
                this.close();
            };
            notification.onclose = function() {
                parent.focus();
                window.focus();
                this.close();
            };
            notification.onerror = function() {
                parent.focus();
                window.focus();
                this.close();
            };
        }
    }
}


function onRegistered() {
    if (isIOS) {
        //do nothing
    } else {
        if (Notification.permission === "granted") {
            $("#asknotificationpermission").hide();
        }
    }

    $("#signin").hide();
    $("#dial").show();
    $("#incall").hide();
    $("#ext").val("");
    var span = document.getElementById('calling');
    $("#calling_input").val("");
    span.innerText = "...";

    var span = document.getElementById('whoami');
    var txt = document.createTextNode($("#login").val());
    span.innerText = txt.textContent + " (" + $("#yourname").val() + ")";

    isRegistered = true;


    var countpres = 1;

    while (countpres < 61) {
        if ($("#pres" + countpres).val()) {
            presence_array[countpres] = ua.subscribe($("#pres" + countpres).val(), 'presence', {
                expires: 120
            });

            const mycountpres = countpres;
            presence_array[countpres].on('notify', function(notification) {
                //console.log(notification.request.body);

                var presence = notification.request.body.match(/<dm:note>(.*)<\/dm:note>/i);
                if (presence) {
                    var ispresent = presence[1];

                    if (ispresent.match(/unregistered/i)) {
                        $("#pres" + mycountpres + "btn").removeClass('btn-success btn-warning btn-default btn-danger').addClass('btn-danger');
                    } else {
                        if (ispresent.match(/available/i) || ispresent.match(/closed/i)) {
                            $("#pres" + mycountpres + "btn").removeClass('btn-success btn-warning btn-default btn-danger').addClass('btn-success');

                        } else {
                            $("#pres" + mycountpres + "btn").removeClass('btn-success btn-warning btn-default btn-danger').addClass('btn-warning');
                        }
                    }

                    var span = document.getElementById('ispresent' + mycountpres);
                    $("#pres" + mycountpres + "_label").val($("#pres" + mycountpres + "_label").val().substr(0, 10));
                    if (ispresent.match(/available/i) || ispresent.match(/closed/i)) {
                        span.innerText = $("#pres" + mycountpres + "_label").val();
                    } else {
                        span.innerText = $("#pres" + mycountpres + "_label").val() + ": " + ispresent;
                    }
                }

            });



            $("#pres" + mycountpres + "btn").click(function() {
                $("#ext").val($("#pres" + mycountpres).val());
		oldext=$("#ext").val();
                docall();
            });



        } else {

            $("#pres" + countpres + "btn").remove();

        }
        countpres++;
    }

    $("#webphone_blf").show();


    // Once subscribed, receive notifications and handle
    vmail_subscription = ua.subscribe($("#login").val() + '@' + $("#domain").val(), 'message-summary', {
        extraHeaders: ['Accept: application/simple-message-summary'],
        expires: 120
    });
    vmail_subscription.on('notify', handleNotify);

    if (isAndroid || isIOS) {
        $("#calling_input").hide();
    }
}

$("#checkvmailbtn").click(function() {
    $("#extstarbtn").click();
    $("#ext9btn").click();
    $("#ext8btn").click();
    $("#callbtn").click();
});

$("#gotopanel1").click(function() {
    gotopanel = true;
    console.error("GOTOPANEL1");
    window.location.assign('/core/user_settings/user_dashboard.php');
});

$("#gotopanel2").click(function() {
    gotopanel = true;
    console.error("GOTOPANEL2");
    window.location.assign('/core/user_settings/user_dashboard.php');
});

$("#gotopanel3").click(function() {
    gotopanel = true;
    console.error("GOTOPANEL3");
    window.location.assign('/core/user_settings/user_dashboard.php');
});

function handleNotify(r) {
    //console.log(r.request.method);
    //console.log(r.request.body);

    var newMessages = 0;
    var oldMessages = 0;
    var span = document.getElementById('vmailcount');
    var gotmsg = r.request.body.match(/voice-message:\s*(\d+)\/(\d+)/i);
    if (gotmsg) {
        newMessages = parseInt(gotmsg[1]);
        oldMessages = parseInt(gotmsg[2]);
        if (newMessages) {
            $("#checkvmailbtn").removeClass('btn-info').addClass('btn-warning');

        } else {
            $("#checkvmailbtn").removeClass('btn-warning').addClass('btn-info');

        }
        span.innerText = newMessages + "/" + oldMessages;
    }


}


$("#anscallbtn").click(function() {
    audioElement.pause();
    incomingsession.accept({
        media: {
            constraints: {
                audio: {
                    deviceId: {
                        ideal: $("#selectmic").val()
                    }
                },
                video: false
            },
            render: {
                remote: document.getElementById('audio')
            }
        }
    });
    console.log('answered');

    $("#isIncomingcall").hide();
    $("#isNotIncomingcall").show();
    cur_call = incomingsession;
    var span = document.getElementById('speakingwith');
    var txt = document.createTextNode(cur_call.remoteIdentity.displayName.toString());
    span.innerText = txt.textContent + " (" + cur_call.remoteIdentity.uri.user.toString() + ")";

    cur_call.on('accepted', onAccepted.bind(cur_call));
    cur_call.once('bye', onTerminated.bind(cur_call));
    cur_call.once('failed', onTerminated.bind(cur_call));
    cur_call.once('cancel', onTerminated.bind(cur_call));
    cur_call.once('terminated', onTerminated2.bind(cur_call));
});


$("#rejcallbtn").click(function() {
    audioElement.pause();
    incomingsession.reject({
        statusCode: '486',
        reasonPhrase: 'Busy Here 1'
    });
    console.log('rejected');
    $("#isIncomingcall").hide();
    $("#isNotIncomingcall").show();
    var span = document.getElementById('calling');
    $("#calling_input").val("");
    span.innerText = "...";
    incomingsession = null;
});



function handleInvite(s) {
    if (cur_call) {
        s.reject({
            statusCode: '486',
            reasonPhrase: 'Busy Here 2'
        });
    }
    if (isDnd) {
        s.reject({
            statusCode: '486',
            reasonPhrase: 'Busy Here 3'
        });
    } else {
        if (!cur_call) {
            var span = document.getElementById('calling');
            var txt = "---";
            isIncomingCall = true;
            isOutboundCall = false;
	    if(s.remoteIdentity.displayName && s.remoteIdentity.displayName.toString()) {
            	txt = document.createTextNode(s.remoteIdentity.displayName.toString());
	    }
            span.innerText = "CALL FROM: " + txt.textContent + " (" + s.remoteIdentity.uri.user.toString() + ")";
            incomingsession = s;
            $("#isIncomingcall").show();
            $("#isNotIncomingcall").hide();
            incomingsession.once('cancel', onCancelled.bind(incomingsession));

            if (isIOS) {
                //do nothing
            } else {
                notifyMe("CALL FROM: " + txt.textContent + " (" + s.remoteIdentity.uri.user.toString() + ")");
            }

            if (isNoRing == false) {
                audioElement.currentTime = 0;
                audioElement.play();
            }
            if (isAutoAnswer == true) {
                $("#anscallbtn").trigger("click");
            }
        }
    }
}


function docall() {

    if (cur_call) {
        cur_call.terminate();
        cur_call = null;
        resetOptionsTimer();
    }

    isIncomingCall = false;
    isOutboundCall = true;

    cur_call = ua.invite($("#ext").val(), {
        media: {
            constraints: {
                audio: {
                    deviceId: {
                        ideal: $("#selectmic").val()
                    }
                },
                video: false
            },
            render: {
                remote: document.getElementById('audio')
            }
        }
    });

    cur_call.on('accepted', onAccepted.bind(cur_call));
    cur_call.once('failed', function(response, cause) {
        if (cause != "null") {
            console.log(cause);
        } else {
            cause = "N/A";
        }
        var span = document.getElementById('calling');
        onTerminated(cur_call);
        var txt = document.createTextNode(response.status_code + ": " + cause);
        span.innerText = txt.textContent;
    })

    cur_call.once('bye', function(request) {
        if (request.headers.Reason && !(request.headers.Reason["0"].raw.toString().match(/cause=16/)) && !(request.headers.Reason["0"].raw.toString().match(/cause=31/))) {
            console.log(request);
            var span = document.getElementById('calling');
            onTerminated(cur_call);
            var regex = /.*text="(.*)".*/;
            var txt = document.createTextNode(request.headers.Reason["0"].raw.toString().replace(regex, "$1"));
            span.innerText = txt.textContent;
        } else {
            onTerminated(cur_call);
        }
    })
    cur_call.once('cancel', onTerminated.bind(cur_call));

    var span = document.getElementById('speakingwith');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = txt.textContent;
}


$("#dialctrlbtn").click(function() {
    var x = document.getElementById('dialadv1');
    if (x.style.display === 'none') {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
    x = document.getElementById('dialadv2');
    if (x.style.display === 'none') {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }


});

$("#signinctrlbtn").click(function() {
    var x = document.getElementById('signinadv1');
    if (x.style.display === 'none') {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
});



$("#callbtn").click(function() {
    if ($("#ext").val()) {
        var regex1 = /#/g;
        var new_ext = $("#ext").val().replace(regex1, "_");
        $("#ext").val(new_ext);
	oldext=$("#ext").val();
        docall();
    }
});

$("#delcallbtn").click(function() {
    $("#ext").val("");
    $("#calling_input").val("");
    var span = document.getElementById('calling');
    span.innerText = "...";

    $("#hangupbtn").trigger("click");
});


$("#hangupbtn").click(function() {
    if (cur_call) {
        cur_call.terminate();
        cur_call = null;
        resetOptionsTimer();
    }
    $("#br").show();
    $("#ext").show();
    $("#calling_input").val("");
    var span = document.getElementById('calling');
    span.innerText = "...";
});


$("#loginbtn").click(function() {
    init();
});

$("#xferbtn").click(function() {
   if(isOutboundCall==true){
        cur_call.dtmf("*499", dtmf_options);
    }else{
        cur_call.dtmf("*599", dtmf_options);
    }
});

$("#attxbtn").click(function() {
   if(isOutboundCall==true){
        cur_call.dtmf("*699", dtmf_options);
    }else{
        cur_call.dtmf("*799", dtmf_options);
    }
});

$("#mutebtn").click(function() {
    if (isOnMute) {
        cur_call.unmute();
        isOnMute = false;
        $(this).removeClass('btn-danger').addClass('btn-warning');
    } else {
        cur_call.mute();
        isOnMute = true;

        $(this).removeClass('btn-warning').addClass('btn-danger');
    }
});

$("#holdbtn").click(function() {
    if (isOnHold==false){
        isOnHold = true;
        if(isOutboundCall==true){
            cur_call.dtmf("*299", dtmf_options);
        }else{
            cur_call.dtmf("*399", dtmf_options);
        }
        $("#unholdbtn").show();
        console.error("HOLD begins");
    }
});

$("#unholdbtn").click(function() {
    if (isOnHold == true){
        isOnHold = false;
        $("#extstarbtn").click();
        $("#ext6btn").click();
        $("#ext5btn").click();
        $("#ext5btn").click();
        $("#callbtn").click();
        $("#unholdbtn").hide();
        console.error("HOLD ends");
    }
});

$("#redialbtn").click(function() {
    audioElement.pause();
    $("#ext").val(oldext);
    $("#callbtn").click();
});

$("#callbackbtn").click(function() {
    audioElement.pause();
    $("#extstarbtn").click();
    $("#ext6btn").click();
    $("#ext9btn").click();
    $("#callbtn").click();
});

$("#recordcallbtn").click(function() {
    if (isRecording) {
        cur_call.dtmf("*");
        cur_call.dtmf("2");
        isRecording = false;
        $(this).removeClass('btn-danger').addClass('btn-warning');
    } else {
        cur_call.dtmf("*");
        cur_call.dtmf("2");
        isRecording = true;

        $(this).removeClass('btn-warning').addClass('btn-danger');
    }
});

$("#dndbtn").click(function() {
    if (isDnd) {
        isDnd = false;
        $(this).removeClass('btn-danger').addClass('btn-warning');
    } else {
        isDnd = true;
        $(this).removeClass('btn-warning').addClass('btn-danger');
    }
});

$("#ringbtn").click(function() {
    if (isNoRing) {
        isNoRing = false;
        $(this).removeClass('btn-danger').addClass('btn-warning');
    } else {
        isNoRing = true;
        $(this).removeClass('btn-warning').addClass('btn-danger');
    }
});

$("#autoanswerbtn").click(function() {
    if (isAutoAnswer) {
        isAutoAnswer = false;
        $(this).removeClass('btn-danger').addClass('btn-warning');
    } else {
        isAutoAnswer = true;
        $(this).removeClass('btn-warning').addClass('btn-danger');
    }
});



$("#ext1btn").click(function() {
    $("#ext").val($("#ext").val() + "1");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext2btn").click(function() {
    $("#ext").val($("#ext").val() + "2");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext3btn").click(function() {
    $("#ext").val($("#ext").val() + "3");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext4btn").click(function() {
    $("#ext").val($("#ext").val() + "4");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext5btn").click(function() {
    $("#ext").val($("#ext").val() + "5");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext6btn").click(function() {
    $("#ext").val($("#ext").val() + "6");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext7btn").click(function() {
    $("#ext").val($("#ext").val() + "7");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext8btn").click(function() {
    $("#ext").val($("#ext").val() + "8");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext9btn").click(function() {
    $("#ext").val($("#ext").val() + "9");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#ext0btn").click(function() {
    $("#ext").val($("#ext").val() + "0");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#extstarbtn").click(function() {
    $("#ext").val($("#ext").val() + "*");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#extpoundbtn").click(function() {
    $("#ext").val($("#ext").val() + "#");
    var span = document.getElementById('calling');
    var txt = document.createTextNode($("#ext").val());
    span.innerText = "DIALING: " + txt.textContent;
});

$("#dtmf1btn").click(function() {
    cur_call.dtmf("1", dtmf_options);
});

$("#dtmf2btn").click(function() {
    cur_call.dtmf("2", dtmf_options);
});

$("#dtmf3btn").click(function() {
    cur_call.dtmf("3", dtmf_options);
});

$("#dtmf4btn").click(function() {
    cur_call.dtmf("4", dtmf_options);
});

$("#dtmf5btn").click(function() {
    cur_call.dtmf("5", dtmf_options);
});

$("#dtmf6btn").click(function() {
    cur_call.dtmf("6", dtmf_options);
});

$("#dtmf7btn").click(function() {
    cur_call.dtmf("7", dtmf_options);
});

$("#dtmf8btn").click(function() {
    cur_call.dtmf("8", dtmf_options);
});

$("#dtmf9btn").click(function() {
    cur_call.dtmf("9", dtmf_options);
});

$("#dtmf0btn").click(function() {
    cur_call.dtmf("0", dtmf_options);
});

$("#dtmfstarbtn").click(function() {
    cur_call.dtmf("*", dtmf_options);
});

$("#dtmfpoundbtn").click(function() {
    cur_call.dtmf("#", dtmf_options);
});


function resetOptionsTimer() {
/*
    window.clearTimeout(callTimer);

    callTimer = window.setTimeout(function() {
        console.error("NETWORK DISCONNECT, NO OPTIONS SINCE 25000 msec");
        beep(1000, 2);
        if (cur_call) {
            alert("NETWORK DISCONNECT, CLICK OK TO PROCEED");
        }
        $("#hangupbtn").trigger("click");
        if (gotopanel == false){
            location.reload();
        }
    }, 25000);
*/
}

function init() {

    var nameDomain;
    var nameProxy;
    var uri;
    var password;
    var login;
    var yourname;
    var wssport;

    cur_call = null;
    resetOptionsTimer();
    yourname = $("#yourname").val();
    nameDomain = $("#domain").val();
    nameProxy = $("#proxy").val();
    wssport = $("#port").val();
    which_server = "wss://" + nameProxy + ":" + wssport;

    if (yourname === "") {
        yourname = $("#login").val();
    }

    login = $("#login").val();
    password = $("#passwd").val();

    uri = login + "@" + nameDomain;

    //console.error("uri: " + uri);

    ua = new SIP.UA({
        wsServers: which_server,
        uri: uri,
        password: password,
        userAgentString: 'SIP.js/0.7.8 SaraPhone 04',
        traceSip: true,
        displayName: yourname,
        iceCheckingTimeout: 1000,
        registerExpires: 120,
        allowLegacyNotifications: true,
        hackWssInTransport: true,
        wsServerMaxReconnection: 5000,
        wsServerReconnectionTimeout: 1,
        connectionRecoveryMaxInterval: 3,
        connectionRecoveryMinInterval: 2,
        log: {
            level: 2,
            connector: function(level, category, label, content) {
                var str = content;
                var patt2 = new RegExp("WebSocket abrupt disconnection");
                var res2 = patt2.exec(str);
/*
                var patt = new RegExp("OPTIONS sip");
                var res = patt.exec(str);

                if (res) {
                    resetOptionsTimer();
                }
*/
                if (res2) {
                    if (gotopanel == false){
                        console.error('WebSocket ABRUPT DISCONNECTION');
			tempAlert("- WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - WebSocket ABRUPT DISCONNECTION - ",10000);
                    }
                }
            },
        }
    });

    ua.on('notify', handleNotify);
    ua.on('invite', handleInvite);
    ua.on('disconnected', function() {
        console.error('DISCONNECTED');
        //alert("DO YOU HAVE AUTHORIZED SSL CERTS FOR PORT 7443 ???? - READ THE README! :) - NETWORK DISCONNECT, CLICK OK TO PROCEED");
        if (gotopanel == false){
		tempAlert("- NETWORK DISCONNECTED - NETWORK DISCONNECTED - NETWORK DISCONNECTED - NETWORK DISCONNECTED - DO YOU HAVE WSS PORT OPEN ON FIREWALL? DO YOU HAVE AUTHORIZED SSL CERTS? AND YOUR WSS CERTS, ARE AUTHORIZED? - READ THE README! :) - NETWORK DISCONNECTED - NETWORK DISCONNECTED - NETWORK DISCONNECTED - NETWORK DISCONNECTED - ",60000);
        }
    });

    $("#isIncomingcall").hide();

    $(document).keyup(function(event) {
        if (event.keyCode == 13 && !event.shiftKey) {
            if (isRegistered) {
                if (cur_call) {} else {
                    $("#callbtn").trigger("click");
                }
            }
        }
    });

    $(document).keypress(function(event) {
        var key = String.fromCharCode(event.keyCode || event.charCode);
        var i = parseInt(key);
        var tag = event.target.tagName.toLowerCase();
        if (isRegistered) {
            if (cur_call) {
                if (key === "#" || key === "*" || key === "0" || (i > 0 && i <= 9)) {
                    cur_call.dtmf(key, dtmf_options);
                }
            } else {

                if (key === "#" || key === "*" || key === "0" || (i > 0 && i <= 9)) {

                    if (key === "0") $("#ext0btn").click();
                    if (key === "1") $("#ext1btn").click();
                    if (key === "2") $("#ext2btn").click();
                    if (key === "3") $("#ext3btn").click();
                    if (key === "4") $("#ext4btn").click();
                    if (key === "5") $("#ext5btn").click();
                    if (key === "6") $("#ext6btn").click();
                    if (key === "7") $("#ext7btn").click();
                    if (key === "8") $("#ext8btn").click();
                    if (key === "9") $("#ext9btn").click();
                    if (key === "*") $("#extstarbtn").click();
                    if (key === "#") $("#extpoundbtn").click();
                }
            }
        }
    });

    ua.once('registered', onRegistered.bind(cur_call));
    ua.on('unregistered', function() {
        console.error('UNREGISTERED');
        if (gotopanel == false){
		tempAlert("- UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - UNREGISTERED - ",3000);
        }
    });
}

$("#calling_input").keyup(function(event) {
    if (event.keyCode == 13 && !event.shiftKey) {
        $("#ext").val($("#calling_input").val());
        $("#callbtn").trigger("click");
    }
});

/*
window.onbeforeunload = function(e) {
    e = e || window.event;

    console.log("closing window");

    // For IE and Firefox prior to version 4
    if (e) {
        e.returnValue = "Sure?";
    }

    return "Sure?";
};
*/

$(window).load(function() {
    cur_call = null;
    resetOptionsTimer();
    isAndroid = (navigator.userAgent.toLowerCase().indexOf('android') > -1);
    isIOS = /(iPad|iPhone|iPod)/g.test(navigator.userAgent);

    console.log("The doctor is in");
    console.log("Is something troubling you?");


    var url_string = window.location.href; //window.location.href
    var url = new URL(url_string);

    clicklogin = url.searchParams.get("clicklogin");

    $("#signin").hide();
    $("#dial").hide();
    $("#incall").hide();

    $("#controls").hide();
    $("#dialadv1").hide();
    $("#dialadv2").hide();
    $("#unholdbtn").hide();

    $("#yourname").keyup(function(event) {
        if (event.keyCode == 13 && !event.shiftKey) {
            $("#loginbtn").trigger("click");
        }
    });

    $("#passwd").keyup(function(event) {
        if (event.keyCode == 13 && !event.shiftKey) {
            $("#loginbtn").trigger("click");
        }
    });
    $("#login").keyup(function(event) {
        if (event.keyCode == 13 && !event.shiftKey) {
            $("#loginbtn").trigger("click");
        }
    });
    $("#ext").keyup(function(event) {
        if (event.keyCode == 13 && !event.shiftKey) {
            $("#callbtn").trigger("click");
        }
    });


    // Safari requires the user to grant device access before providing
    // all necessary device info, so do that first.
    var constraints = {
        audio: true,
        video: false,
    };
    navigator.mediaDevices.getUserMedia(constraints);

    navigator.mediaDevices.enumerateDevices()
        .then(function(devices) {
            var i = 1;
            var div = document.querySelector("#listmic"),
                frag = document.createDocumentFragment(),
                selectmic = document.createElement("select");

            while (div.firstChild) {
                div.removeChild(div.firstChild);
            }
            i = 1;
            selectmic.id = "selectmic";
            selectmic.style = "background-color: black;";

            devices.forEach(function(device) {


                if (device.kind === 'audioinput') {

                    selectmic.options.add(new Option('Microphone: ' + (device.label ? device.label : (i)), device.deviceId));
                    i++;

                }
            });

            frag.appendChild(selectmic);

            div.appendChild(frag);

        })
        .catch(function(err) {
            console.log(err.name + ": " + err.message);
        });

    document.getElementById("hideAll").style.display = "none";
    $("#signin").show();
    $("#signinadv1").hide();

    $("#webphone_blf").hide();

    if (clicklogin === "yes") {
        $("#loginbtn").trigger("click");
    }
});


$(document).ready(function() {
    audioElement.setAttribute('src', 'mp3/ring.mp3');
    setupCacheHandler();
});

$("#phonebookbtn").click(function() {
    var x = document.getElementById('phonebook');
    if (x.style.display === 'none') {
        x.style.display = 'block';
    } else {
        x.style.display = 'none';
    }
});

var cacheItems = ['login', 'passwd', 'yourname', 'domain', 'proxy', 'port',
    'pres1', 'pres1_label',
    'pres2', 'pres2_label',
    'pres3', 'pres3_label',
    'pres4', 'pres4_label',
    'pres5', 'pres5_label',
    'pres6', 'pres6_label',
    'pres7', 'pres7_label',
    'pres8', 'pres8_label',
    'pres9', 'pres9_label',
    'pres10', 'pres10_label',

];

function setupCacheHandler() {
    for(var i = 0; i < cacheItems.length; i++) {
        var key = cacheItems[i];
        var value = localStorage.getItem("saraphone." + key);
        if (value) document.getElementById(key).value = value;
        $("#" + key).change(function(e) {localStorage.setItem("saraphone." + e.target.id, e.target.value);});
    }
}
