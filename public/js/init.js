$(document).ready(function() {
    var overlay = $('#overlay');
    var open_modal = $('.open_modal');
    var close = $('.modal-close, #overlay');
    var modal = $('.modal_div');

     open_modal.click( function(event){
         event.preventDefault();
         var div = $(this).attr('href');
         overlay.fadeIn(400,
             function(){
                 $(div)
                     .css('display', 'block')
                     .animate({opacity: 1, top: '50%'}, 200);
         });
     });
     close.click( function(){
            modal
             .animate({opacity: 0, top: '45%'}, 200,
                 function(){
                     $(this).css('display', 'none');
                     overlay.fadeOut(400);
                 }
             );
     });
});

jQuery(document).ready(function(){
	jQuery('.spoiler-text').hide()
	jQuery('.spoiler').click(function(){
		jQuery(this).toggleClass("folded").toggleClass("unfolded").next().slideToggle()
	})
});

$(document).ready(function(){
    $(".c-zbc-btnb1-btn1").click(function(){
        $(".c-zbc-accpt").show();
        $(".c-zbc-pick").hide();
    });
});