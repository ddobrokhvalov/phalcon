

$(document).ready(function () {
    $('#edit-user').on('submit', function(){
        var form = $(this);
        $.ajax({
            type: 'POST',
            url: '/users/changeSettings',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                
                if (res.status && res.status == 'ok') {
                    $('.ch-r-sett-dd').slideToggle(300);
                    $('.opacity-cap-compl').attr('data-userfields', '0');
                    $('.ch-r-s-inf-f.userData .c-inp-err-t').text('');
                } else if (res.error) {
                   
                    $('.ch-r-s-inf-f.userData .c-inp-err-t').text('');
                    for (error in res.error) {
                        var keyName = error,
                            keyData = res.error[error];
                        $('.ch-r-s-inf-f input').each(function () {
                            var thisName = $(this).attr('name');
                            if (thisName === keyName) {
                                $(this).parent().find('.c-inp-err-t').text(keyData);
                            }
                        });
                    }
                }
            },
        });
        return false;
    });
	$(".change_password_link").click(function(){
		$("#pop-change_password input[type='password']").val("");
		$("#pop-change_password").show();
		$("#pop-change_password").css("opacity", 1);
	});
	$("#change_password_done_close").click(function(){
		$("#pop-change_password_done").hide();
	});
	$("#edit-user-pass").on('submit', function(){
		$("c-inp-err-t").html("");
		var form = $(this);
		if(!$("#edit-user-pass #usri2").val()){
			//$("#edit-user-pass #usri2").prev(".c-inp-err-t").html("Неправильный новый пароль");
			//return false;
		}
		if(!$("#edit-user-pass #usri3").val()){
			//$("#edit-user-pass #usri3").prev(".c-inp-err-t").html("Неправильное подтверждние пароля");
			//return false;
		}
        $.ajax({
            type: 'POST',
            url: '/users/changePassword',
            data: $(this).serialize(),
            dataType: "json",
            context: form,
            success: function(res) {
                
                if (res.status && res.status == 'ok') {
                    //$('.ch-r-sett-dd').slideToggle(300);
					$('#pop-change_password').hide();
					$("#pop-change_password_done").show();
					$("#pop-change_password_done").css("opacity", 1);
                    $('.opacity-cap-compl').attr('data-userfields', '0');
                    $('.ch-r-s-inf-f.userData .c-inp-err-t').text('');
                } else if (res.error) {
                   
                    $('.ch-r-s-inf-f.userData .c-inp-err-t').text('');
                    for (error in res.error) {
                        var keyName = error,
                            keyData = res.error[error];
                        $('.ch-r-s-inf-f input').each(function () {
                            var thisName = $(this).attr('name');
                            if (thisName === keyName) {
                                $(this).parent().find('.c-inp-err-t').text(keyData);
                            }
                        });
                    }
                }
            },
        });
		return false;
    });
});