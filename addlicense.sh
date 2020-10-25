#!/bin/bash
#TODO: insert check to see wether the file really starts with <?php, not <html> for example
cp $1 $1.bak
tail $1 --lines=+2 > temp.adp
echo "<?php" > $1
cat LICENSE.header >> $1
cat temp.adp >> $1
rm $1.bak
rm temp.adp
