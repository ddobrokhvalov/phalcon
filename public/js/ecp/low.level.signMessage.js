(function() {

    // Доступ всегда осуществляется в ассинхронном режиме
    // Получаем объект Promise для доступа к cades plugin api
    var cades = window.cades;
    var thumbprint = 'F170F6CF858141A44B5141D45C60DD64FC74071A'; // отпечаток сертификата для поиска
    var message = 'Hello World'; // Подписываемые данные
    var signedDateTime = new Date(); // Время подписи
    signedDateTime.setTime(signedDateTime.getTime() + signedDateTime.getTimezoneOffset()*60*1000);
    signedDateTime = new Date(signedDateTime.toUTCString());

    // Создание объекта хранилища сертификатов CAPICOM.Store
    var oStore = cades.then(function() {
        return cades.store();
    }.bind(this));

    // Получаем доступ к хранилищу сертификатов пользователя
    oStore.then(function(Store) {
        return Store.open.apply(Store, [
            Store.CAPICOM_CURRENT_USER_STORE, // Хранилище текущего пользователя
            Store.CAPICOM_MY_STORE, // Имя хранилища "My"
            Store.CAPICOM_STORE_OPEN_READ_ONLY // Доступ только на чтение
        ]);
    });

    // Получение сертификат
    var oCertificate = oStore.then(function(Store) {
        return Store.getCertificates();
    }).then(function(Certificates) { // Получаем объект достпупа к API Certificates
        // Осуществялем поиск сертификата по отмечатку
        return Certificates.find(Certificates.CAPICOM_CERTIFICATE_FIND_SHA1_HASH, thumbprint);
    }).then(function(Certificates) {
        return new Promise(function(resolve, reject){
            Certificates.getCount().then(function(count) {
                if (count == 0) {
                    throw 'Cert not found'
                }
                Certificates.getItem(1).then(function(Certificate) {
                    // получаем объект сертификата
                    resolve(Certificate.getObject());
                }.bind(this));
            }).then(null, function(error) {
                reject(error);
            });
        }.bind(this));
    });

    // Создаем объект типа CAdESCOM.CPSigner
    var oSigner = cades.signer().then(function(Signer){
        return new Promise(function(resolve, reject) {
            oCertificate.then(function(Certificate) {
                Signer.setCertificate(Certificate);
                resolve(Signer);
            }.bind(this));
        }.bind(this));
    }.bind(this));

    // Создаем объект типа CAdESCOM.CPAttribute
    var oCPAttribute = cades.CPAttribute().then(function(CPAttribute) {
        // Устанавливаем время подписи
        CPAttribute.setName(CPAttribute.CAPICOM_AUTHENTICATED_ATTRIBUTE_SIGNING_TIME);
        CPAttribute.setValue(signedDateTime);
        return oSigner.then(function(Signer) {
            return Signer.getAuthenticatedAttributes().then(function(attributes){
                attributes.Add(CPAttribute.getObject());
                return CPAttribute;
            }.bind(this));
        }.bind(this));
    }.bind(this));


    // Создаем объект типа CAdESCOM.CadesSignedData
    var oSignedData = cades.signedData().then(function(SignedData) {
        // Значение свойства ContentEncoding должно быть задано
        // до заполнения свойства Content
        SignedData.setContentEncoding(SignedData.CADESCOM_STRING_TO_UCS2LE);
        SignedData.setContent(message);
        return SignedData;
    }.bind(this));

    // Подписываем данные
    var signatureMessage = oSignedData.then(function(SignedData){
        return new Promise(function(resolve, reject){
            oSigner.then(function(Signer) {
                resolve(SignedData.signCades(Signer.getObject(), SignedData.CADESCOM_CADES_BES, true, SignedData.CAPICOM_ENCODE_BASE64));
            }.bind(this));
        }.bind(this));
    }.bind(this));

    signatureMessage.then(function(signatureMessage) {
        // Получаем подпись
        console.log(signatureMessage);
    });

    // Закрываем хранилище
    oStore.then(function(Store) {
        return Store.close();
    });

})();