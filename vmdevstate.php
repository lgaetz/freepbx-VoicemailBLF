#!/usr/bin/php -q
<?php

//    This is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    This is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//
// Copyright (C) 2013 PBX Open Source Software Alliance
// Version History
// 0.0.1 issued by lgaetz June 1, 2013
// 0.0.2 issued by lgaetz June 3, 2013


//************** User configuration options *****************

// this script only checks a singel voice mail context for active mailboxes, the $vmcontext variable is used to select which context
$vmcontext = "default";    

// The user can choose what the device state statuses are set to for indicating if VM is waiting or not
// available options: UNKNOWN | NOT_INUSE | INUSE | BUSY | INVALID | UNAVAILABLE | RINGING | RINGINUSE | ONHOLD
$freedevstate = "NOT_INUSE";
$busydevstate = "BUSY";

// This prefix will be added to the front of the mailbox extension number for the name of the device state.
$prefixdevstate = "Custom:MWI";

//************** End configuration options *****************


// include FreePBX bootstrap, requires FreePBX 2.9+
if (!@include_once(getenv('FREEPBX_CONF') ? getenv('FREEPBX_CONF') : '/etc/freepbx.conf')) { 
      include_once('/etc/asterisk/freepbx.conf'); 
}

// capture output from asterisk command "voicemail show users for default"
$rawvalue = $astman->command('voicemail show users for '.$vmcontext);
//print_r( $rawvalue);
$rawvalue['data'] = preg_replace ( "/[\d]*? voicemail users configured\./" , " " , $rawvalue['data']); 

// convert raw asterisk output into 2D array of extension mailboxes and number of messages waiting
// this parses output from Asterisk 1.8, may or may not work on later versions.
$chan_array = explode("default",$rawvalue['data']);  
$vmarray = array();
$i = 0;
foreach($chan_array as $foo) {
	$i = $i +1;
	$foo = trim($foo);
	preg_match("/^(\d*?) .*$/",$foo,$bar1);
	preg_match("/^.*? .* (.*)$/",$foo,$bar2);
	$vmarray[$i]['ext'] = $bar1[1];
	$vmarray[$i]['vm']  = $bar2[1];
}

// for debug
// print_r($vmarray);

// get voicemail prefix feature code from FreePBX registry
// script currently does not use it, but variable is available if needed
$fcc = new featurecode('voicemail', 'dialvoicemail');
$vmfeaturecode = $fcc->getCodeActive(); 

// step through each mailbox found, and set the device state depending on whether messages are waiting or not.
foreach ($vmarray as $foo) {
	if ($foo['vm'] == 0) {
		$bar = $astman->command("devstate change ".$prefixdevstate.$foo['ext']." ".$freedevstate);
	} else {
		$bar = $astman->command("devstate change ".$prefixdevstate.$foo['ext']." ".$busydevstate);
	}
}

?>