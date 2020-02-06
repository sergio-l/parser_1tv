<form method="post" action="index.php">
    <button type="submit" name="region_id" value="30">Москва</button>
    <button type="submit" name="region_id" value="67">Свердловская область</button>
    <button type="submit" name="region_id" value="24">Красноярский край</button>
    <button type="submit" name="region_id" value="56">Республика Саха (Якутия)</button>
    <button type="submit" name="region_id" value="66">Сахалинская область</button>
</form>

<?php
//Если нажата одна из 5 кнопок
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	include('result.php');
}

?>
 
 
