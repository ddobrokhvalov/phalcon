
// Доступ всегда осуществляется в ассинхронном режиме
// Получаем объект Promise для доступа к cades plugin api

//<input type="file" onchange="onLoad(event)" style="width:100%">

// Устанавливаем обработчик на загрузку файла
var onLoad = function(event)
{
    var input = event.target;
    var reader = new FileReader();
    reader.readAsDataURL(input.files[0]);
    reader.onload = function () {
        var header = ";base64,";
        var fileData = reader.result;
        var sBase64Data  = fileData.substr(fileData.indexOf(header) + header.length); // Получаем дынные в base64
        signFile(sBase64Data); //подписываем данные
    };
};
var signFile = function(contentBase64,thumbprint, callback)
{
    var cades = window.cades;
  //  var thumbprint = 'F170F6CF858141A44B5141D45C60DD64FC74071A'; // отпечаток сертификата для поиска
  //  var thumbprint =  '0D236BAE9545B8651439986078A2A92E35F09C45';
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
                    $('.error-compl').css({'display':'flex'});
                }
                Certificates.getItem(1).then(function(Certificate) {
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

    // Создаем объект типа CAdESCOM.CadesSignedData
    var oSignedData = cades.signedData().then(function(SignedData) {
        // Значение свойства ContentEncoding должно быть задано
        // до заполнения свойства Content
        SignedData.setContentEncoding(SignedData.CADESCOM_BASE64_TO_BINARY);
        SignedData.setContent(contentBase64);
        return SignedData;
    }.bind(this));

    // Подписываем данные
    var signatureMessage = oSignedData.then(function(SignedData){
        return new Promise(function(resolve, reject){
            oSigner.then(function(Signer) {
                resolve(SignedData.signCades(Signer.getObject(), SignedData.CADESCOM_CADES_BES, true, SignedData.CAPICOM_ENCODE_BASE64));
            }.bind(this));
        }.bind(this)).catch(function(err) {
           console.log(err);
            $('.error-compl').css({'display': 'flex'});
        });
    }.bind(this));

    signatureMessage.then(function(signatureMessage) {
        // Получаем подпись
       // console.log(signatureMessage);
        var data = new FormData();
        data.append('signature', signatureMessage);
        data.append('signFileOriginName',signFileOriginName);
        $.ajax({
            url: "/complaint/signature",
            type: 'POST',
            data: data,
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                //  data = JSON.parse(data);
                console.log(data);
                //alert('Подписано успешно!!!');
                if(callback){
                    callback();
                }
                $('#sign-ecp').removeClass('skyColor');
                $('#send_yfas').addClass('skyColor');
                $('.podpisatEp-popup').css({'display': 'flex'});
                // signMessage('certificate-select', data[1])
            },
            error: function () {
            }
        });
    });

    // Закрываем хранилище
    oStore.then(function(Store) {
        return Store.close();
    });
};
