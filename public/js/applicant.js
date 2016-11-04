$(document).ready(function () {

    $('.addAppCertificate__addBtn').click(function () {
        if (selectedCertif == false)
            return;
        //  if(edit_mode == 0)
        //     return;

        $('.addAppCertificate-main2').fadeOut().css('display', 'none');

        var userData = selectedCertif.SubjectName;

        if (userData.indexOf('OGRN=') != -1) {
            applicant.parseUrLico(selectedCertif);
        }
        if (userData.indexOf('OGRNIP=') != -1) {
            applicant.parseIp(selectedCertif);
        }
        var str = selectedCertif.ValidFromDate;
        str = str.substr(0, 10);
        var field = str + ' | ' + selectedCertif.SubjectDNSName;

        $('#ecp_ur').val(selectedCertif.Thumbprint);
        $('#ecp_text').val(field);

        $('.content').removeClass('hiddenClass');
    });


    $('.applicantCertificate__add').click(function () {
        $('.addAppCertificate-main').fadeIn().css('display', 'flex');
        $('#import-aplicant').addClass('admin-popup-close');
    });

    /*$(".active-tabs-content form").submit(function( event ) {
     alert("Handler for .submit() called.");
     if (!applicantValidator.result) {
     event.preventDefault();
     }
     });*/

    var url = window.location.href;
    if (typeof applicantFirstId !== 'undefined' && url.indexOf('applicant_id=') == -1)
        applicant.selectFirst(applicantFirstId, false);


    if (typeof applicantSelectedId !== 'undefined' && applicantSelectedId != 'All')
        applicant.selectFirst(applicantSelectedId, false);

    $("#add_applicant, .add_applicant").click(function (event) {
        event.preventDefault();
        if ($(".modal-dialog.modal-sm").height() != null && $(".modal-dialog.modal-sm").height() > 0) {
            return false;
        }
        applicantValidator.start();
        if (applicantValidator.result) {
            //$('#applicant_form').submit();
            $(".active-tabs-content form").submit();
        } else {
            event.preventDefault();
            return false;
        }
    });

    $('.select_applicant').click(function () {
        /*if (applicant.id) {
         $('#cl' + applicant.id).prop('checked', false);
         }*/
        var is_remove = false;
        var index_element = jQuery.inArray($(this).attr("value"), applicant.id);
        if (index_element >= 0) {
            is_remove = true;
            applicant.id = jQuery.grep(applicant.id, function (elem, index) {
                return index != index_element;
            });
        }
        applicant.selectApplicant($(this).attr("value"), $(this).html(), true, is_remove, false);
    });

    /* $('#urlico').click(function(){ applicant.setUrlico(); });
     $('#ip').click(function(){ applicant.setIp(); });
     $('#fizlico').click(function(){ applicant.setFizlico(); });
     applicant.setUrlico();
     */


    $(".apllicant-field-container").on("focusout", "#czvr3", function () {

        applicant.checkInn($('#czvr3').val());
    });

    $('.c-content input[type="checkbox"]').click(function () {
        if ($(this).prop('checked')) {
            $('.c-cs-btns').addClass('c-cs-btns-after');
        } else {
            if ($("input[class='complaint-checkbox']:checked").length == 0) {
                $('.c-cs-btns').removeClass('c-cs-btns-after');
            }
        }
    });
});

function validateApplicantform() {
    applicantValidator.start();
    if (applicantValidator.result) {
        $(".active-tabs-content form").submit();
    } else {
        return false;
    }
}

var zakupka = {
    info: [],
};

var procedura = {
    info: [],
};

