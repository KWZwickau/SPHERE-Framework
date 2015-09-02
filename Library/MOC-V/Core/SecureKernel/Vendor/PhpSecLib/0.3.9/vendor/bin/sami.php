#!/usr/bin/env sh
SRC_DIR="`pwd`"
cd "`dirname "$0"`"
cd "../sami/sami"
BIN_TARGET="`pwd`/sami.php"
cd "$SRC_DIR"
"$BIN_TARGET" "$@"
