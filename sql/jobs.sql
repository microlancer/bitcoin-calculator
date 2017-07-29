CREATE TABLE IF NOT EXISTS `jobs` (
`id` int(11) NOT NULL,
  `client_user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `category` varchar(255) NOT NULL,
  `btc_offer` double NOT NULL,
  `status` varchar(255) NOT NULL,
  `created_ts` datetime DEFAULT NULL,
  `updated_ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `jobs`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `jobs`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
