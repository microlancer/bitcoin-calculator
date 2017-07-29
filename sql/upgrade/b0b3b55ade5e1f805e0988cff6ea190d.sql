-- Add passwordResetCode
alter table users add passwordResetCode varchar(255) default null after verifyCode;
