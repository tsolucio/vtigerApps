#!/bin/sh
rm evvtApps.zip
zip -r -x "*.svn*" "*schema.xml" "*hold*" "*~" @ evvtApps.zip manifest.xml modules
