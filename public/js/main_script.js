var arrCheck = new Array();
jQuery(document).ready(function($) {
    $(".modal-close").click(function(){
        var obj = $(this);
        $('#overlay').fadeOut(400,
            function(){
                $(obj).parent().css('display', 'none');
            });
    });
    $('.lawyer-wrap .lebel-checkbox').click(function(evt){
        evt.preventDefault();
        if($(this).hasClass('main-active-checkbox')){
            $(this).removeClass('main-active-checkbox');
            $('#only_new_form #only_new').val('');
            $('#only_new_form').submit();
        } else{
            $(this).addClass('main-active-checkbox');
            $('#only_new_form #only_new').val('j');
            $('#only_new_form').submit();
        }
    });
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
        arrCheck = [];
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
                arrCheck.push({
                    'id': $(this).find('#complaint-id').val(),
                    'status': $(this).attr('sort-status')
                });
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
            arrCheck = [];
		}
        changeShowHideButtonBackground();
        changeBlockUnblockButtonBackground();
        changeApplicantsButtonBackground();
        changeAdminButtonsBackground();
        changeComplaintButtonsBackground();
        changeUsersDetailsButtonsBackground();
        changeMessagesButtonBackground();
	});
	$('.lt-content-main').click(function(evt) {
        if(evt.target != undefined && typeof evt.target.classList == "object" && evt.target.classList.contains('lt-psevdo-check')){
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

            if($(this).find('.lt-psevdo-check').hasClass('psevdo-checked')){
                arrCheck.push({
                    'id': $(this).find('#complaint-id').val(),
                    'status': $(this).attr('sort-status')
                });
            } else {
                var id = $(this).find('#complaint-id').val();
                for(var i = 0; i < arrCheck.length; i++){
                    if(arrCheck[i].id == id){
                        arrCheck.splice(i, 1);
                        break;
                    }
                }
            }

            changeShowHideButtonBackground();
            changeBlockUnblockButtonBackground();
            changeApplicantsButtonBackground();
            changeAdminButtonsBackground();
            changeComplaintButtonsBackground();
            changeUsersDetailsButtonsBackground();
            changeMessagesButtonBackground();
        } else if(evt.target != undefined && typeof evt.target.classList == "object" && (evt.target.classList.contains('with-dropdown') || evt.target.classList.contains('jl-status') || evt.target.id == 'dLabel')) {
            $(this).find('.with-dropdown div[data-toggle=dropdown]').dropdown('toggle');
            return false;
        } else if (evt.currentTarget != undefined && typeof evt.currentTarget.classList == "object" && evt.currentTarget.classList.contains('show-message-popup')) {
            $('#message-text').val(evt.currentTarget.children[1].innerHTML);
            $('.modal-message-detail').modal('show');
            setMessageRed(evt.currentTarget.children[0].children[0].firstChild.value);
            evt.currentTarget.classList.remove('new-message');
        } else {
            var to_url = $(this).attr('detail-url');
            if (to_url != undefined) {
                window.location.href = to_url;
            } else {
                $(this).find('.lt-psevdo-check').trigger('click');
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
	/*$('.appllicant-page .lt-content-main, .appllicant-page .select-all').click(function() {
		if ($('.appllicant-page .lt-psevdo-check').hasClass('psevdo-checked')) {
			$('.appllicant-page .lt-head-btns').addClass('active-lt-head-btns');
		} else {
			$('.appllicant-page .lt-head-btns').removeClass('active-lt-head-btns');
		}
	});*/
	// close admin popup
	$('.admin-popup-bg, .admin-popup-close').click(function() {
		$('.admin-popup-wrap').fadeOut();
	});
	// show complain details
	$('.deploy-complaint').click(function() {
		$('.deploy-complaint span').toggleClass('dep-up');
        //$('.complaints-content').slideToggle();
		$('.details-complaint .c-jadd-text').slideToggle();
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
        if($(this).hasClass('disabled-tab')){
            return false;
        }
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

    $('#j-permisions-save').on('click', function () {
        submit_form_ajax('#admin_permissions');
        return false;
    });

    $("ul.dropdown-menu li").click(function(){
        if ($(this).hasClass('header-dropdown')) {
            return false;
        }
        var status = $(this).find('span').attr('data-status');
        var complaintId = $(this).parent().parent().parent().parent().find('li:first-child #complaint-id').val();
        /*if (status.length && complaintId != undefined) {
            if ($(".admin-main-wrap").hasClass("complaints-list")) {*/
                show_confirm_changing_status_popup(complaintId, status);
            /*} else {
                change_complaint_status(complaintId, status);
            }
        }*/
    });
    $("#show-send-massage-dialog").click(function(){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #user-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.modal-send-message-lg').modal('show');
        }
    });
    $(".complaints-list #delete-button").click(function(){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #complaint-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-deletion-complaint-lg').modal('show');
        }
    });
    $(".admin-lt-holder.lt-arg #delete-button").click(function(){
        var id_array = [];
        $('.admin-lt-holder.lt-arg .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #argument-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-modal-argument-lg').modal('show');
        }
    });
    $(".admin-lt-holder #delete-button").click(function(){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #applicant-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-deletion-applicant-lg').modal('show');
        }
    });
    $(".applicants-part-user-edit #delete-button").click(function(){
        var id_array = [];
        $('.admin-lt-content.userPageLtContent .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #applicant-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-deletion-applicant-lg').modal('show');
        }
    });
    $(".admin-list #delete-button").click(function(){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #user-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-deletion-admin-lg').modal('show');
        }
    });
    $(".admin-lt-holder.lt-arg-second .delete-category-btn").click(function(){
        var id_array = [];
        $('.admin-lt-holder.lt-arg-second .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #argument-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-modal-category-lg').modal('show');
        }
    });
    $(".user-list #delete-button").click(function(){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #user-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $('.confirm-deletion-user-lg').modal('show');
        }
        return false;
    });
    $('.edit-aplicant .delete-file, .front-applicant .delete-file').click(function(){
        $('#delete-file-id').val($(this).find('#file-id').val());
        $('.confirm-deletion-file-lg').modal('show');
    });
    $('.edit-complaint-page.edit-now .delete-file').click(function(){
        //$('#delete-file-id').val($(this).find('#file-id').val());
        //$('.confirm-deletion-file-lg').modal('show');
        delete_complaint_file_edit_form($("#update-complaint-id").val(), $(this).find('#file-id').val());
    });
    var li_mess_count = 0;
    var ul_mes_height = 0;
    $('.ch-r-m-text ul li').each(function(){
        ++li_mess_count;
        if (li_mess_count > 0) {
            ul_mes_height = li_mess_count * 150 - 120;
            if (ul_mes_height > 600) {
                ul_mes_height = 600;
            }
        }
    });
    $('.ch-r-m-text').css('height', ul_mes_height + 'px');

    $('.argComp .current-option').click(function() {
        $(this).find('div').toggleClass('transDiv');
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

function delete_complaint_file_edit_form(complaint_id, file_id) {
    if (complaint_id != undefined && file_id != undefined) {
        $.ajax({
            url: "/complaint/deleteFile",
            type:'POST',
            data: { file_id: file_id, complaint_id: complaint_id },
            dataType: 'json',
            success: function(data){
                showStyledPopupMessage("#pop-before-ask-question", "Уведомление", "Файл успешно удален");
                $("#delete-file-row-" + file_id).remove();
            },
        });
    }
}

function get_last_closing_sign_position(text) {
    var position = 0;
    for (var u = text.length; u >= 0; u--) {
        if (text[u] == ">") {
            position = u;
            break;
        }
    }
    return position;
}

function add_simple_tags_text(text) {
    var no_styles_text = text.match(/<\/w:r>[\s\S]*?<w:r>/g);
    if (no_styles_text == null) {
        return text;
    }
    for (var i = 0; i < no_styles_text.length; i++) {
        if(no_styles_text[i] != "</w:r><w:r>" && no_styles_text[i] != ""){
            var clone_text = no_styles_text[i];
            clone_text = clone_text.substr(6, clone_text.length - 11);
            text = text.replace(no_styles_text[i], "</w:r><w:r><w:t>" + clone_text + "</w:t></w:r><w:r>");
        }
        
    }
    /*var no_styles_text = text.match(/\<\/w:r>(.*?)\<w:r>/);
    if (no_styles_text.length > 1) {
        if (no_styles_text[1].length > 0) {
            text = text.replace(no_styles_text[1], '<w:r><w:t>' + no_styles_text[1] + '</w:t></w:r>');
        }
    }
    no_styles_text = text.match(/\<\/w:r>(.*?)\<w:r>/);
    if (no_styles_text.length > 1) {
        if (no_styles_text[1].length > 0) {
            text = add_simple_tags_text(text);
        }
    }*/
    return text;
}

function replaceWordTags(text) {debugger;
    while(text.search("<br>") >= 0 || text.search("<p>") >= 0 || text.search("</p>") >= 0){
        text = text.replace("<br>", '\r\n');
        text = text.replace("<p>", '');
        text = text.replace("</p>", '');
    }

    /* 3 style selected */
    while (text.search("<strong><em><u>") >= 0) {
        text = text.replace("<strong><em><u>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</u></em></strong>", '</w:t></w:r>');
    }
    while (text.search("<strong><u><em>") >= 0) {
        text = text.replace("<strong><u><em>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</em></u></strong>", '</w:t></w:r>');
    }
    while (text.search("<u><strong><em>") >= 0) {
        text = text.replace("<u><strong><em>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</em></strong></u>", '</w:t></w:r>');
    }
    while (text.search("<u><em><strong>") >= 0) {
        text = text.replace("<u><em><strong>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</strong></em></u>", '</w:t></w:r>');
    }
    while (text.search("<em><u><strong>") >= 0) {
        text = text.replace("<em><u><strong>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</strong></u></em>", '</w:t></w:r>');
    }
    while (text.search("<em><strong><u>") >= 0) {
        text = text.replace("<em><strong><u>", '<w:r><w:rPr><w:b/><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</u></strong></em>", '</w:t></w:r>');
    }

    /* 2 style selected */
    while (text.search("<strong><em>") >= 0) {
        text = text.replace("<strong><em>", '<w:r><w:rPr><w:b/><w:i/></w:rPr><w:t>');
        text = text.replace("</em></strong>", '</w:t></w:r>');
    }
    while (text.search("<em><strong>") >= 0) {
        text = text.replace("<em><strong>", '<w:r><w:rPr><w:b/><w:i/></w:rPr><w:t>');
        text = text.replace("</strong></em>", '</w:t></w:r>');
    }
    while (text.search("<u><strong>") >= 0) {
        text = text.replace("<u><strong>", '<w:r><w:rPr><w:b/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</strong></u>", '</w:t></w:r>');
    }
    while (text.search("<strong><u>") >= 0) {
        text = text.replace("<strong><u>", '<w:r><w:rPr><w:b/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</u></strong>", '</w:t></w:r>');
    }
    while (text.search("<em><u>") >= 0) {
        text = text.replace("<em><u>", '<w:r><w:rPr><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</u></em>", '</w:t></w:r>');
    }
    while (text.search("<u><em>") >= 0) {
        text = text.replace("<u><em>", '<w:r><w:rPr><w:i/><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</em></u>", '</w:t></w:r>');
    }
    while (text.search("<u>") >= 0) {
        text = text.replace("<u>", '<w:r><w:rPr><w:u w:val="single"/></w:rPr><w:t>');
        text = text.replace("</u>", '</w:t></w:r>');
    }
    while (text.search("<em>") >= 0) {
        text = text.replace("<em>", '<w:r><w:rPr><w:i/></w:rPr><w:t>');
        text = text.replace("</em>", '</w:t></w:r>');
    }
    while (text.search("<strong>") >= 0) {
        text = text.replace("<strong>", '<w:r><w:rPr><w:b/></w:rPr><w:t>');
        text = text.replace("</strong>", '</w:t></w:r>');
    }
    text = add_simple_tags_text(text);
    var start_sign = text.search("<");
    if (start_sign >= 0) {
        var start_text = text.substr(0, start_sign);
        var text_to_end = text.substr(start_sign, text.length);
        text = '<w:r><w:t>' + start_text + '</w:t></w:r>' + text_to_end;
        var position = (get_last_closing_sign_position(text)) + 1;
        start_text = text.substr(0, position);
        text_to_end = text.substr(position, text.length);
        text = start_text + '<w:r><w:t>' + text_to_end + '</w:t></w:r>';
    } else {
        text = '<w:r><w:t>' + text + '</w:t></w:r>';
    }
    return "<w:p>" + text + "</w:p>";
}

function compare_dates(date_response) {
    var current_date = new Date();
    date_response = date_response.split(" ");
    date_response[0] = date_response[0].split(".");
    date_response = date_response[0][2] + "-" + date_response[0][1] + "-" + date_response[0][0] + " " + date_response[1] + ":00";
    date_response = new Date(date_response);
    return current_date.getTime() > date_response.getTime();
}

function delete_applicant_file() {
    var applicant_id = $('#delete-applicant-id').val();
    var file_id = $('#delete-file-id').val();
    if (applicant_id != undefined && file_id != undefined) {
        $.ajax({
            url: "/admin/applicants/deleteFile",
            type:'POST',
            data: { file_id: file_id, applicant_id: applicant_id },
            dataType: 'json',
            success: function(data){
                window.location.href = '/admin/applicants/edit/' + applicant_id;
            },
            complete: function(){
                $('#delete-file-id').val('');
            }
        });
    }
}

function setMessageRed(message_id) {
    if (message_id != undefined) {
        $.ajax({
            url: "/users/setMessageRead",
            type:'POST',
            data: { message_id: message_id },
            dataType: 'json',
            success: function(data) {
                if (data == "ok") {
                    
                }
                //window.location.href = '/applicant/edit/' + applicant_id;
            },
            complete: function(){
                //$('#delete-file-id').val('');
            }
        });
    }
}

function delete_applicant_file_front() {
    var applicant_id = $('#delete-applicant-id').val();
    var file_id = $('#delete-file-id').val();
    if (applicant_id != undefined && file_id != undefined) {
        $.ajax({
            url: "/applicant/deleteFile",
            type:'POST',
            data: { file_id: file_id, applicant_id: applicant_id },
            dataType: 'json',
            success: function(data){
                window.location.href = '/applicant/edit/' + applicant_id;
            },
            complete: function(){
                $('#delete-file-id').val('');
            }
        });
    }
}

function delete_arguments() {
    var id_array = [];
    $('.admin-lt-holder.lt-arg .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #argument-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "delete",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
            }
        });
    }
    $('.confirm-modal-argument-lg').modal('hide');
}

function delete_messages() {
    var id_array = [];
    $('.lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #message-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "delete",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                window.location.reload(true);
            }
        });
    }
    $('.confirm-deletion-message').modal('hide');
}

function confirm_change_applicant_type(current_type, current_form) {
    if (current_type != current_form) {
        $('.modal-confirm-change-applicant-type .btn-primary').attr('applicant-type', current_form);
        $('.modal-confirm-change-applicant-type').modal('show');
        return false;
    } else {
        $('form#' + current_form).submit();
    }
}

function delete_categories() {
    var id_array = [];
    $('.admin-lt-holder.lt-arg-second .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #argument-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "deleteCategory",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                $('.confirm-modal-category-lg').modal('hide');
                if (data == 'ok') {
                   window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                } else {
                    $('.modal-cant-delete-category').modal('show');
                }
            }
        });
    }
}

