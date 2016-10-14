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
                    window.location = 'complaint/index';
                }else if(res.error){
                    for(mess in res.error){
                        $(this).find('.errors').html('<p>'+ res.error[mess] + '</p>');
                    }
                }
            },
        });
        return false;
    });
});
