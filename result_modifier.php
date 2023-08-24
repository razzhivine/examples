<? if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * @var CBitrixComponentTemplate $this
 * @var CatalogElementComponent $component
 */

$component = $this->getComponent();
$arParams = $component->applyTemplateModifications();


// Похожие товары
if ($arParams['DETAIL_PROPS_ANALOG']) {
  $arSelect = array(
    "ID",
    "IBLOCK_ID",
    "IBLOCK_SECTION_ID",
    "NAME"
  );
  foreach ($arParams['DETAIL_PROPS_ANALOG'] as $det_props) {
    if ($det_props != '') {
     $arSelect[] = 'PROPERTY_'.$det_props;
    }
  }
  $arFilter = Array(
    "IBLOCK_ID" => $arResult["IBLOCK_ID"],
    "SECTION_ID" => $arResult["SECTION"]["ID"], "ACTIVE" => "Y",
    "!ID" => $arResult["ID"],
  );
  $arr_analogs = CIBlockElement::GetList(Array("RAND" => "ASC"), $arFilter, false, Array("nPageSize"=>50), $arSelect);

  $analog_count_id = Array();


  while ($arr_analog = $arr_analogs->GetNextElement()) {
    $element = $arr_analog->GetFields();
    $i = 0; //количество совпадений по свойствам
    foreach ($arParams['DETAIL_PROPS_ANALOG'] as $analog_propers) {
      if ($arResult['PROPERTIES'][$analog_propers]['VALUE'] == $element['PROPERTY_' . $analog_propers . '_VALUE']) {
        $i++;
      }
    }
    
    if($i > 2){
      $arAnalogID[] = $element['ID'];
    }
    
    
  }
  
  $arResult["ANALOG"] = $arAnalogID;
  
}

// установка картинки раздела если у элемента не указана детальная
if(empty($arResult['DETAIL_PICTURE'])){
  $arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], "ID" => $arResult['~IBLOCK_SECTION_ID']); 
  $arSelect = array('PICTURE');
  $rsSect = CIBlockSection::GetList(
    Array("SORT"=>"ASC"),
    $arFilter,
    false,
    $arSelect
  );
  while ($arSect = $rsSect->GetNext()) {
    $sect = $arSect;
  }
  if(!empty($sect['PICTURE'])){
      $picture = CFile::ResizeImageGet($sect['PICTURE'], array('width' => 600, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
      $arResult['DETAIL_PICTURE']['SRC'] = $picture['src'];
      $arResult['DETAIL_PICTURE']['WIDTH'] = $picture['width'];
      $arResult['DETAIL_PICTURE']['HEIGHT'] = $picture['height'];
  }else{
      $arResult['DETAIL_PICTURE']['SRC'] = SITE_TEMPLATE_PATH . '/images/dest/no-photo-square.svg';
      $arResult['DETAIL_PICTURE']['WIDTH'] = 600;
      $arResult['DETAIL_PICTURE']['HEIGHT'] = 600;

  }
}

$res = CIBlockSection::GetList(array("SORT"=>"ASC"), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], "ID" => $arResult['~IBLOCK_SECTION_ID']), false, array("UF_PHOTOS"));

while($ar = $res->GetNext()){
  if($ar['UF_PHOTOS'] != false) {
    $arResult['UF_PHOTOS'] = $ar['UF_PHOTOS'];
  }
}
foreach ($arResult['UF_PHOTOS'] as $key => $photo) {
    $picture = CFile::ResizeImageGet($photo, array('width' => 600, 'height' => 600), BX_RESIZE_IMAGE_PROPORTIONAL, true);
    $arResult['PHOTOS'][$key]['SRC'] = $picture['src'];
    $arResult['PHOTOS'][$key]['WIDTH'] = $picture['width'];
    $arResult['PHOTOS'][$key]['HEIGHT'] = $picture['height'];
}

// Получение связаных категорий для перелинковки
$relatedSections = array();
$dbList = CIBlockSection::GetList(
  false,
  array('IBLOCK_ID' => $arResult['ORIGINAL_PARAMETERS']['IBLOCK_ID'], 'ID' => $arResult['ORIGINAL_PARAMETERS']['IBLOCK_SECTION_ID'], '!UF_RELATED_DETAIL' => false),
  false,
  array('UF_RELATED_DETAIL'),
);
while ($sect = $dbList->GetNext()) {
  $relatedSections = $sect['UF_RELATED_DETAIL'];
}

foreach ($relatedSections as $key => $section) {
  $dbList = CIBlockSection::GetList(
    false,
    array('IBLOCK_ID' => $arResult['ORIGINAL_PARAMETERS']['IBLOCK_ID'], 'ID' => $section),
    false,
    array('NAME', 'SECTION_PAGE_URL'),
  );
  while ($sect = $dbList->GetNext()) {
    $arResult['RELATED_DETAIL'][] = $sect;
  }
}

// получение отзывов из hl-блока 
use Bitrix\Main\Loader; 
Loader::includeModule("highloadblock"); 
use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity;

$hlbl = 7;
$hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 

$entity = HL\HighloadBlockTable::compileEntity($hlblock); 
$entity_data_class = $entity->getDataClass(); 

$rsData = $entity_data_class::getList(array(
   "select" => array("*"),
   "order" => array("ID" => "ASC"),
   "filter" => array("UF_PRODUCT"=> $arResult['ID']),
   'limit' => 3 
));

