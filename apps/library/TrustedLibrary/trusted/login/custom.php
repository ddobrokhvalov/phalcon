<?php
use Multiple\Frontend\Models\User;
/**
 * Событие происходит если пользователь уже зарегистрирован в системе
 * @param \TUser $user Пользователе
 */

function checkUserExistence($user){

}

function createNewUser($user_array){
    $user_new = new User();
    $user_new->username = $user_array['username'];
    $user_new->email = $user_array['email'];
    $user_new->password = hash('md5',time());
    $user_new->status = 1;
    $user_new->date_registration = date("Y-m-d H:i:s");
    return $user_new->id;
}

function onRegUserFound($user) {

}

/**
 * Событие происходит до момента добавления информации о привязке пользователя в БД
 * @param \TUser $user Пользователь
 * @throws OAuth2Exception
 */
function onBeforeUserInsert(&$user, $user_array = null) {
    $userid = createNewUser($user_array);
    var_dump($userid);
    if($userid)$user->setUserId($userid);

    //Ваш код здесь
}

/**
 * Событие происходит после успешной авторизации пользователя
 * @param \TUser $user
 */
function onUserAuthorized($user) {
    //checkUserExistence($user);
    die('657567');
}

/**
 * Событие на возникновение ошибки OAuth2Exception
 * @param \OAuth2Exception $e
 */
function onOAuth2Exception($e) {
    //Ваш код здесь
    onException($e);
}

/**
 * Событие на возникновение ошибки Exception
 * @param \Exception $e
 */
function onException($e) {
    //Ваш код здесь
    unset($_SESSION['TRUSTEDNET']);
    $tpl = file_get_contents(TRUSTED_MODULE_ROOT. "/../error.php");
    $message = "onOAuth2Exception" . PHP_EOL . $e->getMessage() . PHP_EOL;
    $tpl2 = str_replace('${message}', $message, $tpl);
    echo($tpl2);
    die();
}
