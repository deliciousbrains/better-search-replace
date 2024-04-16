#!/usr/bin/env bash

function usage() {
	echo "Usage: $0 [ slug1 slug2 ... ]"
	exit 1
}

SCRIPT_ARGS="$@"
PROJECT_ROOT="$( cd "$( dirname "${BASH_SOURCE[0]}" )/.." && pwd )"
PLUGIN_SRC_PATH="$PROJECT_ROOT"
PLUGIN_BUILDS_PATH="$PROJECT_ROOT/builds"
PLUGIN_BUILD_CONFIG_PATH="$PROJECT_ROOT/build-cfg"
PUBLISH=""
if [[ $* == *--publish* ]]
then
  PUBLISH="-p"
fi

function echog() {
    echo "$(tput setaf 2)$1$(tput sgr 0)"
}
function echor() {
    echo "$(tput setaf 1)$1$(tput sgr 0)"
}

function build_plugin() {
    PLUGIN_DIR="$PLUGIN_BUILD_CONFIG_PATH/$PLUGIN"

    if [[ -d "$PLUGIN_DIR" && ! -L "$PLUGIN_DIR" ]]; then
        if [ "utils" != "$PLUGIN" ] && [ "common" != "$PLUGIN" ];
        then
            VERSION=$(php -f "$PLUGIN_BUILD_CONFIG_PATH/utils/get_plugin_version.php" "$PROJECT_ROOT" $PLUGIN)
            ZIP_NAME=$(php -f "$PLUGIN_BUILD_CONFIG_PATH/utils/get_plugin_zip_name.php" "$PROJECT_ROOT" $PLUGIN)
            BUILD_ZIP="$PLUGIN_BUILDS_PATH/$ZIP_NAME-$VERSION.zip";

            if [ -f "$BUILD_ZIP" ]
            then
                rm "$BUILD_ZIP"
            fi
          
            echog "Building $PLUGIN v$VERSION..."
            cd "$PLUGIN_BUILD_CONFIG_PATH/$PLUGIN/"
            "$PLUGIN_BUILDS_PATH/plugin-build" "$VERSION" "$PUBLISH"
            echog "Plugin built: $BUILD_ZIP"
            echo "--------------------------"
        fi
    else
        usage
    fi
}

cd "$PROJECT_ROOT";

if [[ $SCRIPT_ARGS == *--pro-version* ]]
then
  echo $(php -f "$PLUGIN_BUILD_CONFIG_PATH/utils/get_plugin_version.php" "$PROJECT_ROOT" wp-offload-ses)
  exit;
fi

if [ -z "${SCRIPT_ARGS}" ]
then
    echog "Clearing previously built plugins..."
    rm -rf "$PLUGIN_BUILDS_PATH"
fi

if [ ! -d "$PLUGIN_BUILDS_PATH" ]
then
    mkdir -p "$PLUGIN_BUILDS_PATH"
fi

if [ ! -f "$PLUGIN_BUILDS_PATH/plugin-build" -o ! -x "$PLUGIN_BUILDS_PATH/plugin-build" ]
then
    echog "Downloading plugin-build script..."
    curl -sSL https://raw.githubusercontent.com/deliciousbrains/wp-plugin-build/582fdeb3f6d19ae0b1f2bd0da9b48f45c131ac34/plugin-build -o "$PLUGIN_BUILDS_PATH/plugin-build"
    chmod +x "$PLUGIN_BUILDS_PATH/plugin-build"
fi

echog "Building plugins..."
if [ -z "${SCRIPT_ARGS}" ]
then
    for PLUGIN_DIR in "$PLUGIN_BUILD_CONFIG_PATH"/*/
    do
        PLUGIN=$(basename "$PLUGIN_DIR");

        build_plugin
    done
else
    for PLUGIN in ${SCRIPT_ARGS}
    do
        build_plugin
    done
fi