<?php

//error_reporting(E_ALL);

define("TRUSTED_DEBUG", false);

define("TRUSTED_SSL_VERSION", 0);

/* ===== Database ===== */
// Использовать базу данных
/*define("TRUSTED_DB", true);
// Хост базы данных
define("TRUSTED_DB_HOST", "localhost");
// Имя базы данных
define("TRUSTED_DB_NAME", "fas");
// Логин для доступа к базе данных
define("TRUSTED_DB_LOGIN", "root");
// Пароль для доступа к базе данных
define("TRUSTED_DB_PASSWORD", "");*/


/* ===== Module trustednet ===== */
// Путь к модулю trustednet
define('TRUSTED_MODULE_PATH', '/apps/library/TrustedLibrary/trusted');


/* ===== Login ===== */
// Учетные данные приложения trusted.login
//define("TRUSTED_LOGIN_CLIENT_ID", "07805581299303de4b97b3fa6143d39e");
//define("TRUSTED_LOGIN_CLIENT_SECRET", "secret");
// Путь перехода после успешной аутентификации
define("TRUSTED_AUTHORIZED_REDIRECT", "/complaint/index");
