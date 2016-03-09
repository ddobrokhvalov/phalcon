$(document).ready(function () {

    $('#complaint_edit_save').click(function(){
        complaint_edit.prepareData();
    });

});

var complaint_edit = {
    auctionData: '',
    complaintText: '',
    prepareData: function(){

        this.complaintText = $('.complaint-text').html();
        this.auctionData = '';
        var k = 0;
        for (var i in auction.data) {
            if (k == 0) {
                this.auctionData += i + '=' + auction.data[i];
                k += 1;
            } else {
                this.auctionData += '&' + i + '=' + auction.data[i];
            }
        }
        this.save();
    },
    save: function () {
        var sdata = complaint_edit.auctionData + '&complaint_text=' + complaint_edit.complaintText;
        $.ajax({
            type: 'POST',
            url: '/complaint/save',
            data: sdata,
            success: function (msg) {
               if(msg == '1'){
                   document.location.href = '/complaint/edit/'+auction.data['complaint_id'];
               }else{
                   showSomePopupMessage('error', 'Ошибка');
               }



            },
            error: function (msg) {
                console.log(msg);
            }
        });

    },

};