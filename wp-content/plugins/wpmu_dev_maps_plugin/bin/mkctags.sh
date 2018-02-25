#/bin/bash

hasctags=$(which ctags);
if [ "" == "$hasctags" ]; then
	echo "Install ctags"
	exit 1
fi

find wpmu-dev-maps-plugin_DIR \
	-type f \
	-regextype posix-egrep \
	-regex ".*\.(php|js)" \
	! -path "*/.git*" \
	! -path "*/node_modules/*" \
	! -path "*/build/*"  \
	! -path "*/wp-content/uploads/*" \
	! -path "*/*.min.js" \
| ctags -f "wpmu-dev-maps-plugin_DIR/wpmu-dev-maps-plugin.tags" --fields=+KSn -L -
