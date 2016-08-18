jQuery(document).ready(function($) {
	$('.current-option').click(function() {
        $(this).next('.custom-options').slideToggle();
	    $(this).find('span').toggleClass('rotate-icon');
	});
	$('.custom-options').on('click', 'li', function() {
		if ($(this).hasClass('selectArgType')) {
			var choosenValueType = [];
			choosenValueType.push($(this).attr('data-value'));
			$(this).parent().parent().parent().find('.hidden-select').val(choosenValueType).prop('selected', true);
			$(this).parent().parent().find('.current-option span').text($(this).text());
			$(this).parent().parent().find('.current-option span').removeClass('rotate-icon');
			$(this).parent().parent().find('.current-option').attr('data-value', choosenValueType);
			$(this).parent().parent().find('.custom-options').slideUp();
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