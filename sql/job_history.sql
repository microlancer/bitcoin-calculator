CREATE TABLE IF NOT EXISTS `job_history` (
`id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `job_history`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `job_history`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