function add_category(name){
    if (name.length) {
        $.ajax({
            url: "addCategory",
            type:'POST',
            data: { name: name },
            dataType: 'json',
            success: function(data){
                if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                } else {
                    $('.modal-add-category-lg').modal('hide');
                    window.location.reload(true);
                }
            }
        });
    }
}

function show_hide_arguments(hide) {
    var id_array = [];
    $('.admin-lt-holder.lt-arg .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #argument-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "hideShow",
            type:'POST',
            data: { ids: id_array, hide: hide },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
            }
        });
    }
    $('.confirm-modal-lg').modal('hide');
}

function changeShowHideButtonBackground() {
    //if($('.admin-main-wrap').hasClass('list-arguments')) {
    //    var id_array = [];
    //    $('.admin-lt-holder.lt-arg .lt-content-main.hidden-arg').each(function(){
    //        var id = $(this).find('div.psevdo-checked #argument-id').val();
    //        if (id != undefined) {
    //            id_array.push(id);
    //        }
    //    });
    //    if (id_array.length) {
    //        $(".btn-show").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $(".btn-show").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //    var id_array2 = [];
    //    $('.admin-lt-holder.lt-arg .lt-content-main').each(function(elem, item){
    //        if (!$(item).hasClass("hidden-arg")) {
    //            var id = $(item).find('div.psevdo-checked #argument-id').val();
    //            if (id != undefined) {
    //                id_array2.push(id);
    //            }
    //        }
    //    });
    //    if (id_array2.length) {
    //        $(".btn-hide").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $(".btn-hide").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //    if (id_array.length || id_array2.length) {
    //        $("#delete-button").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $("#delete-button").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //    // Cetegories.
    //    var id_array = [];
    //    $('.admin-lt-holder.lt-arg-second .lt-content-main').each(function(){
    //        var id = $(this).find('div.psevdo-checked #argument-id').val();
    //        if (id != undefined) {
    //            id_array.push(id);
    //        }
    //    });
    //    if (id_array.length) {
    //        $(".delete-category-btn").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $(".delete-category-btn").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //}
}

