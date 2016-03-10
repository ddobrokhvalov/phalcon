$(document).ready(function () {
    var url      = window.location.href;
    if (typeof applicantFirstId !== 'undefined' && url.indexOf('applicant_id=') == -1)
        applicant.selectFirst(applicantFirstId,false);


   if(typeof applicantSelectedId !== 'undefined')
       applicant.selectFirst(applicantSelectedId,false);

    $("#add_applicant").click(function (event) {
        event.preventDefault();
        applicantValidator.start();
        if (applicantValidator.result)
            $('#applicant_form').submit();
    });

    $('.select_applicant').click(function () {
        if (applicant.id) {
            $('#cl' + applicant.id).prop('checked', false);
        }
        applicant.selectApplicant($(this).attr("value"), $(this).html(),true);
    });

    $('#urlico').click(function(){ applicant.setUrlico(); });
    $('#ip').click(function(){ applicant.setIp(); });
    $('#fizlico').click(function(){ applicant.setFizlico(); });
    applicant.setUrlico();



    $(".apllicant-field-container").on("focusout", "#czvr3", function () {

        applicant.checkInn($('#czvr3').val());
    });
});
var applicant = {
    id: false,
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
        $('.apllicant-field-container').html(applicantField.urlico);

    },
    setIp:function(){
        this.type = 'ip';
        $('.apllicant-field-container').html(applicantField.ip);
    },
    setFizlico:function(){
        this.type = 'fizlico';
        $('.apllicant-field-container').html(applicantField.fizlico);

    },
    selectApplicant: function (id, name,redirect) {
        this.id = id;
        $('.applicant-name-container').html(name);
        if(typeof currentPage !== 'undefined' && currentPage == 'complaint/index' && redirect ){
            complaint.filterComplaintByApplicant(id);
        }
    },
    selectFirst: function (id,redirect) {
        this.selectApplicant(id, $('#name_applicant_' + id).html(),redirect);
        $('#cl' + id).prop('checked', true);
    }

};
var applicantValidator = {
    result: true,
    start: function () {

        if(applicant.save === false && applicant.type != 'fizlico'){
            showMessagePopup('error','Заполните все поля корректно');
            this.result = false;
            return false;
        }

        this.result = true;
       if(applicant.type == 'urlico' || applicant.type =='ip')
        if (!validator.text($('#czvr1').val(), 3, 200) )
            this.showError('#czvr1', 'Ошибка! Полное наименование должно быть от 3 до 200 символов');
        else
            this.done('#czvr1');
        if(applicant.type == 'urlico')
            if (!validator.text($('#czvr2').val(), 3, 200))
            this.showError('#czvr2', 'Ошибка! Краткое наименование должно быть от 3 до 50 символов');
        else
            this.done('#czvr2');
        if(applicant.type == 'urlico' || applicant.type =='ip')
        if (!validator.numeric($('#czvr3').val(), 10, 10))
            this.showError('#czvr3', 'Ошибка! ИНН состоит из 10 цифр');
        else
            this.done('#czvr3');
        if(applicant.type == 'urlico')
            if (!validator.numeric($('#czvr4').val(), 9, 9))
            this.showError('#czvr4', 'Ошибка! КПП состоит из 9 цифр');
        else
            this.done('#czvr4');

        if (!validator.text($('#czvr5').val(), 3, 100))
            this.showError('#czvr5', 'Ошибка! Адрес местонахождения должен быть от 3 до 100 символов');
        else
            this.done('#czvr5');
        if(applicant.type == 'urlico' || applicant.type =='ip')
        if (!validator.text($('#czvr6').val(), 3, 100))
            this.showError('#czvr6', 'Ошибка! Должность заявителя должна быть от 3 до 100 символов');
        else
            this.done('#czvr6');

        if (!validator.text($('#czvr7').val(), 3, 100))
            this.showError('#czvr7', 'Ошибка! ФИО заявителя должна быть от 3 до 100 символов');
        else
            this.done('#czvr7');

        if (!validator.text($('#czvr8').val(), 3, 100))
            this.showError('#czvr8', 'Ошибка! ФИО контактного лица должна быть от 3 до 100 символов');
        else
            this.done('#czvr8');

        if (!validator.text($('#czvr9').val(), 3, 20))
            this.showError('#czvr9', 'Ошибка! Контактный факс, телефон должен быть');
        else
            this.done('#czvr9');

        if (!validator.email($('#czvr10').val()))
            this.showError('#czvr10', 'Ошибка! Неверный E-mail');
        else
            this.done('#czvr10');


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
var applicantField = {
    urlico: '<div class="c-zv-f-str"><label for="czvr1">Полное наименование</label><textarea id="czvr1" name="name_full" ></textarea></div><div class="c-zv-f-str"><label for="czvr2">Краткое наименование</label><input type="text" id="czvr2" name="name_short"> </div><div class="c-zv-f-str"><label for="czvr3">ИНН</label><input type="text" id="czvr3" name="inn" class="c-zv-frmb-in1" ></div><div class="c-zv-f-str"><label for="czvr4">КПП</label><input type="text" id="czvr4" name="kpp" class="c-zv-frmb-in1"></div><div class="c-zv-f-str"><label for="czvr5">Адрес местонахождения</label><input type="text" id="czvr5" name="address" class="c-zv-frmb-in2"></div><div class="c-zv-f-str"><label for="czvr6">Должность заявителя</label><input type="text" name="position" id="czvr6"></div><div class="c-zv-f-str"><label for="czvr7">ФИО заявителя</label><input type="text" id="czvr7" name="fio_applicant"></div><div class="c-zv-f-str"><label for="czvr8">ФИО контактного лица</label><input type="text" id="czvr8" name="fio_contact_person"></div><div class="c-zv-f-str"><label for="czvr9">Контактный факс, телефон</label><input type="text" id="czvr9" name="telefone" class="c-zv-frmb-in1"></div><div class="c-zv-f-str"><label for="czvr10">E-mail</label><input type="text" name="email" id="czvr10"></div>',
    ip: '<div class="c-zv-f-str"><label for="czvr1">Полное наименование</label><textarea id="czvr1" name="name_full" ></textarea></div><div class="c-zv-f-str"><label for="czvr3">ИНН</label><input type="text" id="czvr3" name="inn" class="c-zv-frmb-in1" ></div><div class="c-zv-f-str"><label for="czvr5">Адрес местонахождения</label><input type="text" id="czvr5" name="address" class="c-zv-frmb-in2"></div><div class="c-zv-f-str"><label for="czvr6">Должность заявителя</label><input type="text" name="position" id="czvr6"></div><div class="c-zv-f-str"><label for="czvr7">ФИО заявителя</label><input type="text" id="czvr7" name="fio_applicant"></div><div class="c-zv-f-str"><label for="czvr8">ФИО контактного лица</label><input type="text" id="czvr8" name="fio_contact_person"></div><div class="c-zv-f-str"><label for="czvr9">Контактный факс, телефон</label><input type="text" id="czvr9" name="telefone" class="c-zv-frmb-in1"></div><div class="c-zv-f-str"><label for="czvr10">E-mail</label><input type="text" name="email" id="czvr10"></div>',
    fizlico: '<div class="c-zv-f-str"><label for="czvr7">ФИО заявителя</label><input type="text" id="czvr7" name="fio_applicant"></div><div class="c-zv-f-str"><label for="czvr5">Адрес местонахождения</label><input type="text" id="czvr5" name="address" class="c-zv-frmb-in2"></div><div class="c-zv-f-str"><label for="czvr8">ФИО контактного лица</label><input type="text" id="czvr8" name="fio_contact_person"></div><div class="c-zv-f-str"><label for="czvr9">Контактный факс, телефон</label><input type="text" id="czvr9" name="telefone" class="c-zv-frmb-in1"></div><div class="c-zv-f-str"><label for="czvr10">E-mail</label><input type="text" name="email" id="czvr10"></div>',

};