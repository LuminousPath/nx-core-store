#!/usr/bin/env bash
BIN_PATH="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
DISTPATH="${BIN_PATH}/../docs/dist"
WIKIPATH="${BIN_PATH}/../docs/wiki"

rm composer.json composer.lock
composer require phpdocumentor/phpdocumentor
composer require evert/phpdoc-md

rm -rf docs/

mkdir -p docs/dist

./vendor/bin/phpdoc
./vendor/bin/phpdocmd docs/structure.xml docs/dist
rm docs/structure.xml

rm -rf "${WIKIPATH}"
git clone git@nxfifteen.me.uk:nx-health/store.wiki.git "${WIKIPATH}"

mkdir -p "${WIKIPATH}/phpdoc"
mv "${DISTPATH}/ApiIndex.md" "${WIKIPATH}/phpdoc/Index.md"

# shellcheck disable=SC2164
cd "${DISTPATH}"
# shellcheck disable=SC2044
# shellcheck disable=SC2006
for DISTFILE in `find ./ -type f -name '*.md'`
do
    if [ "${DISTFILE}" != "./ApiIndex.md" ]; then
        DESTFILE=${DISTFILE//-//}
        echo "$DISTFILE => $DESTFILE"

        DESTDIR=$(dirname "${DESTFILE}")
        if [ ! -d "${WIKIPATH}/phpdoc/${DESTDIR}" ]; then mkdir -p "${WIKIPATH}/phpdoc/${DESTDIR}"; fi

        mv "$DISTFILE" "${WIKIPATH}/phpdoc/${DESTFILE}"

        MDLINKORI=${DISTFILE/.\//}
        MDLINKNEW=${DESTFILE/.\//}
        MDLINKNEW=${MDLINKNEW/.md/}
        sed -i "s|${MDLINKORI}|${MDLINKNEW}|g" "${WIKIPATH}/phpdoc/Index.md"
    fi
done

sed -i "s|](|](phpdoc/|g" "${WIKIPATH}/phpdoc/Index.md"

cd "${WIKIPATH}/phpdoc/"
git add .
git commit -m "Updated php documentor file"
git push
