<?php
use Multiple\Frontend\Models\User;
/**
 * Событие происходит если пользователь уже зарегистрирован в системе
 * @param \TUser $user Пользователе
 */

function checkUserExistence($user, $user_array){
    if($user->getUserId())
        return false;
    else
        return createNewUser($user_array);

}

function createNewUser($user_array){
    $user_new = new User();
    $user_new->username = $user_array['username'];
    $user_new->email = $user_array['email'];
    $user_new->password = hash('md5',time());
    $user_new->status = 1;
    $user_new->date_registration = date("Y-m-d H:i:s");
    if($user_new->save()!==false)
        return $user_new->id;
    else
        return false;
}

function onRegUserFound($user) {

}

/**
 * Событие происходит до момента добавления информации о привязке пользователя в БД
 * @param \TUser $user Пользователь
 * @throws OAuth2Exception
 */
function onBeforeUserInsert(&$user, $user_array = null) {
    if($user_array!==null) {
        $userid = createNewUser($user_array);
        if ($userid)
            $user->setUserId($userid);
    }
}

/**
 * Событие происходит после успешной авторизации пользователя
 * @param \TUser $user
 */
function onUserAuthorized($user, $user_array = null) {
    if($user_array!==null) {
        $userid = checkUserExistence($user);
        if ($userid) {
            $user->setUserId($userid);
            $user->save();
        }
    }
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
