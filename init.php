<?
use Bitrix\Main\Loader; 
Loader::includeModule("highloadblock"); 
use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity;
$eventManager = \Bitrix\Main\EventManager::getInstance(); 
$eventManager->addEventHandler('form', 'onAfterResultAdd', 'addWebformPage');
 
function my_onBeforeResultAdd($WEB_FORM_ID, &$arFields, &$arrVALUES){
  if ($WEB_FORM_ID == 15){
    $el = new CIBlockElement;
    $PROP = array();
    $PROP['CLIENT_NAME'] = $arrVALUES['form_text_47'];
    $PROP['TEL'] = $arrVALUES['form_text_48'];
    $PROP['EMAIL'] = $arrVALUES['form_email_49'];
    $PROP['CUSTOMER_QUESTION'] = $arrVALUES['form_textarea_50'];

    $arLoadProductArray = Array(
      "MODIFIED_BY"    => '1',
      "IBLOCK_SECTION_ID" => false,
      "IBLOCK_ID"      => 13,
      "PROPERTY_VALUES"=> $PROP,
      "NAME"           => $arrVALUES['form_text_47'] . date("Y-m-d H:i:s"),
      "ACTIVE"         => "N",
    );
    $PRODUCT_ID = $el->Add($arLoadProductArray);
  }
  if ($WEB_FORM_ID == 14){

    $hlbl = 7;
    $hlblock = HL\HighloadBlockTable::getById($hlbl)->fetch(); 

    $entity = HL\HighloadBlockTable::compileEntity($hlblock); 
    $entity_data_class = $entity->getDataClass(); 

      $data = array(
          "UF_DATE" => date("d.m.Y"),
          "UF_RATING" => $arrVALUES['form_text_45'],
          "UF_NAME" => $arrVALUES['form_text_41'],
          "UF_EMAIL" => $arrVALUES['form_email_42'],
          "UF_FEEDBACK" => $arrVALUES['form_textarea_44'],
          "UF_PRODUCT" => $arrVALUES['form_text_46']
      );
      $result = $entity_data_class::add($data);

  }
}
$eventManager->AddEventHandler('form', 'onBeforeResultAdd', 'my_onBeforeResultAdd');

$eventManager->AddEventHandler("sale", "OnOrderNewSendEmail", "bxModifySaleMails");


function bxModifySaleMails($orderID, &$eventName, &$arFields)
{
    $arOrder = CSaleOrder::GetByID($orderID);

    $order_props = CSaleOrderPropsValue::GetOrderProps($orderID);
    $phone = "";
    $index = "";
    $country_name = "";
    $city_name = "";
    $adress = "";
    $comment = "";
    $name_organization = "";
    $name = "";
    $inn = "";
    $kpp = "";
    $ur_adress = "";
    $delivery_price = "";
    while ($arProps = $order_props->Fetch())
    {
        if ($arProps["CODE"] == "PHONE")
        {
            $phone = htmlspecialchars($arProps["VALUE"]);
        }
        if ($arProps["CODE"] == "FIO" || $arProps["CODE"] == "CONTACT_PERSON")
        {
            $name = $arProps["VALUE"];
        }
        if ($arProps["CODE"] == "EMAIL")
        {
            $email = $arProps["VALUE"];
        }
        if ($arProps["CODE"] == "COMPANY")
        {
            $name_organization = $arProps["VALUE"];
        }   
        if ($arProps["CODE"] == "INN")
        {
            $inn = $arProps["VALUE"];
        }   
        if ($arProps["CODE"] == "KPP")
        {
            $kpp = $arProps["VALUE"];
        } 
        if ($arProps["CODE"] == "COMPANY_ADR")
        {
            $ur_adress = $arProps["VALUE"];
        } 
        if ($arProps["CODE"] == "CUSTOM_ADDRESS")
        {
            $adress = $arProps["VALUE"];
        } 
        if ($arProps["CODE"] == "COMMENT")
        {
            $comment = $arProps["VALUE"];
        } 
        if ($arProps["CODE"] == "DELIVERY_DEFAULT_PRICE")
        {
            $delivery_price = $arProps["VALUE"];
        } 
    }

    $arDeliv = CSaleDelivery::GetByID($arOrder["DELIVERY_ID"]);
    $delivery_name = "";
    if ($arDeliv)
    {
        $delivery_name = $arDeliv["NAME"];
    }

   
    $arPaySystem = CSalePaySystem::GetByID($arOrder["PAY_SYSTEM_ID"]);
    $pay_system_name = "";
    if ($arPaySystem)
    {
        $pay_system_name = $arPaySystem["NAME"];
    }

    $arFields["ORDER_DESCRIPTION"] = $comment;
    $arFields["PHONE"] =  $phone;
    $arFields["MY_NAME"] =  $name;
    $arFields["MY_EMAIL"] =  $email;
    $arFields["DELIVERY_NAME"] =  $delivery_name;
    $arFields["DELIVERY_PRICE"] =  $delivery_price;
    $arFields["PAY_SYSTEM_NAME"] =  $pay_system_name;
    $arFields["P1"] =$name_organization; 
    $arFields["P2"] =$inn; 
    $arFields["P3"] =$kpp; 
    $arFields["P4"] =$ur_adress; 
    $arFields["adr"] =$adress; 
    
}   

function debug($value, $mode = 0)
{
    echo '<pre>';
    if($mode == 1){
        var_dump($value);
    }else{
        print_r($value);
    }
    echo '</pre>';
}

function addWebformPage($WEB_FORM_ID, $RESULT_ID)
{
    CFormResult::SetField($RESULT_ID, 'page', $_SERVER['HTTP_REFERER']);
}

AddEventHandler('main', 'OnEpilog', 'OnEpilogHandler');
 
function OnEpilogHandler() {

    global $APPLICATION;
    if (!empty($_GET['PAGEN_2']) && intval($_GET['PAGEN_2'])>1)
    {                 
      $h1 = $APPLICATION->GetTitle();
      $title = $APPLICATION->GetPageProperty("title");
      $description = $APPLICATION->GetPageProperty("description");
         
      if(!empty($title))
          $APPLICATION->SetPageProperty('title', $title . ' - страница ' . $_GET['PAGEN_2']);
         
      if(!empty($description))
          $APPLICATION->SetPageProperty('description', $description . ' - страница ' . $_GET['PAGEN_2']);

      if(!empty($h1))
          $APPLICATION->SetTitle($h1 . ' - страница ' . $_GET['PAGEN_2']);

    }
}
