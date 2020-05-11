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
local username = session:getVariable("username");
if(destination_number == nil) then destination_number = '0' end
if(caller_id_number == nil) then caller_id_number = '0' end
local saraphone_destination_user_agent = api:execute("sofia_presence_data", "user_agent internal/"..destination_number.."@"..domain_name);
local saraphone_caller_user_agent = api:execute("sofia_presence_data", "user_agent internal/"..username.."@"..domain_name);

local saraphone_bind = session:getVariable("saraphone_bind");
if(saraphone_bind == nil) then saraphone_bind = "false" end

local saraphone_ringback = session:getVariable("us-ring");
if(saraphone_ringback == nil) then saraphone_ringback = "%(2000,4000,440,480)" end

freeswitch.consoleLog(loglevel, uuid .. " ------------ BEGIN ----------------------------------------------------------\n")

freeswitch.consoleLog(loglevel, uuid .. " domain_name: " .. domain_name .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " destination_number: " .. destination_number .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " caller_id_number: " .. caller_id_number .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " username: " .. username .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_destination_user_agent: " .. saraphone_destination_user_agent .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_caller_user_agent: " .. saraphone_caller_user_agent .. "\n");
freeswitch.consoleLog(loglevel, uuid .. " saraphone_bind: " .. saraphone_bind .. "\n");

if(saraphone_bind == "false") then

	session:execute("export","saraphone_bind=true");
	
	local saraphone_is_caller = string.find(saraphone_caller_user_agent, "SaraPhone");
	if(saraphone_is_caller) then
		saraphone_is_caller = "true"
		session:execute("export","ignore_early_media=false");
		session:setVariable("ringback", saraphone_ringback);
		session:setVariable("instant_ringback", "true");
		session:answer()
		api:execute("msleep", "1000");
	end
	
	session:execute("export","saraphone_is_both=true");
	
	session:execute("bind_digit_action","saraphone_local,*299,exec:execute_extension,saraphone_hold XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*399,exec:execute_extension,saraphone_hold XML ${context},peer,peer");
	session:execute("bind_digit_action","saraphone_local,*499,exec:execute_extension,saraphone_dx XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*599,exec:execute_extension,saraphone_dx XML ${context},peer,peer");
	session:execute("bind_digit_action","saraphone_local,*699,exec:execute_extension,saraphone_att_xfer XML ${context},aleg,bleg");
	session:execute("bind_digit_action","saraphone_local,*799,exec:execute_extension,saraphone_att_xfer XML ${context},peer,peer");
	session:execute("digit_action_set_realm","saraphone_local");
end

freeswitch.consoleLog(loglevel, uuid .. " ------------ END   ----------------------------------------------------------\n")


------------------------------------------------------------------------
------------------------------------------------------------------------
------------------------------------------------------------------------

