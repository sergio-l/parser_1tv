<?php
ini_set('max_execution_time', 100);
include "simple_html_dom.php";

$url = 'http://www.1tv.ru/schedule/';
//дата текущего понедельника (xx.xx.2018)
$last_monday = date('Y-m-d', strtotime("this monday"));

//массив где будут хранится все ссылки вида (http://www.1tv.ru/schedule/2016-xx-xx)
$urls = [$url.$last_monday]; //текущий понедельник

//заполняем массив ссылками на 14 дней
for ($i = 1; $i < 14; $i++){
	$urls[] = $url.date('Y-m-d', strtotime($last_monday) + $i * 86400);
}

//START.Выполняем весь процес парсинга страниц
$html = multiple_threads($urls, $_POST['region_id']);
//Выводим всю программу
echo "<strong>Регион: </strong>".regionName($_POST['region_id'])."<br><br>";
foreach($html as $key => $value)
{
	$dt = explode('/', $key); //розбиваем URL
	$date = date_create_from_format('Y-m-d', $dt[4]); //получаем дату 2016-xx-xx
	echo '<div><strong>'.  getNameDayRu(date_format($date, 'w')) .'<br>'. date_format($date, 'Y-m-d')  .'</strong>'.parse($value).'</div><br>';
}

//END

/*Мульти запрос на парсинг всех страниц. Парсим страницу целиком.
	$nodes - массив всех ссылок которые будут парсится
	$region - нужный регион, по умолчанию москва
*/
function multiple_threads($nodes, $region = 30){
	$mh = curl_multi_init();
	$curl_array = array();
	foreach($nodes as $i => $url)
	{
		$curl_array[$i] = curl_init($url);
		curl_setopt($curl_array[$i], CURLOPT_COOKIE, "region_id=".$region);
		curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
		curl_multi_add_handle($mh, $curl_array[$i]);
	}
	$running = NULL;
	do {
		usleep(10000);
		curl_multi_exec($mh, $running);
	} while($running > 0);

	$res = array();
	foreach($nodes as $i => $url)
	{
		//записываем все данные страниц в массив
		$res[$url] = curl_multi_getcontent($curl_array[$i]);
	}

	foreach($nodes as $i => $url){
		curl_multi_remove_handle($mh, $curl_array[$i]);
	}
	curl_multi_close($mh);
	return $res;
}


//Получаем нужные данные из полученых страниц
function parse($content)
{
	$html = str_get_html($content);
	//Ищем блок где хранится таблица расписания
	$html = $html->find('section[class=schedule-cards]', 0);
	$content = '';
	foreach ($html->find('div[class=card]') as $element) {
		//получаем время
		$content .= '<div>'.substr($element->find('section.time', 0)->plaintext, 0, 5);
		//ищем названия передачи
		$title = $element->find('section.body', 0);
		$content .= '<span>&nbsp;'.strip_tags($title->firstChild()).'</span>';
		//ищем возраст
		$age = $element->find('section.age', 0);
		//если он есть, то получаем
		if (isset($age)) {
			$content .= '<span>&nbsp;'.$age->plaintext.'</span>';
		}

		$content .= '</div>';
	}

	return $content;
}

//Возвращаем названия региона зная его id
function regionName($region_id){
	switch($region_id){
		case 30: return "Москва";
		case 67: return "Свердловская область";
		case 24: return "Красноярский край";
		case 56: return "Республика Саха (Якутия)";
		case 66: return "Сахалинская область";
		default: return "Не определено";
	}
}

//дни на русском
function getNameDayRu($day){
	$ruDays = [ 0 => 'Воскресенье', 
				1 => 'Понедельник',
				2 => 'Вторник', 
				3 => 'Среда', 
				4 => 'Четверг',
				5 => 'Пятница',
				6 => 'Суббота'];
	return $ruDays[$day];
}

