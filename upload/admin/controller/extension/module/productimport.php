<?php
class ControllerModuleProductImport extends Controller {


protected function scrap_url($url,$headers=0,$mobsite = 0)
{  //get html from url

//$url.= "?siteplatform=www";

    //$url="http://localhost/dump.php";

$ua = $_SERVER['HTTP_USER_AGENT'];
  //echo $ua;  
/*if ($mobsite == 1)
{
$ua = "Mozilla/5.0 (Linux; Android 4.1.2; Spice Mi-496 Build/JZO54K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.135 Mobile Safari/537.36";}
else {
$ua ="Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0";}*/
  $curl=curl_init();
curl_setopt($curl, CURLOPT_URL,$url);
//settings
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
 
curl_setopt($ch, CURLOPT_HEADER, $headers);

curl_setopt($curl, CURLOPT_USERAGENT, $ua);

$dir                   = dirname(__FILE__);
$config['cookie_file']= $dir . md5($_SERVER['REMOTE_ADDR']).'.txt';
curl_setopt($curl, CURLOPT_COOKIEFILE, $config['cookie_file']);
curl_setopt($curl, CURLOPT_COOKIEJAR, $config['cookie_file']);
    $exec=curl_exec($curl); if ($headers == 0)
{
return $exec;}
else { return curl_getinfo($curl,CURLINFO_REDIRECT_URL);}
curl_close($curl); 

/*
$url = 'http://www.smartprix.com/l.php?k=RRRVUVph6856R_9.oR_9nB:D:r-ri4R_9fG7syhRKiG2MUffgg3gwvVRRybfffffffUS-3v8H5L.1hP4J3qv54FOF&click_src=shop-now';
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
$c = curl_exec($ch);
echo curl_getinfo($ch,CURLINFO_REDIRECT_URL);
curl_close($ch);*/

//print_r(get_headers($url,1));


}


protected function scrap_spec($data)
{
 

/*scrap smartprix*/

include_once('/home/mobilesb/public_html/naaptolke.com/admin/controller/catalog/dom/simple_html_dom.php');

$html = str_get_html($data);
//get full specifications
$tables =$html->find('table[class="specs-table"]');
$c = 0;
foreach ($tables as $table){

echo $table->innertext;
$groups = $table->find('tr');
$i = 1; 
foreach ($groups as $group)
{



if ($group->plaintext !== "" && $i== 1)
{
$grup = $group->plaintext;

$ret[$c][0] = $grup;

//echo "grup added &nbsp ".$grup;
$i= 2 ;
}
else if (!empty($group->plaintext) && $i == 2 )
{//echo "attr added &nbsp ".$group->plaintext;

$ret[$c][1][]= $group->plaintext;
}
else 
{ echo "empty"; $i = 1; $c = $c + 1;

}

 }
   }

/*scrap ebd*/



  $html->clear();
    unset($html);

    return $ret;
}

protected function
scrap_urlprice($data)
{include_once('/home/mobilesb/public_html/naaptolke.com/admin/controller/catalog/dom/simple_html_dom.php');
$html = str_get_html($data);



$prod= $html->find('h1[itemprop="name"]',0);
$ret[0] = $prod->plaintext;


$tr = $html->find('div[id="compare-prices"]',0);
//print_r($tr->innertext);

 
$li = $tr->find('li[class="price-row"]');
$c = 0;
foreach ($li as $link)
{
$img = $link->find('img',0);
echo $img->alt;
$baba[$c]['store_name'] = $img->alt;

$price = $link->find('div[class="price"]',0);
$price = str_replace('Rs.','',$price->plaintext);
$price = str_replace(',','',$price);
$baba[$c]['store_price'] = $price;

$ship = $link->find('div[class="sub-text _fll"]',0);
$ship = explode(".",$ship->plaintext);

$baba[$c]['shipping'] = $ship[0];
$baba[$c]['delivery'] = $ship[1];

$button = $link->find('a[class=button]',0);
$href =  "http://www.smartprix.com".$button->href;
$baba[$c]['product_url'] = $this->scrap_url($href,1,1);

$c = $c + 1 ;
}

$html->clear();
    unset($html);
$ret[1] = $baba;
    return $ret;
}

protected function saveImage($urlImage, $fullpath){
    

$userAgent = 'Mozilla/5.0 (Windows NT 5.1; rv:31.0) Gecko/20100101 Firefox/31.0';


    $ch = curl_init ($urlImage);
    curl_setopt($ch, CURLOPT_HEADER,0);curl_setopt( $ch, CURLOPT_USERAGENT, $userAgent );

    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $rawdata=curl_exec($ch);
    curl_close ($ch);if(file_exists($fullpath)){
        unlink($fullpath);}
    $fp = @fopen($fullpath,'x');
    $r = fwrite($fp, $rawdata);
    
    fclose($fp);return $r;}

protected function setMemoryLimit($filename){
   
   $maxMemoryUsage =258;
   $width  =0;
   $height =0;
   $size   = ini_get('memory_limit');
   list($width, $height)= getimagesize($filename);
   $size = $size + floor(($width * $height *4*1.5+1048576)/1048576);if($size > $maxMemoryUsage) $size = $maxMemoryUsage;
   }
/* all custom functiins ends */

