#!/bin/bash

ME="$(realpath $(dirname $0))"
ROOTDIR="$(realpath $(dirname $0)/../)"

rm -rf $ROOTDIR/example/twigcache
mkdir $ROOTDIR/example/twigcache

$ME/tools/generate-twig-cache.php \
    --twig-cache-dir="$ROOTDIR/example/twigcache" \
    --twig-templates-dir="$ROOTDIR/example/templates" \
    --twig-templates-po-files="example/templates/" \
    --json-mapping="$ROOTDIR/example/locale/replace.json" \
    --title="Wdes example" \
    --copyright="William Desportes <williamdes@wdes.fr>" \
    --package-version="1.0.0"
sh $ME/tools/generate-pot.sh $ROOTDIR/example/twigcache $ROOTDIR/example/locale example.pot
$ME/tools/update-po-files.php \
    --po-dir="$ROOTDIR/example/locale" \
    --po-template="$ROOTDIR/example/locale/example.pot" \
    --json-mapping="$ROOTDIR/example/locale/replace.json"
rm -rf $ROOTDIR/example/twigcache
rm $ROOTDIR/example/locale/replace.json

msgfmt --directory="$ROOTDIR/example/locale" --check -o "$ROOTDIR/example/locale/fr.mo" "$ROOTDIR/example/locale/fr.po"
