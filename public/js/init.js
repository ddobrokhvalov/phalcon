$(document).ready(function() {
    var overlay = $('#overlay');
    var open_modal = $('.open_modal');
    var open_modal2 = $('.open_modal2');
    var close = $('.modal-close, #overlay, .popup-close');
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
     open_modal2.click( function(event){
         event.preventDefault();
         var div = $(this).attr('href');
         var label = $(this).parent().parent().find('label').text().trim() + "?";
         $("#applicant-name").html(label);
         var applicant_id = $(this).attr('applicant-id');
         $(".delete-applicant").attr('href', "/applicant/delete/" + applicant_id);
         $(div).css('display', 'block').animate({opacity: 1, top: '50%'}, 200);
         $("#overlay").css('display', 'block');
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
    //if ($('.content .wrapper').hasClass('wrap-with-menuPanel')) {
    //    var windowWidth = $(window).width() + 17,
    //        objCont = $('.content .wrap-with-menuPanel'),
    //        objHead = $('.c-header .wrapper');
    //
    //    if (windowWidth > 1300) {
    //        var off = objCont.offset().left - 284,
    //            rounding = Math.round(off / 2);
    //
    //        if (off > 1) {
    //            objCont.css({marginRight: rounding});
    //            objHead.css({marginRight: rounding});
    //        }
    //    }
    //}
    // adding stylized scrolling ыidebar
   /* if ($(window).height() <= 860) {
        $('.left-menu-holder').addClass('scroll-pane');
        $('.scroll-pane').jScrollPane();
    }  */

});

jQuery(document).ready(function(){
	jQuery('.spoiler-text').hide()
	jQuery('.spoiler').click(function(){
		jQuery(this).toggleClass("folded").toggleClass("unfolded").next().slideToggle();
	})
});

jQuery(document).ready(function(){
	jQuery('.c-jadd-text').hide()
	jQuery('.c-jadd-spoiler').click(function(){
		jQuery(this).toggleClass("folded").toggleClass("unfolded").prev().slideToggle();
        var txt = jQuery('.c-jadd-spoiler span').text();
        if (txt == "Показать подробно") {
            jQuery('.c-jadd-spoiler span').text("Свернуть");
            jQuery('.c-jadd-spoiler span').addClass("dep-up");
        } else {
            jQuery('.c-jadd-spoiler span').text("Показать подробно");
            jQuery('.c-jadd-spoiler span').removeClass("dep-up");
        }
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
    $(".ch-l-dd").hide("slow", function(){
        console.log(1);
    });
    $(".ch-left").click(function(){
        if ($("#is_edit_now").val() == "is_edit_now") {
            showStyledPopupMessage("#pop-before-ask-question", "Ошибка", "Нельзя менять заявителя во время редактирования жалобы");
        } else {
            $(".ch-l-dd").slideToggle(300);
            $('.opacity-layer').show();
        }
    });
    $(".ch-left-cl").click(function(){
        if(window.location.pathname != '/complaint/add' &&
            window.location.pathname.indexOf('/complaint/edit') == -1){
            location.reload();
        }
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
        if ($(".ch-l-dd").css('display') == 'block') {$(".ch-l-dd").slideUp(300, function(){
            if(window.location.pathname != '/complaint/add' &&
                window.location.pathname.indexOf('/complaint/edit') == -1){
                location.reload();
            }
        });}
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
            element['fade'+ (f(this).scrollTop() > 200 ? 'In': 'Out')](200);
        });
    });
});