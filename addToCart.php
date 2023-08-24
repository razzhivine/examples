<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
// добавление товара в корзину
if(CModule::IncludeModule("sale")){
    $elementId = $_POST['PRODUCT_ID'];
    if(isset($_POST['QUANTITY']) && !empty($_POST['QUANTITY'])){
        $quantity = $_POST['QUANTITY'];
    }else{
        $quantity = 1;
    }
    $arProduct = array(
        'PRODUCT_ID' => $elementId,
        'QUANTITY' => $quantity,
    );
    $basketFields = [];
    $options = [
        'USE_MERGE' => 'Y',
    ];
    \Bitrix\Catalog\Product\Basket::addProduct(
        $arProduct, $basketFields, $options
    );
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>
