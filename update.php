<?php
//�{�Ԋ��ł�"test2_"�̕������폜���邱�Ɓ@2019/06/07 ���E

// ---�G���[���e---
// 9999=�����e�i���X��
// 9997=MySQL�ւ̐ڑ����s
// 9996=�����Z���X�F�؎��s       		(���C�Z���X�����ێ烊�X�g�ɑ��݂��Ȃ� or �������؂�Ă���)
// 9995=�F�؃p�X���[�h�̏ƍ����s 		(�����Ă��������p�X���[�h��DB�̃n�b�V���l�̃p�X���[�h���s��v)
// 9994=15���Ԃ�11��ȏ�F�؃p�X���[�h�̏ƍ����s(���ꃉ�C�Z���X�̃��O�̃p�X���[�h�ƍ����s�Ŕ��f)
// 9993=15���Ԃ�11��ȏ�F�؃p�X���[�h�̏ƍ����s(����IP�̃��O�̃p�X���[�h�ƍ����s�Ŕ��f)

error_reporting( 0 );

$job = $_GET["job"];				//�W���u([revlist:Rev�ꗗ�̎擾],[start:�޳�۰�ފJ�n],[end:�޳�۰�ޏI��],[allend:�S�޳�۰�ޏI��],[cancel:��ݾ�],[orver:�e�ʵ��ް],[authentication:�F��])
$mlt = $_GET["mltname"];			//MLT��
$use = $_GET["userrev"];			//���[�U�[���r�W����
$rev = $_GET["rev"];				//�擾���r�W����
$lic = $_GET["licenseno"];			//���C�Z���X��
$pass = $_GET["password"];			//�F�؃p�X���[�h(����)
$ip = $_SERVER["REMOTE_ADDR"];
$path = 'data/'.$mlt;

//MySQL�ڑ��̊m�F
require_once('mysql_config.php');
$con = mysql_connect(
    $dsn['host'],
    $dsn['user'],
    $dsn['pass']
);

if ( $con == $FALSE ) {
	print "9997";				//MySQL�ւ̐ڑ����s
	exit;
}
mysql_select_db($dsn['user']."db3",$con);
//mysql_set_charset('sjis');			//���X�g�̕���������h��
mysql_query("set names sjis");
  
//���O�̏�������
if ($job=="end" or $job=="allend" or $job=="cancel" or $job=="orver"){
	logwrite();
	exit;					//�I���A�L�����Z���A�e�ʃI�[�o�[�̓��O�̂ݕۑ����ďI��
}

//�����e�i���X�̊m�F
$ans = "";
$Query = "SELECT flg FROM status";
$result = mysql_query($Query);
$num = mysql_num_rows($result);
for ($i=0; $i<$num; $i++) {
	$r = mysql_fetch_row($result);
	for ($j=0; $j<count($r); $j++) {
		if ($r[$j] == "1"){
			$ans = "9999";			//�����e�i���X��
			$job = $ans;
			logwrite();			//���O
			print $ans;			
			exit;
		}
	}
}

//MLT���X�g�̎擾
$ans="";
if ($job=="revlist"){
	$nowdate = date("Y-m-d");			//�V�X�e�����t
	$Query = "SELECT licenseno FROM hoshulist WHERE licenseno = '$lic' AND enddate >= '$nowdate'";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//�����Z���X�F�؎��s
		$job = $job."-".$ans;
		logwrite();				//���O
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
	logwrite();		//���O
	print $ans;
	exit;
}

//�T�C�Y
$ans="";
if ($job=="size"){
	$ans = filesize($path.'/setup.exe');

	$job = $job."-OK";
	logwrite();		//���O
	print $ans;			
	exit;
}

//�_�E�����[�h
$ans="";
if ($job=="start"){
	readfile($path.'/setup.exe');

	$job = $job."-OK";
	logwrite();		//���O
	exit;
}

