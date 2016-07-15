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
        debugger;
        var status = $(this).attr("value");
        if(!$(this).hasClass("button_copy_deactive")){
            indexComplaint.changeStatus(status);
        }
    });
    $('.button-copy').click(function(){
        debugger;
        if(indexComplaint.copyButton === true && indexComplaint.selectedComplaint.length == 1){
            indexComplaint.changeStatus('copy');
        }
    });

    $('.button-copy-edit-page').click(function(){
            indexComplaint.selectedComplaint.push( $(this).attr("value"));
            indexComplaint.changeStatus('copy');

    });
    $('.button-back-copy-edit-page').click(function(){
        indexComplaint.selectedComplaint.push( $(this).attr("value"));
        indexComplaint.changeStatusBack('copy');

    });

    $('.button-recall').click(function(){
        //if(currentStatus == 'submitted'){
            indexComplaint.recall();
        //}
    });

});

var indexComplaint = {
    selectedComplaint: [],
    arrComplaint: [],
    copyButton: false,
    returnButton:false,
    inArchivButton:false,
    deleteButton:false,
    recall: function(){//todo: mabe we need to use changeStatus
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
                //document.location.href = '/complaint/index?status=recalled';
            },
            error: function (msg) {
                console.log(msg);
            }

        });
    },
    activeButton: function (button) {
        if(button == '.button-copy'){
            this.copyButton = true;
        }
        if(button == '.to-archive'){
            this.inArchivButton = true;
        }
        if(button == '.to-delete'){
            this.deleteButton = true;
        }
        $(button).removeClass('button_copy_deactive');
        $(button).addClass('button_copy_active');
    },
    deActiveButton: function (button) {
        if(button == '.button-copy'){
            this.copyButton = false;
        }
        if(button == '.to-archive'){
            this.inArchivButton = false;
        }
        if(button == '.to-delete'){
            this.deleteButton = false;
        }
        $(button).removeClass('button_copy_active');
        $(button).addClass('button_copy_deactive');
    },
    changeStatus: function (status) {
        debugger;
        console.log(this);
        var data = JSON.stringify(this.selectedComplaint);
        var edit = $(".c-jadd1-1-b").attr("edit");
        if(edit != undefined){
            var temp = new Array();
            temp.push(edit);
            data= JSON.stringify(temp);
        }
        $.ajax({
            type: 'POST',
            url: '/complaint/status',
            data: 'status=' + status + '&complaints=' + data,
            success: function (msg) {
                console.log(msg);
                switch(status){
                    case 'copy':
                        $(".admin-popup-content p").text("Копия сделана успешно!");
                        $('.admin-popup-close, .admin-popup-bg').on('click', function() {
                            document.location.href = '/complaint/edit/' + msg
                        });
                        $(".admin-popup-wrap").show();
                        setTimeout( function(){
                            document.location.href = '/complaint/edit/' + msg
                        }, 2000);
                    break;
                    case 'archive':
                        $(".admin-popup-content p").text("Жалоба успешно помещена в архив!");
                        $(".admin-popup-wrap").show();
                        setTimeout( function(){
                            location.reload();
                        }, 2000);
                    break;
                    case 'delete':
                        $(".admin-popup-content p").text("Жалоба успешно удалена!");
                        $('.admin-popup-close, .admin-popup-bg').on('click', function() {
                            location.reload();
                        });
                        $(".admin-popup-wrap").show();
                        setTimeout( function(){
                            location.reload();
                        }, 2000);
                    break;
                    case 'activate':
                        $(".admin-popup-content p").text("Жалоба успешно активирована!");
                        $('.admin-popup-close, .admin-popup-bg').on('click', function() {
                            location.reload();
                        });
                        $(".admin-popup-wrap").show();
                        setTimeout( function(){
                            location.reload();
                        }, 2000);
                    break;
                    default:
                        document.location.href = '/complaint/index';
                    break
                }
            },
            error: function (msg) {
                console.log(msg);
            }

        });
    },
    changeStatusBack: function (status) {
        var data = JSON.stringify(this.selectedComplaint);
        $.ajax({
            type: 'POST',
            url: '/admin/complaints/status',
            data: 'status=' + status + '&complaints=' + data,
            success: function (msg) {
                console.log(msg);
                if(status == 'copy')
                    document.location.href = 'admin/complaints/edit/'+msg;
                else
                    document.location.href = 'admin/complaints/index';
            },
            error: function (msg) {
                console.log(msg);
            }

        });
    },
    addComplain: function (id) {
        debugger;
        this.selectedComplaint.push(id);
        this.arrComplaint.push({
            'id':id,
            'status': jQuery("input[name=jlist" + id + "]").parent().find('#current-status').val()
        });


        if(this.arrComplaint.length == 0){
            $(".c-cs-btns").removeClass("c-cs-btns-after");
            $(".button_copy_active").removeClass("button_copy_active");
        }

        if(this.arrComplaint.length == 1){
            if(this.arrComplaint[0].status == 'draft' || this.arrComplaint[0].status == 'archive'){
                this.activeButton('.button-copy');
            }
            if(this.arrComplaint[0].status == 'draft'){
                this.activeButton('.to-archive');
            }
            if(this.arrComplaint[0].status == 'archive'){
                this.deActiveButton('.to-archive');
            }
        }

        if(this.arrComplaint.length > 1){
            this.deActiveButton('.button-copy');
            //$(".button-copy ").removeClass("button_copy_active");

            var checkSame = true;
            var current = this.arrComplaint[0].status;
            var archive = false;
            var draft   = false;
            for(var i = 0; i < this.arrComplaint.length; i++){
                if(this.arrComplaint[i].status == 'draft')     draft = true;
                if(this.arrComplaint[i].status == 'archive')   archive = true;
                if(this.arrComplaint[i].status != current){
                    checkSame = false;
                }
            }

            if(checkSame && draft){
                this.activeButton('.to-archive');
            }
            if(draft && archive){
                this.deActiveButton('.to-archive');
            }
        }


        //if (this.selectedComplaint.length > 0) {
        //    if (jQuery("input[name=jlist" + id + "]").parent().find('#current-status').val() != "archive") {
        //        this.activeButton('.to-archive');
        //        $(".to-archive").addClass("button_copy_active");
        //    }
        //    this.activeButton('.set-active');
        //}
        //if (this.selectedComplaint.length == 1) {
        //    this.activeButton('.button-copy');
        //    if(currentStatus == 'submitted')
        //      this.activeButton('.button-recall');
        //
        //}else {
        //    this.deActiveButton('.button-copy');
        //    this.deActiveButton('.to-archive');
        //    //if(currentStatus == 'submitted')
        //       // this.deActiveButton('.button-recall');
        //}
    },
    removeComplain: function (id) {
        debugger;
        var index = this.selectedComplaint.indexOf(id);
        if (index > -1) {
            this.selectedComplaint.splice(index, 1);
        }

        if(this.arrComplaint.length > 0){
            for(var i = 0; i < this.arrComplaint.length; i++){
                if(this.arrComplaint[i].id == id) this.arrComplaint.splice(i, 1);
            }
        }


        if(this.arrComplaint.length == 0){
            $(".c-cs-btns").removeClass("c-cs-btns-after");
            $(".button_copy_active").removeClass("button_copy_active");
        }

        if(this.arrComplaint.length == 1){
            if(this.arrComplaint[0].status == 'draft' || this.arrComplaint[0].status == 'archive'){
                this.activeButton('.button-copy');
            }
            if(this.arrComplaint[0].status == 'draft'){
                this.activeButton('.to-archive');
            }
            if(this.arrComplaint[0].status == 'archive'){
                this.deActiveButton('.to-archive');
            }
        }

        if(this.arrComplaint.length > 1){
            this.deActiveButton('.button-copy');
            //$(".button-copy ").removeClass("button_copy_active");

            var checkSame = true;
            var current = this.arrComplaint[0].status;
            var archive = false;
            var draft   = false;
            for(var i = 0; i < this.arrComplaint.length; i++){
                if(this.arrComplaint[i].status == 'draft')     draft = true;
                if(this.arrComplaint[i].status == 'archive')   archive = true;
                if(this.arrComplaint[i].status != current){
                    checkSame = false;
                }
            }
            if(checkSame && draft){
                this.activeButton('.to-archive');
            }
            if(draft && archive){
                this.deActiveButton('.to-archive');
            }
        }
        //if (this.selectedComplaint.length == 1)
        //    this.activeButton('.button-copy');
        //else
        //    this.deActiveButton('.button-copy');
        //
        //if(this.selectedComplaint.length < 1) {
        //    this.deActiveButton('.button-recall');
        //    this.deActiveButton('.to-archive');
        //    $(".to-archive").removeClass("button_copy_active");
        //}
    },
    selectAll: function () {
        debugger;
        this.deActiveButton('.button-copy');
        $(".to-archive").addClass("button_copy_active");
        if(currentStatus == 'submitted')
            this.activeButton('.button-recall');
        $('.complaint-checkbox').each(function () {
            indexComplaint.selectedComplaint.push($(this).val());
            $(this).prop('checked', true);
        });
    },
    deSelectAll: function () {
        debugger;
        console.log(this.selectedComplaint);
        $(".to-archive").removeClass("button_copy_active");
        if(currentStatus == 'submitted')
          this.deActiveButton('.button-recall');
        this.selectedComplaint = [];
        $('.complaint-checkbox').each(function () {
            $(this).prop('checked', false);
        });
    }
};