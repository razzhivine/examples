
<?
// Генерирование торговых предложений 
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

function translit_sef($value)
{
	$converter = array(
		'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
		'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
		'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
		'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
		'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
		'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
		'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
	);
 
	$value = mb_strtolower($value);
	$value = strtr($value, $converter);
	$value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
	$value = mb_ereg_replace('[-]+', '-', $value);
	$value = trim($value, '-');	
 
	return $value;
}

function create_offer($id, $name, $price, $measure, $ratio){
    $ciBlockElement = new CIBlockElement;
    $arLoadProductArray = array(
        "IBLOCK_ID"      => 36,
        "NAME"           => $name,
        "ACTIVE"         => "Y",
        'PROPERTY_VALUES' => array(
            'CML2_LINK' => $id, 
        )
    );
    $product_offer_id = $ciBlockElement->Add($arLoadProductArray);
    if (!empty($ciBlockElement->LAST_ERROR)) {
        echo "Ошибка добавления торгового предложения: ". $ciBlockElement->LAST_ERROR;
        // die();
    }
    CCatalogProduct::Add(
        array(
            "ID" => $product_offer_id,
            "MEASURE" => $measure,
        )
    );
    CPrice::Add(
        array(
            "CURRENCY" => "RUB",
            "PRICE" => $price,
            "CATALOG_GROUP_ID" => 1,
            "PRODUCT_ID" => $product_offer_id,
        )
    );
    $ID = CCatalogMeasureRatio::add(Array('PRODUCT_ID' => $product_offer_id, 'RATIO' => $ratio));
    CCatalogMeasureRatio::update($ID, Array('PRODUCT_ID' => $product_offer_id, 'RATIO' => $ratio));
}

$arSelect = Array("ID", "NAME", "PROPERTY_PRICE_tonna", "PROPERTY_PRICE_metr", "PROPERTY_PRICE_shtuka", "PROPERTY_PRICE_pogonnyy_metr", "PROPERTY_PRICE_kg", "PROPERTY_PRICE_m2", "PROPERTY_PRICE_list", "PROPERTY_PRICE_kilogramm", "PROPERTY_PRICE_kg", "PROPERTY_PRICE_sht", "PROPERTY_PRICE_pog_m", "PROPERTY_PRICE_kvadratnyy_metr", "PROPERTY_PRICE_tn", "PROPERTY_PRICE_m", "PROPERTY_PRICE_tn1");
$arFilter = Array("IBLOCK_ID"=>34, "!CATALOG_TYPE" => 3, 'CREATED_DATE' => date('07.06.2023'));
$res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
$arItems = array();
$i = 0;
while($arFields = $res->Fetch())
{
    $arItems[$i]['ID'] = $arFields['ID'];
    $arItems[$i]['NAME'] = $arFields['NAME'];
    if($arFields['PROPERTY_PRICE_TONNA_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_TONNA_VALUE'] = $arFields['PROPERTY_PRICE_TONNA_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_METR_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_METR_VALUE'] = $arFields['PROPERTY_PRICE_METR_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_SHTUKA_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_SHTUKA_VALUE'] = $arFields['PROPERTY_PRICE_SHTUKA_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_POGONNYY_METR_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_POGONNYY_METR_VALUE'] = $arFields['PROPERTY_PRICE_POGONNYY_METR_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_KG_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_KG_VALUE'] = $arFields['PROPERTY_PRICE_KG_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_M2_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_M2_VALUE'] = $arFields['PROPERTY_PRICE_M2_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_LIST_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_LIST_VALUE'] = $arFields['PROPERTY_PRICE_LIST_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_KILOGRAMM_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_KILOGRAMM_VALUE'] = $arFields['PROPERTY_PRICE_KILOGRAMM_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_SHT_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_SHT_VALUE'] = $arFields['PROPERTY_PRICE_SHT_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_POG_M_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_POG_M_VALUE'] = $arFields['PROPERTY_PRICE_POG_M_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_KVADRATNYY_METR_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_KVADRATNYY_METR_VALUE'] = $arFields['PROPERTY_PRICE_KVADRATNYY_METR_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_TN_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_TN_VALUE'] = $arFields['PROPERTY_PRICE_TN_VALUE'];
    }
    if($arFields['PROPERTY_PRICE_M_VALUE']){
        $arItems[$i]["PRICES"]['PROPERTY_PRICE_M_VALUE'] = $arFields['PROPERTY_PRICE_M_VALUE'];
    }
    $i++;
}

foreach ($arItems as $item) {
    foreach ($item["PRICES"] as $key => $price) {
        $price_1 = str_replace(' ', '', $price);
        $price_1 = str_replace(',', '.', $price_1);
        $price_1 = str_replace('₽', '.', $price_1);
        if(!empty($price) && $price != 'По запросу'){
            if($key == 'PROPERTY_PRICE_TONNA_VALUE' && $key == 'PROPERTY_PRICE_TN_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 6, 0.1);
            }elseif($key == 'PROPERTY_PRICE_METR_VALUE' && $key == 'PROPERTY_PRICE_M_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 1, 1);
            }elseif($key == 'PROPERTY_PRICE_SHTUKA_VALUE' && $key == 'PROPERTY_PRICE_SHT_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 5, 1);
            }elseif($key == 'PROPERTY_PRICE_POGONNYY_METR_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 7, 1);
            }elseif($key == 'PROPERTY_PRICE_KG_VALUE' || $key == 'PROPERTY_PRICE_KILOGRAMM_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 8, 1);
            }elseif($key == 'PROPERTY_PRICE_M2_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 9, 1);
            }elseif($key == 'PROPERTY_PRICE_LIST_VALUE'){
                create_offer($item['ID'], $item['NAME'], $price_1, 10, 1);
            }
        }
    }
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");