//�� 2019/06/07 Up
//WEB�F�؁@
$ans="";
if ($job=="authentication"){
	//�ƍ����s11��ȏ�̊m�F
	$sdatetime = date("Y-m-d H:i:s",strtotime("-15 minute"));	//�V�X�e������-15��
	$edatetime = date('Y-m-d H:i:s');				//�V�X�e������
	$Query = "";
	$Query = $Query."SELECT licenseno FROM updatelog";
	$Query = $Query." WHERE licenseno = '$lic' AND updatetime BETWEEN '$sdatetime' and '$edatetime' AND (job like 'authentication-9995%' OR job like 'authentication-9996%')";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num>=11){
		$ans="9994";				//15���Ԃ�11��ȏ�F�؃p�X���[�h�̏ƍ����s(���ꃉ�C�Z���X)
		$job = $job."-".$ans;
		logwrite();				//���O
		print $ans;			
		require_once('alert_mail.php');		//���[���̑��M
		exit;
	} else {
		$Query = "";
		$Query = $Query."SELECT ip FROM updatelog";
		$Query = $Query." WHERE ip = '$ip' AND updatetime BETWEEN '$sdatetime' and '$edatetime' AND (job like 'authentication-9995%' OR job like 'authentication-9996%')";
		$result = mysql_query($Query);
		$num = mysql_num_rows($result);
		if ($num>=11){
			$ans="9993";				//15���Ԃ�11��ȏ�F�؃p�X���[�h�̏ƍ����s(����IP)
			$job = $job."-".$ans;
			logwrite();				//���O
			print $ans;			
			require_once('alert_mail.php');		//���[���̑��M

			//print "-".$Query;	//�f�o�b�O�p
			//print "-".$num;	//�f�o�b�O�p
			//print "-".$sw;	//�f�o�b�O�p

			exit;
		}
	}

	//SSH�ADB���̎擾
	$nowdate = date("Y-m-d");			//�V�X�e�����t
	$Query = "";
	$Query = $Query."SELECT sship,sshport,dbip,dbport,dbaccount,dbpassword,authpassword,sshaccount,sshpassword,clientsuu FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN sshmaster ON  hoshulist.sshsrvno = sshmaster.sshsrvno";
	$Query = $Query." LEFT OUTER JOIN dbmaster  ON  hoshulist.dbsrvno  = dbmaster.dbsrvno";
	$Query = $Query." WHERE licenseno = '$lic' AND enddate >= '$nowdate'";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//�����Z���X�F�؎��s
		$job = $job."-".$ans;
		logwrite();				//���O
		print $ans;			
		exit;
	}

	//���p�K��URL�̎擾
	$Query = "";
	$Query = $Query."SELECT url FROM termsurl";
	$Query = $Query." ORDER BY setdate desc limit 1";
	$result2 = mysql_query($Query);
	$num2 = mysql_num_rows($result2);
	if ($num2==0){
		$ans="9996";				//�����Z���X�F�؎��s
		$job = $job."-".$ans;
		logwrite();				//���O
		print $ans;			
		exit;
	}

	//�����e�i���X���̎擾
	//$Query = "";
	//$Query = $Query."SELECT startdatetime, enddatetime, title, remarks FROM maintenance";
	//$Query = $Query." ORDER BY setdate desc limit 1";

	$Query = "";
	$Query = $Query."SELECT startdatetime, enddatetime, title, remarks FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN maintenance ON (";
	$Query = $Query."    (hoshulist.sshsrvno = maintenance.sshsrvno)";		//�g�p���Ă���sshsrvno�������e�Ώ�
	$Query = $Query." OR (hoshulist.dbsrvno  = maintenance.dbsrvno)";			//�g�p���Ă���dbsrvno�������e�Ώ�
	$Query = $Query." OR (maintenance.dbsrvno = '' AND maintenance.sshsrvno= '')";	//sshsrvno,dbsrvno����(�S���[�U�[���Ώۂ̃����e)
	$Query = $Query." )";
	$Query = $Query." WHERE hoshulist.licenseno = '$lic'";
	$Query = $Query." ORDER BY maintenance.setdate desc limit 1";

	$result3 = mysql_query($Query);
	$num3 = mysql_num_rows($result3);
	if ($num3==0){
		$ans="9996";				//�����Z���X�F�؎��s
		$job = $job."-".$ans;
		logwrite();				//���O
		print $ans;			
		exit;
	}
	

	$ans = "";
	for ($i=0; $i<$num; $i++) {
		$r = mysql_fetch_row($result);
		for ($j=0; $j<count($r); $j++) {
			//�F�؃p�X���[�h�̏ƍ�
			if ($j==6) {						//$j==6��"authpassword"�̗�̈Ӗ�
				$hased_pass = crypt($pass,$r[$j]);		//crypt�֐��Ńn�b�V�����Bsalt��DB����擾�����n�b�V�������ꂽ�l���g�p

				if ($r[$j] != $hased_pass) {
					$ans="9995";				//�F�؃p�X���[�h�̏ƍ����s
					$job = $job."-".$ans."(".$pass.")";
					logwrite();				//���O
					print $ans;			
					exit;
				}
			} else {
				$ans = $ans.$r[$j]."\t";
			}
		}
		
		$r = mysql_fetch_row($result2);
		for ($j=0; $j<count($r); $j++) {
			//�F�؃p�X���[�h�̏ƍ�
			if ($j==6) {						//$j==6��"authpassword"�̗�̈Ӗ�
				$hased_pass = crypt($pass,$r[$j]);		//crypt�֐��Ńn�b�V�����Bsalt��DB����擾�����n�b�V�������ꂽ�l���g�p

				if ($r[$j] != $hased_pass) {
					$ans="9995";				//�F�؃p�X���[�h�̏ƍ����s
					$job = $job."-".$ans."(".$pass.")";
					logwrite();				//���O
					print $ans;			
					exit;
				}
			} else {
				$ans = $ans.$r[$j]."\t";
			}
		}

		$r = mysql_fetch_row($result3);
		for ($j=0; $j<count($r); $j++) {
			//�F�؃p�X���[�h�̏ƍ�
			if ($j==6) {						//$j==6��"authpassword"�̗�̈Ӗ�
				$hased_pass = crypt($pass,$r[$j]);		//crypt�֐��Ńn�b�V�����Bsalt��DB����擾�����n�b�V�������ꂽ�l���g�p

				if ($r[$j] != $hased_pass) {
					$ans="9995";				//�F�؃p�X���[�h�̏ƍ����s
					$job = $job."-".$ans."(".$pass.")";
					logwrite();				//���O
					print $ans;			
					exit;
				}
			} else {
				$ans = $ans.$r[$j]."\t";
			}
		}
		

	}

	$job = $job."-OK";
	logwrite();		//���O
	print $ans;
	exit;
}

