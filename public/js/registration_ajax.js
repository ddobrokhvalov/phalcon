$(document).ready(function () {
    $('.reg-new-user').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/register/index',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res){
                if(res.status){
                    switch(res.status){
                        case 'ok':
                            $(this).html('<strong style="font-size: 20px;">На указанный вами почтовый ящик отправлена ссылка для завершения регистрации</strong><br/>');
                            $(this).append('<button>Закрыть</button>');
                            break;
                        case 'user exists':
                            $(this).html('<strong style="font-size: 20px;">«Уже зарегистрированы – авторизуйтесь»</strong><br/>');
                            $(this).append('<a >Закрыть</a>');
                            break;

                    }

                }
                if(res.status == 'ok'){
                    $(this).html('<strong style="font-size: 20px;">На указанный вами почтовый ящик отправлена ссылка для завершения регистрации</strong><br/>');
                    $(this).append('<button>Закрыть</button>');
                }
            },
        });
        return false;
    });
});