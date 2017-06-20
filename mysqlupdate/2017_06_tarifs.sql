ALTER TABLE  `user` ADD  `tarif_id` INT NOT NULL DEFAULT  '1',
ADD  `tarif_date_activate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
ADD  `tarif_count` INT NOT NULL DEFAULT  '1',
ADD  `tarif_active` TINYINT NOT NULL DEFAULT  '1';

CREATE TABLE  `fas`.`tarifs` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`tarif_name` VARCHAR( 255 ) NOT NULL ,
`tarif_anounce` VARCHAR( 255 ) NULL ,
`tarif_description` TEXT NULL ,
`tarif_type` SET(  'complaint',  'month' ) NOT NULL ,
`tarif_price` INT NOT NULL,
`tarif_discount` INT NOT NULL DEFAULT  '0'
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;


CREATE TABLE  `fas`.`tarif_order` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`user_id` INT NOT NULL ,
`applicant_id` INT NOT NULL ,
`applicant_type` VARCHAR( 255 ) NOT NULL ,
`applicant_side` SET(  'in',  'out' ) NOT NULL ,
`address` VARCHAR( 255 ) NOT NULL ,
`post_address` VARCHAR( 255 ) NOT NULL ,
`name_full` VARCHAR( 255 ) NOT NULL ,
`name_short` VARCHAR( 255 ) NOT NULL ,
`inn` VARCHAR( 255 ) NOT NULL ,
`kpp` VARCHAR( 255 ) NOT NULL ,
`phone` VARCHAR( 255 ) NOT NULL ,
`email` VARCHAR( 255 ) NOT NULL ,
`tarif_count` VARCHAR( 255 ) NOT NULL ,
`tarif_price_one` INT NOT NULL ,
`tarif_price` INT NOT NULL ,
`tarif_id` INT NOT NULL ,
`tarif_name` VARCHAR( 255 ) NOT NULL
) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;

ALTER TABLE  `tarif_order` ADD  `invoce_payment` TINYINT NOT NULL DEFAULT  '0',
ADD  `order_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP

ALTER TABLE  `tarif_order` ADD  `order_number` INT NOT NULL DEFAULT  '1' AFTER  `user_id`

ALTER TABLE  `tarif_order` CHANGE  `kpp`  `kpp` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL

INSERT INTO `fas`.`permission` (`id`, `name`, `name_ru`) VALUES (NULL, 'tarif', 'Тарифы');