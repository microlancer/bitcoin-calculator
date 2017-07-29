CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `verifyCode` varchar(255) DEFAULT NULL,
  `passwordResetCode` varchar(255) DEFAULT NULL,
  `btcPublicKey` varchar(255) DEFAULT NULL,
  `btcPrivateKey` varchar(255) DEFAULT NULL,
  `createdTs` datetime NOT NULL,
  `updatedTs` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

-- Test users: 
-- a@b.com / pass123
-- b@c.com / pass123

INSERT INTO `users` (`id`, `email`, `password`, `verifyCode`, `btcPublicKey`, `btcPrivateKey`, `createdTs`, `updatedTs`) VALUES
  (1, 'a@b.com', '$2y$10$cYAmNXFNvE6tQDEDLRO/qujLsnEQTy8DnytyOM6VdcVhBo4e6dhs2', NULL, NULL, NULL, NOW(), NOW()),
  (2, 'b@c.com', '$2y$10$cYAmNXFNvE6tQDEDLRO/qujLsnEQTy8DnytyOM6VdcVhBo4e6dhs2', NULL, NULL, NULL, NOW(), NOW());
