$(document).ready(function () {
    $('.addAppCertificate__addBtn').click(function () {
        if (selectedCertif == false)
            return;
        //  if(edit_mode == 0)
        //     return;

        $('.addAppCertificate-main2').fadeOut().css('display', 'none');

        var userData = selectedCertif.SubjectName;

        if (userData.indexOf('OGRN=') != -1 || userData.indexOf('ОГРН=') != -1) {
            applicant.parseUrLico(selectedCertif);
        }
        if (userData.indexOf('OGRNIP=') != -1) {
            applicant.parseIp(selectedCertif);
        }
        if (userData.indexOf('ОГРНИП=') != -1) {
            applicant.parseIp(selectedCertif);
        }

        var str = selectedCertif.ValidFromDate;
        str = str.toString().substr(0, 10);
        var field = str + ' | ' + selectedCertif.SubjectDNSName;

        $('.ecp_ur').val(selectedCertif.Thumbprint);
        $('.ecp_text').val(field);

        $('.content').removeClass('hiddenClass');


        if ($('.tabcontent-ur #entity-inn').val()) {

            if($('.tabcontent-ur #entity-inn').val().substr(0, 1) == 0)
            {
                inn = "0"+parseInt($('.tabcontent-ur #entity-inn').val());
                //$('.tabcontent-ur #entity-inn').val(inn);
            }
            else
                inn = $('.tabcontent-ur #entity-inn').val();

            alert(inn);
            var url = 'https://ru.rus.company/интеграция/компании/?инн=' + inn;

            $.ajax({
                type: 'POST',
                data: 'url=' + url,
                url: '/applicant/getEgrulInfo',
                success: function (response) {
                    if (response.length) {
                        var url = 'https://ru.rus.company/интеграция/компании/' + response[0].id + '/';

                        $.ajax({
                            type: 'POST',
                            data: 'url=' + url,
                            url: '/applicant/getEgrulInfo',
                            success: function (response) {

                                if (!!response.id) {
                                    $('.tabcontent-ur #entity-address').val(response.address.fullHouseAddress);
                                    $('.tabcontent-ur #post-address').val(response.address.fullHouseAddress);
                                }
                            },
                            error: function (msg) {
                                console.log(msg);
                            }
                        });
                    }
                },
                error: function (msg) {
                    console.log(msg);
                }
            });
        }
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

    $("#add_applicant, .add_applicant").on('click', function (event) {
        var checkinn = false;
        $('input[name="inn"]').each(function () {
            if ($(this).val()) {
                checkinn = $(this).val();
            }
        });
        applicant.checkInn(checkinn);
        if (!applicant.save) return false;
        event.preventDefault();
        if ($(".modal-dialog.modal-sm").height() != null && $(".modal-dialog.modal-sm").height() > 0) {
            return false;
        }
        applicantValidator.start();
        if (applicantValidator.result) {
            //$('#applicant_form').submit();
            $(this).parent().submit();
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
    edit_mode: true,
    inn: false,
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
        $('#wrap_kpp').show();
        $('.tabcontent-ur').css('display', 'block');
        $('.tabcontent-ph').css('display', 'none');
        $('.tabcontent-ip').css('display', 'none');

        this.setUrlico();
        var name = this.getName(selectedCertif.SubjectName);
        var position = this.getPosition(selectedCertif.SubjectName);
        var street = this.getStreet(selectedCertif.SubjectName);
        var fio = this.getFio(selectedCertif.SubjectName);
        var email = this.getEmail(selectedCertif.SubjectName);
        var inn = this.getInn(selectedCertif.SubjectName);
        var kpp = this.getKpp(selectedCertif.SubjectName);

        $('.tabcontent-ur #entity-short').val((name) ? name : '');
        $('.tabcontent-ur #entity-inn').val((inn) ? inn : '');
        $('.tabcontent-ur #entity-kpp').val((kpp) ? kpp : '');
        $('.tabcontent-ur #entity-address').val((street) ? street : '');
        $('.tabcontent-ur #entity-position').val((position) ? position : '');
        $('.tabcontent-ur #entity-fio-z').val((fio) ? fio : '');
        $('.tabcontent-ur #entity-email').val((email) ? email : '');
    },

    getPosition: function (data) {
        var position = data.match(/[\s\,]T=([«»a-zA-Zа-яА-Я\s]+)/i);
        if (position) return position[1];
        return false;
    },

    getName: function (data) {
        var name = data.match(/[\s\,]O=([«»a-zA-Zа-яА-Я\s\"\']+)/i);
        if (name) return name[1];
        return false;
    },

    getInn: function (data) {
        var inn = data.match(/[\s\,]?INN=([\d]+)[\/,\,\s]?/i);
        if (!inn) inn = data.match(/[\s\,]?ИНН=([\d]+)[\/,\,\s]?/i);
        if (inn) return inn[1];
        return false;
    },

    getStreet: function (data) {
        var street = data.match(/[\s\,]?STREET=[\"]?([«»a-zA-Zа-яА-Я\s\"\'\.\,\d\/\-]+)([\"]|[\,])/);
        if (street) {
            return street[1].replace(/\"/g, '');
        }
        return false;
    },

    getFio: function (data) {
        var fio = data.match(/[\s\,]CN=([a-zA-Zа-яА-Я\s]+)[\/,\,\s]?/i);
        if (fio) return fio[1];
        return false;
    },

    getEmail: function (data) {
        var email = data.match(/[\s\,]E=([\w@\-\.]+)[\/,\,\s]?/i);
        if (email) return email[1];
        return false;
    },

    getKpp: function (data) {
        var kpp = data.match(/[\s\,]?КПП=([\d]+)[\/,\,\s]?/i);
        if (!kpp) kpp = data.match(/[\s\,]?KPP=([\d]+)[\/,\,\s]?/i);
        if (kpp) return kpp[1];
        return false;
    },


    /*
     "SN=Соколов"
     " G=Александр Ильич"
     " OID.1.2.840.113549.1.9.2="INN=780620162964""
     " STREET=ул. Михаила Дудина д. 25 кор. 1 кв. 764"
     " CN=Соколов Александр Ильич"
     " L=п. Парголово"
     " S=78 Санкт-Петербург"
     " C=RU"
     " E=sokolov_ai1982@mail.ru"
     " INN=780620162964"
     " SNILS=06260829053"
     " OGRNIP=316784700059783"
     */
    parseIp: function (selectedCertif) {
        $('.tabs-ur').css('visibility', 'hidden');
        $('.tabs-fl').css('visibility', 'hidden');
        $('#wrap_kpp').css('visibility', 'hidden');
        $('.tabcontent-ur').css('display', 'none');
        $('.tabcontent-ph').css('display', 'none');
        $('.tabcontent-in').css('display', 'block');

        this.setIp();
        var name = this.getName(selectedCertif.SubjectName);
        var street = this.getStreet(selectedCertif.SubjectName);
        var fio = this.getFio(selectedCertif.SubjectName);
        var inn = this.getInn(selectedCertif.SubjectName);
        var email = this.getEmail(selectedCertif.SubjectName);


        $('.tabcontent-in #entity-email').val((email) ? email : '');
        $('.tabcontent-in #entity-inn').val((inn) ? inn : '');
        $('.tabcontent-in #entity-address').val((street) ? street : '');
        $('.tabcontent-in #entity-fio-z').val((fio) ? fio : '');
        if (!name) {
            $('.tabcontent-in #entity-short').val('ИП ' + fio);
        } else {
            $('.tabcontent-in #entity-short').val(name);
        }

        if (this.edit_mode == false) {
            this.inn = inn;
            this.checkInn(inn);
        }
    },
    checkInn: function (inn) {
        /*if (!validator.numeric($('#czvr3').val(), 10, 10)) {
         applicantValidator.showError('#czvr3', 'Ошибка! ИНН состоит из 10 цифр');
         return false;
         }*/
        $.ajax({
            type: 'POST',
            url: '/applicant/checkinn',
            data: 'inn=' + inn,
            success: function (msg) {

                if (msg == 'true') {
                    applicant.save = false;
                    showMessagePopup('error', 'Заявитель с таким ИНН уже зарегистрирован в системе. ');
                    applicantValidator.showError('.ent-inn', 'Ошибка! Заявитель с таким ИНН уже зарегистрирован в системе.');
                } else {
                    applicantValidator.done('#entity-inn');
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
                var field_selector = '.tabcontent-ur #entity-short';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Краткое наименование должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #entity-inn';
                // if (!validator.numeric($(field_selector).val(), 10, 10)) {
                //     this.showError(field_selector, 'Ошибка! ИНН состоит из 10 цифр');
                //     this.result = false;
                // } else {
                //     this.done(field_selector);
                // }
                // var field_selector = '.tabcontent-ur #entity-kpp';
                // if (!validator.numeric($(field_selector).val(), 9, 9)) {
                //     this.showError(field_selector, 'Ошибка! КПП состоит из 9 цифр');
                //     this.result = false;
                // } else {
                //     this.done(field_selector);
                // }
                var field_selector = '.tabcontent-ur #entity-address';
                if (!validator.text($(field_selector).val(), 3, 255)) {
                    this.showError(field_selector, 'Ошибка! Адрес местонахождения должен быть от 3 до 255 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #entity-position';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Должность заявителя должна быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #entity-fio-z';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО заявителя должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #entity-fio-k';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО контактного лица должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #entity-phone';
                if (!validator.text($(field_selector).val(), 5, 100)) {
                    this.showError(field_selector, 'Ошибка! Контактный факс, телефон должен быть от 5 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #entity-email';
                if (!validator.email($(field_selector).val())) {
                    this.showError(field_selector, 'Ошибка! Неверный E-mail');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-ur #post-address';
                // if (!validator.post($(field_selector).val())) {
                //     this.showError(field_selector, 'Ошибка! Неверный почтовый индекс');
                //     this.result = false;
                // } else {
                //     this.done(field_selector);
                // }
                break;
            case 'ip':
                /*  var field_selector = '.active-tabs-content #entity-textarea';
                 if (!validator.text($(field_selector).val(), 3, 255)) {
                 this.showError(field_selector, 'Ошибка! Полное наименование должно быть от 3 до 255 символов');
                 this.result = false;
                 } else {
                 this.done(field_selector);
                 } */
                var field_selector = '.tabcontent-in #entity-short';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Краткое наименование должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                // var field_selector = '.tabcontent-in #entity-inn';
                // if (!validator.numeric($(field_selector).val(), 12, 12)) {
                //     this.showError(field_selector, 'Ошибка! ИНН состоит из 12 цифр');
                //     this.result = false;
                // } else {
                //     this.done(field_selector);
                // }
                var field_selector = '.tabcontent-in #entity-address';
                if (!validator.text($(field_selector).val(), 3, 255)) {
                    this.showError(field_selector, 'Ошибка! Адрес местонахождения должен быть от 3 до 255 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-in #entity-position';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! Должность заявителя должна быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-in #entity-fio-z';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО заявителя должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-in #entity-phone';
                if (!validator.text($(field_selector).val(), 5, 100)) {
                    this.showError(field_selector, 'Ошибка! Контактный факс, телефон должен быть от 5 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-in #entity-email';
                if (!validator.email($(field_selector).val())) {
                    this.showError(field_selector, 'Ошибка! Неверный E-mail');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                var field_selector = '.tabcontent-in #entity-fio-k';
                if (!validator.text($(field_selector).val(), 3, 100)) {
                    this.showError(field_selector, 'Ошибка! ФИО контактного лица должно быть от 3 до 100 символов');
                    this.result = false;
                } else {
                    this.done(field_selector);
                }
                // var field_selector = '.tabcontent-in #post-address';
                // if (!validator.post($(field_selector).val())) {
                //     this.showError(field_selector, 'Ошибка! Неверный почтовый индекс');
                //     this.result = false;
                // } else {
                //     this.done(field_selector);
                // }
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
            // var field_selector = '.active-tabs-content #post-address';
            // if (!validator.post($(field_selector).val())) {
            //     this.showError(field_selector, 'Ошибка! Неверный почтовый индекс');
            //     this.result = false;
            // } else {
            //     this.done(field_selector);
            // }
            // break;
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
