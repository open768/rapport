#!/bin/sh
FILE="rapport/release.zip"
cd ..
rm $FILE 2> /dev/null
mv rapport/inc/secret.php rapport/inc/secret-orig.php
cp rapport/inc/secret-example.php rapport/inc/secret.php
zip -q -9 -r $FILE rapport -x \*\.git\/\* -x \*\\[\* -x \*\/vendor\/\* -x \*\/composer\* -x \*\/secret-orig.php -x \*\/images\/src\/\*
zip -q -9 -r $FILE phpinc/ckinc
zip -q -9 -r $FILE phpinc/appd -x \*.git\/\*
zip -q -9 -r $FILE phpinc/extra/php-openssl-crypt
zip -q -9 -r $FILE jsinc/ck-inc -x \*\/space\/
zip -q -9 -r $FILE jsinc/extra/jquery-datetimepicker  -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/uri-parser -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/bean -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-flowtype -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-inview -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-mdl-dialog -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-qtip -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-spinner -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-ui -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery-visible -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/jquery -x \*\.zip
zip -q -9 -r $FILE jsinc/extra/tablesorter -x \*\.zip
rm -f rapport/inc/secret.php
cp -f  rapport/inc/secret-orig.php rapport/inc/secret.php
echo "finished"