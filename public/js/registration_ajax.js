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
                    $(this).html('<strong style="font-size: 20px;">На указанный вами почтовый ящик отправлена ссылка для завершения регистрации</strong>');
                    $(this).append('<button>Закрыть</button>');
                }
            },
        });
        return false;
    });
});