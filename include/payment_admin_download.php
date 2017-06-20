<?php

function num2str($num) {
	$nul='ноль';
	$ten=array(
		array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
		array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
	);
	$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
	$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
	$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
	$unit=array( // Units
		array('копейка' ,'копейки' ,'копеек',	 1),
		array('рубль'   ,'рубля'   ,'рублей'    ,0),
		array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
		array('миллион' ,'миллиона','миллионов' ,0),
		array('миллиард','милиарда','миллиардов',0),
	);
	//
	list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
	$out = array();
	if (intval($rub)>0) {
		foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
			if (!intval($v)) continue;
			$uk = sizeof($unit)-$uk-1; // unit key
			$gender = $unit[$uk][3];
			list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
			// mega-logic
			$out[] = $hundred[$i1]; # 1xx-9xx
			if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
			else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
			// units without rub & kop
			if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
		} //foreach
	}
	else $out[] = $nul;
	$out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
	$out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
	return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}


function morph($n, $f1, $f2, $f5) {
	$n = abs(intval($n)) % 100;
	if ($n>10 && $n<20) return $f5;
	$n = $n % 10;
	if ($n>1 && $n<5) return $f2;
	if ($n==1) return $f1;
	return $f5;
}

session_start();
if(!$_SESSION["auth"]){
	//header("Location: /");
}

if(!$tarif_order){
	header("Location: /complaint/changetarif");
}

//error_reporting(E_ALL);
require_once($_SERVER["DOCUMENT_ROOT"]."/apps/library/mpdf/mpdf.php");

/*print_r("<pre>");
print_r($tarif_order);
print_r("</pre>");*/

$month_name = array(1=>"января",
					2=>"февраля",
					3=>"марта",
					4=>"апреля",
					5=>"мая",
					6=>"июня",
					7=>"июля",
					8=>"августа",
					9=>"сентября",
					10=>"октября",
					11=>"ноября",
					12=>"декабря",);

$html2 = '
<style type="text/css">
.pdf_container{
	padding: 20px;
}
.pdf_container p{
	margin: 0;
	padding: 0;
}
.pdf_container, .pdf_container table{
	font-weight: normal;
}
.border_table{
	border: 1px solid #000;
	width: 100%; 
	border-collapse: collapse;
}
.border_table td{
	border: 1px solid #000;width: 100%;
	padding: 3px;
}
.underline{
	
	border-bottom: 1px solid #000;
	
}
</style>
<div class="pdf_container">
<p>
	<table style="width:100%;">
		<tr>
			<td style="width: 220px; vertical-align: middle;">
				<img src="/images/hlogo.png">
			</td>
			<td>
				<table class="border_table" style="">
					<tr>
						<td rowspan="2" colspan="2" style="">
							ФИЛИАЛ "САНКТ-ПЕТЕРБУРГСКИЙ" АО "АЛЬФА-БАНК" Г. САНКТ-ПЕТЕРБУРГ<br>
							Банк получателя
						</td>
						<td style="">
							БИК
						</td>
						<td rowspan="2" style="">
							044030786<br><br>
							30101810600000000786
						</td>
					</tr>
					<tr>
						<td style="">
							Сч. №
						</td>
					</tr>
					<tr>
						<td style="">
							ИНН 7814659211
						</td>
						<td style="">
							КПП 781401001
						</td>
						<td rowspan="2" style="vertical-align: top;">
							Сч. №
						</td>
						<td rowspan="2" style="vertical-align: top;">
							40702810032280001256
						</td>
					</tr>
					<tr>
						<td colspan="2">
							ООО "ФАС-ОНЛАЙН"<br><br><br><br>
							Получатель
						</td>
						
					</tr>
				</table>
			</td>
		</tr>
	</table>
</p>
<p style="padding-bottom: 0; margin-bottom: 0;">
	<h2>Счет на оплату № '.$tarif_order["user_id"].'/'.$tarif_order["order_number"].' от '.date("d").' '.$month_name[intval(date("m"))].' '.date("Y").' г.</h2>
	<hr style="padding: 0; margin: 0;">
