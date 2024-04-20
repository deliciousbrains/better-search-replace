build-translations:
	grunt makepot

build-css:
	grunt cssmin

uglify:
	grunt uglify

zip:
	./build-cfg/build-plugin.sh

package: build-translations build-css uglify zip

publish: build-translations build-css uglify
	./build-cfg/build-plugin.sh better-search-replace --publish