function changeApplicantsButtonBackground(){
    if($('.admin-main-wrap').hasClass('applicant-list')) {
        var id_array = [];
        $('.admin-lt-holder .lt-content-main.hidden-arg').each(function(){
            var id = $(this).find('div.psevdo-checked #applicant-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".unblock-applicant").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".unblock-applicant").addClass("disabled-btn").removeClass("enabled-btn");
        }
        var id_array2 = [];
        $('.admin-lt-holder .lt-content-main').each(function(elem, item){
            if (!$(item).hasClass("hidden-arg")) {
                var id = $(item).find('div.psevdo-checked #applicant-id').val();
                if (id != undefined) {
                    id_array2.push(id);
                }
            }
        });
        if (id_array2.length) {
            $(".block-applicant").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".block-applicant").addClass("disabled-btn").removeClass("enabled-btn");
        }
        if (id_array.length || id_array2.length) {
            $("#delete-button").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $("#delete-button").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
}

function changeUsersDetailsButtonsBackground(){
    if($('.admin-main-wrap').hasClass('user-page')) {
        var id_array = [];
        $('.admin-lt-content.userPageLtContent .lt-content-main').each(function(elem, item){
            if ($(item).hasClass("hidden-arg")) {
                var id = $(item).find('div.psevdo-checked #applicant-id').val();
                if (id != undefined) {
                    id_array.push(id);
                }
            }
        });
        if (id_array.length) {
            $(".unblock-applicant").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".unblock-applicant").addClass("disabled-btn").removeClass("enabled-btn");
        }
        var id_array2 = [];
        $('.admin-lt-content.userPageLtContent .lt-content-main').each(function(elem, item){
            if (!$(item).hasClass("hidden-arg")) {
                var id = $(item).find('div.psevdo-checked #applicant-id').val();
                if (id != undefined) {
                    id_array2.push(id);
                }
            }
        });
        if (id_array2.length) {
            $(".block-applicant").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".block-applicant").addClass("disabled-btn").removeClass("enabled-btn");
        }
        if (id_array.length || id_array2.length) {
            $(".applicants-part-user-edit #delete-button").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".applicants-part-user-edit #delete-button").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
}

function changeMessagesButtonBackground(){
    if($('.content').hasClass('message-page')) {
        var id_array = [];
        $('.admin-lt-content .lt-content-main').each(function(elem, item){
            var id = $(item).find('div.psevdo-checked #message-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $("#delete-button").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $("#delete-button").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
}

function delete_complaint(complaint_id) {
    if (complaint_id) {
        $.ajax({
            url: "/admin/complaints/deleteComplaint",
            type:'POST',
            data: { id: complaint_id },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.href = "/admin/complaints/index";
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                } else {
                    window.location.reload(true);
                }
            }
        });
    }
    $('.confirm-modal-lg').modal('hide');
}

function show_confirm_changing_status_popup(complaint_id, status) {
    if (complaint_id && status.length) {
        showStyledPopupMessageWithButtons(
            "#pop-confirm-change-complaint-status",
            "Подтвердите действие",
            "Вы действительно хотите сменить статус?",
            "change_complaint_status(" + complaint_id +", '" + status + "');"
        );
    }
}

function show_confirm_delete_ufas_popup(ufas_id) {
    if (ufas_id) {
        showStyledPopupMessageWithButtons(
            "#pop-confirm-delete-ufas",
            "Подтвердите действие",
            "Вы действительно хотите удалить данные по УФАС?",
            "delete_ufas(" + ufas_id + ");"
        );
    }
}

function delete_ufas(ufas_id) {
    if (ufas_id) {
        $.ajax({
            url: "/admin/ufas/delete",
            type:'POST',
            data: { id: ufas_id },
            dataType: 'json',
            success: function(data){
                if (data.success == 'reload') {
                   window.location.href = "/admin/ufas/detail/" + ufas_id;
                } else {
                    window.location.href = "/admin/ufas/index";
                }
            }
        });
    }
}

function change_complaint_status(complaint_id, st) {
    if (complaint_id && st.length) {
        $.ajax({
            url: "/admin/complaints/changeComplaintStatus",
            type:'POST',
            data: { id: complaint_id, status: st },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                }
            }
        });
    }
}

function changeStatusInComplaintList(status) {
    console.log(this);
    //if (status.length) {
    //    var id_array = [];
    //    if (status == 'recalled') {
    //        $('.admin-lt-holder .lt-content-main.podana').each(function(){
    //            var id = $(this).find('div.psevdo-checked #complaint-id').val();
    //            if (id != undefined) {
    //                id_array.push(id);
    //            }
    //        });
    //        if (id_array.length) {
    //            change_complaint_status(id_array, status);
    //        } else {
    //            return false;
    //        }
    //    } else if (status == 'archive') {
    //        $('.admin-lt-holder .lt-content-main').each(function(item, elem){
    //            if (!$(elem).hasClass("alr-archive")) {
    //                var id = $(elem).find('div.psevdo-checked #complaint-id').val();
    //                if (id != undefined) {
    //                    id_array.push(id);
    //                }
    //            }
    //
    //        });
    //        if (id_array.length) {
    //            change_complaint_status(id_array, status);
    //        } else {
    //            return false;
    //        }
    //    } else {
    //        $('.admin-lt-holder .lt-content-main').each(function(){
    //            var id = $(this).find('div.psevdo-checked #complaint-id').val();
    //            if (id != undefined) {
    //                id_array.push(id);
    //            }
    //        });
    //        if (status == "copy" && id_array.length > 1) {
    //            return false;
    //        }
    //        if (id_array.length) {
    //            change_complaint_status(id_array, status);
    //        }
    //    }
    //}
    var id_array = new Array();
    for(var i = 0; i < arrCheck.length; i++){
        id_array.push(arrCheck[i].id);
    }

    if(id_array.length) {
        switch (status) {
            case 'copy':
                if (!$(".copy-complaint").hasClass('disabled-btn')) {
                    change_complaint_status(id_array, status);
                }
                break;
            case 'recalled':
                if (!$(".recall-complaint").hasClass('disabled-btn')) {
                    change_complaint_status(id_array, status);
                }
                break;
            case 'archive':
                if (!$(".archive-complaint").hasClass('disabled-btn')) {
                    change_complaint_status(id_array, status);
                }
                break;
            case 'activate':
                if (!$(".un-archive-complaint").hasClass('disabled-btn')) {
                    change_complaint_status(id_array, status);
                }
                break;
        }
    }




    //$('.admin-lt-holder .lt-content-main').each(function(){
    //    var id = $(this).find('div.psevdo-checked #complaint-id').val();
    //    if (id != undefined) {
    //        arr_check.push({
    //            'id': id,
    //            'status': $(this).find('div.psevdo-checked #complaint-id').parent().parent().parent().attr('sort-status')
    //        });
    //    }
    //});

}
function changeStatusInUserComplaintList(status) {
    if (status.length) {
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #complaint-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            change_complaint_status(id_array, status);
        }
    }
}

function complaints_to_archive(){
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #complaint-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        change_complaint_status(id_array, 'archive');
    }
}