</p>
<p style="padding-top: 0; margin-top: 0;">
	<table style="width:100%;">
		<tr>
			<td style="width: 120px; vertical-align: middle;">
				Поставщик<br>
				(Исполнитель):
			</td>
			<td style="vertical-align: middle;">
				<b>ООО "ФАС-ОНЛАЙН", ИНН 7814659211, КПП 781401001, 197375, Санкт-Петербург г, Вербная ул, дом № 27, офис 716</b>
			</td>
		</tr>
		<tr>
			<td style="width: 120px; vertical-align: middle;">
				<br>
			</td>
			<td style="vertical-align: middle;">
				<br>
			</td>
		</tr>
		<tr>
			<td style="width: 120px; vertical-align: middle;">
				Покупатель<br>
				(Заказчик):
			</td>
			<td style="vertical-align: middle;">
				<b>'.$tarif_order["name_short"].', ИНН '.$tarif_order["inn"].', КПП '.$tarif_order["kpp"].', '.$tarif_order["address"].', тел.: '.$tarif_order["phone"].'</b>
			</td>
		</tr>
		<tr>
			<td style="width: 120px; vertical-align: middle;">
				<br>
			</td>
			<td style="vertical-align: middle;">
				<br>
			</td>
		</tr>
		<tr>
			<td style="width: 120px; vertical-align: middle;">
				Основание:
			</td>
			<td style="vertical-align: middle;">
				<b>Счет '.$tarif_order["user_id"].'/'.$tarif_order["order_number"].' от '.date("d.m.Y").'</b>
			</td>
		</tr>
	</table>
</p>
<p>
	<table style="width:100%;">
		<tr>
			<td>
				<table class="border_table" style="border: 3px solid #000;">
					<tr>
						<td>
							№
						</td>
						<td>
							Товары (работы, услуги)
						</td>
						<td>
							Кол-во
						</td>
						<td>
							Ед.
						</td>
						<td>
							Цена
						</td>
						<td>
							Сумма
						</td>
					</tr>
					<tr>
						<td>
							1
						</td>
						<td>
							Услуги по предоставлению доступа к системе "ФАС-Онлайн".<br>
							Тариф "'.$tarif_order["tarif_name"].'" на '.$tarif_order["tarif_count"].'
						</td>
						<td>
							1
						</td>
						<td>
							шт
						</td>
						<td>
							'.$tarif_order["tarif_price"].',00
						</td>
						<td>
							'.$tarif_order["tarif_price"].',00
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</p>
<p>
	<table style="width:100%;">
		<tr>
			<td style="text-align: right;">
				<b>Итого:</b>
			</td>
			<td style="width: 120px;text-align: right;">
				<b>'.$tarif_order["tarif_price"].',00</b>
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<b>Без налога (НДС)</b>
			</td>
			<td style="width: 120px;text-align: right;">
				<b>-</b>
			</td>
		</tr>
		<tr>
			<td style="text-align: right;">
				<b>Всего к оплате:</b>
			</td>
			<td style="width: 120px;text-align: right;">
				<b>'.$tarif_order["tarif_price"].',00</b>
			</td>
		</tr>
	</table>
</p>
<p>
	Всего наименований 1, на сумму '.$tarif_order["tarif_price"].',00 руб.
</p>
<p>
	<b>'.num2str($tarif_order["tarif_price"]).'</b>
<hr>
</p>
<p>
	<table style="width:100%;">
		<tr>
			<td style="width: 20%; text-align: right;">
				<b>Руководитель</b> 
			</td>
			<td class="underline" style="width: 30%;">
				Козин Р. А.
			</td>
			<td style="width: 20%; text-align: right;">
				<b>Бухгалтер</b> 
			</td>
			<td class="underline" style="width: 30%;">
				
			</td>
		</tr>
	</table>
</p>
</div>
';

$mpdf = new mPDF('utf-8', 'A4', '10', 'Arial', 5, 5, 5, 5, 5, 5);
//$html = "Проверка работы mPDF";
$mpdf->WriteHTML($html2);
$mpdf->Output('', 'I');