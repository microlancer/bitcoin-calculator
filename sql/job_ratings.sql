CREATE TABLE IF NOT EXISTS `job_ratings` (
  `job_id` int(11) NOT NULL,
  `worker_rating` int(11) NOT NULL,
  `worker_comments` text,
  `worker_rating_ts` datetime NOT NULL,
  `client_rating` int(11) NOT NULL,
  `client_comments` text,
  `client_rating_ts` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `job_ratings`
 ADD PRIMARY KEY (`job_id`);
