#!/bin/bash

hascbf=$(which phpcbf);
if [ "" == "$hascbf" ]; then
	echo "Install PHP Code Beautifier"
	exit 1
fi
if [[ ! -f ./phpcs.ruleset.xml ]]; then
	echo "No ruleset file, aborting"
	exit 1
fi

phpcbf $(find . -name "*.php") --standard=./phpcs.ruleset.xml
