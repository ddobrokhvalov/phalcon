$(document).ready(function() {
    $('#argComplSelect .custom-options').on('click', 'li', function() {
        $('#argComplBtn').slideDown(400);
        $('.argCompl-review li').css('color', '#000');
    });
    $('#argComplBtn').click(function(e) {
        nextStep(e);
    });
    $('.argCompl-review').on('click', 'li', function() {
        ajaxSendObj.showHideBtn($(this));
    });
});

function nextStep(e) {
    var id = $('#argComplSelect .current-option').attr('data-value');
    var data = '?id=' + id + '&step=' + ajaxSendObj.step;
    ajaxSendObj.sendRequest(data);
    $('#argComplBtn').slideUp(400);
    e.preventDefault();
}

var ajaxSendObj = {
    step: 2,
    sendRequest: function(data) {
        $.ajax({
            type: "GET",
            url: "http://fas/complaint/ajaxStepsAddComplaint" + data,
            dataType: 'json',
            success: function (value) {
                ajaxSendObj.showDopBlocks();
                if (value.cat_arguments.length == 0) {
                    $('#argComplSelect').slideUp(400);
                }
                $('#argComplSelect .custom-options li').remove();
                for (var i = 0; i < value.cat_arguments.length; i++) {
                    $('#argComplSelect .custom-options div div:first').append(
                        '<li class="argo"' +
                        ' data-value="' + value.cat_arguments[i].id +
                        '" data-parent="' + value.cat_arguments[i].parent_id +
                        '">' + value.cat_arguments[i].name + '</li>'
                    );
                }
                if (ajaxSendObj.step == 3 || ajaxSendObj.step == 4) {
                    $('.argCompl-review li').remove();
                    for (var i = 0; i < value.arguments.length; i++) {
                        $('.argCompl-review ul').append(
                            '<li data-value="' + value.arguments[i].id +
                            '" data-parent="' + value.arguments[i].category_id +
                            '">' + value.arguments[i].name + '</li>'
                        );
                    }
                }
                ajaxSendObj.stepsChangeLine();
                if (ajaxSendObj.step > 0 && ajaxSendObj.step < 5) {
                    ajaxSendObj.step++;
                }
            },
            error: function (xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    showDopBlocks: function() {
        if (ajaxSendObj.step < 3 && ajaxSendObj.step > 0) {
            $('.last-argComplList').slideUp(400);
            $('#argComplSelect').slideDown(400);
            $('.btn-div').fadeOut(400);
        } else if (ajaxSendObj.step == 3) {
            $('.last-argComplList').slideDown(400);
            $('#argComplSelect').slideDown(400);
            $('#argComplSelect .custom-options').on('click', 'li', function() {
                $('.btn-div').slideUp(400);
            });
        } else if ( ajaxSendObj.step == 4) {
            $('#argComplSelect').slideUp(400);
        }
    },
    showHideBtn: function(obj) {
        $('.argCompl-review li').css('color', '#000');
        obj.css('color', '#00aeef');
        $('.btn-div').slideDown(400);
        $('#argComplBtn').slideUp(400);
        $('#argComplSelect .custom-options').slideUp();
        $('#argComplSelect .current-option span').removeClass('rotate-icon');
        $('#argComplSelect .current-option div').removeClass('transDiv');
    },
    stepsChangeLine: function() {
        if (ajaxSendObj.step == 2) {
            changeLine(1, 2);
        } else if (ajaxSendObj.step == 3) {
            changeLine(2, 3);
        } else if (ajaxSendObj.step == 4) {
            changeLine(3, 4);
        } else if (ajaxSendObj.step == 5) {
            changeLine(4);
        }
        function changeLine(numb, numb2) {
            $('.steps-line:nth-child(' + numb + ')').removeClass('arg-nextStep');
            $('.steps-line:nth-child(' + numb + ')').addClass('arg-dunStep');
            $('.steps-line:nth-child(' + numb2 + ')').addClass('arg-nextStep');
        }
    }
};