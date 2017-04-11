
$(document).ready(function(){


var price_t2 = ["600", "550", "500", "450", "400"];

var price_t3 = ["3000", "7500", "12000", "15000"];
var price_t3_m = ["1", "3", "6", "12"];


$("#t-price-2")
    .slider({
        max: 5,
        value: 1,
        min: 1,
        slide: function( event, ui ) {
        $( "#tariff_price2" ).html(ui.value *  price_t2[ui.value-1] +' <span class="rub">i</span>'); 
        $( "#t-summ-2" ).html(price_t2[ui.value-1] +' <span class="rub">i</span>  / 1 жалоба'); 
      },
      change: function( event, ui ) {
        $( "#tariff_price2" ).html(ui.value *  price_t2[ui.value-1] +' <span class="rub">i</span>');
        $( "#t-summ-2" ).html(price_t2[ui.value-1] +' <span class="rub">i</span>  / 1 жалоба');
      },
      create: function( event, ui ) {
        $( "#tariff_price2" ).html(1 *  price_t2[0] +' <span class="rub">i</span>');
        $( "#t-summ-2" ).html(price_t2[0] +' <span class="rub">i</span>  / 1 жалоба');

      }
    })
    .slider("pips", {
        rest: "label"
    });



$("#t-price-3")
    .slider({
        max: 3,
        value: 0,
        // step: 1,
        slide: function( event, ui ) {
        $( "#tariff_price3" ).html(price_t3[ui.value] +' <span class="rub">i</span>');  
        $( "#t-summ-3" ).html(price_t3[ui.value] / price_t3_m[ui.value] +' <span class="rub">i</span>  / месяц'); 
      },
      change: function( event, ui ) {
        $( "#tariff_price3" ).html(price_t3[ui.value] +' <span class="rub">i</span>');  
        $( "#t-summ-3" ).html(price_t3[ui.value] / price_t3_m[ui.value] +' <span class="rub">i</span>  / месяц'); 
      },
      create: function( event, ui ) {
         $( "#tariff_price3" ).html(price_t3_m[0] *  price_t3[0] +' <span class="rub">i</span>');
        $( "#t-summ-3" ).html(price_t3[0] +' <span class="rub">i</span>  / месяц');

      }
    })
    .slider("pips", {
        rest: "label",
        labels: ['1', '3', '6', '12'],  
    });



});

