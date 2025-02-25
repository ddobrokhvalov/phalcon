$(document).ready(function () {
    $(".addAppCertificate__addBtn_edit").on('click', function(){
        if (selectedCertif == false)
            return;
        applicant.edit_mode = true;
        $('.addAppCertificate-main2').fadeOut().css('display', 'none');

        var userData = selectedCertif.SubjectName;

        if (userData.indexOf('OGRN=') != -1) {
            applicant.parseUrLico(selectedCertif);
        }
        if (userData.indexOf('OGRNIP=') != -1) {
            applicant.parseIp(selectedCertif);
        }

        var str = selectedCertif.ValidFromDate;
        str = str.toString().substr(0, 10);
        var field = str + ' | ' + selectedCertif.SubjectDNSName;

        // $('.ecp_ur').val(selectedCertif.Thumbprint);
        // $('.ecp_text').val(field);

        count_ecp++;
        var str = '<li class="apCerList__apCeritem apCerItem" data-thumbprint="'+ selectedCertif.Thumbprint +'">'+ field;
        str += '<input type="hidden" name="cert['+ count_ecp +'][name]" value="'+ field +'">';
        str += '<input type="hidden" name="cert['+ count_ecp +'][thumbprint]" value="'+ selectedCertif.Thumbprint +'">';
        str += '</li>';
        $("#mCSB_2_container, #mCSB_3_container").append(str);


        $('.content').removeClass('hiddenClass');
    });

    $('.applicantCertificate__remove ').on('click', function(){
        $('.apCerItem-active').remove();
    });

    $('.applicantCertificate__add').on('click', function(){
        findCertificates();
        $('.addAppCertificate').toggle();
    });

    $('.addAppCertificate__cancelBtn').on('click', function(){
        $('.addAppCertificate').toggle();
    });
});

var pluginNotFound = true;
var timer_id = false;
var userCertificates = false;
function findCertificates() {
    timer_id = setTimeout(hideWaitPopup, 1000*2);

    // Доступ всегда осуществляется в ассинхронном режиме
    // Получаем объект Promise для доступа к cades plugin api
    var cades = window.cades;

    // Создание объекта хранилища сертификатов CAPICOM.Store
    var oStore = cades.then(function() {
        return cades.store();
    }.bind(this));

    // Получаем доступ к хранилищу сертификатов пользователя
    oStore.then(function(Store) {
        pluginNotFound = false;
        hideWaitPopup();
        return Store.open.apply(Store, [
            Store.CAPICOM_CURRENT_USER_STORE, // Хранилище текущего пользователя
            Store.CAPICOM_MY_STORE, // Имя хранилища "My"
            Store.CAPICOM_STORE_OPEN_READ_ONLY // Доступ только на чтение
        ]);
    });

    // Получение списка сертификатов из хранилища
    oStore.then(function(Store) {
        return Store.getCertificates();
    }).then(function(Certificates) { // Получаем объект достпупа к API Certificates
        // Получаем список сертификатов у которых не истек срок дейтсвия
        return Certificates.find(Certificates.CAPICOM_CERTIFICATE_FIND_TIME_VALID);
        // return Certificates.find();
    }).then(function(Certificates) {
        return new Promise(function(resolve, reject) {
            // Получаем необходимые сведения по сертификатам
            Certificates.getCount().then(function(count) {
                var promises = [];
                for (var index = 1; index <= count; index++) {
                    promises.push(new Promise(function(resolve, reject) {
                        // Получаем объект доступа к сертификату из полученно списка по ключу
                        Certificates.getItem(index).then(function(Certificate) {
                            // Получаем необходимые данные из сертификата
                            return {
                                SerialNumber: Certificate.getSerialNumber(), // Серийный номер
                                IssuerName: Certificate.getIssuerName(), // Строка издателя сертификата
                                SubjectName: Certificate.getSubjectName(), // Наименование сертификата
                                Thumbprint: Certificate.getThumbprint(), // Отпечаток сертификата
                                ValidFromDate: Certificate.getValidFromDate(), // Дата с которой сертификат считается валидным
                                ValidToDate: Certificate.getValidToDate(), // Дата после который сертификат считается истекшим
                                UserName: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_SUBJECT_SIMPLE_NAME),
                                UserEmail: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_SUBJECT_EMAIL_NAME),
                                certificateName: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_ISSUER_SIMPLE_NAME),
                                certificateEmail: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_ISSUER_EMAIL_NAME),
                                SubjectUPN: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_SUBJECT_UPN),
                                IssuerUPN: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_ISSUER_UPN),
                                SubjectDNSName: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_SUBJECT_DNS_NAME),
                                IssuerDNSName: Certificate.getInfo(Certificate.CAPICOM_CERT_INFO_ISSUER_DNS_NAME)
                            };
                        }).then(function(SimpleCertificate) {
                            resolve(PromiseHelper.prototype.all(SimpleCertificate));
                        }).then(null, function(error) {
                            reject(error);
                        });
                    }.bind(this)));
                }
                resolve(Promise.all(promises));
            });
        }.bind(this));
    }).then(function(aCertificate) {
        // Получаем массыв сертификатов с необходимыми данным
        // ...
        console.log(aCertificate);
        userCertificates = aCertificate;
        var html = '';
        var current_inn = false;
        $('input[name="inn"]').each(function(){
            if($(this).val()){
                current_inn = $(this).val();
            }
        });
        var checked_element = $('.apCerItem');
        for(var i in aCertificate){
            console.log(aCertificate[i].SubjectName.indexOf(current_inn) );
            if(aCertificate[i].SubjectName.indexOf(current_inn) == -1){
                continue;
            }
            var isChecked = false;
            for(var j = 0; j < checked_element.length; j++){
                var th = $(checked_element[j]).attr('data-thumbprint');
                if(th == aCertificate[i].Thumbprint){
                    isChecked = true;
                    break;
                }
            }
            if(isChecked) continue;

            var str = aCertificate[i].ValidFromDate;
            str = str + '';
            if(+str[0] >= 0) {
                str = str.slice(0, 10);
            } else {
                str = str.slice(4, 16);
            }
            var field = str + ' | ' + aCertificate[i].SubjectDNSName;
            html +='<li class="existingCerListBox__item" onclick="setCertItem('+i+')" >'+field+'</li>';
        }

        $('.certificate-box-2').html(html);
    });

    // Закрываем хранилище
    oStore.then(function(Store) {
        return Store.close();
    });
};
var selectedCertif = false;
function setCertItem(num){
    selectedCertif = userCertificates[num];
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
