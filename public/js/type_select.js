jQuery(document).ready(function($) {
	$('.current-option').click(function() {
        $(this).next('.custom-options').slideToggle();
	    $(this).find('span').toggleClass('rotate-icon');
        pushTypeToVal($(this));
	});
	$('.custom-options').on('click', 'li', function() {
		if ($(this).parent().parent().hasClass('selectArgType')) {
            addRemoveType($(this))
		} else {
			var choosenValue = $(this).attr('data-value');
			$(this).parent().parent().parent().find('.hidden-select').val(choosenValue).prop('selected', true);
			$(this).parent().parent().find('.current-option span').text($(this).text());
			$(this).parent().parent().find('.current-option span').removeClass('rotate-icon');
			$(this).parent().parent().find('.current-option').attr('data-value', choosenValue);
			$(this).parent().parent().find('.custom-options').slideUp();
		}
		if ($(this).hasClass('argo')) {
			$(this).parent().parent().parent().parent().parent().parent().find('.hidden-select').val(choosenValue).prop('selected', true);
			$(this).parent().parent().parent().parent().parent().find('.current-option span').text($(this).text());
			$(this).parent().parent().parent().parent().parent().find('.current-option span').removeClass('rotate-icon');
			$(this).parent().parent().parent().parent().parent().find('.current-option div').removeClass('transDiv');
			$(this).parent().parent().parent().parent().parent().find('.current-option').attr('data-value', choosenValue);
			$(this).parent().parent().parent().parent().parent().find('.custom-options').slideUp();
		}
	});
});

var selectArgType = {
    value: [],
    name: [],
    selected: false,
    availableObj: {
        value: '',
        name: ''
    }
};

function pushTypeToVal(obj) {
    if (obj.parent().hasClass('selectArgType')) {
        obj.attr('data-value', '');
        obj.attr('data-value', selectArgType.value);
    }
}
function addRemoveType(obj) {
    if (obj.parent().parent().find('.current-option span').text() == 'Тип довода') {
        obj.parent().parent().find('.current-option span').text('');
    }
    for (var i = 0; i < selectArgType.name.length; i++) {
        if (selectArgType.name[i] == obj.text()) {
            selectArgType.selected = true;
            selectArgType.availableObj.name = obj.text();
            selectArgType.availableObj.value = obj.attr('data-value');
        }
    }
    if (selectArgType.selected) {
        obj.parent().parent().find('.current-option span').text('');
        for (var i = 0; i < selectArgType.name.length; i++) {
            if (selectArgType.name[i] == selectArgType.availableObj.name) {
                delete selectArgType.name[i];
            }
            if (i == 0) {
                if (selectArgType.name[i] != undefined)
                obj.parent().parent().find('.current-option span').append(selectArgType.name[i]);
            } else {
                if (selectArgType.name[i] != undefined)
                obj.parent().parent().find('.current-option span').append(', ' + selectArgType.name[i]);
            }
        }
        for (var i = 0; i < selectArgType.value.length; i++) {
            if (selectArgType.value[i] == selectArgType.availableObj.value) {
                selectArgType.value.splice([i], 1);
            }
        }
        obj.removeClass('choosenArgType');
        selectArgType.selected = false;
    } else {
        selectArgType.value.push(obj.attr('data-value'));
        selectArgType.name.push(obj.text());
        if (obj.parent().parent().find('.current-option span').text() == '') {
            obj.parent().parent().find('.current-option span').append(obj.text());
        } else {
            obj.parent().parent().find('.current-option span').append(', ' + obj.text());
        }
        obj.addClass('choosenArgType');
    }
}
