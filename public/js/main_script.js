jQuery(document).ready(function($) {
	// challenge left menu
	$('.lm-burger-open').click(function() {
		$('.admin-lm-wrap').animate({left: 0}, 400);
		$('.lm-burger-open').hide();
		$('.lm-burger-close').show();
	});
	$('.lm-burger-close').click(function() {
		$('.admin-lm-wrap').animate({left: -242}, 400);
		$('.lm-burger-open').show();
		$('.lm-burger-close').hide();
	});
	// checkbox styling for left menu
	$('.lm-menu input[type="checkbox"]').click(function() {
		if ($(this).prop('checked')) {
			$(this).parent().addClass('lm-active-checkbox');
		} else {
			$(this).parent().removeClass('lm-active-checkbox');
		}
	});
	// checkbox styling for main
	$('.admin-main-wrap input[type="checkbox"]').click(function() {
		if ($(this).prop('checked')) {
			$(this).parent().addClass('main-active-checkbox');
		} else {
			$(this).parent().removeClass('main-active-checkbox');
		}
	});
	// checkbox styling for popup 
	$('.admin-popup-content input[type="checkbox"]').click(function() {
		if ($(this).prop('checked')) {
			$(this).parent().find('div').css('background-position', '0 bottom');
		} else {
			$(this).parent().find('div').css('background-position', '0 0');
		}
	});
	// psevdo checkbox on table
	var selectObject = false;
	$('.select-all').click(function() {
		selectObject = $(this).find('div');
		$(this).find('div').toggleClass('select-checked');
		if ($(selectObject).hasClass('select-checked')) {
			$(this).parent().parent().find('.lt-psevdo-check').addClass('psevdo-checked');
			$(this).parent().parent().find('.lt-content-main').each(function() {
				if ($(this).hasClass('red-bg')) {
					$(this).css('background-color', '#fef0f2');
				} else {
					$(this).css('background-color', '#e5f7fd');
				}
			});
		} else {
			$(this).parent().parent().find('.lt-psevdo-check').removeClass('psevdo-checked');
			$(this).parent().parent().find('.lt-content-main').each(function() {
				if ($(this).hasClass('red-bg')) {
					$(this).css('background-color', '#fef0f2');
				} else {
					$(this).css('background-color', '#fff');
				}
			});
		}
	});
	$('.lt-content-main').click(function() {
		$(this).find('.lt-psevdo-check').toggleClass('psevdo-checked');
		if ($(this).find('.lt-psevdo-check').hasClass('psevdo-checked')) {
			if ($(this).hasClass('red-bg')) {
				$(this).css('background-color', '#fef0f2');
			} else {
				$(this).css('background-color', '#e5f7fd');
			}
		} else {
			if ($(this).hasClass('red-bg')) {
				$(this).css('background-color', '#fef0f2');
			} else {
				$(this).css('background-color', '#fff');
			}
		}
	});
	// change text on arguments list category
	$('.lt-arg-second .lt-content-main li p').click(function() {
		var pText = $(this).text();
		if ($(this).parent().find('span').css('display') == 'none') {
			$(this).parent().find('input').fadeIn('fast').val(pText);
			$(this).parent().find('span').fadeIn('fast');
			$(this).parent().find('input').click(function() {
				$(this).parent().find('.lt-psevdo-check').toggleClass('psevdo-checked');
			});
		}
	});
	$('.lt-arg-second .lt-content-main li span').click(function() {
		var inputVal = $(this).parent().find('input');
		$(this).parent().find('p').text(inputVal.val());
		$(this).fadeOut('fast');
		inputVal.fadeOut('fast');
	});
	// editing of the applicant's complaint - the backlight in the header buttons
	$('.appllicant-page .lt-content-main, .appllicant-page .select-all').click(function() {
		if ($('.appllicant-page .lt-psevdo-check').hasClass('psevdo-checked')) {
			$('.appllicant-page .lt-head-btns').addClass('active-lt-head-btns');
		} else {
			$('.appllicant-page .lt-head-btns').removeClass('active-lt-head-btns');
		}
	});
	// close admin popup
	$('.admin-popup-bg, .admin-popup-close').click(function() {
		$('.admin-popup-wrap').fadeOut();
	});
	// show complain details
	$('.deploy-complaint').click(function() {
		$('.deploy-complaint span').toggleClass('dep-up');
		$('.complaints-content').slideToggle();
	});
	// show and close answer to lawyer question on complaints
	$('.opacity-btn').click(function() {
		$(this).hide();
		$(this).parent().find('.hidden-textarea').show();
		$(this).parent().find('.hidden-textarea div').animate({opacity: 1}, 700);
		$(this).parent().find('span').css('display', 'block').animate({
			opacity: 1,
			marginTop: 25
		}, 300);
		$(this).parent().find('button').animate({
			top: 284,
			left: 600
		}, 300);
		$(this).parent().find('textarea').animate({
			padding: '16px 26px',
			width: '100%',
			height: '140px'
		}, 300);
	});
	$('.hidden-textarea div').click(function() {
		var thisCross = $(this);
		$(this).animate({opacity: 0}, 300);
		$(this).parent().find('span').css('display', 'block').animate({
			opacity: 0,
			marginTop: 0
		}, 300);
		$(this).parent().parent().find('button').animate({
			top: 72,
			left: 0
		}, 350);
		$(this).parent().find('textarea').animate({
			padding: 0,
			width: 0,
			height: 0
		}, 300);
		setTimeout(function() {
			thisCross.parent().slideUp(100);
		}, 200);
		$(this).parent().parent().find('.opacity-btn').show();
	});
	// tabs show and hide
	$('.aplicant-tabs-label div').click(function() {
		var tabsLabelBtnClass = $(this).attr('data-tabsLabel');
		$('.aplicant-tabs-label div').removeClass('active-tabs-label');
		$(this).addClass('active-tabs-label');
		$('.tabs-content').each(function() {
			if ($(this).attr('data-tabs') == tabsLabelBtnClass) {
				$('.tabs-content').removeClass('active-tabs-content');
				$(this).addClass('active-tabs-content');
			}
		}); 
	});
	// call and close administrator settings panel 
	$('.admin-settings').click(function() {
		$('.admin-settings-modal').slideDown('fast');
	});
	$('.admin-set-top, .admin-set-bg').click(function() {
		$('.admin-settings-modal').slideUp('fast');
	});
	// determining the height of the cells in the first table of the user page
	$('.userPageLtContent .lt-content-main').each(function() {
		userPageLtContentLi = 0;
		$(this).find('li').each(function() {
			if ($(this).height() > userPageLtContentLi) {
				userPageLtContentLi = $(this).height();
			}
		});
		$(this).find('li').height(userPageLtContentLi);
	});
	// change status
	$('.appll-status-icon').click(function() {
		$(this).parent().find('.close-status-list').show();
		$('.close-status-list').css('z-index', 11);
		$(this).parent().parent().addClass('someClass').append(statModal);
		$('.status-list-holder li').click(function(){
			$(this).closest('.j-user-info').find('.j-user-status-info input.j-comlient-status').val($(this).attr('rel'));
		});
	});
	$('.close-status-list').click(function() {
		$(this).hide();
		$(this).parent().next().remove();
	});

	$('#j-profile-save').click(function() {
		ajaxProfileUpdate();
		return false;
	});

	$('.alert-box').on('click', 'div', function () {
		$('.alert-wrap, .alert-box').fadeOut(400);
	});

	$('.alert-substrate').on('click', function () {
		$('.alert-wrap, .alert-box').fadeOut(400);
	});

    $('.j-change-access').on('click', function () {
        $('.admin-popup-wrap').fadeIn(200);
    });

});
var userPageLtContentLi = 0;
//status block
var statModal = '<ul class="status-list-holder">' +
					'<li rel = "draft">Черновик</li>' +
					'<li rel = "submitted">Подана</li>' +
					'<li rel = "under_consideration">На рассмотрении</li>' +
					'<li rel = "justified">Обоснована</li>' +
					'<li rel = "unfounded">Необоснована</li>' +
					'<li rel = "recalled">Отозвана</li>' +
					'<li rel = "archive">Архив</li>' +
				'</ul>';

