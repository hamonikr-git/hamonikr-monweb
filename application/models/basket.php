<?php defined('SYSPATH') OR die('No direct access allowed.');

/**
 * Retrieves and manipulates baskets (and basket items?)
 */
class Basket_Model extends System_Model  {

		public $basketlist = array();
		public $tempbasket = array();
		public $xmlbasket = NULL;

		private $_basketfile = "";
		private $_nobasketname = "__session";

		/*
		 *
		 */
		public function __construct(){

				$this->config = new Config_Model();    
				$this->config->read_config();
				$this->auth = new Auth_Model();    

				$this->_basketfile = $this->config->conf['basket']."/".$_SERVER['REMOTE_USER'].".xml";

								$this->xmlbasket = new DomDocument('1.0', 'UTF-8');
								$this->xmlbasket->formatOutput = true;
								$this->xmlbasket->preserveWhiteSpace = false;

				if(is_readable($this->_basketfile)){
						$fh = fopen($this->_basketfile, 'r');
						fclose($fh);
				}
				else {

						$elementBasket = $this->xmlbasket->createElement('baskets');
						$nodeName = $this->xmlbasket->appendChild($elementBasket);
						$this->saveBasket();

				}

				$this->makeBasketList();
		}

		public function getTest(){

				return $this->config->conf['basket'];
		}

		public function makeBasketList(){

				if(is_readable($this->_basketfile)){

						if (($fsize = filesize($this->_basketfile)) > 0) {


								$this->xmlbasket->Load($this->_basketfile);

								$xpath = new DomXPath($this->xmlbasket);	

								$elmts = $xpath->query('/baskets/basket');

								if($elmts->length > 0) {

										foreach ($elmts as $elmt) {
												$name = $elmt->getAttribute('name');
												$nodes = $elmt->childNodes;

												if($nodes->length > 0) {
														foreach ($nodes as $node) {
																$this->basketlist[$name][] = $node->nodeValue;
														}
												}
												else {
														$this->basketlist[$name] = array();
												}
										}
								}

								$elmts = $xpath->query('/baskets/tempbasket');
								if(!is_null($elmts)) {

										foreach ($elmts as $elmt) {
												$name = $elmt->getAttribute('name');
												$nodes = $elmt->childNodes;

												foreach ($nodes as $node) {
														$this->tempbasket[$name][] = $node->nodeValue;
												}
										}
								}
						}
				}
		}

		public function makeBasketList_simplexml(){

				if(is_readable($this->_basketfile)){

						if (($fsize = filesize($this->_basketfile)) > 0) {

								$this->xmlbasket = simplexml_load_file($this->_basketfile);
								$sxe = $this->xmlbasket->xpath('//basket');

								foreach ($sxe as $basketname => $items) {

										$this->xml2array($items);
								}
						}
				}
		}

		public function xml2array($xmlObject) {

				/*
				   SimpleXMLElement Object ( 
				   [@attributes] => Array ( [name] => http ) 
				   [item] => Array ( [0] => http1 [1] => http2 ) 
				   )
				 */

				foreach((array) $xmlObject as $index => $node) {

						if($index == "@attributes")  {
								$name = $node['name'];
						}
						elseif($index == "item")
								$this->basketlist[$name] = (is_object($node)) ? $this->xmlbasket2array( $node) : $node;
						else {
								echo "Error!!!";
						}
				}
		}

		public function getBasketList(){

				return $this->basketlist;
		}

		public function getBasketNames(){

				$xpath = new DomXPath($this->xmlbasket);	

				$sxe = $xpath->query('/baskets');

				print_r((array)$sxe);

				foreach ($sxe as $basketname => $items) {

						$this->xml2array($items);
				}

		}

		public function setBasketList($arrBasketList){

				$this->basketlist = $arrBasketList;
		}


		public function getItem($basketname = ""){

				if(is_readable($this->_basketfile)){

						if (($fsize = filesize($this->_basketfile)) > 0) {

								$this->xmlbasket = simplexml_load_file($this->_basketfile);

								$sxe = $this->xmlbasket->xpath('/baskets/basket[@name ="'.$basketname.'"]/item');
								foreach ($sxe as $key => $item) {

										$this->basketlist[$basketname] = array($item);
								}
						}
				}
				return $this->basketlist;
		}

		public function getBasket(){
				return "btest";
		}

		public function getBasketItems($basketname = ""){

				//echo "getBasketItems Start with $basketname\n"; exit;

				if(empty($basketname)) {
						if(array_key_exists($this->_nobasketname, $this->tempbasket)) 
								return $this->tempbasket[$this->_nobasketname];
						else
								return array();
				}
				else {
						if(array_key_exists($basketname, $this->basketlist)) 
								return $this->basketlist[$basketname];
						else
								return array();
				}
		}

