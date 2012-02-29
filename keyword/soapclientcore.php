<?php
/**
 * Baidu Api PHP client core service. Please change the define of USERNAME, PASSWORD, TOKEN before usage.
 *
 * @author Baidu Api Team
 *
 */
require_once 'simple_html_dom.php';
require_once 'sbmmysql.class.php';
// Sandbox URL
// define('URL', 'https://sfapitest.baidu.com');
// Online URL
define('URL', 'https://api.baidu.com');
//USERNAME
define('USERNAME', '<用户名>');
//PASSWORD
define('PASSWORD', '<密码>');
//TOKEN
define('TOKEN', '<申请到的token>');

class Baidu_Api_Client_Core {
	private $soapClient;

	/**
	 * construcor of Baidu_Api_Client_Core, only need the service name.
	 * @param String $serviceName
	 */
	public function __construct($serviceName) {
		$this->soapClient = new SoapClient ( URL . '/sem/sms/v2/' . $serviceName . '?wsdl', array ('trace' => TRUE, 'connection_timeout' => 30 ) );

		// Prepare SoapHeader parameters
		$sh_param = array ('username' => USERNAME, 'password' => PASSWORD, 'token' => TOKEN );
		$headers = new SoapHeader ( 'http://api.baidu.com/sem/common/v2', 'AuthHeader', $sh_param );

		// Prepare Soap Client
		$this->soapClient->__setSoapHeaders ( array ( $headers ) );
	}

	public function getFunctions() {
		return $this->soapClient->__getFunctions();
	}

	public function getTypes() {
		return $this->soapClient->__getTypes();
	}

