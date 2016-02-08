$(document).ready(function () {
    $("#notice_button").click(function (event) {
        event.preventDefault();

        var auction_id = $('#auction_id').val();
        if (validator.numeric(auction_id, 5, 25)) {
            $('#auction_id').removeClass('c-inp-error');
            $('#auction_id').removeClass('c-inp-done');
            $('#msg_status_parser').remove();

            $.ajax({
                type: 'POST',
                url: '/purchase/get',
                data: 'auction_id=' + auction_id,
                success: function (msg) {

                    var data = $.parseJSON(msg);
                    console.log(data);
                    var auction = auctionObj;
                    auction.processData(data);
                    if (auction.validation) {
                        $('#auction_id').addClass('c-inp-done');
                        $('#result_container').append('<b id="msg_status_parser">Данные Получены!</b>');
                        auction.setData();
                    } else {
                        $('#auction_id').addClass('c-inp-error');
                        $('#result_container').append('<b style="color:red!important;" id="msg_status_parser">Ошибка!</b>');
                        auction.clearData();
                        auction.setData();
                    }
                },
                error: function (msg) {
                    alert(msg);
                }

            });

        } else {
            $('#auction_id').addClass('c-inp-error');
        }
    });
});
var auctionObj = {
    data: {
        type: '',
        purchases_made: '',
        purchases_name: '',
        contact: '',
        date_start: '',
        date_end: '',
        date_opening: '',
        date_review: ''
    },
    validation: true,
    processData: function (data) {
        this.validation = true;

        if (data.info.type == undefined || data.zakazchik.length == 0) {
            this.validation = false;
            return false;
        }

        if (validator.text(data.info.type, 3, 200))
            this.type = data.info.type;
        else
            this.validation = false;

        if (validator.text(data.zakazchik[0].name), 3, 300)
            this.purchases_made = data.zakazchik[0].name;
        else
            this.validation = false;

        if (validator.text(data.info.object_zakupki), 3, 500)
            this.purchases_name = data.info.object_zakupki;
        else
            this.validation = false;

        this.contact = data.zakazchik[0].name + '<br>' +
            data.zakazchik[0].pochtovy_adres + '<br>' +
            data.zakazchik[0].kontaktnoe_lico + '<br>' +
            'E-mail: ' + data.zakazchik[0].email + '<br>' +
            'Телефон: ' + data.zakazchik[0].tel + '<br>';
        this.date_start = data.procedura.nachalo_podachi;
        this.date_end = data.procedura.okonchanie_podachi;
        this.date_opening = data.procedura.vskrytie_konvertov;
        this.date_review = data.procedura.vremya_provedeniya;
    },
    setData: function () {
        $('#type').html(this.type);
        $('#purchases_made').html(this.purchases_made);
        $('#purchases_name').html(this.purchases_name);
        $('#contact').html(this.contact);
        $('#date_start').html(this.date_start);
        $('#date_end').html(this.date_end);
        $('#date_opening').html(this.date_opening);
        $('#date_review').html(this.date_review);
    },
    clearData: function () {
        this.type = '',
            this.purchases_made = '',
            this.purchases_name = '',
            this.contact = '',
            this.date_start = '',
            this.date_end = '',
            this.date_opening = '',
            this.date_review = ''
    }


};
