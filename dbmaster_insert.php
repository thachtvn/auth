<?php

	
	// MySQL �ڑ�
	require_once('mysql_config.php');
	if (!($cn = mysql_connect($dsn['host'],$dsn['user'],$dsn['pass']))) {
		die;
	}
	// MySQL DB �I��
	if (!(mysql_select_db("ichiban"))) {
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	// MySQL �g�����U�N�V�����J�n
	$sql = "begin";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

	// statusð��ٍ폜
	$sql = "delete from status";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

	// statusð��ق��׸ނ����Ă�
	$nowdate = date("Y/m/d");//�V�X�e�����t
	$sql = "insert into status values ('$nowdate','1')";	
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}

	// MySQL ���R�[�h�폜
	$sql = "delete from dbmaster";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	

	
		
	//DBCSV�ް���ݻ��
	$fileName = "files/dbmaster.txt"; 
	$file = fopen($fileName,"r");
	$nowdatetime = date("Y/m/d H:i:s");//�V�X�e�����t����
	while(!feof($file)){ 
		$str = fgetcsv($file); 
		if ($str[0] > ""){
		
			// �ێ��� ���R�[�h�ǉ�
			$sql = "insert into dbmaster values ('$str[0]','$str[1]','$str[2]','$nowdatetime')";
			if(!mysql_query($sql)){
				echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
				die;
		
			}
		}
	}
	fclose($file);
	//���@2020.01.21�@DB�T�[�o�[�̃f�[�^���A�b�v���[�h����悤�ǉ�

	// statusð��ق��׸ނ�����
	$sql = "update status SET updatetime ='$nowdate',flg= '0'";
	if (!(mysql_query($sql))) {
		die;
	}

	// MySQL �g�����U�N�V�����R�~�b�g
	$sql = "commit";
	if (!(mysql_query($sql))) {
		die;
	}
	
	
	// MySQL �ؒf
	mysql_close($cn);

	print "Clear";
	//print "<P>�X�V������ɏI�����܂���</P>";

?>