//SSH�L�[�擾
$ans="";
if ($job=="sshkeyget"){
	$nowdate = date("Y-m-d");		//�V�X�e�����t
	$Query = "";
	$Query = $Query."SELECT sshprivatekey FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN sshmaster ON  hoshulist.sshsrvno = sshmaster.sshsrvno";
	$Query = $Query." WHERE licenseno = '$lic' AND enddate >= '$nowdate'";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//�����Z���X�F�؎��s
		$job = $job."-".$ans;
		logwrite();				//���O
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
	logwrite();		//���O
	print $ans;
	exit;
}

//�� 2020/08/04 Up(���E)
//���{�������e�i���X���̎擾
$ans="";
if ($job=="menteconfirm"){
	$Query = "";
	$Query = $Query."SELECT startdatetime, enddatetime, title, remarks FROM hoshulist";
	$Query = $Query." LEFT OUTER JOIN maintenance ON (";
	$Query = $Query."    (hoshulist.sshsrvno = maintenance.sshsrvno)";		//�g�p���Ă���sshsrvno�������e�Ώ�
	$Query = $Query." OR (hoshulist.dbsrvno  = maintenance.dbsrvno)";			//�g�p���Ă���dbsrvno�������e�Ώ�
	$Query = $Query." OR (maintenance.dbsrvno = '' AND maintenance.sshsrvno= '')";	//sshsrvno,dbsrvno����(�S���[�U�[���Ώۂ̃����e)
	$Query = $Query." )";
	$Query = $Query." WHERE hoshulist.licenseno = '$lic'";
	$Query = $Query." AND maintenance.progressflg = '1'";
	$Query = $Query." ORDER BY maintenance.setdate desc limit 1";
	$result = mysql_query($Query);
	$num = mysql_num_rows($result);
	if ($num==0){
		$ans="9996";				//�����Z���X�F�؎��s
		$job = $job."-".$ans;
		logwrite();				//���O
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

	//print $Query;	//�f�o�b�O�p

	//���O����
	print $ans;
	exit;
}
//�� 2020/08/04 Up(���E)

//�s����job�������ꍇ�͂����Ń��O�����
logwrite();		//���O
exit;
//�� 2019/06/07 Up

//���������������� �֐� ������������������
function logwrite(){

	global $nowdatetime,$job,$lic,$mlt,$use,$rev,$ip;	//�֐��̒��ł�"global"�Ə����Ȃ��ƊO�̕ϐ���F�����Ȃ�

	$nowdatetime = date("Y/m/d H:i:s");			//�V�X�e�����t����
	
	mysql_query("begin");
	
	$Query = "INSERT INTO updatelog VALUES ('$nowdatetime','$job','$lic','$mlt','$use','$rev','$ip')";
	$result = mysql_query($Query);
	
	mysql_query("commit");
	
	return;
}
//���������������� �֐� ������������������

mysql_close($con);
?>
