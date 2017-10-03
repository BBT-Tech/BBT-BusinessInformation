CREATE DATABASE `business_information` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE business_information;

CREATE TABLE `businesses`(
	`business_id` INTEGER NOT NULL AUTO_INCREMENT,
	`name` TEXT,
	`industry` TEXT,
	`contact` TEXT,
	`address` TEXT,
	`willingness` TEXT,
	`sponsorship_content` TEXT,
	`charge_history` TEXT,
	`business_evaluation` TEXT,
	`remarks` TEXT,
	`is_contacted` BOOLEAN,
	`contact_history` TEXT,
	`import_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`update_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
	PRIMARY KEY (business_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `accounts`(
	`account_id` INTEGER NOT NULL AUTO_INCREMENT,
	`username` VARCHAR(30) NOT NULL,
	`salt` TEXT NOT NULL,
	`salted_password_hash` TEXT NOT NULL,
	`name` TEXT NOT NULL,
	`is_minister` BOOLEAN DEFAULT 0 NOT NULL,
	`register_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	`update_time` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
	PRIMARY KEY (account_id),
	UNIQUE (username)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;
