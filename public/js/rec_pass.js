$(document).ready(function () {
    $('.rec-pass').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/register/recoverypass',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                if (res.status && res.status == 'ok'){
                    $('#pop-recovery').css('display', 'none');
                    $('#pop-recovery input[name="email"]').val('');
                    $('.pop-done-txt').html('На указанную вами электронную почту<br>'+ res.email +' отправлено письмо для завершения<br>восстановления пароля');
                    $('#pop-done').css('display', 'block').animate({opacity: 1, top: '35%'}, 200);
                    $('#overlay').css('display', 'block');
                } else if(res.error){
                    $('#pop-recovery').css('display', 'none');
                    $('#pop-recovery input[name="email"]').val('');
                    $('.pop-done-txt').html(res.error);
                    $('#pop-done').css('display', 'block').animate({opacity: 1, top: '35%'}, 200);
                }
            },
        });
        return false;
    });

    if(location.search == '?success=recovery'){
        //$('#pop-done').modal('show');
        $('#pop-done h2').text('Восстановление');
        $('.pop-done-txt').html('На ваш email выслан новый пароль');
        $('#pop-done').show();
        $('#overlay').show();

        $('body').on('click', '.logreg', function(){
            $('#pop-done').hide();
            $('#pop-login').show();
        });
    }
});