function delete_users(){
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #user-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "deleteUsers",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                $('.confirm-deletion-user-lg').modal('hide');
                if (data == 'ok') {
                   window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
            }
        });
    }
}

function block_unblock_users(block) {
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #user-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "blockUnblock",
            type:'POST',
            data: { ids: id_array, block: block },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
            }
        });
    }
}

function block_unblock_admins(block) {
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #user-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "blockUnblock",
            type:'POST',
            data: { ids: id_array, block: block },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
            }
        });
    }
}

function block_unblock_applicant(block) {
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #applicant-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "blockUnblock",
            type:'POST',
            data: { ids: id_array, block: block },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                }
            }
        });
    }
}

function block_unblock_user_applicant(block) {
    var id_array = [];
    $('.admin-lt-content.userPageLtContent').each(function() {
        var id = $(this).find('div.psevdo-checked #applicant-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "/admin/user/blockUnblockUserApplicant",
            type:'POST',
            data: { ids: id_array, block: block },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                   window.location.reload(true);
                }
            }
        });
    }
}

function changeBlockUnblockButtonBackground() {
    if($('.admin-main-wrap').hasClass('user-list')){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main.hidden-arg').each(function(){
            var id = $(this).find('div.psevdo-checked #user-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".disabled-btn.block").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".enabled-btn.block").addClass("disabled-btn").removeClass("enabled-btn");
        }
        var id_array2 = [];
        $('.admin-lt-holder .lt-content-main').each(function(elem, item){
            if (!$(item).hasClass("hidden-arg")) {
                var id = $(item).find('div.psevdo-checked #user-id').val();
                if (id != undefined) {
                    id_array2.push(id);
                }
            }
        });
        if (id_array2.length) {
            $(".disabled-btn.unblock").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".enabled-btn.unblock").addClass("disabled-btn").removeClass("enabled-btn");
        }
        if (id_array.length || id_array2.length) {
            $("#show-send-massage-dialog, #delete-button").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $("#show-send-massage-dialog, #delete-button").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
}

function changeAdminButtonsBackground() {
    if($('.admin-main-wrap').hasClass('admin-list')){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main.hidden-arg').each(function(){
            var id = $(this).find('div.psevdo-checked #user-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".unblock-admin").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".unblock-admin").addClass("disabled-btn").removeClass("enabled-btn");
        }
        var id_array2 = [];
        $('.admin-lt-holder .lt-content-main').each(function(elem, item){
            if (!$(item).hasClass("hidden-arg")) {
                var id = $(item).find('div.psevdo-checked #user-id').val();
                if (id != undefined) {
                    id_array2.push(id);
                }
            }
        });
        if (id_array2.length) {
            $(".block-admin").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".block-admin").addClass("disabled-btn").removeClass("enabled-btn");
        }
        if (id_array.length || id_array2.length) {
            $("#show-send-massage-dialog, #delete-button").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $("#show-send-massage-dialog, #delete-button").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
}

function changeComplaintButtonsBackground() {
    $(".copy-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    $(".archive-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    $(".recall-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    $(".un-archive-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    $("#delete-button").addClass('disabled-btn').removeClass('enabled-btn');

    var arrStatus = new Array();
    var same = true;
    var curStat = arrCheck[0].status;
    arrStatus.push(curStat);

    if(arrCheck.length == 1){
        $(".copy-complaint").addClass('enabled-btn').removeClass('disabled-btn');
    }else if( arrCheck.length > 1){
        $(".copy-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    }

    for(var i = 0; i < arrCheck.length; i++){
        if(curStat != arrCheck[i].status){
            same = false;
            arrStatus.push(arrCheck[i].status);
        }
    }

    if(arrStatus.indexOf('archive') != -1){
        $(".archive-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    } else if(arrStatus.indexOf('archive') == -1) {
        $(".archive-complaint").addClass('enabled-btn').removeClass('disabled-btn');
    }
    if(arrStatus.indexOf('archive') != -1 && same == true){
        $(".un-archive-complaint").addClass('enabled-btn').removeClass('disabled-btn');
    }

    if(arrStatus.indexOf('submitted') != -1 && same != true){
        $(".recall-complaint").addClass('disabled-btn').removeClass('enabled-btn');
    } else if(arrStatus.indexOf('submitted') != -1 && same == true){
        $(".recall-complaint").addClass('enabled-btn').removeClass('disabled-btn');
    }
    if(arrStatus.indexOf('draft') != -1 || arrStatus.indexOf('submitted') != -1
        || arrStatus.indexOf('recalled') != -1  || arrStatus.indexOf('under_consideration')
        || arrStatus.indexOf('justified') || arrStatus.indexOf('unfounded')){
        $("#delete-button").addClass('enabled-btn').removeClass('disabled-btn');
    }


    //if($('.admin-main-wrap').hasClass('complaints-list')){
    //    var id_array = [];
    //    var submitted_id = [];
    //    var archived_id = [];
    //    $('.admin-lt-holder .lt-content-main').each(function(elem, item){
    //        var id = $(this).find('div.psevdo-checked #complaint-id').val();
    //        if (id != undefined) {
    //            id_array.push(id);
    //        }
    //    });
    //    if (id_array.length) {
    //        $(".disabled-btn").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $(".enabled-btn").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //    // Disable "Copy" button if choosed more than 1 complaint.
    //    if (id_array.length > 1) {
    //        $(".copy-complaint").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //    // Check logic for "Otozvat" button if status is "submitted".
    //    $('.admin-lt-holder .lt-content-main.podana').each(function(elem, item){
    //        var id = $(this).find('div.psevdo-checked #complaint-id').val();
    //        if (id != undefined) {
    //            submitted_id.push(id);
    //        }
    //    });
    //    if (submitted_id.length == 0) {
    //        $(".recall-complaint").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //    // Check logic for "activate"/"deactivate" button.
    //    $('.admin-lt-holder .lt-content-main.alr-archive').each(function(elem, item){
    //        var id = $(this).find('div.psevdo-checked #complaint-id').val();
    //        if (id != undefined) {
    //            archived_id.push(id);
    //        }
    //    });
    //    if (archived_id.length > 0) {
    //        $(".un-archive-complaint").addClass("enabled-btn").removeClass("disabled-btn");
    //        $(".archive-complaint").addClass("disabled-btn").removeClass("enabled-btn");
    //    } else {
    //        $(".un-archive-complaint").addClass("disabled-btn").removeClass("enabled-btn");
    //        //$(".archive-complaint").addClass("enabled-btn").removeClass("disabled-btn");
    //    }
    //    if (id_array.length > archived_id.length) {
    //        $(".archive-complaint").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $(".archive-complaint").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //} else if($('.user-page .appllicant-page').hasClass('complaints-list')) {
    //    var id_array = [];
    //    $('.appllicant-page.complaints-list .admin-lt-holder .lt-content-main').each(function(elem, item){
    //        var id = $(item).find('div.psevdo-checked #complaint-id').val();
    //        if (id != undefined) {
    //            id_array.push(id);
    //        }
    //    });
    //    if (id_array.length) {
    //        $(".appllicant-page.complaints-list .disabled-btn").addClass("enabled-btn").removeClass("disabled-btn");
    //    } else {
    //        $(".appllicant-page.complaints-list .enabled-btn").addClass("disabled-btn").removeClass("enabled-btn");
    //    }
    //}
}

function send_message(subject, body){
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #user-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "sendMessage",
            type:'POST',
            data: { toids: id_array, subject: subject, body: body },
            dataType: 'json',
            success: function(data){
                $('.modal-send-message-lg').modal('hide');
                window.location.reload(true);
            }
        });
    }
}

function delete_admins(){
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #user-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "deleteAdmins",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                $('.confirm-deletion-admin-lg').modal('hide');
                if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                } else {
                    window.location.reload(true);
                }
            }
        });
    }
}

