#!/bin/bash

cd "$(dirname "$0")"

HEADLESS=$1
FULL=$2

FACTORIO="../factorio"

# Check if both parameters are present and actual files.
if [[ -z "$HEADLESS" || -z "$FULL" ]] || ! [[ -a "$HEADLESS" && -a "$FULL" ]]; then
    echo "Usage: install-factorio.sh <headless.zip> <full.zip>"
    exit 1
fi

# Check the filenames
if ! [[ "$HEADLESS" =~ factorio_headless_x64_[0-9]+\.[0-9]+\.[0-9]+\.tar\.xz$ ]]; then
    echo "First file seems not to be the headless version of Factorio. Expected filename: factorio_headless_x64_<version>.tar.xz"
    exit 1
fi
if ! [[ "$FULL" =~ factorio_[a-z0-9_-]+_x64_[0-9]+\.[0-9]+\.[0-9]+\.tar\.xz$ ]]; then
    echo "Second file seems not to be the full version of Factorio. Expected filename: factorio_<something>_x64_<version>.tar.xz"
    exit 1
fi

# Check that both files have the same version.
VERSION=`echo "$HEADLESS" | cut -d "_" -f 4 | cut -d "." -f 1,2,3`
VERSION2=`echo "$FULL" | cut -d "_" -f 4 | cut -d "." -f 1,2,3`
if [[ "$VERSION" != "$VERSION2" ]]; then
    echo "The Factorio files seems to have a different version: Headless $VERSION vs. Full $VERSION2"
    exit 1
fi

# Do the work
echo "Cleaning old files..."
rm -rf "$FACTORIO/bin" "$FACTORIO/data" "$FACTORIO/config-path.cfg" "$FACTORIO"/mods/base_*.zip

echo "Extracting headless..."
tar -xf "$HEADLESS" --strip-components 1 -C "$FACTORIO"

echo "Removing base mod from headless..."
rm -rf "$FACTORIO/data/base"

echo "Extracting base mod from full game..."
tar -xf "$FULL" --strip-components 2 -C "$FACTORIO/data" "factorio/data/base"

echo "Zipping base mod..."
cd "$FACTORIO/data"
mv base "base_$VERSION"
zip -rq "../mods/base_$VERSION.zip" "base_$VERSION"
rm -rf "base_$VERSION"
cd "../../bin"

echo "Done."
