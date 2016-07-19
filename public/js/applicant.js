$(document).ready(function () {
    
    /*$(".active-tabs-content form").submit(function( event ) {debugger;
      alert("Handler for .submit() called.");
      if (!applicantValidator.result) {
        event.preventDefault();
      }
    });*/
    
    var url = window.location.href;
    if (typeof applicantFirstId !== 'undefined' && url.indexOf('applicant_id=') == -1)
        applicant.selectFirst(applicantFirstId,false);


   if(typeof applicantSelectedId !== 'undefined' && applicantSelectedId != 'All')
       applicant.selectFirst(applicantSelectedId,false);

    $("#add_applicant, .add_applicant").click(function (event) {
        event.preventDefault();
        if ($(".modal-dialog.modal-sm").height() != null && $(".modal-dialog.modal-sm").height() > 0) {
            return false;
        }
        applicantValidator.start();
        if (applicantValidator.result) {
            $('#applicant_form').submit();
            $(".active-tabs-content form").submit();
        } else {
            event.preventDefault();
            return false;
        }
    });

    $('.select_applicant').click(function () {
        debugger;
        /*if (applicant.id) {
            $('#cl' + applicant.id).prop('checked', false);
        }*/
        var is_remove = false;
        var index_element = jQuery.inArray($(this).attr("value"), applicant.id);
        if (index_element >=0) {
            is_remove = true;
            applicant.id = jQuery.grep(applicant.id, function(elem, index){
                return index != index_element;
            });
        }
        applicant.selectApplicant($(this).attr("value"), $(this).html(), true, is_remove, false);
    });

    $('#urlico').click(function(){ applicant.setUrlico(); });
    $('#ip').click(function(){ applicant.setIp(); });
    $('#fizlico').click(function(){ applicant.setFizlico(); });
    applicant.setUrlico();



    $(".apllicant-field-container").on("focusout", "#czvr3", function () {

        applicant.checkInn($('#czvr3').val());
    });

    $('.c-content input[type="checkbox"]').click(function() {
        debugger;
        if ($(this).prop('checked')) {
            $('.c-cs-btns').addClass('c-cs-btns-after');
        } else {
            if($( "input[class='complaint-checkbox']:checked" ).length == 0) {
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

var applicant = {
    id: [],
    type:false,
    save:false,
    checkInn: function(inn){
        if (!validator.numeric($('#czvr3').val(), 10, 10)) {
            applicantValidator.showError('#czvr3', 'Ошибка! ИНН состоит из 10 цифр');
            return false;
        }
        $.ajax({
            type: 'POST',
            url: '/applicant/checkinn',
            data: 'inn=' + inn,
            success: function (msg) {

              if(msg == 'true'){
                  applicant.save = false;
                  showMessagePopup('error','Заявитель с таким ИНН уже зарегистрирован в системе. ');
                  applicantValidator.showError('#czvr3', 'Ошибка! Заявитель с таким ИНН уже зарегистрирован в системе.');
              }else{
                  applicantValidator.done('#czvr3');
                  applicant.save = true;
              }
            },
            error: function (msg) {
                console.log(msg);
            }

        });
    },
    setUrlico: function(){
       this.type = 'urlico';
    },
    setIp:function(){
        this.type = 'ip';
    },
    setFizlico:function(){
        this.type = 'fizlico';
    },
    selectApplicant: function (id, name, redirect, is_remove, is_select_first) {
        debugger;
        if (!is_remove) {
            this.id.push(id);
            if ($('.applicant-name-container').html().length) {
                $('.applicant-name-container').html($('.applicant-name-container').html().trim() + ", " + name.trim());
            } else {
                if(name != undefined) {
                    $('.applicant-name-container').html(name.trim());
                }
            }
        }else {
            var temp = $('.applicant-name-container').html();
            temp = temp.replace(name.trim(),'');
            temp = temp.split(',');
            for (var i = temp.length; i > 0; i--) {
                if (temp[i] == " ") {
                    temp.splice(i,1);
                }
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
        $.each(id, function(index, value){
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
               var field_selector = '.active-tabs-content #entity-textarea';
               if (!validator.text($(field_selector).val(), 3, 255)) {
                   this.showError(field_selector, 'Ошибка! Полное наименование должно быть от 3 до 255 символов');
                   this.result = false;
               } else {
                   this.done(field_selector);
               }
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
               var field_selector = '.active-tabs-content #entity-textarea';
               if (!validator.text($(field_selector).val(), 3, 255)) {
                   this.showError(field_selector, 'Ошибка! Полное наименование должно быть от 3 до 255 символов');
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