function delete_complaints(){
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #complaint-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "deleteComplaint",
            type:'POST',
            data: { id: id_array, is_array: true },
            dataType: 'json',
            success: function(data){
                 if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                } else {
                    window.location.reload(true);
                }
            }
        });
    }
}

function delete_answer(answer_id) {
    if (answer_id != undefined) {
        $.ajax({
            url: "/admin/complaints/deleteAnswer",
            type:'POST',
            data: { id: answer_id },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                    window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
                $('.confirm-deletion-answer').modal('hide');
            }
        });
    }
}

function save_answer(answer_id, answer_text) {
    console.log(answer_id);
    console.log(answer_text);
    if (answer_id != undefined && answer_text != undefined && answer_text.length) {
        $.ajax({
            url: "/admin/complaints/updateAnswer",
            type:'POST',
            data: { id: answer_id, text: answer_text },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                    window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
                $('.modal-edit-answer').modal('hide');
            }
        });
    }
}

function delete_applicants(){
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #applicant-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "deleteApplicants",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                $('.confirm-deletion-applicant-lg').modal('hide');
                window.location.reload(true);
            }
        });
    }
}

function delete_user_applicants(){
    var id_array = [];
    $('.admin-lt-content.userPageLtContent').each(function(){
        var id = $(this).find('div.psevdo-checked #applicant-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "/admin/user/deleteUserApplicants",
            type:'POST',
            data: { ids: id_array },
            dataType: 'json',
            success: function(data){
                $('.confirm-deletion-applicant-lg').modal('hide');
                window.location.reload(true);
            }
        });
    }
}