	private $error = array();

	public function index() {
		$this->load->language('module/productimport');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

$this->load->model('catalog/product');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') ) {
			

if (isset($this->request->post['link']))
{




/*get links and prices*/
$html_data = $this->scrap_url($this->request->post['link']);
$baba = $this->scrap_urlprice($html_data);
echo "<pre>";
print_r($baba);
echo "</pre>";
$product_name = $baba[0];

$product_name_new = str_replace("(","",$product_name);
$product_name_new = str_replace(")","",$product_name_new);
$ret = $baba[1];



/*create data to insert */
$data['product_description'][1]['name'] = $product_name;


$data['product_description'][1]['meta_title'] = $product_name;
$data['tax_class_id'] = 0;

    $data['quantity'] = 1;
    $data['minimum'] = 1;
    $data['subtract'] = 1;
    $data[stock_status_id] = 6;
    $data['shipping'] = 1;
  $data['date_available'] = "2016-07-11";
$data['length_class_id'] = 1;
$data['weight_class_id'] = 1;
$data['status'] = 1;
    $data['sort_order'] = 1;
    
    $data['manufacturer_id'] =0;

  $data['product_category'][0] =
33;
 $data['product_store'][0] = 0 ;


/* end of data */
  /* Example usage of the Amazon Product Advertising API */
    require'/home/mobilesb/public_html/naaptolke.com/admin/controller/catalog/amazon_api_class.php';

    $obj = new AmazonProductAPI();
    
    try
    { 

        $result = $obj->searchProducts(trim($product_name_new),
                                       AmazonProductAPI::ELECTRONICS,
                                       "TITLE");
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }

    $json = json_encode($result);
$result= json_decode($json, true);    
echo "<pre>";
//print_r($result);
   echo "</pre>";
if ($result['Items']['TotalResults'] == 1) { $item = $result['Items']['Item'];} 
else
{

 $item = $result['Items']['Item'][0];}

   echo "<pre>";
 //print_r($item);echo "</pre>";
$main_image= $item['LargeImage']['URL'];
$extra_images = $item['ImageSets']
['ImageSet'];


foreach ($extra_images as $extra_image)
{ $extra_image = $extra_image['LargeImage']['URL'];
//echo $extra_image;


$i = $i + 1;


//this is to get imagename
$image_name = strtolower(
str_replace(" ","_",trim($product_name)));



$image_name= "catalog/".$image_name."_".$i.".jpg";


if (
$this->saveImage($extra_image,DIR_IMAGE.$image_name)

)
{
/*$this->request->post*/
$data['product_image'][$i]['image']
= $image_name;

/*$this->request->post*/

$data['product_image'][$i]['sort_order']
= $i;}

}


/*amazon end*/


/*for getting model name from link*/


$expld= explode('-',$this->request->post[link]);

$model=end($expld);
$model= explode('.',$model);
$model=$model[0];

/*$this->request->post[model]=$model;*/

/* setting model*/
$data['model'] = $model;


//this is to get imagename
$image_name =strtolower(
str_replace(" ","_",trim($product_name)));
echo $image_name;
$image_name= "catalog/".$image_name."_main.jpg";





   
if (
$this->saveImage($main_image, DIR_IMAGE.$image_name))

{
/*$this->request->post[image]*/
$data['image']= $image_name;}


/* end of image sabing*/



$specs = $this->scrap_spec($html_data);



echo "<pre>";
print_r($specs);
echo "</pre>";
foreach ($specs as $group)
{//echo $group[0]."its group";
foreach ($group[1] as $attribute)
{ // echo $attribute;
$attribute = explode(":",$attribute);


//check if attribute exits
$this->load->model('catalog/attribute');
$results = $this->model_catalog_attribute->checkAttribute(trim($attribute[0]),trim($group[0]));
//print_r($results);
if (!empty($results['attribute_id']))
{ 

/*$this->request->post*/
$data['product_attribute'][$atr_count]['name'] = $attribute[0] ;


/*$this->request->post*/
$data['product_attribute'][$atr_count]['attribute_id'] =  $results['attribute_id'] ;


$text_no = $new_count;
/*$this->request->post*/
$data['product_attribute'][$atr_count]['product_attribute_description'][1] ['text']= $attribute[1] ;


$atr_count = $atr_count + 1 ;
}
else { echo "attrihutr do not exists " .$attribute[0];}
}

}}

/*$this->request->post*/
$data['product_description'][1]['meta_title'] = $product_name;

/*$this->request->post*/
$data['product_description'][1]['name']=$product_name
;
} /*if post[link] ends*/


echo "<pre>";
print_r($data);
echo "</pre>";
$product_id = $this->model_catalog_product->addProduct($data);
echo "<pre>";print_r($product_id);
echo "</pre>";
/*
$link_add = $this->model_catalog_product->addLink($product_id,$this->request->post['link']);
if ($link_add)
{echo "link added";}*/

if ($product_id)
{
foreach ($ret as $store)
{
//print_r($store);
 $result_id=$this->model_catalog_product->checkAffstore($store['store_name']);
echo "result of storename";print_r($result_id);
if (!empty($result_id['store_id']))
{ 
$store['store_id']= $result_id['store_id'];
$store['name'] = $baba[0];

echo $store['store_id'];
$result_add=$this->model_catalog_product->addPrice($product_id,$store);
if ($result_add==true)
{
echo "store price added";}


}
}
}/*if $product_id */





/*$this->model_setting_setting->editSetting('productimport', $this->request->post);*/

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');

		$data['entry_code'] = $this->language->get('entry_code');
		$data['entry_status'] = $this->language->get('entry_status');

		$data['help_code'] = $this->language->get('help_code');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('module/helloworld', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('module/productimport', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/module', 'token=' . $this->session->data['token'], 'SSL');

		/*if (isset($this->request->post['helloworld_code'])) {
			$data['helloworld_code'] = $this->request->post['helloworld_code'];
		} else {
			$data['helloworld_code'] = $this->config->get('helloworld_code');
		}

		if (isset($this->request->post['helloworld_status'])) {
			$data['helloworld_status'] = $this->request->post['helloworld_status'];
		} else {
			$data['helloworld_status'] = $this->config->get('helloworld_status');
		} */

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('module/productimport.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'module/productimport')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['helloworld_code']) {
			$this->error['code'] = $this->language->get('error_code');
		}

		return !$this->error;
	}
}
