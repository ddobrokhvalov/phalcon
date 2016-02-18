$(document).ready(function () {
    $('.select_all_complaint').click(function () {
        if ($(this).is(':checked')) {
            indexComplaint.selectAll();
        } else {
            indexComplaint.deSelectAll();
        }
    });

    $('.complaint-checkbox').click(function () {
        if ($(this).is(':checked')) {
            indexComplaint.addComplain($(this).val());
        } else {
            indexComplaint.removeComplain($(this).val());
        }
    });

    $('.change-status').click(function () {
        var status = $(this).attr("value");

        indexComplaint.changeStatus(status);
    });
    $('.button-copy').click(function(){
        if(indexComplaint.copyButton === true && indexComplaint.selectedComplaint.length == 1){
            indexComplaint.changeStatus('copy');
        }
    });

});

var indexComplaint = {
    selectedComplaint: [],
    copyButton: false,
    returnButton:false,
    inArchivButton:false,
    deleteButton:false,
    activeButtonCopy: function () {
        this.copyButton = true;
        $('.button-copy').removeClass('button_copy_deactive');
        $('.button-copy').addClass('button_copy_active');
    },
    deActivButtonCopy: function () {
        this.copyButton = false;
        $('.button-copy').removeClass('button_copy_active');
        $('.button-copy').addClass('button_copy_deactive');
    },
    changeStatus: function (status) {
        var data = JSON.stringify(this.selectedComplaint);
        $.ajax({
            type: 'POST',
            url: '/complaint/status',
            data: 'status=' + status + '&complaints=' + data,
            success: function (msg) {
                console.log(msg);
                if(status == 'copy')
                    document.location.href = '/complaint/edit/'+msg;
                else
                 document.location.href = '/complaint/index';
            },
            error: function (msg) {
                alert(msg);
            }

        });
    },
    addComplain: function (id) {
        this.selectedComplaint.push(id);
        if (this.selectedComplaint.length == 1)
            this.activeButtonCopy();
        else
            this.deActivButtonCopy();
    },
    removeComplain: function (id) {
        var index = this.selectedComplaint.indexOf(id);
        if (index > -1) {
            this.selectedComplaint.splice(index, 1);
        }
        if (this.selectedComplaint.length == 1)
            this.activeButtonCopy();
        else
            this.deActivButtonCopy();

    },
    selectAll: function () {
        this.deActivButtonCopy();
        $('.complaint-checkbox').each(function () {
            indexComplaint.selectedComplaint.push($(this).val());
            $(this).prop('checked', true);
        });
    },
    deSelectAll: function () {
        console.log(this.selectedComplaint);
        this.selectedComplaint = [];
        $('.complaint-checkbox').each(function () {
            $(this).prop('checked', false);
        });
    }
};