function readAvatarURL(input) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			$('#cur-avatar')
				.attr('src', e.target.result);
		};
		reader.readAsDataURL(input.files[0]);
	}
}

function ajaxProfileUpdate() {
	var formData = new FormData();
	if($("#download-avatar").val()!=='')
		formData.append('file', $("#download-avatar")[0].files[0]);
	formData.append('email', $("#adm-set-email").val());
	formData.append('name', $("#adm-set-name").val());
	formData.append('surname', $("#adm-set-surname").val());
	formData.append('patronymic', $("#adm-set-patronymic").val());
	formData.append('opassword', $("#set-oldPass").val());
	formData.append('password', $("#set-newPass").val());
	formData.append('rpassword', $("#set-newPass-agane").val());
	$.ajax({
		url : '/admin/admins/profilesave',
		type : 'POST',
		data : formData,
		processData: false,
		dataType: 'json',
		contentType: false,
		enctype: 'multipart/form-data',
		success : function(data) {
			if(data['success']==true)
				showSomePopupMessage('info', 'Ваш профиль успешно обновлен');
			else {
				var errors_list = '';
				for(var index in data['errors'] ) 
					errors_list = errors_list + data['errors'][index]+'\n\r';				
				showSomePopupMessage('error', errors_list);
			}

		}
	});
	return false;
}

function showSomePopupMessage(type, message) {
	$('.alert-wrap').fadeIn(400).css('position','static');
	setTimeout(function () {
		$('.alert-box').fadeIn(200).text(message);
		$('.alert-box').append('<div></div>');
	}, 400);
	if (type == 'info') {
		$('.alert-box').addClass('alert-info');
	}
}