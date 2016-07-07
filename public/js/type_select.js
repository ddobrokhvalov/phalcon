// 'select' options stylization and record the value in a hidden "select" 

jQuery(document).ready(function($) {
	$('.current-option').click(function(){
        $(this).next('.custom-options').slideToggle();
	    $(this).find('span').toggleClass('rotate-icon');
	});
	$('.custom-options li').click(function(){
	    var choosenValue = $(this).attr('data-value');
	    $(this).parent().parent().parent().find('.hidden-select').val(choosenValue).prop('selected', true);
	    $(this).parent().parent().find('.current-option span').text($(this).text());
	    $(this).parent().parent().find('.current-option').attr('data-value', choosenValue);
	    $(this).parent().parent().find('.custom-options').slideUp();
	});
});