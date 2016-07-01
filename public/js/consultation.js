$(document).ready(function () {

    $('#question_button').click(function(){
       if(complaint.complaint_id !== false){

           var text = $('#question_text').val();

            if(validator.text(text,5,5000)){
                $.ajax({
                    type: 'POST',
                    url: '/consultation/addquestion',
                    data: 'question_text=' + text + '&complaint_id=' + complaint.complaint_id,
                    success: function (msg) {
                      var html =   '<div class="c-jd3-cb-usr">'+
                          $('#question_text').val()+
                        '</div>'+
                        '<div class="c-jd3-cb-answ">'+
                            '<div class="c-jd3-cb-answ-t">'+
                            'Когда наш консультант ответит Вам прийдет уведомление.'+
                        '</div>'+
                        '</div>';
                        $('.q-container').show();
                        $('.q-container').append(html);
                        $('#question_text').val('');
                    },
                    error: function (msg) {
                        console.log(msg);
                    }
                });

            }else{
                showStyledPopupMessage("#pop-before-ask-question", "Уведомление", "Текст жалобы должен состоять максимум из 5000, минимум из 5 символов");
            }

        }else{
            showStyledPopupMessage("#pop-before-ask-question", "Уведомление", "Сначала сохраните жалобу в черновик");
        }
    });

    $('.alert-box').on('click', 'div', function() {
        $('.alert-wrap, .alert-box').fadeOut(400);
    }); 

});

function showMessagePopup(type, message) {
    $('.alert-wrap').fadeIn(400);
    setTimeout(function(){
        $('.alert-box').fadeIn(200).text(message);
        $('.alert-box').append('<div></div>');
    },400);
}