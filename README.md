What is SaraPhone?
--------------------------------------

SaraPhone is an open source bare bone SIP WebRTC office phone (no video), complete with most features real companies want to use in real world: HotDesking, Redial, BLFs, MWI, DND, PhoneBook, Hold, Transfer, Mute, Attended Transfer, Notifications, running on all Browsers both on Desktop and SmartPhone.

SaraPhone is fully integrated with FusionPBX, the full-featured domain based multi-tenant PBX and voice switch for FreeSwitch.

Based on SIP.js, SaraPhone works with all WebRTC compliant SIP proxies, gateways, and servers (FreeSWITCH, Asterisk, OpenSIPS, Kamailio, etc).

Initial author is Giovanni Maruzzelli, and SaraPhone gets its name from Giovanni's wife, Sara.


In addition to providing all of the usual DeskPhone functionality, SaraPhone got:

- Desktop Notification for Incoming Calls
- Live MWI update
- Real Time BLFs status update
- BLF click to call
- Caller Name and Number Display
- Call Error Cause Display
- AutoAnswer
- Network Disconnect Reload
- Show and Set Caller-ID (incoming-outbound)

Software Requirements
--------------------------------------

- FusionPBX
- or
- WSS SIP Server (FreeSWITCH, Asterisk, OpenSIPS, Kamailio, etc) + Web Server (Apache, Nginx, etc)

How to Install SaraPhone on FusionPBX
----------------------------

YOU **REALLY** NEED TO DO **ALL** FOLLOWING STEPS

**ALL THOSE BORING FOLLOWING STEPS**

**SAD, BUT TRUE**

**1 As root do the following:**

```
cd /var/www/fusionpbx/app;
git clone https://github.com/gmaruzz/saraphone.git;
chown -R www-data:www-data saraphone;
```

**2 Login as superadmin to your FusionPBX Web GUI,**

Menu->Advanced->Upgrade, check:
- App Defaults
- Menu Defaults
- Permission Defaults

then click "Execute"

**3 Then, go to**

Menu->Advanced->Default Settings
SaraPhone settings:
- wss_proxy SIP external IP Address of FusionPBX server

then click "Reload"

**4 Go to Menu->Advanced->Sip Profiles**

click on "internal", then:
- liberal-dtmf true true
- send-message-query-on-register true true
- send-presence-on-register true true
- wss-binding :7443 true

**5 Go to menu->status->sipstatus**

- click fluchcache
- click reloadxml


**6 You NEED well working letsencrypt SSL certificates:**

```
cd /usr/src/fusionpbx-install.sh/debian/resources/
./letsencrypt.sh

cat /etc/dehydrated/certs/XXX/fullchain.pem /etc/dehydrated/certs/XXX/privkey.pem > /etc/freeswitch/tls/wss.pem

```
**7 then restart FreeSWITCH:**

```
systemctl restart freeswitch;
```

**8 For well working MWI:**

edit /etc/freeswitch/autoload_configs/lua.conf.xml, uncomment line:
```
<param name="startup-script" value="app/voicemail/resources/scripts/mwi_subscribe.lua"/>
```

**9 then restart FreeSWITCH:**

```
systemctl restart freeswitch;
```

**10 check USERs, EXTENSIONs, DEVICEs**

User **MUST** have one or more **EXTENSION** assigned to her, and at least one of such extensions **MUST** be assigned to a **DEVICE** (you can create a fake device making up the macaddress).

SaraPhone will get its config from the **DEVICE** so, you want to configure the BLFs in the DEVICE page (menu->Accounts->Devices).

Saraphone will not care about "Port" and "Transport" settings in the DEVICE page. Saraphone will always use WSS transport, and the port defined in menu->Advanced->Default Settings->saraphone.

(optional: for best looking results, in the menu->Accounts->Extensions extension page, set effective-caller-id-name)


**11 Logout from FusionPBX and login as a normal user, you will find:**

Menu->Apps->SaraPhone