while($arData = $rsData->Fetch()){
  $arResult['REVIEWS'][] = $arData;
}


// Формирование переменых для шаблонных текстов
$res = CIBlockSection::GetList(array("SORT"=>"ASC"), array('IBLOCK_ID' => $arParams['IBLOCK_ID'], "ID" => $arResult['~IBLOCK_SECTION_ID']), false, array("UF_PRODUCT_DETAIL_TEXT", "UF_PRODUCT_DELIVERY_TEXT", "UF_PRODUCT_PAYMENT_TEXT"));
while($ar = $res->GetNext()){
  if(isset($ar['UF_PRODUCT_DETAIL_TEXT']) && !empty($ar['UF_PRODUCT_DETAIL_TEXT']) && empty($arResult['DETAIL_TEXT'])){
    $arResult['DETAIL_TEXT'] = htmlspecialcharsBack($ar['UF_PRODUCT_DETAIL_TEXT']);
  }
  if(isset($ar['UF_PRODUCT_DELIVERY_TEXT']) && !empty($ar['UF_PRODUCT_DELIVERY_TEXT']) && empty($arResult['PROPERTIES']['DELIVERY_TEXT']['~VALUE']['TEXT'])){
    $arResult['DELIVERY_TEXT'] = htmlspecialcharsBack($ar['UF_PRODUCT_DELIVERY_TEXT']);
  }else{
    $arResult['DELIVERY_TEXT'] = $arResult['PROPERTIES']['DELIVERY_TEXT']['~VALUE']['TEXT'];
  }
  if(isset($ar['UF_PRODUCT_PAYMENT_TEXT']) && !empty($ar['UF_PRODUCT_PAYMENT_TEXT']) && empty($arResult['PROPERTIES']['PAYMENT_TEXT']['~VALUE']['TEXT'])){
    $arResult['PAYMENT_TEXT'] = htmlspecialcharsBack($ar['UF_PRODUCT_PAYMENT_TEXT']);
  }else{
    $arResult['PAYMENT_TEXT'] = $arResult['PROPERTIES']['PAYMENT_TEXT']['~VALUE']['TEXT'];
  }
}

$productsInCat = CIBlockSection::GetSectionElementsCount($arResult['~IBLOCK_SECTION_ID'],  Array("CNT_ACTIVE"=>"Y"));
$dbRes = \Bitrix\Sale\Order::getList();
while ($order = $dbRes->fetch()){
  $arResult['ORDERS'][] = $order; 
}

$resPrice = CIBlockElement::GetList(
  array("CATALOG_PRICE_1" => "ASC"),
  array("SECTION_ID" => $arResult['~IBLOCK_SECTION_ID']),
  false,
  false,
  array("ID", "CATALOG_PRICE_1")
);
while ($getPrices = $resPrice->Fetch()) {
  $arPrices[] = $getPrices['CATALOG_PRICE_1'];
}

$arResult['MIN_CAT_PRICE'] = floor(array_shift($arPrices));
$arResult['MAX_CAT_PRICE'] = floor(array_pop($arPrices));
$arResult['AVG_CAT_PRICE'] = floor(array_sum($arPrices) / count($arPrices));

if(isset($arResult['OFFERS']) && !empty($arResult['OFFERS'])){
  foreach ($arResult['OFFERS'] as $key => $item) {
    $arCurPrices[] = $item['ITEM_PRICES'][0]['PRICE'];
  }
}

asort($arCurPrices);

$arResult['MIN_PRICE'] = array_shift($arCurPrices);
$arResult['MAX_PRICE'] = array_pop($arCurPrices);


$arResult['DETAIL_TEXT'] = str_replace("{PRODUCTS_CAT_COUNT}", $productsInCat, $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{PRODUCT_CAT_NAME}", $arResult['SECTION']['NAME'], $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{PRODUCT_NAME}", $arResult['NAME'], $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{ORDERS_COUNT}", count($arResult['ORDERS']), $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{MIN_CAT_PRICE}", $arResult['MIN_CAT_PRICE'], $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{MAX_CAT_PRICE}", $arResult['MAX_CAT_PRICE'], $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{AVG_CAT_PRICE}", $arResult['AVG_CAT_PRICE'], $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{MIN_PRICE}", $arResult['MIN_PRICE'], $arResult['DETAIL_TEXT']);
$arResult['DETAIL_TEXT'] = str_replace("{MAX_PRICE}", $arResult['MAX_PRICE'], $arResult['DETAIL_TEXT']);
preg_match_all('/[\w]*_NAME/', $arResult['DETAIL_TEXT'], $nameMatches);
foreach ($nameMatches as $key => $arr) {
  foreach ($arr as $key => $name) {
    $str = substr($name, 0, -5);
    $arResult['DETAIL_TEXT'] = str_replace("{" . $name . "}", $arResult['PROPERTIES'][$str]['NAME'], $arResult['DETAIL_TEXT']);
  }
}
preg_match_all('/[\w]*_VALUE/', $arResult['DETAIL_TEXT'], $valueMatches);
foreach ($valueMatches as $key => $arr) {
  foreach ($arr as $key => $value) {
    $str = substr($value, 0, -6);
    $arResult['DETAIL_TEXT'] = str_replace("{" . $value . "}", $arResult['PROPERTIES'][$str]['VALUE'], $arResult['DETAIL_TEXT']);
  }
}
