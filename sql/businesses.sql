CREATE TABLE IF NOT EXISTS `businesses` (
`id` bigint(20) NOT NULL,
  `businessname` varchar(250) NOT NULL,
  `shortname` varchar(30) NOT NULL,
  `founder` bigint(20) NOT NULL,
  `created_ts` datetime NOT NULL,
  `updated_ts` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `businesses`
 ADD PRIMARY KEY (`id`),
 ADD UNIQUE KEY `businessname` (`businessname`),
 ADD UNIQUE KEY `shortname` (`shortname`);

ALTER TABLE `businesses`
MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;