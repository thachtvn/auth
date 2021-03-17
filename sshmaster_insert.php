<?php

	
	// MySQL 接続
	require_once('mysql_config.php');
	if (!($cn = mysql_connect($dsn['host'],$dsn['user'],$dsn['pass']))) {
		die;
	}
	// MySQL DB 選択
	if (!(mysql_select_db("ichiban"))) {
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	// MySQL トランザクション開始
	$sql = "begin";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

	// statusﾃｰﾌﾞﾙ削除
	$sql = "delete from status";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

	// statusﾃｰﾌﾞﾙにﾌﾗｸﾞをたてる
	$nowdate = date("Y/m/d");//システム日付
	$sql = "insert into status values ('$nowdate','1')";	
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

	//↓　2020.01.21　SSH、DBサーバー追加
	$sql = "delete from sshmaster";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

		
	//CSVﾃﾞｰﾀをｲﾝｻｰﾄ
	//↓　2020.01.21　SSHサーバーのデータをアップロードするよう追加
	//SSHCSVﾃﾞｰﾀをｲﾝｻｰﾄ
	$fileName = "files/sshmaster.txt"; 
	$file = fopen($fileName,"r");
	$nowdatetime = date("Y/m/d H:i:s");//システム日付時間
	while(!feof($file)){ 
		$str = fgetcsv($file); 
		if ($str[0] > ""){
		
			// 保守情報 レコード追加
			$sql = "insert into sshmaster values ('$str[0]','$str[1]','$str[2]','$str[3]','$nowdatetime')";
			if(!mysql_query($sql)){
				echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
				die;
			}
		}
	}
	fclose($file);
	//↑　2020.01.21　SSHサーバーのデータをアップロードするよう追加

	// statusﾃｰﾌﾞﾙのﾌﾗｸﾞを解除
	$sql = "update status SET updatetime ='$nowdate',flg= '0'";
	if (!(mysql_query($sql))) {
		die;
	}

	// MySQL トランザクションコミット
	$sql = "commit";
	if (!(mysql_query($sql))) {
		die;
	}
	
	//print "<P>---更新結果---</P>";
	// MySQL 問い合わせ

	//↓　2020.01.21　SSH、DBサーバーのデータを追加
	//$sshsql = "select * from test2_sshmaster";
	//if (!($rs2 = mysql_query($sshsql))) {
	//	die;
	//}
	// ↑ 2020.01.21
	
	// MySQL レコード参照
	//↓　2020.01.21　SSH、DBサーバーのデータを追加
	//$i = 0;
	//print "SSHNo　IP　PortNo　公開鍵 更新日付<BR>";
	//while ($item = mysql_fetch_array($rs2)) {
	//	$i++;
	//	print "$i ";
	//	print "${item['sshno']} ";
	//	print "${item['sship']} ";
	//	print "${item['sshport']} ";
	//	print "${item['sshpublickey']} ";
	//	print "${item['updatetime']}<BR>";
	//}
	// ↑ 2020.01.21

	// MySQL 切断
	mysql_close($cn);

	print "Clear";
	//print "<P>更新が正常に終了しました</P>";

?>