**12 Desktop Notifications of incoming calls**

To allow for desktop notifications of incoming calls, click on "Allow Notification" on the bottom of SaraPhone web page


**13 Upgrading After Install**

```
cd /var/www/fusionpbx/app/saraphone;
git stash; git pull; git stash apply
```
often, and you will get latest features/bigfixes, and maintain your own modifications


How to Install SaraPhone on WSS SIP Server + Web Server
----------------------------

* As root go into HTML directory of your webserver, and:

```
git clone https://github.com/gmaruzz/saraphone.git;
chown -R www-data:www-data saraphone;
```
then edit saraphone.html to preset WSS proxy address and port, and the SIP domain.

You can then access SaraPhone at:
```
https://your.webserver.address/saraphone/saraphone.html
```
<!--
WSS BLFs on FreeSWITCH
----------------------------
At this moment (2020-04-06) BLFs in FreeSWITCH are not working on SIP via WSS (bug filed: https://github.com/signalwire/freeswitch/issues/398 ).

While the problem is getting fixed in an elegant way upstream, you can apply the quick and dirty patch included into SaraPhone sources (saraphone/patch.diff) and recompile FreeSWITCH mod_sofia.
```
cd /usr/src/freeswitch;
git apply patch.diff;
make mod_sofia-install;
systemctl restart freeswitch;
```
-->


DON'T: Self -Signed SSL certs
----------------------------

DON'T: To authorize self-signed certificates (only for test) for WSS, from your browser (works on Opera and FireFox, Chrome does not accept self signed WSS at all) go to:
```
https://your.fusionpbx.address:7443/
```
and force the browser to accept (I understand the risks, etc)


FAQs, PROBLEMs, Troubleshooting
----------------------------

**Q:** There is a sensible delay in establishing audio after call is connected

**A:** Check if you have two network interfaces (eg: Ethernet and VPN on PCs, or WiFi and Data on Cells) active at same moment. ICE gathering is confused by two Net interfaces. Disable "Data always on" on smartphones, so you will have either WiFi OR Data at each single moment.

**Q:** In FusionPBX, I want to click on VoiceMail/Messages button and go straight to my messages, no login no password

**A:** Into saraphone.js, edit the lines:
```
$("#checkvmailbtn").click(function() {
    $("#extstarbtn").click();
    $("#ext9btn").click();
    $("#ext8btn").click();
    $("#callbtn").click();
});

```
to become:
```
$("#checkvmailbtn").click(function() {
    $("#extstarbtn").click();
    $("#ext9btn").click();
    $("#ext7btn").click();
    $("#callbtn").click();
});

```

eg, it will call *97 instead of *98

then edit the dialplan extension named vmain_user (*97) and add:

```
action set voicemail_authorized=true
```

at order 37 (before app.lua voicemail.lua)

**Q:** I want to use SaraPhone with multiple "Internel" SIP Profiles in FusionPBX

**A:** You must edit BOTH your SIP Profiles AND your Domains:

SIP Profiles:

menu->Advanced->Sip Profiles

for each "internal" Sip Profile:

wss-binding :74XX True

#note the colon in the port value, sao is colon then portnumber, XX is a number

DOMAINS:

menu->advanced->domains

click on a domainname

for each domainname

go at bottom right of page

click on Add (domain setting)

Category: saraphone

Subcategory: wss_port

Type: text

Value: the port number (no colon) you assigned to the profile of this domain

Enabled: True



SCREENSHOTS !
----------------------------

![saraphone_01](https://user-images.githubusercontent.com/331862/79241436-5758d600-7e73-11ea-92ce-7522db44fe63.jpg)
![saraphone_02](https://user-images.githubusercontent.com/331862/79241434-5627a900-7e73-11ea-8196-549379e603ec.jpg)
![saraphone_03](https://user-images.githubusercontent.com/331862/79241430-558f1280-7e73-11ea-9994-c8b9d48a587d.jpg)
