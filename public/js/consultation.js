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
                        console.log(msg);
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
                $('.alert-box').text('Мало много текста');
            }

        }else{
            $('.alert-wrap').fadeIn(400);
            setTimeout($('.alert-box').fadeIn(200).text('Сначала, сохраните жалобу в черновик'),400);
            $('.alert-box').append('<div></div>');
            $('.alert-box div').click(function() {
                $('.alert-wrap, .alert-box').fadeOut(400);
            });         

        }
    });
});
