$(document).ready(function() {
    $('#argComplBtn').click(function(e) {
        var id = $('#argComplSelect .current-option').attr('data-value');
        var data = '?id=' + id + '&step=' + ajaxSendObj.step;
        ajaxSendObj.sendRequest(data);
        e.preventDefault();
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
                    var newChild = new ajaxSendObj.ChildrenGeneration(
                        value.cat_arguments[i].id,
                        value.cat_arguments[i].name,
                        value.cat_arguments[i].parent_id
                    );
                    $('#argComplSelect .custom-options div div:first').append(
                        '<li class="argo"' +
                        ' data-value="' + newChild.id +
                        '" data-parent="' + newChild.parent_id +
                        '">' + newChild.name + '</li>'
                    );
                    console.log(newChild);
                }
                ajaxSendObj.step++;
            },
            error: function (xhr) {
                alert(xhr + 'Request Status: ' + xhr.status + ' Status Text: '
                + xhr.statusText + ' ResponseText:' + xhr.responseText);
            }
        });
    },
    ChildrenGeneration: function(id, name, parent_id) {
        this.id = id;
        this.name = name;
        this.parent_id = parent_id;
    },
    showDopBlock: function() {
        if (ajaxSendObj.step >= 3) {
            $('.last-argComplList').slideDown(200);
            $('.btn-div').show();
        } else if (ajaxSendObj.step < 3) {
            $('.last-argComplList').slideUp(200);
            $('.btn-div').hide();
        }
    }
};