		public function setBasketItem($item, $basketname) {

				//echo "setBasketItem Start: ".$item.",".$basketname."\n"; 

				$xpath = new DomXPath($this->xmlbasket);
				$basketroot = $xpath->query("/baskets");

				if(empty($basketname)) {

						if(array_key_exists($this->_nobasketname, $this->tempbasket)) {

								$basket = $this->tempbasket[$this->_nobasketname];

								if(!is_array($this->tempbasket[$this->_nobasketname])){
										$this->tempbasket[$this->_nobasketname][] = $item;
								}else{
										if(!in_array($item,$this->tempbasket[$this->_nobasketname])){
												$this->tempbasket[$this->_nobasketname][] = $item;
										}
								}
						}
						else {
								$this->tempbasket[$this->_nobasketname][] = $item;
						}

						$basketname = $this->_nobasketname;
						$baskettag = "tempbasket";

						$query = "/baskets/$baskettag";

				}
				else {
						/* basketlist 객체 처리 */

						if(array_key_exists($basketname, $this->basketlist)) {

								if(!is_array($this->basketlist[$basketname])){

										$this->basketlist[$basketname][] = $item;

								}else{

										if(!in_array($item,$this->basketlist[$basketname])){
												$this->basketlist[$basketname][] = $item;
										}
								}
						}
						else {
								$this->basketlist[$basketname][] = $item;
						}

						$baskettag = "basket";
						$query = "/baskets/".$baskettag."[@name='".$basketname."']";
				}

				$query_dup_check = "/baskets/".$baskettag."[@name='".$basketname."']/item[. = '".$item."']";

				/* $this->xmlbasket 객체 처리 */

				/*
				   $query = "/baskets/tempbasket";
				   $query = "/baskets/basket[@name='".$basketname."']";
				 */
				$elmts = $xpath->query($query);


				/* 기존에 존재하는 Basketname을 등록하게 되는 경우, child가 중복 고려 없이 모두 등록하면 됨. */
				if($elmts->length > 0) {
						$elementBasket = $elmts->item(0);
				}
				else {
						$elementBasket = $this->xmlbasket->createElement($baskettag);
						$nodeName = $this->xmlbasket->appendChild($elementBasket);
						$nodeName->setAttribute("name", $basketname);
				}

				/* item 이 이미 존재하는 경우, 중복 방지 루틴 */
				$tmpxml = $xpath->query($query_dup_check);

				if($tmpxml->length == 0) {

						$elementItem = $this->xmlbasket->createElement("item");
						$nodeItem = $this->xmlbasket->createTextNode($item);

						$elementItem->appendChild($nodeItem);
						$elementBasket->appendChild($elementItem);

						$basketroot->item(0)->appendChild($elementBasket);

						$this->saveBasket();
				}


				//echo "setBasketItem End\n";

		}

		public function deleteBasket($basketname = "") {

				if(empty($basketname)) {

						if(array_key_exists($this->_nobasketname, $this->tempbasket)) {

								unset($this->tempbasket[$this->_nobasketname]);
						}

						$query_delete_check = "/baskets/tempbasket[@name='".$this->_nobasketname."']";
				}
				else {

						if(array_key_exists($basketname, $this->basketlist)) {

								unset($this->basketlist[$basketname]);
						}

						$query_delete_check = "/baskets/basket[@name='".$basketname."']";

				}

				$xpath = new DomXPath($this->xmlbasket);
				$basketroot = $xpath->query("/baskets");
				$elmts = $xpath->query($query_delete_check);

				if($elmts->length > 0) {

						$elementBasket = $elmts->item(0);

						$elementBasket->parentNode->removeChild($elementBasket);

						$this->saveBasket();
				}
		}

		public function deleteBasketItem($item = "", $basketname = "") {

				if(empty($basketname)) {

						if(array_key_exists($this->_nobasketname, $this->tempbasket)) {

								$basket = $this->tempbasket[$this->_nobasketname];

								if(($key = array_search($item, $basket)) != false) {

										unset($basket[$key]);
								}
								$this->tempbasket[$this->_nobasketname] = $basket;
						}

						$query_delete_check = "/baskets/tempbasket[@name='".$this->_nobasketname."']/item[. = '".$item."']";
				}
				else {

						if(array_key_exists($basketname, $this->basketlist)) {
								$basket = $this->basketlist[$basketname];

								if(($key = array_search($item, $basket)) !== false) {
										unset($basket[$key]);
								}
								$this->basketlist[$basketname] = $basket;
						}

						$query_delete_check = "/baskets/basket[@name='".$basketname."']/item[. = '".$item."']";

				}

				$xpath = new DomXPath($this->xmlbasket);
				$basketroot = $xpath->query("/baskets");
				$elmts = $xpath->query($query_delete_check);


				/* 기존에 존재하는 Basketname을 등록하게 되는 경우, child가 중복 고려 없이 모두 등록하면 됨. */
				if($elmts->length > 0) {
						$elementBasket = $elmts->item(0);

						$elementBasket->parentNode->removeChild($elementBasket);

						$this->saveBasket();
				}
		}

		public function saveBasket(){

				//echo "saveBasket Start\n";

				$this->xmlbasket->formatOutput = true;
				$this->xmlbasket->preserveWhiteSpace = false;

				$fp = fopen($this->_basketfile,'w+');
				fwrite($fp, $this->xmlbasket->saveXML(), strlen($this->xmlbasket->saveXML()));
				fclose($fp);

				//echo "saveBasket End\n";
		}
}

class XmlConstruct extends XMLWriter { 
		public function __construct($prm_rootElementName, $prm_xsltFilePath='') {
				$this->openMemory(); 
				$this->setIndent(true); 
				$this->setIndentString(' '); 
				$this->startDocument('1.0', 'UTF-8'); 
				if($prm_xsltFilePath) $this->writePi('xml-stylesheet', 'type="text/xsl" href="'.$prm_xsltFilePath.'"'); 
				$this->startElement($prm_rootElementName); 
		} 
		public function setElement($prm_elementName, $prm_ElementText) {
				$this->startElement($prm_elementName); 
				$this->text($prm_ElementText); 
				$this->endElement(); 
		} 
		public function fromArray($prm_array) {
				if(!is_array($prm_array)) return;
				foreach ($prm_array as $index => $element) {
						if(is_array($element)) {
								$this->startElement($index); 
								$this->fromArray($element); 
								$this->endElement(); 
						} 
						else $this->setElement($index, $element); 
				} 
		} 
		public function getDocument() {
				$this->endElement(); 
				$this->endDocument(); 
				return $this->outputMemory(); 
		} 
		public function output() {
				header('Content-type: text/xml'); 
				echo $this->getDocument(); 
		} 
}

?>
