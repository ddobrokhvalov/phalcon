INSERT INTO `permission` (`id`, `name`, `name_ru`) VALUES (NULL, 'complaints', 'Жалобы');
INSERT INTO `permission` (`id`, `name`, `name_ru`) VALUES (NULL, 'arguments', 'Доводы');
INSERT INTO `permission` (`id`, `name`, `name_ru`) VALUES (NULL, 'applicants', 'Заявители');
INSERT INTO `permission` (`id`, `name`, `name_ru`) VALUES (NULL, 'lawyer', 'Вопросы юристу');

INSERT INTO `permission_admin` (`id`, `admin_id`, `permission_id`, `read`, `edit`) VALUES (NULL, '1', '9', '1', '1');
INSERT INTO `permission_admin` (`id`, `admin_id`, `permission_id`, `read`, `edit`) VALUES (NULL, '1', '10', '1', '1');
INSERT INTO `permission_admin` (`id`, `admin_id`, `permission_id`, `read`, `edit`) VALUES (NULL, '1', '11', '1', '1');
INSERT INTO `permission_admin` (`id`, `admin_id`, `permission_id`, `read`, `edit`) VALUES (NULL, '1', '12', '1', '1');
INSERT INTO `permission_admin` (`id`, `admin_id`, `permission_id`, `read`, `edit`) VALUES (NULL, '1', '13', '1', '1');
