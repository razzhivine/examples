<?
// Импорт товаров из xml
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

$file = $_GET['c'];
$sectionID = $_GET['id'];
$i = 0;
$products = array();
$xml = simplexml_load_file('subcats/' . $file);

foreach ($xml->category->products->product as $product) {
    $products[$i]['NAME'] = $product->title;
    $prices = array();
    $properties = array();
    foreach ($product->price->measure as $measure) {
        $prices[translit_sef($measure->attributes()->name)] = $measure;
    }
    $products[$i]['PRICES'] = $prices;
    foreach ($product->properties->property as $property) {
        $properties[translit_sef($property->attributes()->name)] = $property;
    }
    $products[$i]['PROPERTIES'] = $properties;
    $i++;
}

$products = json_encode($products); 
$products = json_decode($products, TRUE);

foreach ($products as $product) {
    $PROP = array();
    foreach ($product['PRICES'] as $key => $price) {
        $PROP_PRICE[strtolower($key)]['NAME'] = 'Цена - ' . $price['@attributes']['name'];
        $PROP_PRICE[strtolower($key)]['PRICE'] = $price[0];

        $PROP["PRICE_" . strtolower($key)] = $price[0];
    } 
    foreach ($PROP_PRICE as $key => $prop) {
        $arFilter = array('IBLOCK_ID' => 34, "NAME" => $prop['NAME']);
        $sort = array('SORT' => 'ASC');
        $props = CIBlockProperty::GetList($sort, $arFilter);
        $prop_fields = $props->GetNext();
        $code = str_replace('-', '_', "PRICE_" . strtolower($key));
        if($prop_fields == false){
            $arFields = Array(
                "NAME" => $prop['NAME'],
                "ACTIVE" => "Y",
                "SORT" => "500",
                "CODE" => $code,
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => 34,
                "WITH_DESCRIPTION" => "N",
            );
            $iblockproperty = new CIBlockProperty  ;
            if($PropertyID = $iblockproperty->Add($arFields)){
                echo "New ID: ".$PropertyID . '<br>';
            }else{
                echo "Error: ".$iblockproperty->LAST_ERROR . '<br>';
            }
        }
    }
    foreach ($product['PROPERTIES'] as $prop) {
        $arFilter = array('IBLOCK_ID' => 34, "NAME" => $prop['@attributes']['name']);
        $sort = array('SORT' => 'ASC');
        $props = CIBlockProperty::GetList($sort, $arFilter);
        $prop_fields = $props->GetNext();
        $code = str_replace('-', '_', 'PROP_' . strtoupper(translit_sef($prop['@attributes']['name'])));
        if($prop_fields == false){
            $arFields = Array(
                "NAME" => $prop['@attributes']['name'],
                "ACTIVE" => "Y",
                "SORT" => "500",
                "CODE" => $code,
                "PROPERTY_TYPE" => "S",
                "IBLOCK_ID" => 34,
                "WITH_DESCRIPTION" => "N",
            );
            $iblockproperty = new CIBlockProperty  ;
            if($PropertyID = $iblockproperty->Add($arFields)){
                echo "New ID: ".$PropertyID . '<br>';
            }else{
                echo "Error: ".$iblockproperty->LAST_ERROR . '<br>';
            }
        }
        $PROP[$code] = $prop[0];
    }

    $el = new CIBlockElement;
    $arLoadProductArray = Array(
        "MODIFIED_BY" => 1,
        "IBLOCK_SECTION_ID" => $sectionID,
        "IBLOCK_ID" => 34,
        "PROPERTY_VALUES"=> $PROP,
        "NAME" => $product['NAME'][0],
        "CODE" => translit_sef($product['NAME'][0]),
        "ACTIVE" => "Y",
    );
    if($PRODUCT_ID = $el->Add($arLoadProductArray)){
        echo "New ID: ".$PRODUCT_ID . '<br>';
    }else{
        echo "Error: ".$el->LAST_ERROR . '<br>';
    }
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
