$(document).ready(function(){
    $(".save-btn").click(function(event){
        event.preventDefault();
        ufasValidator.start();
        if (ufasValidator.result) {
            $.when(
                $.ajax({
                    url: "/admin/ufas/checkInn",
                    type:'POST',
                    data: { inn: $("#number").val() },
                    dataType: 'json'
                })
            ).done(function(data) {debugger;
                if (data.success == 'ok') {
                   ufasValidator.done("#number");
                   $('#ufas-form').submit();
                } else {
                    ufasValidator.showError("#number", 'Ошибка! Такой налоговый номер уже существует');
                    return false;
                }
            });
        } else {
            event.preventDefault();
            return false;
        }
    });
});

var ufasValidator = {
    result: true,
    start: function () {

        this.result = true;
        var field_selector = '#name';
        if (!validator.emptyText($(field_selector).val(), 1)) {
           this.showError(field_selector, 'Ошибка! Наименование территориального органа не может быть пустым');
           this.result = false;
        } else {
           this.done(field_selector);
        }
        var field_selector = '#number';
        if (!validator.numeric($(field_selector).val(), 2, 2)) {
           this.showError(field_selector, 'Ошибка! Налоговый номер состоит из 2 цифр');
           this.result = false;
        } else {
           this.done(field_selector);
        }
        var field_selector = '#address';
        if (!validator.emptyText($(field_selector).val(), 1)) {
           this.showError(field_selector, 'Ошибка! Адрес не должен быть пустым');
           this.result = false;
        } else {
           this.done(field_selector);
        }
        var field_selector = '#phone';
        if (!validator.text($(field_selector).val(), 1)) {
           this.showError(field_selector, 'Ошибка! Телефон/факс не должен быть пустым');
           this.result = false;
        } else {
           this.done(field_selector);
        }
        var field_selector = '#email';
        if (!validator.email($(field_selector).val())) {
           this.showError(field_selector, 'Ошибка! Неверный E-mail');
           this.result = false;
        } else {
           this.done(field_selector);
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