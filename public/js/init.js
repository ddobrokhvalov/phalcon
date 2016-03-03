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

jQuery(document).ready(function($) {
    // the correct location of the sidebar
    if ($('.content .wrapper').hasClass('wrap-with-menuPanel')) {
        var windowWidth = $(window).width() + 17,
            objCont = $('.content .wrap-with-menuPanel'),
            objHead = $('.c-header .wrapper');

        if (windowWidth > 1300) {
            var off = objCont.offset().left - 284,
                rounding = Math.round(off / 2);

            if (off > 1) {
                objCont.css({marginRight: rounding}); 
                objHead.css({marginRight: rounding}); 
            }   
        }
    }
    // adding stylized scrolling ыidebar
    if ($(window).height() <= 860) {
        $('.left-menu-holder').addClass('scroll-pane');
        $('.scroll-pane').jScrollPane();
    }

});

jQuery(document).ready(function(){
	jQuery('.spoiler-text').hide()
	jQuery('.spoiler').click(function(){
		jQuery(this).toggleClass("folded").toggleClass("unfolded").next().slideToggle()
	})
});

jQuery(document).ready(function(){
	jQuery('.c-jadd-text').hide()
	jQuery('.c-jadd-spoiler').click(function(){
		jQuery(this).toggleClass("folded").toggleClass("unfolded").prev().slideToggle()
	})
});

$(document).ready(function(){
    $(".c-zbc-btnb1-btn1").click(function(){
        $(".c-zbc-accpt").show();
        $(".c-zbc-pick").hide();
    });
});

$(document).ready(function(){
  //  $('#leftmenu').sidr({
   //     displace: false
   // });
});

$(document).ready(function() {
    $(".ch-l-dd").hide();
    $(".ch-left").click(function(){
        $(".ch-l-dd").slideToggle(300);
        $('.opacity-layer').show();
    });
    $(".ch-left-cl").click(function(){
        $(".ch-l-dd").slideToggle(300);
        $('.opacity-layer').hide();
    });

    $(".ch-r-mail-dd").hide();
    $(".ch-r-mail").click(function(){
        $(".ch-r-mail-dd").slideToggle(300);
        $('.opacity-layer').show();
    });
    $(".ch-r-mail-dd-z").click(function(){
        $(".ch-r-mail-dd").slideToggle(300);
        $('.opacity-layer').hide();
    });

    $(".ch-r-sett-dd").hide();
    $(".ch-r-sett").click(function(){
        $(".ch-r-sett-dd").slideToggle(300);
        $('.opacity-layer').show();
    });
    $(".ch-r-sett-dd-z").click(function(){
        $(".ch-r-sett-dd").slideToggle(300);
        $('.opacity-layer').hide();
    });

    $('.opacity-layer').click(function() {
        if ($(".ch-l-dd").css('display') == 'block') {$(".ch-l-dd").slideUp(300);}
        if ($(".ch-r-mail-dd").css('display') == 'block') {$(".ch-r-mail-dd").slideUp(300);}
        if ($(".ch-r-sett-dd").css('display') == 'block') {$(".ch-r-sett-dd").slideUp(300);}
        $(this).hide();
    });

    $(".c-jd2-f-dov-dd").hide();
    $(".c-jd2-f-dov").click(function(){
        $(".c-jd2-f-dov-dd").slideToggle(300);
    });
    $(".c-jd2-f-dov-dd-z").click(function(){
        $(".c-jd2-f-dov-dd").slideToggle(300);
    });



    $(".lm-opcl-btn1").click(function(){
        $(".lm-opcl-btn1").css("display","none");
        $(".lm-opcl-btn2").css("display","block");
        $(".left-menu-block").css("left","0")
    });
    $(".lm-opcl-btn2").click(function(){
        $(".lm-opcl-btn2").css("display","none");
        $(".lm-opcl-btn1").css("display","block");
        $(".left-menu-block").css("left","-242px")
    });
});

$(document).ready(function(){
    jQuery(function(f){
        var element = f('#chsort');
        f(window).scroll(function(){
            element['fade'+ (f(this).scrollTop() > 400 ? 'In': 'Out')](200);
        });
    });
});