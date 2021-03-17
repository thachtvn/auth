<?php
//本番環境では"test2_"の文字を削除すること　2019/06/07 荻窪

// ---エラー内容---
// 9999=メンテナンス中
// 9997=MySQLへの接続失敗
// 9996=ランセンス認証失敗       		(ライセンス№が保守リストに存在しない or 期限が切れている)
// 9995=認証パスワードの照合失敗 		(送られてきた平文パスワードとDBのハッシュ値のパスワードが不一致)
// 9994=15分間に11回以上認証パスワードの照合失敗(同一ライセンスのログのパスワード照合失敗で判断)
// 9993=15分間に11回以上認証パスワードの照合失敗(同一IPのログのパスワード照合失敗で判断)

error_reporting( 0 );

$job = $_GET["job"];				//ジョブ([revlist:Rev一覧の取得],[start:ﾀﾞｳﾝﾛｰﾄﾞ開始],[end:ﾀﾞｳﾝﾛｰﾄﾞ終了],[allend:全ﾀﾞｳﾝﾛｰﾄﾞ終了],[cancel:ｷｬﾝｾﾙ],[orver:容量ｵｰﾊﾞｰ],[authentication:認証])
$mlt = $_GET["mltname"];			//MLT名
$use = $_GET["userrev"];			//ユーザーリビジョン
$rev = $_GET["rev"];				//取得リビジョン
$lic = $_GET["licenseno"];			//ライセンス№
$pass = $_GET["password"];			//認証パスワード(平文)
$ip = $_SERVER["REMOTE_ADDR"];
$path = 'data/'.$mlt;

//MySQL接続の確認
require_once('mysql_config.php');
$con = mysql_connect(
    $dsn['host'],
    $dsn['user'],
    $dsn['pass']
);

if ( $con == $FALSE ) {
	print "9997";				//MySQLへの接続失敗
	exit;
}
mysql_select_db($dsn['user']."db3",$con);
//mysql_set_charset('sjis');			//リストの文字化けを防ぐ
mysql_query("set names sjis");
  
//ログの書き込み
if ($job=="end" or $job=="allend" or $job=="cancel" or $job=="orver"){
	logwrite();
	exit;					//終了、キャンセル、容量オーバーはログのみ保存して終了
}

//メンテナンスの確認
$ans = "";
$Query = "SELECT flg FROM status";
$result = mysql_query($Query);
$num = mysql_num_rows($result);
for ($i=0; $i<$num; $i++) {
	$r = mysql_fetch_row($result);
	for ($j=0; $j<count($r); $j++) {
		if ($r[$j] == "1"){
			$ans = "9999";			//メンテナンス中
			$job = $ans;
			logwrite();			//ログ
			print $ans;			
			exit;
		}
	}
}

//MLTリストの取得
$ans="";
if ($job=="revlist"){
	$nowdate = date("Y-m-d");			//システム日付
	$Query = "SELECT licenseno FROM hoshulist WHERE licenseno = '$lic' AND enddate >= '$nowdate'";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//ランセンス認証失敗
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		exit;
	}
	
	$Query = "SELECT * FROM mltlist ORDER BY no";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	$ans = "";
	for ($i=0; $i<$num; $i++) {
		$r = mysql_fetch_row($result);
		for ($j=0; $j<count($r); $j++) {
			$ans = $ans.$r[$j].",";
		}
	$ans = $ans."@";
	}

	$job = $job."-OK";
	logwrite();		//ログ
	print $ans;
	exit;
}

//サイズ
$ans="";
if ($job=="size"){
	$ans = filesize($path.'/setup.exe');

	$job = $job."-OK";
	logwrite();		//ログ
	print $ans;			
	exit;
}

//ダウンロード
$ans="";
if ($job=="start"){
	readfile($path.'/setup.exe');

	$job = $job."-OK";
	logwrite();		//ログ
	exit;
}

