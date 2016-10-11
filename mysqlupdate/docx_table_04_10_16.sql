CREATE TABLE `docx_files` (
	`docx_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
	`docx_file_name` VARCHAR(200) NOT NULL COLLATE 'utf8_general_ci',
	`complaint_id` INT UNSIGNED NOT NULL,
	`user_id` INT UNSIGNED NOT NULL,
	PRIMARY KEY (`docx_id`)
)
COLLATE='utf8_general_ci'
ENGINE=InnoDB;
ALTER TABLE `docx_files` CHANGE COLUMN `complaint_id` `complaint_id` INT(10) UNSIGNED NULL AFTER `docx_file_name`;
ALTER TABLE `docx_files` ADD COLUMN `complaint_name` VARCHAR(255) NULL DEFAULT NULL AFTER `complaint_id`;