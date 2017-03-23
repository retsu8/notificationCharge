DROP TRIGGER IF EXISTS `chargeback_notifications`;
DELIMITER $$
CREATE TRIGGER `chargeback_notifications` AFTER INSERT ON druporta_tss_data.chargebacks
FOR EACH ROW
BEGIN
	select mid into @vMID from druporta_tss_data.chargebacks limit 1;
	select `merchant-contact-email` into @vEmail from druporta_tss_data.mrchprofile where mid=@vMID;
	select `merchant-name` into @vName from druporta_tss_data.mrchprofile where mid=@vMID limit 1;
	select CONCAT_WS(',',COALESCE(`processing-date`, '0'),COALESCE(`MID`,'0'),COALESCE(`merchant-name`,'0'),COALESCE(`tran-type`,'0'),COALESCE(`tran-identifier`,'0'),COALESCE(`amount`,'0'),COALESCE(`case-number`,'0'),COALESCE(`card-type`,'0'),COALESCE(`dbcr-indicator`,'0'),COALESCE(`reason-code`,'0'),COALESCE(`reason-desc`,'0'),COALESCE(`record-type`,'0'),COALESCE(`auth-code`,'0'),COALESCE(`card-number`,'0'),COALESCE(`reference-number`,'0'),COALESCE(`bin-ica`,'0'),COALESCE(`transaction-date`,'0'),COALESCE(`acquirer-reference`,'0'),COALESCE(`message`,'0')) into @vBlock from druporta_tss_data.chargebacks order by `ID` desc limit 1;
	insert into druporta_tss_data.notifications(email,MID, name,block, notified, message, url) values(@vEmail, @vMID, @vName, @vBlock, 0,'', '' );
END $$
DELIMITER ;
