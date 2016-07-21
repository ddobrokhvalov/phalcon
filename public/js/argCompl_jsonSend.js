$(document).ready(function() {
    $('#argComplBtn').click(function(e) {
        var id = $('#argComplSelect .current-option').attr('data-value');
        var data = '?id=' + id + '&step=' + ajaxSendObj.step;
        ajaxSendObj.sendRequest(data);
        e.preventDefault();
    });
    $('.argCompl-review').on('click', 'li', function() {
        ajaxSendObj.showHideBtn('add', $(this));
    });
});

var ajaxSendObj = {
    step: 2,
    sendRequest: function(data) {
        $.ajax({
            type: "GET",
            url: "http://fas/complaint/ajaxStepsAddComplaint" + data,
            dataType: 'json',
            success: function (value) {
                ajaxSendObj.showDopBlock();
                $('#argComplSelect .custom-options li').remove();
                for (var i = 0; i < value.cat_arguments.length; i++) {
                    $('#argComplSelect .custom-options div div:first').append(
                        '<li class="argo"' +
                        ' data-value="' + value.cat_arguments[i].id +
                        '" data-parent="' + value.cat_arguments[i].parent_id +
                        '">' + value.cat_arguments[i].name + '</li>'
                    );
                    console.log(value.cat_arguments[i]);
                }
                if (ajaxSendObj.step > 0 && ajaxSendObj.step < 4) {
                    ajaxSendObj.step++;
                }
                console.log(ajaxSendObj.step);
            },
            error: function (xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    showDopBlock: function() {
        if (ajaxSendObj.step < 3 && ajaxSendObj.step > 0) {
            $('.last-argComplList').slideUp(200);
            $('#argComplSelect').slideDown(200);
            $('.btn-div').fadeOut(200);
        } else if (ajaxSendObj.step == 3) {
            $('.last-argComplList').slideDown(200);
            $('#argComplSelect').slideDown(200);
        } else if ( ajaxSendObj.step == 4) {
            $('#argComplSelect').slideUp(200);
        }
    },
    showHideBtn: function(input, obj) {
        function show() {
            $('.argCompl-review li').css('color', '#000');
            obj.css('color', '#00aeef');
            $('.btn-div').fadeIn(200);
        }
        function hide() {

        }
        if (input == 'add') {
            return show();
        } else if (input == 'hide') {
            return hide();
        }
    }
};