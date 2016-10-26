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
                    $('#pop-recovery').hide();
                    $('#pop-recovery input[name="email"]').val('');
                    $('.pop-done-txt').html('На указанную вами электронную почту<br>'+ res.email +' отправлено письмо для завершения<br>восстановления пароля');
                    $('#pop-done').show();
                } else if(res.error){
                    $('#pop-recovery').hide();
                    $('#pop-recovery input[name="email"]').val('');
                    $('.pop-done-txt').html(res.error);
                    $('#pop-done').show();
                }
            },
        });
        return false;
    });
});