var applicant = {
    id: [],
    type: false,
    save: false,
    applicant_info: [],
    parseSnUr: function (data, searchValue, start, lenght) {
        for (var i = 0; i < data.length; i++) {
            var str = data[i];
            if (str.indexOf(searchValue) != -1) {
                return str.substr(start, str.length - lenght);
            }
        }
        return '';
    },
    parseUrLico: function (selectedCertif) {
        $('.tabs-ip').css('visibility', 'hidden');
        $('.tabs-fl').css('visibility', 'hidden');

        this.setUrlico();
        var data = selectedCertif.SubjectName;
        data = data.split(',');
        //"SN=Болквадзе, G=Мамука Фридонович, T=Генеральный директор, OID.1.2.840.113549.1.9.2="INN=7804525956/KPP=780401001/OGRN=1147847049998", CN=Болквадзе Мамука Фридонович, OU=0, O="ООО ПСК ""СТРОЙПРОЕКТ""", L=Санкт-Петербург, S=78 Санкт-Петербург, C=RU, E=f-ree-z@inbox.ru, INN=007804525956, OGRN=1147847049998, SNILS=08571995623"
        // "SN=Барба, G=Денис Валерьевич, T=Генеральный директор, OID.1.2.840.113549.1.9.2="INN=7811164468/KPP=781101001/OGRN=1157847035235", STREET=пр-т Солидарности 12/2Е - 10-Н, CN=Барба Денис Валерьевич, OU=0, O=ООО 'ГЕФЕСТ', L=Санкт-Петербург, S=78 г. Санкт-Петербург, C=RU, E=info@gefest-doors.ru, INN=007811164468, OGRN=1157847035235, SNILS=17739245308"


        console.log(data);


        $('#entity-short').val(this.parseSnUr(data, 'O=', 3, 4));
        $('#entity-inn').val(this.parseSnUr(data, ' INN=', 7, 4));

        var kpp = data[3];
        kpp = kpp.split('/');
        kpp = kpp[1];
        kpp = kpp.substr(4, kpp.length);
        $('#entity-kpp').val(kpp);
        $('#entity-address').val(this.parseSnUr(data, ' L=', 3, 0) + ' ' + this.parseSnUr(data, 'STREET=', 8, 1));
        $('#entity-position').val(this.parseSnUr(data, 'T=', 3, 0));
        $('#entity-fio-z').val(this.parseSnUr(data, 'CN=', 4, 0));

       // var email = data[10];
      //  email = email.substr(3, email.length);
        $('#entity-email').val(this.parseSnUr(data, ' E=', 3, 0));


    },
    parseIp: function (selectedCertif) {
        $('.tabs-ur').css('visibility', 'hidden');
        $('.tabs-fl').css('visibility', 'hidden');
        this.setIp();
        var data = selectedCertif.SubjectName;
        data = data.split(',');
        console.log(data);
        var shortName1 = data[0];
        shortName1 = shortName1.substr(3, shortName1.lenght);
        var shortName = data[1];
        shortName = shortName.substr(3, shortName.lenght);
        shortName = shortName1 + ' ' + shortName;
        $('#entity-short').val(shortName);
        var inn = data[9];
        inn = inn.substr(5, inn.length);
        $('#entity-inn').val(inn);

        var city = data[6];
        city = city.substr(6, city.lenght);
        var address = data[3];
        address = address.substr(8, address.lenght);
        $('#entity-address').val(city + ' ' + address);
        $('#entity-fio-z').val(shortName);

        var email = data[8];
        email = email.substr(3, email.lenght);
        $('#entity-email').val(email);


    },
    checkInn: function (inn) {
        if (!validator.numeric($('#czvr3').val(), 10, 10)) {
            applicantValidator.showError('#czvr3', 'Ошибка! ИНН состоит из 10 цифр');
            return false;
        }
        $.ajax({
            type: 'POST',
            url: '/applicant/checkinn',
            data: 'inn=' + inn,
            success: function (msg) {

                if (msg == 'true') {
                    applicant.save = false;
                    showMessagePopup('error', 'Заявитель с таким ИНН уже зарегистрирован в системе. ');
                    applicantValidator.showError('#czvr3', 'Ошибка! Заявитель с таким ИНН уже зарегистрирован в системе.');
                } else {
                    applicantValidator.done('#czvr3');
                    applicant.save = true;
                }
            },
            error: function (msg) {
                console.log(msg);
            }

        });
    },
    setUrlico: function () {
        this.type = 'urlico';
    },
    setIp: function () {
        this.type = 'ip';
    },
    setFizlico: function () {
        this.type = 'fizlico';
    },
    selectApplicant: function (id, name, redirect, is_remove, is_select_first) {
        if (!is_remove) {
            if (this.id == 'All') this.id = [];
            for (var i = 0; i < this.id.length; i++) {
                if (this.id[i] == 'All' || this.id[i] == '') {
                    this.id.splice(i, 1);
                }
            }
            this.id.push(id);
            if ($('.applicant-name-container').html().length) {
                var temp = $('.applicant-name-container').html();
                temp = temp.replace(name.trim(), '');
                temp = temp.split(',');
                for (var i = temp.length; i > 0; i--) {
                    if (temp[i] == " " || temp[i] == "") {
                        temp.splice(i, 1);
                    }
                }
                temp.push(name.trim());
                $('.applicant-name-container').html(temp.join(','));
            } else {
                if (name != undefined) {
                    $('.applicant-name-container').html(name.trim());
                }
            }
        } else {
            var temp = $('.applicant-name-container').html();
            temp = temp.split(',');
            for (var i = 0; i < temp.length; i++) {
                temp[i] = temp[i].trim();
                if (temp[i] == name.trim()) {
                    temp.splice(i, 1);
                }
            }

            if (temp.length == 0) {
                this.id.push('All');
            }
            $('.applicant-name-container').html(temp.join(','));
        }
        if (this.id.length == 0) {
            this.id.push("All");
            $('.applicant-name-container').empty();
        }
        ////if(typeof currentPage !== 'undefined' && currentPage == 'complaint/index' && redirect ){
        if (!is_select_first) {
            complaint.filterComplaintByApplicant(this.id);
        }
        // }
    },
    selectFirst: function (id, redirect) {
        id = id.split(',');
        var globalThis = this;
        $.each(id, function (index, value) {
            globalThis.selectApplicant(value, $('#name_applicant_' + value).html(), redirect, false, true);
            $('#cl' + value).prop('checked', true);
        });
    }

};
var applicantValidator = {
    result: true,
    start: function () {

        this.result = true;
        switch (applicant.type) {
            case 'urlico':
                /*  var field_selector = '.active-tabs-content #entity-textarea';
                 if (!validator.text($(field_selector).val(), 3, 255)) {
                 this.showError(field_selector, 'Ошибка! Полное наименование должно быть от 3 до 255 символов');
                 this.result = false;
                 } else {
                 this.done(field_selector);
                 } */
                var field_selector = '.active-tabs-content #entity-short';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Краткое наименование должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-inn';
                if (!validator.numeric($(field_selector).val(), 10, 10)) {
                    this.showError(field_selector, 'Ошибка! ИНН состоит из 10 цифр');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-kpp';
                if (!validator.numeric($(field_selector).val(), 9, 9)) {
                    this.showError(field_selector, 'Ошибка! КПП состоит из 9 цифр');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-address';
                if (!validator.text($(field_selector).val(), 3, 255)) {
                    this.showError(field_selector, 'Ошибка! Адрес местонахождения должен быть от 3 до 255 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-position';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Должность заявителя должна быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-fio-z';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО заявителя должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-fio-k';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО контактного лица должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-phone';
                if (!validator.text($(field_selector).val(), 5, 100)) {
                    this.showError(field_selector, 'Ошибка! Контактный факс, телефон должен быть от 5 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-email';
                if (!validator.email($(field_selector).val())) {
                    this.showError(field_selector, 'Ошибка! Неверный E-mail');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                break;
            case 'ip':
                /*  var field_selector = '.active-tabs-content #entity-textarea';
                 if (!validator.text($(field_selector).val(), 3, 255)) {
                 this.showError(field_selector, 'Ошибка! Полное наименование должно быть от 3 до 255 символов');
                 this.result = false;
                 } else {
                 this.done(field_selector);
                 } */
                var field_selector = '.active-tabs-content #entity-inn';
                if (!validator.numeric($(field_selector).val(), 12, 12)) {
                    this.showError(field_selector, 'Ошибка! ИНН состоит из 12 цифр');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-address';
                if (!validator.text($(field_selector).val(), 3, 255)) {
                    this.showError(field_selector, 'Ошибка! Адрес местонахождения должен быть от 3 до 255 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-position';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Должность заявителя должна быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-fio-z';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО заявителя должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-phone';
                if (!validator.text($(field_selector).val(), 5, 100)) {
                    this.showError(field_selector, 'Ошибка! Контактный факс, телефон должен быть от 5 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-email';
                if (!validator.email($(field_selector).val())) {
                    this.showError(field_selector, 'Ошибка! Неверный E-mail');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-fio-k';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО контактного лица должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                break;
            case 'fizlico':
                var field_selector = '.active-tabs-content #entity-fio-z';
                if (!validator.text($(field_selector).val(), 3, 255)) {
                    this.showError(field_selector, 'Ошибка! ФИО заявителя должно быть от 3 до 255 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-address';
                if (!validator.text($(field_selector).val(), 3, 255)) {
                    this.showError(field_selector, 'Ошибка! Адрес местонахождения должен быть от 3 до 255 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-phone';
                if (!validator.text($(field_selector).val(), 5, 100)) {
                    this.showError(field_selector, 'Ошибка! Контактный факс, телефон должен быть от 5 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-email';
                if (!validator.email($(field_selector).val())) {
                    this.showError(field_selector, 'Ошибка! Неверный E-mail');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.active-tabs-content #entity-fio-k';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО контактного лица должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                break;
            default:
                this.result = false;
                break;
        }
    },
    done: function (element) {
        $(element).addClass('c-inp-done');
        $(element).parent().children('.c-inp-err-t').remove();
    },
    showError: function (element, msg) {
        this.result = false;
        $(element).removeClass('c-inp-done');
        $(element).addClass('c-inp-error');
        $(element).parent().children('.c-inp-err-t').remove();
        $(element).parent().append('<div class="c-inp-err-t">' + msg + '</div>');

    }


}
