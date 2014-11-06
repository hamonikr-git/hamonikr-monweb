<?php 
$basket = $this->session->get('basket');

echo "<div class=\"ui-widget\">\n";

echo "<div class=\"p2 ui-widget-header ui-corner-top\">\n";
echo Kohana::lang('common.basket-box-header')."</div>\n";

echo "<div class=\"p4 ui-widget-content ui-corner-bottom\">\n";

//echo "<div align=\"center\" class=\"p2\">\n";
//echo "</div>\n";

echo "<div id=\"basket_lists\">\n";

$selbasketname = $this->session->get('basketname');

$basketlist = $this->basket->getBasketList();

//echo "basketname: '".$selbasketname."' [".strlen($selbasketname)."]\n";
echo "<select id=\"basket-list\" class=\"basketnameselectbox\">\n";
echo " <option value=\"\">".Kohana::lang('common.basket-defaultname')."</option>\n";
if(is_array($basketlist) && sizeof($basketlist) > 0 ){

		foreach($basketlist as $basketname=>$items){

				if($basketname != $selbasketname)
						echo " <option value=\"".$basketname."\">".$basketname." (".sizeof($items).")</option>\n";
				else
						echo " <option value=\"".$basketname."\" selected>".$basketname." (".sizeof($items).")</option>\n";

		}
}
echo "</select>\n";
echo "<button id=\"basket-delete\">".Kohana::lang('common.basket-delete')."</button>\n";
echo "</div>\n"; // "<div id=\"basket_lists\">\n";

//$basket = $this->session->get('basket');
$basket = $this->basket->getBasketItems($selbasketname);

echo "<div id=\"basket_items\">\n";
if(is_array($basket) && sizeof($basket) > 0 ){
	foreach($basket as $key=>$item){
		echo "<li class=\"ui-state-default basket_action_remove\" id=\"".
                     $item."\"><a title=\"".Kohana::lang('common.basket-remove', $item)."\" ".
                     "id=\"".$item.
                     "\"><img width=12px height=12px src=\"".url::base().
                     "media/images/remove.png\"></a>".
                     pnp::shorten($item)."</li>\n";
	}
}
if(is_array($basket) && sizeof($basket) > 0 ){
	if(empty($selbasketname)) {
		echo "<input type=\"text\" name=\"basket-name\" id=\"basket-name\" class=\"basketnametextbox\" />\n";
		echo "<button id=\"basket-save\">".Kohana::lang('common.basket-save')."</button>\n";
	}
    echo "<div align=\"center\" class=\"p2\">\n";
    echo "<button id=\"basket-show\">".Kohana::lang('common.basket-show')."</button>\n";
    echo "<button id=\"basket-clear\">".Kohana::lang('common.basket-clear')."</button>\n";
    echo "</div>\n";
    #echo "<div><a class=\"multi0\" href=\"".url::base(TRUE)."page/basket\">".Kohana::lang('common.basket-show')."</a></div>\n";
}else{
	echo "<div>".Kohana::lang('common.basket-empty')."</div>\n";
}

echo "</div>\n";  // "<div id=\"basket_items\">\n";

echo "</div>\n";
echo "</div><br>\n";
?>
<div id="basket_box"></div>

<!-- Basket Box End -->