function delete_all_complaints() {
    var id_array = [];
    $('.admin-lt-holder .lt-content-main').each(function(){
        var id = $(this).find('div.psevdo-checked #complaint-id').val();
        if (id != undefined) {
            id_array.push(id);
        }
    });
    if (id_array.length) {
        $.ajax({
            url: "/admin/complaints/deleteComplaint?is_array=true",
            type:'POST',
            data: { id: id_array },
            dataType: 'json',
            success: function(data){
                if (data == 'ok') {
                    window.location.reload(true);
                } else if(data == 'access_denied') {
                    window.location.href = '/admin/access/denied';
                }
            }
        });
    }
    $('.confirm-deletion-complaints-lg').modal('hide');
}

function delete_applicant(applicantId){
    if (applicantId) {
        var countApplicant = 0;
        $('.admin-lt-holder .lt-content-main').each(function(){
            ++countApplicant;
        });
        if (countApplicant == 0) {
            $.ajax({
                url: "/admin/applicants/deletet/" + applicantId,
                type:'POST',
                data: { ids: countApplicant },
                dataType: 'json',
                success: function(data){
                    if(data.success == 'ok'){
                        $('.confirm-deletion-applicant-lg').modal('hide');
                        window.location.href = '/admin/applicants/index';
                    } else if(data.success == 'redirect') {
                        window.location.href = '/admin/applicants/index';
                    } else {
                        window.location.reload(true);
                    }
                }
            });
        } else {
            $('.confirm-deletion-applicant-lg').modal('hide');
            $('.modal-cant-delete-applicant').modal('show');
        }
    }
}

