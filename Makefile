PHP_SRC := better-search-replace.php $(wildcard includes/*.php) $(wildcard ext/*.php)
POT_OBJ := languages/better-search-replace.pot
CSS_DIR := assets/css
CSS_ALL := $(wildcard $(CSS_DIR)/*.css)
CSS_SRC := $(filter-out %.min.css,$(CSS_ALL))
CSS_OBJ := $(CSS_SRC:.css=.min.css)
JS_DIR := assets/js
JS_ALL := $(wildcard $(JS_DIR)/*.js)
JS_SRC := $(filter-out %.min.js,$(JS_ALL))
JS_OBJ := $(JS_SRC:.js=.min.js)
JS_MAP := $(JS_SRC:.js=.min.js.map)

.PHONY: all
all: build-translations build-css uglify

.PHONY: build-translations
build-translations: $(POT_OBJ)

$(POT_OBJ): $(PHP_SRC) | node_modules
	grunt makepot

.PHONY: build-css
build-css: $(CSS_OBJ)

$(CSS_OBJ): %.min.css: %.css | node_modules
	grunt cssmin

.PHONY: uglify
uglify: $(JS_OBJ)

$(JS_OBJ): %.min.js: %.js | node_modules
	grunt uglify

.PHONY: zip
zip: all
	./build-cfg/build-plugin.sh

.PHONY: package
package: zip

.PHONY: install
install: node_modules

node_modules: package.json
	yarn install

.PHONY: update-deps
update-deps:
	yarn upgrade

.PHONY: product-info
product-info:
	php build-cfg/utils/create-json.php README.txt better-search-replace.php > builds/info.json

.PHONY: clean
clean:
	rm -rf builds
	rm -f $(CSS_OBJ)
	rm -f $(JS_OBJ) $(JS_MAP)

.PHONY: clean-all
clean-all: clean
	rm -rf node_modules
