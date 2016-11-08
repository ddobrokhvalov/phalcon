$(document).ready(function () {
    $('#pop-login form').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/login/start',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                if(res.status && res.status == 'ok'){
                    window.location = '/complaint/index';
                }else if(res.error){
                    $(this).find('input').each(function(){
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

    $('#pop-login input').on('keyup', function(){
        $('#pop-login .c-inp-error').each(function(){
            $(this).removeClass('c-inp-error');
            $(this).addClass('c-inp-done');
            $(this).prev(".c-inp-err-t").text('');
        });
    });
});
