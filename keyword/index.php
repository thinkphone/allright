<?php
set_time_limit(0);
ini_set('memory_limit', '256M');
$stime=microtime(TRUE);

require_once 'soapclientcore.php';
$s=isset($_GET["s"])?$_GET["s"]:"";
if(isset($_GET["api"])){
	header("Content-type: text/plain; charset=utf-8");
	echo serialize(get_kw($s,intval($_GET["api"])));return;
}
// var_dump(get_kr($s));return;
if(strlen($s)){
	$result=array(get_kw($s,1),get_kw($s,2),get_kw($s,3),get_kw($s,4),get_kw($s,5));
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style>
#kw {
	width: 444px;
	border-color: #9A9A9A #CDCDCD #CDCDCD #9A9A9A;
	border-style: solid;
	border-width: 1px;
	font: 16px arial;
	height: 22px;
	padding: 4px 7px;
	vertical-align: top;
	margin: 0 5px 0 0;
}

#btn {
	background:
		url("http://su.bdimg.com/static/superpage/img/spis_3793e12d.png")
		repeat scroll 0 -35px #DDDDDD;
	border: 0 none;
	cursor: pointer;
	height: 32px;
	padding: 0;
	width: 95px;
}

table {
	border-collapse: collapse;
}

td {
	border: none;
}

.inner td {
	border: #ccc solid 1px;
	padding: 5px;
}

table caption {
	font-weight: bold;
}
</style>
<title><?php if($s)echo $s." - "?>关键词匹配工具(在线版)</title>
</head>
<body>
	<form action="index.php">
		<input maxlength="100" autocomplete="off" name="s" id="kw"
			value="<?php echo $s?>"><input type="submit" id='btn' value="搜索一下">
	</form>
	
	
	
	
<?php 
$caption=array("百度关键词工具API获得的词","相关搜索词","搜索建议词(下拉框)","商务通本地记录","综合相关");
if($result){
	echo "<br>用时 <i>".(microtime(true)-$stime)."</i> m.<hr>";
	echo "<table><tr valign='top'>";
	foreach ($result as $in=>$r) {
		echo "<td width='20%'><table class='inner'><caption>{$caption[$in]}&nbsp;<a href='http://{$_SERVER["SERVER_NAME"]}{$_SERVER["REQUEST_URI"]}&api={$in}' target='_blank' title='php请用unserialize函数反序列化这个数组'><b>api</b></a></caption><tr><td>序号</td><td>词</td><td>次数</td></tr>";
		$i=1;
		foreach ($r as $k=>$v) {
			echo "<tr><td>".($i++)."</td><td>{$k}</td><td>{$v}</td></tr>";
		}echo "</table></td>";
	}
	echo "</tr><table>";
}else{
	echo "<p>现在使用的是百度关键词api，别忘了，还有一个<a href='http://{$_SERVER["SERVER_NAME"]}/tools/tongji'>基于商务通数据的关键词工具</a>.</p>";
}
?>
</body>
</html>
