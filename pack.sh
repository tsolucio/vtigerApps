#!/bin/sh
rm evvtApps.zip
cp modules/evvtApps/manifest.xml .
zip -r -x "*.svn*" "*schema.xml" "*hold*" "*~" @ evvtApps.zip manifest.xml modules
rm manifest.xml
