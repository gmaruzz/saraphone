--        Giovanni Maruzzelli <gmaruzz@gmail.com>

--prepare the api object
api = freeswitch.API();

local loglevel = "debug"
session:setAutoHangup(false);

------------------------------------------------------------------------
------------------------------------------------------------------------
------------------------------------------------------------------------


--connect to the database
local Database = require "resources.functions.database";
local dbh = Database.new('switch')


--get the variables
local uuid = session:getVariable("uuid");
local domain_name = session:getVariable("domain_name");
local destination_number = session:getVariable("destination_number");
local caller_id_number = session:getVariable("caller_id_number");
if(destination_number == nil) then destination_number = '0' end
if(caller_id_number == nil) then caller_id_number = '0' end
local saraphone_destination_user_agent = api:execute("sofia_presence_data", "user_agent internal/"..destination_number.."@"..domain_name);
local saraphone_caller_user_agent = api:execute("sofia_presence_data", "user_agent internal/"..caller_id_number.."@"..domain_name);

local saraphone_bind = session:getVariable("saraphone_bind");
if(saraphone_bind == nil) then saraphone_bind = "false" end

freeswitch.consoleLog(loglevel, uuid .. " ------------ BEGIN ----------------------------------------------------------\n")

freeswitch.consoleLog(loglevel, uuid .. " domain_name: " .. domain_name .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " destination_number: " .. destination_number .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " caller_id_number: " .. caller_id_number .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_destination_user_agent: " .. saraphone_destination_user_agent .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_caller_user_agent: " .. saraphone_caller_user_agent .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_bind: " .. saraphone_bind .. "\n");

if(saraphone_bind == "false") then
session:setVariable("saraphone_bind", "true");

local saraphone_is_destination = string.find(saraphone_destination_user_agent, "SaraPhone");
if(saraphone_is_destination) then
	saraphone_is_destination = "true"
else
	saraphone_is_destination = "false"
end

local saraphone_is_caller = string.find(saraphone_caller_user_agent, "SaraPhone");
if(saraphone_is_caller) then
	saraphone_is_caller = "true"
	-- XXX why next line breaks call ? XXX
	--session:execute("export","absolute_codec_string='PCMA,PCMU'");
	session:execute("export","ignore_early_media=false");
	session:setVariable("ringback", "${us-ring}");
	-- XXX why next line breaks call ? XXX
	--session:setVariable("transfer_ringback", "${us-ring}");
	session:setVariable("instant_ringback", "true");
	session:answer()
	api:execute("msleep", "1000");
else
	saraphone_is_caller = "false"
end

local saraphone_is_both = "false"
if( saraphone_is_destination == "true" and saraphone_is_caller == "true" ) then
	saraphone_is_both ="true"
	saraphone_is_destination ="false"
	saraphone_is_caller ="false"
else
	saraphone_is_both ="false"
end

freeswitch.consoleLog(loglevel, uuid .. " saraphone_is_destination: " .. saraphone_is_destination .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_is_caller: " .. saraphone_is_caller .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_is_both: " .. saraphone_is_both .. "\n");

session:execute("export","saraphone_is_destination=" .. saraphone_is_destination);
session:execute("export","saraphone_is_caller=" .. saraphone_is_caller);
session:execute("export","saraphone_is_both=" .. saraphone_is_both);

if(saraphone_is_both == "true") then
	session:execute("bind_digit_action","saraphone_local,*2,exec:execute_extension,saraphone_hold XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*3,exec:execute_extension,saraphone_hold XML ${context},peer,peer");
	session:execute("bind_digit_action","saraphone_local,*4,exec:execute_extension,saraphone_dx XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*5,exec:execute_extension,saraphone_dx XML ${context},peer,peer");
	session:execute("bind_digit_action","saraphone_local,*6,exec:execute_extension,saraphone_att_xfer XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*7,exec:execute_extension,saraphone_att_xfer XML ${context},peer,peer");
	session:execute("digit_action_set_realm","saraphone_local");
elseif(saraphone_is_caller == "true") then
	session:execute("bind_digit_action","saraphone_local,*2,exec:execute_extension,saraphone_hold XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*4,exec:execute_extension,saraphone_dx XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*6,exec:execute_extension,saraphone_att_xfer XML ${context},aleg,bleg");
	session:execute("digit_action_set_realm","saraphone_local");
elseif(saraphone_is_destination == "true") then
	session:execute("bind_digit_action","saraphone_local,*3,exec:execute_extension,saraphone_hold XML ${context},peer,peer");
	session:execute("bind_digit_action","saraphone_local,*5,exec:execute_extension,saraphone_dx XML ${context},peer,peer");
	session:execute("bind_digit_action","saraphone_local,*7,exec:execute_extension,saraphone_att_xfer XML ${context},peer,peer");
	session:execute("digit_action_set_realm","saraphone_local");
end

end

freeswitch.consoleLog(loglevel, uuid .. " ------------ END   ----------------------------------------------------------\n")


------------------------------------------------------------------------
------------------------------------------------------------------------
------------------------------------------------------------------------

