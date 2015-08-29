#!/bin/bash

#REMOVE THE DIRECTORIES AND FILES USED FOR THE EXTENSION 
rm -rf /usr/share/mediawiki/extensions/Latch
ls -lisah /usr/share/mediawiki/extensions/

#REMOVE NAME FILES FROM LocalSettings.php
cat /usr/share/mediawiki/LocalSettings.php | grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/PHP_SDK/Error.php""'"";" | 
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/LatchController.php""'"";" | 
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/LatchAccount.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/dbHelper.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/LatchConfig.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/PHP_SDK/Latch.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/PHP_SDK/LatchResponse.php""'"";" > /usr/share/mediawiki/temp.txt

mv /usr/share/mediawiki/LocalSettings.php /usr/share/mediawiki/LocalSettings.php.old
mv /usr/share/mediawiki/temp.txt /usr/share/mediawiki/LocalSettings.php
chown -R root /usr/share/mediawiki/LocalSettings.php
chgrp -R root /usr/share/mediawiki/LocalSettings.php
chmod -R 775 /usr/share/mediawiki/LocalSettings.php

#REMOVE THE LATCH TABLE FROM THE MEDIAWIKI DATABASE
echo "*****************************************************************************************************"
echo "Find the file LocalSettings.php under /usr/share/mediawiki and find the section Database settings"
echo -n "Enter the server name: "
read DBserverName
echo -n "Enter the wiki name: "
read DBwikiName 
echo "You will be asked for the root password to enter MySQL"
mysql -u root -h $DBserverName -p << EOF
use $DBwikiName;
DROP TABLE latch;
DESCRIBE latch;
EOF
