#!/bin/sh
FILE="rapport/release.zip"
cd ..
rm $FILE 2> /dev/null
mv rapport/inc/secret.php rapport/inc/secret-orig.php
cp rapport/inc/secret-example.php rapport/inc/secret.php
zip -q -9 -r $FILE rapport -x \*\.git\/\* -x \*\\[\* -x \*\/vendor\/\* -x \*\/composer\* -x \*\/secret-orig.php -x \*\/images\/src\/\*
echo step 1
zip -q -9 -r $FILE phpinc/ckinc
echo step 2
zip -q -9 -r $FILE phpinc/appd -x \*.git\/\*
echo step 3
zip -q -9 -r $FILE phpinc/extra/php-openssl-crypt
echo step 4
zip -q -9 -r $FILE jsinc/ck-inc -x \*\/space\/
echo step 5
zip -q -9 -r $FILE jsinc/extra/jquery-datetimepicker  -x \*\.zip
echo step 6
zip -q -9 -r $FILE jsinc/extra/uri-parser -x \*\.zip
echo step 7
zip -q -9 -r $FILE jsinc/extra/bean -x \*\.zip
echo step 8
zip -q -9 -r $FILE jsinc/extra/jquery-flowtype -x \*\.zip
echo step 9
zip -q -9 -r $FILE jsinc/extra/jquery-inview -x \*\.zip
echo step 10
zip -q -9 -r $FILE jsinc/extra/jquery-mdl-dialog -x \*\.zip
echo step 11
zip -q -9 -r $FILE jsinc/extra/jquery-qtip -x \*\.zip
echo step 12
zip -q -9 -r $FILE jsinc/extra/jquery-spinner -x \*\.zip
echo step 13
zip -q -9 -r $FILE jsinc/extra/jquery-ui -x \*\.zip
echo step 14
zip -q -9 -r $FILE jsinc/extra/jquery-visible -x \*\.zip
echo step 15
zip -q -9 -r $FILE jsinc/extra/jquery -x \*\.zip
echo step 16
zip -q -9 -r $FILE jsinc/extra/tablesorter -x \*\.zip
rm -f rapport/inc/secret.php
cp -f  rapport/inc/secret-orig.php rapport/inc/secret.php
echo "finished"
