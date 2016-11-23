$(document).ready(function () {
    $('.reg-new-user').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/register/index',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                console.log(res);
                if (res.status){
                    switch (res.status) {
                        case 'ok':
                            $(this).find('input[name="email"]')
                                .removeClass('c-inp-error')
                                .removeClass('c-inp-done')
                                .val('');
                            $(this).find('input[name="password"]')
                                .removeClass('c-inp-error')
                                .removeClass('c-inp-done')
                                .val('');
                            $(this).find('input[name="confpassword"]')
                                .removeClass('c-inp-error')
                                .removeClass('c-inp-done')
                                .val('');
                            $(this).find('.c-inp-err-t').text('');
                            $(this).parent().hide();
                            $('#pop-done h2').text('Подтверждение');
                            $('.pop-done-txt').html('На указанную вами электронную почту<br>'+ res.email +' отправлено письмо для завершения регистрации');
                            $('#pop-done').show().animate({opacity: 1}, 200);
                        break;
                    }
                } else if(res.error){
                    $(this).find('input').each(function() {
                        $(this).removeClass('c-inp-done');
                    });
                    for(mess in res.error){
                        $(this).find('input[name="'+ mess +'"]').prev(".c-inp-err-t").text(res.error[mess]);
                        $(this).find('input[name="'+ mess +'"]').addClass('c-inp-error');
                    }
                }
            },
        });
        return false;
    });

    $('.reg-new-user input').on('keyup', function(){
        if($(this).attr("type") != 'submit'){
            $(this).prev(".c-inp-err-t").text('');
            $(this).addClass('c-inp-done');
        }
    });


    if(location.search == '?success=confirm'){
        //$('#pop-done').modal('show');
        $('#pop-done h2').text('Подтверждение');
        $('.pop-done-txt').html('Наши поздравления, Вы зарегистрировались в<br> интеллектуальной системе ФАС-Онлайн.<br/> <a href="#" class="logreg">Авторизируйтесь</a>, чтобы начать работу');
        $('#pop-done').show();
        $('#overlay').show();

        $('body').on('click', '.logreg', function(){
            $('#pop-done').hide();
            $('#pop-login').show();
        });
    }

});