//▼ 2019/06/07 Up
//WEB認証　
$ans="";
if ($job=="authentication"){
	//照合失敗11回以上の確認
	$sdatetime = date("Y-m-d H:i:s",strtotime("-15 minute"));	//システム日時-15分
	$edatetime = date('Y-m-d H:i:s');				//システム日時
	$Query = "";
	$Query = $Query."SELECT licenseno FROM updatelog";
	$Query = $Query." WHERE licenseno = '$lic' AND updatetime BETWEEN '$sdatetime' and '$edatetime' AND (job like 'authentication-9995%' OR job like 'authentication-9996%')";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num>=11){
		$ans="9994";				//15分間に11回以上認証パスワードの照合失敗(同一ライセンス)
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		require_once('alert_mail.php');		//メールの送信
		exit;
	} else {
		$Query = "";
		$Query = $Query."SELECT ip FROM updatelog";
		$Query = $Query." WHERE ip = '$ip' AND updatetime BETWEEN '$sdatetime' and '$edatetime' AND (job like 'authentication-9995%' OR job like 'authentication-9996%')";
		$result = mysql_query($Query);
		$num = mysql_num_rows($result);
		if ($num>=11){
			$ans="9993";				//15分間に11回以上認証パスワードの照合失敗(同一IP)
			$job = $job."-".$ans;
			logwrite();				//ログ
			print $ans;			
			require_once('alert_mail.php');		//メールの送信

			//print "-".$Query;	//デバッグ用
			//print "-".$num;	//デバッグ用
			//print "-".$sw;	//デバッグ用

			exit;
		}
	}

	//SSH、DB情報の取得
	$nowdate = date("Y-m-d");			//システム日付
	$Query = "";
	$Query = $Query."SELECT sship,sshport,dbip,dbport,dbaccount,dbpassword,authpassword,sshaccount,sshpassword,clientsuu FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN sshmaster ON  hoshulist.sshsrvno = sshmaster.sshsrvno";
	$Query = $Query." LEFT OUTER JOIN dbmaster  ON  hoshulist.dbsrvno  = dbmaster.dbsrvno";
	$Query = $Query." WHERE licenseno = '$lic' AND enddate >= '$nowdate'";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//ランセンス認証失敗
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		exit;
	}

	//利用規約URLの取得
	$Query = "";
	$Query = $Query."SELECT url FROM termsurl";
	$Query = $Query." ORDER BY setdate desc limit 1";
	$result2 = mysql_query($Query);
	$num2 = mysql_num_rows($result2);
	if ($num2==0){
		$ans="9996";				//ランセンス認証失敗
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		exit;
	}

	//メンテナンス情報の取得
	//$Query = "";
	//$Query = $Query."SELECT startdatetime, enddatetime, title, remarks FROM maintenance";
	//$Query = $Query." ORDER BY setdate desc limit 1";

	$Query = "";
	$Query = $Query."SELECT startdatetime, enddatetime, title, remarks FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN maintenance ON (";
	$Query = $Query."    (hoshulist.sshsrvno = maintenance.sshsrvno)";		//使用しているsshsrvnoがメンテ対象
	$Query = $Query." OR (hoshulist.dbsrvno  = maintenance.dbsrvno)";			//使用しているdbsrvnoがメンテ対象
	$Query = $Query." OR (maintenance.dbsrvno = '' AND maintenance.sshsrvno= '')";	//sshsrvno,dbsrvnoが空欄(全ユーザーが対象のメンテ)
	$Query = $Query." )";
	$Query = $Query." WHERE hoshulist.licenseno = '$lic'";
	$Query = $Query." ORDER BY maintenance.setdate desc limit 1";

	$result3 = mysql_query($Query);
	$num3 = mysql_num_rows($result3);
	if ($num3==0){
		$ans="9996";				//ランセンス認証失敗
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		exit;
	}
	

	$ans = "";
	for ($i=0; $i<$num; $i++) {
		$r = mysql_fetch_row($result);
		for ($j=0; $j<count($r); $j++) {
			//認証パスワードの照合
			if ($j==6) {						//$j==6は"authpassword"の列の意味
				$hased_pass = crypt($pass,$r[$j]);		//crypt関数でハッシュ化。saltはDBから取得したハッシュ化された値を使用

				if ($r[$j] != $hased_pass) {
					$ans="9995";				//認証パスワードの照合失敗
					$job = $job."-".$ans."(".$pass.")";
					logwrite();				//ログ
					print $ans;			
					exit;
				}
			} else {
				$ans = $ans.$r[$j]."\t";
			}
		}
		
		$r = mysql_fetch_row($result2);
		for ($j=0; $j<count($r); $j++) {
			//認証パスワードの照合
			if ($j==6) {						//$j==6は"authpassword"の列の意味
				$hased_pass = crypt($pass,$r[$j]);		//crypt関数でハッシュ化。saltはDBから取得したハッシュ化された値を使用

				if ($r[$j] != $hased_pass) {
					$ans="9995";				//認証パスワードの照合失敗
					$job = $job."-".$ans."(".$pass.")";
					logwrite();				//ログ
					print $ans;			
					exit;
				}
			} else {
				$ans = $ans.$r[$j]."\t";
			}
		}

		$r = mysql_fetch_row($result3);
		for ($j=0; $j<count($r); $j++) {
			//認証パスワードの照合
			if ($j==6) {						//$j==6は"authpassword"の列の意味
				$hased_pass = crypt($pass,$r[$j]);		//crypt関数でハッシュ化。saltはDBから取得したハッシュ化された値を使用

				if ($r[$j] != $hased_pass) {
					$ans="9995";				//認証パスワードの照合失敗
					$job = $job."-".$ans."(".$pass.")";
					logwrite();				//ログ
					print $ans;			
					exit;
				}
			} else {
				$ans = $ans.$r[$j]."\t";
			}
		}
		

	}

	$job = $job."-OK";
	logwrite();		//ログ
	print $ans;
	exit;
}