	public function soapCall($function_name, array $arguments, array &$output_headers) {
		return $this->soapClient->__soapCall($function_name, $arguments, null, null, $output_headers);
	}
}
class KRService extends Baidu_Api_Client_Core {
	public function __construct() {
		parent::__construct('KRService');
	}
}
function get_kr($keyword){
	$service = new KRService();
	$output_headers = array();
	// Call getKRbySeedWord function
	$arguments = array('getKRbySeedWordRequest' => array('seedWord' => $keyword, 'seedFilter' =>
	array ('matchType' => 3
	,"negativeWord"=>array("药","麦","谷","家","塑","米","药","绵","胶","椒")
	)));
	$output_response = $service->soapCall('getKRbySeedWord', $arguments, $output_headers);
	$ret=array();
	foreach (($output_response->krResult) as $res) {
		if(isset($ret[$res->word])){
			$ret[$res->word]+=intval($res->broadPV);
		}else{
			$ret[$res->word]=intval($res->broadPV);
		}
	}
	arsort($ret);
	return $ret;
}
//推荐词
function get_sug($k){
	$k=urlencode($k);
	$ret=array();
	//soso
	$soso=iconv("gbk", "utf-8", file_get_contents("http://www.soso.com/wh.q?w={$k}"));
	$r=explode("\n", $soso);
	foreach ($r as $s) {
		$s=str_replace("0", "", $s);
		$ret[trim($s)]=isset($ret[trim($s)])?($ret[trim($s)]+1):1;
	}
	//baidu
	$baidu=iconv("gbk", "utf-8", file_get_contents("http://suggestion.baidu.com/su?wd={$k}"));
	preg_match("/s\:(.*)\}\)/", $baidu,$r_baidu);
	$baidu=json_decode($r_baidu[1]);
	foreach ($baidu as $s) {
		$ret[trim($s)]=isset($ret[trim($s)])?($ret[trim($s)]+1):1;
	}
	//sogou
	$sogou=iconv("gbk", "utf-8", file_get_contents("http://sugg.sogou.com/sugg/ajaj_json.jsp?key={$k}&type=web"));
	preg_match("/,\[(.*)\]\]/", $sogou,$r_sogou);
	$r=json_decode("[{$r_sogou[1]}]");
	foreach ($r as $s) {
		$ret[trim($s)]=isset($ret[trim($s)])?($ret[trim($s)]+1):1;
	}
	//youdao
	$youdao=file_get_contents("http://www.youdao.com/suggest2/suggest.s?query={$k}&keyfrom=web.index.suggest&o=aa&rn=10&h=15");
	preg_match_all("/c:'(.*?)'\},/", $youdao,$r_youdao);
	$r=$r_youdao[1];
	foreach ($r as $s) {
		$ret[trim($s)]=isset($ret[trim($s)])?($ret[trim($s)]+1):1;
	}
	//jike
	$jike=file_get_contents("http://sug.jike.com:6333/su?k=".$k);
	preg_match_all("/value\":\"(.*?)\"/", $jike,$r_jike);
	$r=$r_jike[1];
	foreach ($r as $s) {
		$ret[trim($s)]=isset($ret[trim($s)])?($ret[trim($s)]+1):1;
	}//*/
	//google
	$google=json_decode(iconv("gbk", "utf-8", file_get_contents("http://www.google.com.hk/complete/search?client=hp&hl=zh-CN&sugexp=kjrmc&cp=1&gs_id=f&q={$k}&xhr=t")));
	$r=$google[1];
	foreach ($r as $s) {
		$ret[trim($s[0])]=isset($ret[trim($s[0])])?($ret[trim($s[0])]+1):1;;
	}
	arsort($ret);
	return $ret;
}
//相关搜索词
function get_rel($k){
	$ret=array();
	//sogou
	$html=iconv("gbk", "utf-8", file_get_contents("http://www.sogou.com/web?query=".urlencode($k)));
	$html=str_get_html($html);
	$kw=$html->find("#hint_container td");
	foreach ($kw as $s) {
		$s=trim($s->plaintext);
		$ret[$s]=isset($ret[$s])?($ret[$s]+1):1;
	}

	//soso
	$html=iconv("gbk", "utf-8", file_get_contents("http://www.soso.com/q?w=".urlencode(iconv("utf-8", "gbk", $k))));
	$html=str_get_html($html);
	$kw=$html->find("#rel td");
	foreach ($kw as $s) {
		$s=trim($s->plaintext);
		$ret[$s]=isset($ret[$s])?($ret[$s]+1):1;
	}
	$kw=$html->find("div[ss_c='w.r.fav'] dd");
	foreach ($kw as $s) {
		$s=trim($s->plaintext);
		$ret[$s]=isset($ret[$s])?($ret[$s]+1):1;
	}

	//baidu
	$html=iconv("gbk", "utf-8", file_get_contents("http://www.baidu.com/s?wd=".urlencode(iconv("utf-8", "gbk", $k))));
	$html=str_get_html($html);
	$kw=$html->find("#rs th a");
	foreach ($kw as $s) {
		$s=trim($s->plaintext);
		$ret[$s]=isset($ret[$s])?($ret[$s]+1):1;
	}

	//youdao
	$html=file_get_contents("http://www.youdao.com/search?q=".urlencode($k));
	$html=str_get_html($html);
	$kw=$html->find("#relatedKeys a");
	foreach ($kw as $s) {
		$s=trim($s->plaintext);
		$ret[$s]=isset($ret[$s])?($ret[$s]+1):1;
	}//*/

	//jike
	$html=file_get_contents("http://www.jike.com/so?q=".urlencode($k));
	$html=str_get_html($html);
	$kw=$html->find(".Brs a");
	foreach ($kw as $s) {
		$s=trim($s->plaintext);
		$ret[$s]=isset($ret[$s])?($ret[$s]+1):1;
	}//*/
	arsort($ret);
	return $ret;
}
function get_swt($k){
	$sql = "SELECT * FROM `keywords` WHERE `kw` LIKE '%{$k}%'";
	$mysql=new SbmMysql();
	$data=$mysql->getData($sql);
	$ret=array();
	foreach ($data as $d) {
		$ret[$d["kw"]]=$d["count"];
	}
	arsort($ret);
	return $ret;
}
function get_all($s){
	$ret=array();
	$result=array(get_kw($s,1),get_kw($s,2),get_kw($s,3),get_kw($s,4));
	foreach ($result as $r) {
		foreach ($r as $k=>$v) {
			if(isset($ret[$k]))$ret[$k]+=$v;else $ret[$k]=$v;
		}
	}
	arsort($ret);
	return $ret;
}
function get_kw($s,$type=1){
	if(empty($s))return array();
	$type=intval($type);
	$mysql=new SbmMysql();
	$s=$mysql->escape($s);
	$sql = "SELECT * FROM `kr_cache` WHERE `keyword` = '".$mysql->escape($s)."' AND `type`={$type}";
	$data=$mysql->getLine($sql);
	$result=array();
	if($data){
		//check cache time
		if(time()-strtotime($data["time"])>3600*12*7){
			//out of data
			$result=($type==1?get_kr($s):($type==2?get_rel($s):($type==3?get_sug($s):($type==4?get_swt($s):get_all($s)))));
			$sql = "UPDATE `kr_cache` SET `result` ='".$mysql->escape(serialize($result))."',`time`=CURRENT_TIMESTAMP WHERE `kr_cache`.`id` = {$data["id"]};";
			// 			echo $sql;
			$mysql->runSql($sql);
		}else{
			$result=unserialize($data["result"]);
		}
	}else{
		$result=($type==1?get_kr($s):($type==2?get_rel($s):($type==3?get_sug($s):($type==4?get_swt($s):get_all($s)))));
		$sql = "INSERT INTO `kr_cache` (`id`,`type`, `keyword`, `result`, `time`) VALUES (NULL,{$type}, '{$s}', '".$mysql->escape(serialize($result))."', CURRENT_TIMESTAMP);";
		$mysql->runSql($sql);
	}
	$mysql->closeDb();
	return $result;
}

//一些辅助函数///
function zoonet2db(){
	$mysql=new SbmMysql();
	$kws=explode("\n", file_get_contents("shangwutong.txt"));
	$cache=array();
	foreach ($kws as $k) {
		$k=trim($k);
		$pos=strrpos($k, ",");
		$key=trim(substr($k, 0,$pos));
		$value=substr($k, $pos+1);
		if($key&&strlen($key)>3&&(!preg_match("(http|site|www|!|\.|&|com|药|麦|谷|雪|学习|家|塑|米|粮|豆|面|药|魔兽|游戏|婴|绵|胶|椒|渔|冰|玉|食|西蒙斯|员工|祝福|饮|羽|美卓|愚|儿|医|家|实验|\?)", $key))){
			if(isset($cache[$key]))$cache[$key]+=intval($value);else $cache[$key]=intval($value);
		}
	}
	foreach ($cache as $key=>$value) {
		$sql = "INSERT INTO `baiduapi`.`keywords` (`id`, `kw`, `count`) VALUES (NULL,'".$mysql->escape($key)."', '{$value}');";
		$mysql->runSql($sql);
	}
	$mysql->closeDb();
}
