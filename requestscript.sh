#/bin/shell

mysqldump statementData > statementData.sql
mysqldump druporta_tss_data > tssdata.sql
tar cfJ sqldump.tar.xz tssdata.sql statementData.sql
rm *.sql
scp -i /home/druportal/.ssh/replicationdb.pem sqldump.tar.xz ec2-user@ec2-54-213-143-102.us-west-2.compute.amazonaws.com:~/
rm *.xz

echo "grabbing dir"
cd /home/ec2-user/
echo "killing old dumnp"
rm *.sql
echo "extracting xz from dump"
tar xf /home/ec2-user/sqldump.tar.xz
echo "pulling in sql to mysql database"
sudo mysql druporta_tss_data < /home/ec2-user/tssdata.sql
sudo mysql statmentData < /home/ec2-user/statement.sql
echo "cleaning up"
rm *.sql
