-- For issue #5
alter table users change verify_code verifyCode varchar(255) DEFAULT NULL;
alter table users change btc_address btcPublicKey varchar(255) DEFAULT NULL;
alter table users change btc_password btcPrivateKey varchar(255) DEFAULT NULL;
alter table users change created_ts createdTs datetime NOT NULL;
alter table users change updated_ts updatedTs timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP;

