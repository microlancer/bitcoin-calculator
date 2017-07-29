CREATE TABLE IF NOT EXISTS `btc_transaction_history` (
`id` int(10) unsigned NOT NULL,
  `type` varchar(255) NOT NULL,
  `from_user_id` int(11) DEFAULT NULL,
  `to_user_id` int(11) DEFAULT NULL,
  `btc_amount` int(11) NOT NULL,
  `external_btc_address` int(11) DEFAULT NULL,
  `job_id` int(11) DEFAULT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `btc_transaction_history`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `btc_transaction_history`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT;
