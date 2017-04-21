#/bin/sh
mdb-export FrontlineStatementData.mdb MonthDescriptors > statements/MonthDescriptors.csv
mdb-export FrontlineStatementData.mdb qryMthDep > statements/qryMthDep.csv
mdb-export FrontlineStatementData.mdb qryMthDep1 > statements/qryMthDep1.csv
mdb-export FrontlineStatementData.mdb StatementReport6101Frontline > statements/StatementReport6101Frontline.csv
mdb-export FrontlineStatementData.mdb StatementReport6101FrontlineMNA > statements/StatementReport6101FrontlineMNA.csv
mdb-export FrontlineStatementData.mdb tblCPDiscountEOM > statements/tblCPDiscountEOM.csv
mdb-export FrontlineStatementData.mdb tblFlexFees > statements/tblFlexFees.csv

cd /home/druportal/trisource/scripts/statements/
php statement-feed.php
