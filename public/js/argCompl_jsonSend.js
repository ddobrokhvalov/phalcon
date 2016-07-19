$('.DALEE').click(function() {
    var sendData = $('option').val();
    sendData.JSON.parse();
    sendRequest(sendData, resultTarget, sendMe());
});


function sendRequest(data, resultTarget, answer_callback) {
    $.ajax({
        type: "POST",
        url: "http://fas/complaint/ajaxCheckStep",
        data: data,
        success: function(msg) {
            var answer = JSON && JSON.parse(msg) || $.parseJSON(msg);
            answer_callback(answer);
        },
        error: function(xhr) {
            alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                + xhr.statusText + ' ResponseText:' + xhr.responseText);
        }
    });
}

function sendMe(msg) {

}