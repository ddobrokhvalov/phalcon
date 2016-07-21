$(document).ready(function () {
    initialize();
    $('.select_all_complaint').click(function () {
        debugger;
        console.log(this)
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
        if(!$(this).hasClass("button_copy_deactive")){
            indexComplaint.recall();
        }
    });

});

var indexComplaint = {
    selectedComplaint: [],
    arrComplaint: [],
    copyButton: false,
    returnButton:false,
    inArchivButton:false,
    deleteButton:false,
    recollButton:false,
    recall: function(){//todo: mabe we need to use changeStatus
        var data = JSON.stringify(this.selectedComplaint);
        $.ajax({
            type: 'POST',
            url: '/complaint/recall/0',
            data: 'status=' + status + '&complaints=' + data,
            success: function (msg) {
                $(".admin-popup-content p").text("Жалоба успешно отозвана!");
                $('.admin-popup-close, .admin-popup-bg').on('click', function() {
                    location.reload();
                });
                $(".admin-popup-wrap").show();
                setTimeout( function(){
                    location.reload();
                }, 2000);
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

    getUrlVars: function(){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
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
        if(button == '.button-recall'){
            this.recollButton = false;
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
        if(button == '.button-recall'){
            this.recollButton = false;
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
                debugger;
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
                            document.location.href = '/complaint/index';
                        });
                        $(".admin-popup-wrap").show();
                        setTimeout( function(){
                            document.location.href = '/complaint/index';
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
                    case 'recalled':
                        $(".admin-popup-content p").text("Жалоба успешно отозвана!");
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
        this.deActiveButton('.button-recall');
        switch (currentStatus){
            case 'draft':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.deActiveButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.deActiveButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'submitted':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
                break;
            case 'under_consideration':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
                break;
            break;
            case 'justified':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'unfounded':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'recalled':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'archive':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                    this.activeButton('.set-active');
                    this.deActiveButton('.button-recall');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                    this.activeButton('.set-active');
                }
            break;
            default:
                var checkSame = true;
                var current = this.arrComplaint[0].status;
                this.deActiveButton('.button-copy');
                this.deActiveButton('.button-recall');
                this.deActiveButton('.to-archive');
                this.deActiveButton('.to-delete');
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                }
                for(var i = 0; i < this.arrComplaint.length; i++){
                    switch(this.arrComplaint[i].status){
                        case 'draft':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                        break;
                        case 'archive':
                            if(this.arrComplaint[i].status != 'archive'){
                                this.activeButton('.to-archive');
                            }
                            this.activeButton('.to-delete');
                            this.activeButton('.set-active');
                        break;
                        case 'submitted':
                            this.activeButton('.button-recall');
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                        break;
                        case 'recalled':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                        break;
                        case 'under_consideration':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                        break;
                        case 'justified':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                        break;
                        case 'unfounded':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                        break;
                    };
                    if(this.arrComplaint[i].status != current){
                        checkSame = false;
                    }
                }
            break;
        }

        if(this.arrComplaint.length == 0){
            $(".c-cs-btns").removeClass("c-cs-btns-after");
            $(".button_copy_active").removeClass("button_copy_active");
            $(".to-delete").removeClass("delete-active");
            this.deActiveButton('.to-delete');
        }

    },
    removeComplain: function (id) {
        debugger;
        var index = this.selectedComplaint.indexOf(id);
        if (index > -1) {
            this.selectedComplaint.splice(index, 1);
        }

        if(this.arrComplaint.length > 0){
            $(".to-delete").addClass("delete-active");
            for(var i = 0; i < this.arrComplaint.length; i++){
                if(this.arrComplaint[i].id == id) this.arrComplaint.splice(i, 1);
            }
        }
        if(this.arrComplaint.length == 0){
            $(".c-cs-btns").removeClass("c-cs-btns-after");
            $(".button_copy_active").removeClass("button_copy_active");
            $(".to-delete").removeClass("delete-active");
            this.deActiveButton('.to-delete');
        }
        this.deActiveButton('.button-recall');
        switch (currentStatus){
            case 'draft':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.deActiveButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.deActiveButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'submitted':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.button-recall');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'under_consideration':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'justified':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'unfounded':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'recalled':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                }
            break;
            case 'archive':
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                    this.activeButton('.set-active');
                    this.deActiveButton('.button-recall');
                } else if(this.arrComplaint.length > 1){
                    this.deActiveButton('.button-copy');
                    this.activeButton('.to-archive');
                    this.activeButton('.to-delete');
                    this.activeButton('.set-active');
                }
            break;
            default:
                var checkSame = true;
                var current = this.arrComplaint[0].status;
                this.deActiveButton('.button-copy');
                this.deActiveButton('.button-recall');
                this.deActiveButton('.to-archive');
                this.deActiveButton('.to-delete');
                if(this.arrComplaint.length == 1){
                    this.activeButton('.button-copy');
                }
                for(var i = 0; i < this.arrComplaint.length; i++){
                    switch(this.arrComplaint[i].status){
                        case 'draft':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                            break;
                        case 'archive':
                            if(this.arrComplaint[i].status != 'archive'){
                                this.activeButton('.to-archive');
                            }
                            this.activeButton('.to-delete');
                            this.activeButton('.set-active');
                            break;
                        case 'submitted':
                            this.activeButton('.button-recall');
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                            break;
                        case 'recalled':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                            break;
                        case 'under_consideration':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                            break;
                        case 'justified':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                            break;
                        case 'unfounded':
                            this.activeButton('.to-archive');
                            this.activeButton('.to-delete');
                            break;
                    };
                    if(this.arrComplaint[i].status != current){
                        checkSame = false;
                    }
                }
            break;
        }
    },
    selectAll: function () {
        debugger;
        var self = this;
        var count = 0;
        this.deActiveButton('.button-copy');
        this.deActiveButton('.button-recall');
        this.deActiveButton('.to-archive');
        this.deActiveButton('.to-delete');
        this.arrComplaint = [];
        $('.complaint-checkbox').each(function () {
            var id = $(this).val();
            var status = jQuery("input[name=jlist" + id + "]").parent().find('#current-status').val();
            self.arrComplaint.push({
                'id':id,
                'status': jQuery("input[name=jlist" + id + "]").parent().find('#current-status').val()
            });
            if(count < 1){
                self.activeButton('.button-copy');
            }else {
                self.deActiveButton('.button-copy');
            }
            switch(status){
                case 'draft':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'archive':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    self.activeButton('.set-active');
                    break;
                case 'submitted':
                    self.activeButton('.button-recall');
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'recalled':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'under_consideration':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'justified':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'unfounded':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
            };
            indexComplaint.selectedComplaint.push($(this).val());
            count++;
            $(this).prop('checked', true);
        });
    },
    deSelectAll: function () {
        this.deActiveButton('.button-copy');
        this.deActiveButton('.button-recall');
        this.deActiveButton('.to-archive');
        this.deActiveButton('.to-delete');
        this.deActiveButton('.set-active');
        $(".c-cs-btns").removeClass("c-cs-btns-after");
        $(".button_copy_active").removeClass("button_copy_active");
        $(".to-delete").removeClass("delete-active");
        this.arrComplaint = [];
        this.selectedComplaint = [];
        $('.complaint-checkbox').each(function () {
            $(this).prop('checked', false);
        });
    }
};


function initialize( ){
    if(typeof edit == undefined) {
        var self = indexComplaint;
        var count = 0;

        self.deActiveButton('.button-copy');
        self.deActiveButton('.button-recall');
        self.deActiveButton('.to-archive');
        self.deActiveButton('.to-delete');
        self.arrComplaint = [];

        $('.complaint-checkbox:checked').each(function () {
            debugger;
            $(".c-cs-btns").addClass("c-cs-btns-after");
            var id = $(this).val();
            var status = jQuery("input[name=jlist" + id + "]").parent().find('#current-status').val();
            self.arrComplaint.push({
                'id': id,
                'status': jQuery("input[name=jlist" + id + "]").parent().find('#current-status').val()
            });
            if (count < 1) {
                self.activeButton('.button-copy');
            } else {
                self.deActiveButton('.button-copy');
            }
            switch (status) {
                case 'draft':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'archive':
                    self.deActiveButton('.to-archive');
                    self.activeButton('.to-delete');
                    self.activeButton('.set-active');
                    break;
                case 'submitted':
                    self.activeButton('.button-recall');
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'recalled':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'under_consideration':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'justified':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
                case 'unfounded':
                    self.activeButton('.to-archive');
                    self.activeButton('.to-delete');
                    break;
            }
            ;
            self.selectedComplaint.push($(this).val());
            count++;
            $(this).prop('checked', true);
        });
    }
}