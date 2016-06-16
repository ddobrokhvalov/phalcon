jQuery(document).ready(function($) {
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
        changeShowHideButtonBackground();
        changeBlockUnblockButtonBackground();
        changeApplicantsButtonBackground();
        changeAdminButtonsBackground();
        changeComplaintButtonsBackground();
        changeUsersDetailsButtonsBackground();
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
            changeShowHideButtonBackground();
            changeBlockUnblockButtonBackground();
            changeApplicantsButtonBackground();
            changeAdminButtonsBackground();
            changeComplaintButtonsBackground();
            changeUsersDetailsButtonsBackground();
        } else if(evt.target != undefined && typeof evt.target.classList == "object" && (evt.target.classList.contains('with-dropdown') || evt.target.classList.contains('jl-status') || evt.target.id == 'dLabel')) {
            $(this).find('.with-dropdown div[data-toggle=dropdown]').dropdown('toggle');
            return false;
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
        if (status.length && complaintId != undefined) {
            change_complaint_status(complaintId, status);
        }
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
    if($('.admin-main-wrap').hasClass('list-arguments')) {
        var id_array = [];
        $('.admin-lt-holder.lt-arg .lt-content-main.hidden-arg').each(function(){
            var id = $(this).find('div.psevdo-checked #argument-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".btn-show").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".btn-show").addClass("disabled-btn").removeClass("enabled-btn");
        }
        var id_array2 = [];
        $('.admin-lt-holder.lt-arg .lt-content-main').each(function(elem, item){
            if (!$(item).hasClass("hidden-arg")) {
                var id = $(item).find('div.psevdo-checked #argument-id').val();
                if (id != undefined) {
                    id_array2.push(id);
                }
            }
        });
        if (id_array2.length) {
            $(".btn-hide").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".btn-hide").addClass("disabled-btn").removeClass("enabled-btn");
        }
        if (id_array.length || id_array2.length) {
            $("#delete-button").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $("#delete-button").addClass("disabled-btn").removeClass("enabled-btn");
        }
        // Cetegories.
        var id_array = [];
        $('.admin-lt-holder.lt-arg-second .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #argument-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".delete-category-btn").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".delete-category-btn").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
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

function change_complaint_status(complaint_id, status) {
    if (complaint_id && status.length) {
        $.ajax({
            url: "/admin/complaints/changeComplaintStatus",
            type:'POST',
            data: { id: complaint_id, status: status },
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

function changeComplaintButtonsBackground() {//debugger;
    if($('.admin-main-wrap').hasClass('complaints-list')){
        var id_array = [];
        $('.admin-lt-holder .lt-content-main').each(function(){
            var id = $(this).find('div.psevdo-checked #complaint-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".disabled-btn").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".enabled-btn").addClass("disabled-btn").removeClass("enabled-btn");
        }
    } else if($('.user-page .appllicant-page').hasClass('complaints-list')) {
        var id_array = [];
        $('.appllicant-page.complaints-list .admin-lt-holder .lt-content-main').each(function(elem, item){
            var id = $(item).find('div.psevdo-checked #complaint-id').val();
            if (id != undefined) {
                id_array.push(id);
            }
        });
        if (id_array.length) {
            $(".appllicant-page.complaints-list .disabled-btn").addClass("enabled-btn").removeClass("disabled-btn");
        } else {
            $(".appllicant-page.complaints-list .enabled-btn").addClass("disabled-btn").removeClass("enabled-btn");
        }
    }
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