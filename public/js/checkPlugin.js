var pluginNotFound = true;
function checkPlugin() {
// Доступ всегда осуществляется в ассинхронном режиме
// Получаем объект Promise для доступа к cades plugin api
    var cades = window.cades;

// Создание объекта хранилища сертификатов CAPICOM.Store
    var oStore = cades.then(function () {
        return cades.store();
    }.bind(this));

// Получаем доступ к хранилищу сертификатов пользователя
    oStore.then(function (Store) {
        pluginNotFound = false;
        hideWaitPopup();
        return Store.open.apply(Store, [
            Store.CAPICOM_CURRENT_USER_STORE, // Хранилище текущего пользователя
            Store.CAPICOM_MY_STORE, // Имя хранилища "My"
            Store.CAPICOM_STORE_OPEN_READ_ONLY // Доступ только на чтение
        ]);
    });

    oStore.then(function(Store) {
        return Store.close();
    });
}

function  hideWaitPopup(){
    clearTimeout(timer_id);
    $('.addAppCertificate-preloader').hide();

    if(pluginNotFound == true){

        $('.addAppCertificate-alert').fadeIn().css('display', 'flex');
    }else{
        $('.addAppCertificate-main2').fadeIn().css('display', 'flex');
    }
}

