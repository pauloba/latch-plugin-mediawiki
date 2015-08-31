#!/bin/bash

#CREATE DIRECTORIES FOR THE EXTENSION 
mkdir /usr/share/mediawiki/extensions/Latch
mkdir /usr/share/mediawiki/extensions/Latch/PHP_SDK
mkdir /usr/share/mediawiki/extensions/Latch/i18n

#COPY ALL THE FILES
TARGET=/usr/share/mediawiki/
rsync -avz ../extensions $TARGET
rsync -avz ../docs $TARGET

#REMOVE INCLUDES FROM LocalSettings.php IN CASE A PREVIOUS INSTALLATION HAS BEEN RAN UNSUCCESFULLY BEFORE
cat /usr/share/mediawiki/LocalSettings.php | grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/PHP_SDK/Error.php""'"";" | 
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/LatchController.php""'"";" | 
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/LatchAccount.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/dbHelper.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/LatchConfig.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/PHP_SDK/Latch.php""'"";" |
grep -v "require_once ""'""/usr/share/mediawiki/extensions/Latch/PHP_SDK/LatchResponse.php""'"";" > /usr/share/mediawiki/temp.txt

rm -rf /usr/share/mediawiki/LocalSettings.php
mv /usr/share/mediawiki/temp.txt /usr/share/mediawiki/LocalSettings.php
chown -R root /usr/share/mediawiki/LocalSettings.php
chgrp -R root /usr/share/mediawiki/LocalSettings.php
chmod -R 775 /usr/share/mediawiki/LocalSettings.php

#ADD INCLUDES TO LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/LatchController.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/LatchAccount.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/dbHelper.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/LatchConfig.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/PHP_SDK/Latch.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/PHP_SDK/LatchResponse.php"\'";"  >> /usr/share/mediawiki/LocalSettings.php
echo "require_once "\'"/usr/share/mediawiki/extensions/Latch/PHP_SDK/Error.php"\'";" >> /usr/share/mediawiki/LocalSettings.php

#CHANGE THE PERMISSIONS, OWNER AND GROUP FOR THE FILES
chown -R root /usr/share/mediawiki/extensions/Latch
chgrp -R root /usr/share/mediawiki/extensions/Latch
chmod -R 775 /usr/share/mediawiki/extensions/Latch

chown -R root /usr/share/mediawiki/docs/LatchExtension.txt
chgrp -R root /usr/share/mediawiki/docs/LatchExtension.txt
chmod -R 744 /usr/share/mediawiki/docs

#CREATE THE LATCH TABLE INTO THE MEDIAWIKI DATABASE
echo "*****************************************************************************************************"
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
echo "*****************************************************************************************************"
echo "If you see the description of the table in the line above this, the table was created successfully."
echo "Otherwise please create the table manually. Read linux_install.txt section [2.5]"
echo ""
echo ":::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::"
echo ":::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::"
echo "	Warning! ~ Achtung! ~ Uwaga! ~ Tähelepanu! ~ Atenție! ~ Pozor! ~ Atenção! ~ Dikkat! ~ Huomio! ~ Attenzione!	 "
echo ":::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::"
echo ":::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::"
echo "Now you need to get an application ID and application secret."
echo "Go to the developer area at https://latch.elevenpaths.com to get them."
echo "Copy&paste the appId and the secret from the web into the file LatchConfig.php"
