Voicmail BLF
============

version 0.0.1

Script that polls all configured mailboxes, and sets a custom device state for each reflecting if there are messages waiting or not. The device states can be monitored by setting hints manually in extensions_custom.conf and BLF buttons on phones can be programmed to show the Voice mailbox status.


Requirements:
FreePBX 2.9 or higher

Installation:
* Download the script vmdevstate.php and save it to /var/lib/asterisk/agi-bin/  change ownership to asterisk:asterisk
* In FreePBX, Voicemail Admin, settings in the field externnotify, add '/var/lib/asterisk/agi-bin/vmdevstate.php' (without quotes)
* In /etc/asterisk/extensions_custom.conf under the [from-internal-custom] heading, add a lines like this for each mailbox you need want a hint. Substitue the actual voicemail dial prefix in place of the *98 and the actual extension number:

```
[from-internal-custom]

; set voicemail hint for ext 201
exten => *98201,hint,Custom:MWI201

; set voicemail hint for ext 202
exten => *98202,hint,Custom:MWI202
```

* On your phone, if you want to monitor the status of Mailbox 202, program a BLF as *98202