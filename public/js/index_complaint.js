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

    $('.button-copy-edit-page').click(function(){

            indexComplaint.selectedComplaint.push( $(this).attr("value"));
            indexComplaint.changeStatus('copy');

    });

    $('.button-recall').click(function(){
        //if(currentStatus == 'submitted'){
            indexComplaint.recall();
        //}
    });

});

var indexComplaint = {
    selectedComplaint: [],
    copyButton: false,
    returnButton:false,
    inArchivButton:false,
    deleteButton:false,
    recall: function(){
        var data = JSON.stringify(this.selectedComplaint);
        $.ajax({
            type: 'POST',
            url: '/complaint/recall/0',
            data: 'status=' + status + '&complaints=' + data,
            success: function (msg) {
                console.log(msg);
             /*   if(status == 'copy')
                    document.location.href = '/complaint/edit/'+msg;
                else
                    document.location.href = '/complaint/index?status=recalled'; */
                document.location.href = '/complaint/index?status=recalled';
            },
            error: function (msg) {
                console.log(msg);
            }

        });
    },
    activeButton: function (button) {
        this.copyButton = true;
        $(button).removeClass('button_copy_deactive');
        $(button).addClass('button_copy_active');
    },
    deActiveButton: function (button) {
        this.copyButton = false;
        $(button).removeClass('button_copy_active');
        $(button).addClass('button_copy_deactive');
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
                console.log(msg);
            }

        });
    },
    addComplain: function (id) {
        this.selectedComplaint.push(id);
        if (this.selectedComplaint.length == 1) {
            this.activeButton('.button-copy');
            if(currentStatus == 'submitted')
              this.activeButton('.button-recall');

        }else {
            this.deActiveButton('.button-copy');
            //if(currentStatus == 'submitted')
               // this.deActiveButton('.button-recall');
        }
    },
    removeComplain: function (id) {
        var index = this.selectedComplaint.indexOf(id);
        if (index > -1) {
            this.selectedComplaint.splice(index, 1);
        }
        if (this.selectedComplaint.length == 1)
            this.activeButton('.button-copy');
        else
            this.deActiveButton('.button-copy');

        if(this.selectedComplaint.length < 1)
            this.deActiveButton('.button-recall');

    },
    selectAll: function () {
        this.deActiveButton('.button-copy');
        if(currentStatus == 'submitted')
            this.activeButton('.button-recall');
        $('.complaint-checkbox').each(function () {
            indexComplaint.selectedComplaint.push($(this).val());
            $(this).prop('checked', true);
        });
    },
    deSelectAll: function () {
        console.log(this.selectedComplaint);
        if(currentStatus == 'submitted')
          this.deActiveButton('.button-recall');
        this.selectedComplaint = [];
        $('.complaint-checkbox').each(function () {
            $(this).prop('checked', false);
        });
    }
};