function get_class_by_file_type(file_type) {
    switch (file_type) {
        case 'pdf':
            return 'pdf-file';
        case 'doc':
        case 'docx':
        case 'rtf':
            return 'msword-file';
        case 'rar':
        case 'zip':
            return 'archive-file';
        case 'jpeg':
        case 'jpg':
        case 'png':
        case 'bmp':
        case 'tif':
        case 'tiff':
            return 'image-file';
        default:
            return '';
    }
}

function showStyledPopupMessage(popup, title, message) {
    $(popup + " h2").html(title);
    $(popup + " .pop-done-txt").html(message);
    $('#overlay').fadeIn(400,
    function(){
        $(popup)
            .css('display', 'block')
            .animate({opacity: 1, top: '50%'}, 200);
    });
}

function showStyledPopupMessageWithButtons(popup, title, message, click_function) {
    $(popup + " h2").html(title);
    $(popup + " .popup-content").html(message);
    $(popup + " .popup-button.apr").attr("onclick", click_function);
    $('#overlay').fadeIn(400,
    function(){
        $(popup)
            .css('display', 'block')
            .animate({opacity: 1, top: '50%'}, 200);
    });
}

function hide_arguments() {
    console.log("hidden");
}
                
function readAvatarURL(input, imgid) {
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			$(imgid).attr('src', e.target.result);
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
			if(data['success']==true) {
                $('.admin-set-top').click();
                showStyledPopupMessage("#styled-popup-container", "Уведомление", "Ваш профиль успешно обновлен");
            }
			else {
				var errors_list = '';
				for(var index in data['errors'] ) {
					errors_list = errors_list + data['errors'][index]+'\n\r';
                }
                $('.admin-set-top').trigger('click');
				showStyledPopupMessage("#styled-popup-container", "Ошибка", errors_list);
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

function submit_form_ajax(selector) {
    var formData = $(selector).serializeArray();
    $.ajax({
        type: "POST",
        url: $(selector).attr('action'),
        dataType: 'json',
        cache: false,
        data: formData,
        error: function(){
            showSomePopupMessage('info', 'Error while saving data');
        },
        success: function(data) {
            var myobjres = data;
            if(myobjres['success']==true)
                showSomePopupMessage('info', 'Success while saving data');
            else {
                var errors_list = '';
                for(var index in myobjres['errors'] )
                    errors_list = errors_list + data['errors'][index]+'\n\r';
                showSomePopupMessage('error', errors_list);
            }
        },
        timeout: 120000 // sets timeout to 2 minutes
    });
    return false;
}