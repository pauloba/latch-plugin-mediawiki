#!/bin/bash

#CREATE DIRECTORIES FOR THE EXTENSION 
mkdir /usr/share/mediawiki/extensions/latch
mkdir /usr/share/mediawiki/extensions/latch/PHP_SDK
mkdir /usr/share/mediawiki/extensions/latch/i18n

#COPY ALL THE FILES
TARGET=/usr/share/mediawiki/
rsync -avz extensions $TARGET
rsync -avz docs $TARGET

#ADD INCLUDES TO LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/LatchController.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/LatchAccount.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/dbHelper.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/LatchConfig.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/PHP_SDK/Latch.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/PHP_SDK/LatchResponse.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/latch/PHP_SDK/Error.php"\'";" >> /usr/share/mediawiki/LocalSettings.php

#CHANGE THE PERMISSIONS, OWNER AND GROUP FOR THE FILES
chown -R root /usr/share/mediawiki/extensions/latch
chgrp -R root /usr/share/mediawiki/extensions/latch
chmod -R 775 /usr/share/mediawiki/extensions/latch

chown -R root /usr/share/mediawiki/docs/LatchExtension.txt
chgrp -R root /usr/share/mediawiki/docs/LatchExtension.txt
chmod -R 744 /usr/share/mediawiki/docs

#CREATE THE LATCH TABLE INTO THE MEDIAWIKI DATABASE
echo "*****************************************************************************************************"
echo "Find the file LocalSettings.php under /usr/share/mediawiki and find the section Database settings"
echo -n "Enter the server name: "
read DBserverName
echo -n "Enter the wiki name: "
read DBwikiName 
echo "You will be asked for the root password to enter MySQL"
mysql -u root -h $DBserverName -p << EOF
use $DBwikiName;
CREATE TABLE latch ( mw_user_id INT NOT NULL, account_id VARCHAR(256) );
CREATE INDEX mw_user_id ON latch(mw_user_id);
SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'latch';
DESCRIBE latch;
EOF
echo "*****************************************************************************************************"
echo "If you see the description of the table in the line above this, the table was created successfully."
echo "Otherwise please create the table manually."
echo "Read install.txt section [2.5] for further details." 
echo "Be aware that this installation script only works if your Mediawiki database is MySQL."