//SSHキー取得
$ans="";
if ($job=="sshkeyget"){
	$nowdate = date("Y-m-d");		//システム日付
	$Query = "";
	$Query = $Query."SELECT sshprivatekey FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN sshmaster ON  hoshulist.sshsrvno = sshmaster.sshsrvno";
	$Query = $Query." WHERE licenseno = '$lic' AND enddate >= '$nowdate'";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//ランセンス認証失敗
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		exit;
	}
	$ans = "";
	for ($i=0; $i<$num; $i++) {
		$r = mysql_fetch_row($result);
		for ($j=0; $j<count($r); $j++) {
			$ans = $ans.$r[$j];
		}
	}

	$job = $job."-OK";
	logwrite();		//ログ
	print $ans;
	exit;
}

//▼ 2020/08/04 Up(荻窪)
//実施中メンテナンス情報の取得
$ans="";
if ($job=="menteconfirm"){
	$Query = "";
	$Query = $Query."SELECT startdatetime, enddatetime, title, remarks FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN maintenance ON (";
	$Query = $Query."    (hoshulist.sshsrvno = maintenance.sshsrvno)";		//使用しているsshsrvnoがメンテ対象
	$Query = $Query." OR (hoshulist.dbsrvno  = maintenance.dbsrvno)";			//使用しているdbsrvnoがメンテ対象
	$Query = $Query." OR (maintenance.dbsrvno = '' AND maintenance.sshsrvno= '')";	//sshsrvno,dbsrvnoが空欄(全ユーザーが対象のメンテ)
	$Query = $Query." )";
	$Query = $Query." WHERE hoshulist.licenseno = '$lic'";
	$Query = $Query." AND maintenance.progressflg = '1'";
	$Query = $Query." ORDER BY maintenance.setdate desc limit 1";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//ランセンス認証失敗
		$job = $job."-".$ans;
		logwrite();				//ログ
		print $ans;			
		exit;
	}
	$ans = "";
	for ($i=0; $i<$num; $i++) {
		$r = mysql_fetch_row($result);
		for ($j=0; $j<count($r); $j++) {
			$ans = $ans.$r[$j]."\t";
		}
	}

	//print $Query;	//デバッグ用

	//ログ無し
	print $ans;
	exit;
}
//▲ 2020/08/04 Up(荻窪)

//不正なjobが来た場合はここでログを取る
logwrite();		//ログ
exit;
//▲ 2019/06/07 Up

//▼▼▼▼▼▼▼▼ 関数 ▼▼▼▼▼▼▼▼▼
function logwrite(){

	global $nowdatetime,$job,$lic,$mlt,$use,$rev,$ip;	//関数の中では"global"と書かないと外の変数を認識しない

	$nowdatetime = date("Y/m/d H:i:s");			//システム日付時間
	
	mysql_query("begin");
	
	$Query = "INSERT INTO updatelog VALUES ('$nowdatetime','$job','$lic','$mlt','$use','$rev','$ip')";
	$result = mysql_query($Query);
	
	mysql_query("commit");
	
	return;
}
//▲▲▲▲▲▲▲▲ 関数 ▲▲▲▲▲▲▲▲▲

mysql_close($con);
?>
