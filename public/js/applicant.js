$(document).ready(function () {
    var url      = window.location.href;
    if (typeof applicantFirstId !== 'undefined' && url.indexOf('applicant_id=') == -1)
        applicant.selectFirst(applicantFirstId,true);


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
});
var applicant = {
    id: false,
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
        this.result = true;

        if (!validator.text($('#czvr1').val(), 3, 200))
            this.showError('#czvr1', 'Ошибка! Полное наименование должно быть от 3 до 200 символов');
        else
            this.done('#czvr1');

        if (!validator.text($('#czvr2').val(), 3, 200))
            this.showError('#czvr2', 'Ошибка! Краткое наименование должно быть от 3 до 50 символов');
        else
            this.done('#czvr2');

        if (!validator.numeric($('#czvr3').val(), 10, 10))
            this.showError('#czvr3', 'Ошибка! ИНН состоит из 10 цифр');
        else
            this.done('#czvr3');

        if (!validator.numeric($('#czvr4').val(), 9, 9))
            this.showError('#czvr4', 'Ошибка! КПП состоит из 9 цифр');
        else
            this.done('#czvr4');

        if (!validator.text($('#czvr5').val(), 3, 100))
            this.showError('#czvr5', 'Ошибка! Адрес местонахождения должен быть от 3 до 100 символов');
        else
            this.done('#czvr5');

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