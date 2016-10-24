(function() {
    // Доступ всегда осуществляется в ассинхронном режиме
    // Получаем объект Promise для доступа к cades plugin api
    var cades = window.cades;
    // Открывает хранилище на чтение

    cades.openStore([cades.CAPICOM_CURRENT_USER_STORE, cades.CAPICOM_MY_STORE, cades.CAPICOM_STORE_OPEN_READ_ONLY]);
    // получаем базовую информацио об установленных сертификатах

    cades.getCertificatesInfo([cades.CAPICOM_CERTIFICATE_FIND_TIME_VALID]).then(function(aCertificate) {

        var element = document.getElementById('certificate-select');
       // var element2 = document.getElementById('certificate-select-file');
        console.log(aCertificate);
        aCertificate.forEach(function(item, i) {
            var option = document.createElement('option');
            option.text = item.UserName + '(' + item.ValidToDate + ')';
            option.value = item.Thumbprint;
            element.add(option);
          //  element2.add(option.cloneNode(true));
        }.bind(this));
    }).then(function() {
        // Зыкрываем хранилище
        cades.closeStore();
    }.bind(this));
})();

/**
 * Подпись сообщения
 * @param certificateSelector
 * @param messageSelector
 * @param signatureSelector
 */
var signMessage = function(certificateSelector,message ) {
    var certificate = document.getElementById(certificateSelector);
   // var message = document.getElementById(messageSelector);
   // var signature = document.getElementById(signatureSelector);
    if (!certificate.value) {
        alert('Need select sertif');
        throw new Error('Need select sertif');
    }
    var cades = window.cades;
    // Открывает хранилище на чтение
    cades.openStore([cades.CAPICOM_CURRENT_USER_STORE, cades.CAPICOM_MY_STORE, cades.CAPICOM_STORE_OPEN_READ_ONLY]);
    // получаем сертификат по его отпечатку
    cades.findCertificate([cades.CAPICOM_CERTIFICATE_FIND_SHA1_HASH, certificate.value]).then(function(Certificate) {
        var signedDateTime = new Date(); // Время подписи
        signedDateTime.setTime(signedDateTime.getTime() + signedDateTime.getTimezoneOffset()*60*1000);
        signedDateTime = new Date(signedDateTime.toUTCString());
        return cades.signMessage(Certificate, message.value, signedDateTime,false);
    }.bind(this)).then(function(signatureMessage) {
        // Получаем сигнатуру в base64
       // signature.value = signatureMessage;
      //  console.log (signatureMessage);
        var data = new FormData();
        data.append('signature', signatureMessage);
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
               // signMessage('certificate-select', data[1])
            },
            error: function () {
            }
        });
    }.bind(this)).then(function() {
        // Зыкрываем хранилище
        cades.closeStore();
    }.bind(this));
};

/**
 * Подпись файла
 * @param event
 * @param certificateSelector
 * @param messageSelector
 * @param signatureSelector
 */
var onLoad = function(event, certificateSelector, messageSelector, signatureSelector)
{
    var message = document.getElementById(messageSelector);
    var input = event.target;
    var reader = new FileReader();
    reader.readAsDataURL(input.files[0]);
    reader.onload = function () {
        var header = ";base64,";
        var fileData = reader.result;
        message.value = fileData.substr(fileData.indexOf(header) + header.length); //Получаем дынные в base64
        signMessage(certificateSelector, messageSelector, signatureSelector); // подписываем файл
    };
};