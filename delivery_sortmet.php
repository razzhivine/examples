<?
//Кастомная система доставки
CModule::IncludeModule("sale");

AddEventHandler("sale", "onSaleDeliveryHandlersBuildList", array('CFTeaCourier', 'Init'));

class CFTeaCourier
{
	function Init()
	{
		return array(
			
			"SID" => "sortmet",
			"NAME" => "Доставка Sortmet",
			"DESCRIPTION" => "Доставка Sortmet",
			"DESCRIPTION_INNER" => "Доставка Sortmet",
			"BASE_CURRENCY" => COption::GetOptionString("sale", "default_currency", "RUB"),
			"HANDLER" => __FILE__,

			"DBGETSETTINGS" => array("CFTeaCourier", "GetSettings"),
			"DBSETSETTINGS" => array("CFTeaCourier", "SetSettings"),
			"GETCONFIG" => array("CFTeaCourier", "GetConfig"),
			"COMPABILITY" => array("CFTeaCourier", "Compability"),
			"CALCULATOR" => array("CFTeaCourier", "Calculate"),

			"PROFILES" => array(
				"courier" => array(
					"TITLE" => "Доставка Sortmet",
					"DESCRIPTION" => "Доставка Sortmet",
					"RESTRICTIONS_WEIGHT" => array(0),
					"RESTRICTIONS_SUM" => array(0),
				),
			)
		);
	}
	
	function GetConfig()
	{
		$arConfig = array(
			"CONFIG_GROUPS" => array(),
			"CONFIG" => array(),
		);

		return $arConfig;
	}

	
	function SetSettings($arSettings)
	{
		foreach ($arSettings as $key => $value) {
			if (strlen($value) > 0) {
				$arSettings[$key] = doubleval($value);
			} else {
				unset($arSettings[$key]);
			}
		}

		return serialize($arSettings);
	}

	
	function GetSettings($strSettings)
	{
		return unserialize($strSettings);
	}

	
	function Compability($arOrder, $arConfig)
	{
		return array('courier');
	}

	
	function Calculate($profile, $arConfig, $arOrder, $STEP, $TEMP = false)
    {

		$price = 5000;
		if(isset($_REQUEST['ORDER_PROP_22']) && !empty($_REQUEST['ORDER_PROP_22'])){
			$price = $_REQUEST['ORDER_PROP_22'];
		}

        return array(
            "RESULT" => "OK",
            "VALUE" => $price,
        );
    }
}
?>