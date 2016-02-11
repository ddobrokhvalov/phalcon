$(document).ready(function () {
  $('#csall1').click(function(){
      if($(this).is(':checked')) {
          indexComplaint.selectAll();
      }else{
          indexComplaint.deSelectAll();
      }
  });

    $('.complaint-checkbox').click(function(){
        if($(this).is(':checked')) {
            indexComplaint.addComplain($(this).val());
        }else{
            indexComplaint.removeComplain($(this).val());
        }
    });

    $('.change-status').click(function(){
      indexComplaint.changeStatus($(this).attr("value"));
    });

});

var indexComplaint ={
    selectedComplaint:[],
    changeStatus:function(status){
        var data = JSON.stringify(this.selectedComplaint);
        $.ajax({
            type: 'POST',
            url: '/complaint/status',
            data: 'status='+status+'&complaints='+data,
            success: function (msg) {
                console.log(msg);
                document.location.href = '/complaint/index';
            },
            error: function (msg) {
                alert(msg);
            }

        });
    },
    addComplain:function(id){
        this.selectedComplaint.push(id);
    },
    removeComplain:function(id){
        var index = this.selectedComplaint.indexOf(id);
        if (index > -1) {
            this.selectedComplaint.splice(index, 1);
        }

    },
    selectAll:function(){
        $('.complaint-checkbox').each(function(){
            indexComplaint.selectedComplaint.push($(this).val());
            $(this).prop('checked', true);
        });
    },
    deSelectAll:function(){
        console.log(this.selectedComplaint);
        this.selectedComplaint = [];
        $('.complaint-checkbox').each(function(){
            $(this).prop('checked', false);
        });
    }
};