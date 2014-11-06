<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Ajax controller.
 *
 * @package    PNPerf 
 * @author     Joerg Linge 
 * @license    GPL
 */
class Ajax_Controller extends System_Controller  {

		public function __construct(){
				parent::__construct();
				// Disable auto-rendering
				$this->auto_render = FALSE;
		}

		public function index(){
				url::redirect("start", 302); 
		}

		public function search() {
				$query     = pnp::clean($this->input->get('term'));
				$result    = array();
				if(strlen($query)>=1) {
						$hosts = $this->data->getHosts();
						foreach($hosts as $host){
								if(preg_match("/$query/i",$host['name'])){
										array_push($result,$host['name']);
								}
						}
						echo json_encode($result);
				}
		}

		public function remove($what){
				if($what == 'timerange'){
						$this->session->delete('start');
						$this->session->delete('end');
						$this->session->set('timerange-reset', 1);
				}
		}

		public function filter($what){
				if($what == 'set-sfilter'){
						$this->session->set('sfilter', $_POST['sfilter']);
				}elseif($what == 'set-spfilter'){
						$this->session->set('spfilter', $_POST['spfilter']);
				}elseif($what == 'set-pfilter'){
						$this->session->set('pfilter', $_POST['pfilter']);
				}
		}

		public function basket($action=FALSE){
				// Disable auto-rendering
				$this->auto_render = FALSE;
				$host     = false;
				$service  = false;
				$basket   = array();

				if($action == "list"){
						$basketname = $_POST['basketname'];
						$this->session->set("basketname", $basketname);
						$basket = $this->basket->getBasketItems($basketname);
						if(is_array($basket) && sizeof($basket) > 0){
								foreach($basket as $item){
										printf("<li class=\"ui-state-default %s\" id=\"%s\"><a title=\"%s\" id=\"%s\"><img width=12px height=12px src=\"%smedia/images/remove.png\"></a>%s</li>\n",
														"basket_action_remove",
														$item,
														$item,
														Kohana::lang('common.basket-remove', $item),
														url::base(),
														pnp::shorten($item)
											  );
								}
						}
				// hihoon : save 기능 추가
				}elseif($action == "save"){

						/* 
						   save는 반드시 newbasketname을 갖는다. 따라서 tempbasket 개체를 그대로 저장한다.
						 */
						$newbasketname = $_POST['basketname'];

						$tempbasket = $this->basket->getBasketItems("");

						if(!empty($newbasketname) && sizeof($tempbasket) > 0) {

								foreach($tempbasket as $key => $item) {
										$this->basket->setBasketItem($item, $newbasketname);
								}

								if(strlen($this->session->get("basketname")) == 0) {
										$this->basket->deleteBasket("");
								}
						}
						else {
								echo "Notice: Basket is empty. You have at least 1 item.";		
						}

						echo "<select id=\"basket-list\" class=\"basketnameselectbox\">\n";
						echo " <option value=\"\">".Kohana::lang('common.basket-defaultname')."</option>\n";

						if(is_array($this->basket->basketlist) && sizeof($this->basket->basketlist) > 0 ){

								foreach($this->basket->basketlist as $basketname => $items){

										if($basketname != $newbasketname)
												echo " <option value=\"".$basketname."\">".$basketname." (".sizeof($items).")</option>\n";
										else
												echo " <option value=\"".$basketname."\" selected>".$basketname." (".sizeof($items).")</option>\n";
								}
						}
						echo "</select>\n";
						echo "<button id=\"basket-delete\">".Kohana::lang('common.basket-delete')."</button>\n";

						$this->session->set("basketname", $basketname);
						$this->basket->saveBasket();

				}elseif($action == "add"){

						/* 신규 item */
						$item = $_POST['item'];

						$basketname = $this->session->get("basketname");

						$this->basket->setBasketItem($item, $basketname);

						$basket = $this->basket->getBasketItems($basketname);

						if(sizeof($basket) == 0)
								$basket = $this->session->get("basket");

						/* 신규 item 추가된 배열 화면(View HTML) 생성 */
						foreach($basket as $item){

								printf("<li class=\"ui-state-default %s\" id=\"%s\"><a title=\"%s\" id=\"%s\"><img width=12px height=12px src=\"%smedia/images/remove.png\"></a>%s</li>\n",
												"basket_action_remove",
												$item,
												$item,
												Kohana::lang('common.basket-remove', $item),
												url::base(),
												pnp::shorten($item)
									  );
						}

				}elseif($action == "sort"){

						$items = $_POST['items'];
						$basket = explode(',', $items);
						array_pop($basket);
						$this->session->set("basket", $basket);

						$basketname = $this->session->get("basketname");
						$this->basket->deleteBasket($basketname);

						foreach($basket as $item){
								$this->basket->setBasketItem($item, $basketname);
								printf("<li class=\"ui-state-default %s\" id=\"%s\"><a title=\"%s\" id=\"%s\"><img width=12px height=12px src=\"%smedia/images/remove.png\"></a>%s</li>\n",
												"basket_action_remove",
												$item,
												$item,
												Kohana::lang('common.basket-remove', $item),
												url::base(),
												pnp::shorten($item)
									  );
						}

				}elseif($action == "remove"){

						$item_to_remove = $_POST['item'];

						$basketname = $this->session->get("basketname");
						$this->basket->deleteBasketItem($item_to_remove, $basketname);

						$basket = $this->basket->getBasketItems($basketname);

						foreach($basket as $item){
								printf("<li class=\"ui-state-default %s\" id=\"%s\"><a title=\"%s\" id=\"%s\"><img width=12px height=12px src=\"%smedia/images/remove.png\"></a>%s</li>\n",
												"basket_action_remove",
												$item,
												$item,
												Kohana::lang('common.basket-remove', $item),
												url::base(),
												pnp::shorten($item)
									  );
						}

				// hihoon : delete 기능 추가
				}elseif($action == "delete"){

						$basketname = $_POST['basketname'];

						$this->basket->deleteBasket($basketname);

						$this->session->delete("basketname");

						$basket = $this->basket->getBasketItems($basketname);


				}elseif($action == "clear"){

						$basketname = $_POST['basketname'];

						$this->basket->deleteBasket($basketname);

				}else{
						echo "Action $action not known";
				}

				if(!($action == "save" || $action == "delete") || $action == "sort") {

						if(is_array($basket) && sizeof($basket) > 0){

								if(empty($basketname)) {
										echo "<input type=\"text\" name=\"basket-name\" id=\"basket-name\" class=\"basketnametextbox\" />\n";
										echo "<button id=\"basket-save\">".Kohana::lang('common.basket-save')."</button>\n";
								}
								echo "<div align=\"center\" class=\"p2\">\n";
								echo "<button id=\"basket-show\">".Kohana::lang('common.basket-show')."</button>\n";
								echo "<button id=\"basket-clear\">".Kohana::lang('common.basket-clear')."</button>\n";
								//echo "<button id=\"basket-save\">".Kohana::lang('common.basket-save')."</button>\n";
								echo "</div>\n";

						}else{
								echo "<div>".Kohana::lang('common.basket-empty')."</div>\n";
						}
				}
		}
}
