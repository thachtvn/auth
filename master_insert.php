<?php

	
	// MySQL �ڑ�
	require_once('mysql_config.php');
	if (!($cn = mysqli_connect($dsn['host'],$dsn['user'],$dsn['pass']))) {
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
	$sql = "delete from hoshulist";
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	
		
	//CSV�ް���ݻ��
	$fileName = "files/master.txt"; 
	$file = fopen($fileName,"r");
	$nowdatetime = date("Y/m/d H:i:s");//�V�X�e�����t����
	while(!feof($file)){ 
		$str = fgetcsv($file); 
		if ($str[0] > ""){
		
			//�_�E�����[�h�s���[�U�[����
			$sql = "SELECT licenseno FROM nodownloadlist WHERE licenseno = '$str[1]'";
			$result = mysql_query($sql);
			$num = mysql_num_rows($result);	
			if ($num==0){
			
				// �ێ��� ���R�[�h�ǉ�
				// �� 2020.01.21 �ێ���@���ڒǉ�
				//$sql = "insert into test2_hoshulist values ('$str[0]','$str[1]','$str[2]','$nowdatetime')";
				$hashed_password = "";
				if(!empty($str[9])){
					$hashed_password = crypt($str[9]);
				}
				$sql = "insert into hoshulist values ('$str[0]','$str[1]','$str[2]','$nowdatetime','$str[3]','$str[4]','$str[5]','$str[6]','$str[7]','$str[8]','$hashed_password','$str[10]','$str[11]')";
				// �� 2020.01.21
				if(!mysql_query($sql)){
					echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
					die;
				}
			}
		}
	}

	// �� 2020.07.21 �ێ���@���ڒǉ�
	// �N���E�h�ڑ��e�X�g�p��ײ�ݽ���������ǉ�
	//$sql = "insert into test2_hoshulist values ('9999','999999999','9999/12/31','$nowdatetime')";
	$sql = "insert into hoshulist values ('111222333','000000000','2023-10-31','2020-03-26 11:47:20','1','1','UVcTrg4G/D5bZmOTUI0I/D/2KXqQiWlok+/MAvKqGio=','PrYAzXfYCdR+pNkZYNz53yOmGC96pEsKUguSu/R4T20=','1e7BHNf6GUQMiiwCAoFjGQ==','VB4GUccXEixfu/guB+HcVWLwzOsIw4GiM7mRkkJsPpc=','\$1\$qQ.5v2ko\$k8ga4s1ju70on8ryZAVLX.','','yWWkE3To/4T+1USu9LJoZA==')";
		if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	$sql = "insert into hoshulist values ('88888888888','888888888','2023-10-31','2020-03-26 11:47:20','1','1','UVcTrg4G/D5bZmOTUI0I/D/2KXqQiWlok+/MAvKqGio=','PrYAzXfYCdR+pNkZYNz53yOmGC96pEsKUguSu/R4T20=','7wKBe80WFWd9DiYgQZ96xA==','rEspkinG51j1ftPxFsdAaIdy7P2hSNxGxytgG1DQwVE=','\$1\$Ixt4bHp3\$frIlLvZ2VirxgVzN/H1OO.','uTanbzQV7jC+7XeK5Zf/8Q==','swk/hu2AWAulO75ODV7dEQ==')";
		if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	// �� 2020.07.21


	// �\�t�e�b�N�p��ײ�ݽ���������ǉ�
	// �� 2020.01.21 �ێ���@���ڒǉ�
	//$sql = "insert into test2_hoshulist values ('9999','999999999','9999/12/31','$nowdatetime')";
	$sql = "insert into hoshulist values ('9999','999999999','9999/12/31','$nowdatetime','','','','','','','','','')";
	// �� 2020.01.21
	if(!mysql_query($sql)){
		echo "Error=".mysql_errno($cn).": ".mysql_error($cn)."<br />\n";
		die;
	}
	fclose($file); 

	
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
	
	//print "<P>---�X�V����---</P>";
	// MySQL �₢���킹
	//$sql = "select * from test2_hoshulist";
	//if (!($rs = mysql_query($sql))) {
	//	die;
	//}

	//���@2020.01.21�@SSH�ADB�T�[�o�[�̃f�[�^��ǉ�
	//$sshsql = "select * from test2_sshmaster";
	//if (!($rs2 = mysql_query($sshsql))) {
	//	die;
	//}

	//$dbsql = "select * from test2_dbmaster";
	//if (!($rs3 = mysql_query($dbsql))) {
	//	die;
	//}
	// �� 2020.01.21
	
	// MySQL ���R�[�h�Q��
	// �� 2020.01.21 �ێ���@���ڒǉ�
	//$i = 0;
	//print "հ�ްid�@ײ�ݽNo�@�I�����t�@�X�V���t<BR>";
	//print "հ�ްid�@ײ�ݽNo�@�I�����t�@�X�V���t SSHNo DBNo SSHհ�ް�� SSH�߽ܰ�� DBհ�ް�� DB�߽ܰ�� �F���߽ܰ�� �ײ��Đ� ײ�ݽ�� <BR>";
	//while ($item = mysql_fetch_array($rs)) {
	//	$i++;
	//	print "$i ";
	//	print "${item['id']} ";
	//	print "${item['licenseno']} ";
	//	print "${item['enddate']} ";
	//	print "${item['updatetime']} ";
	//	print "${item['sshno']} ";
	//	print "${item['dbno']} ";
	//	print "${item['sshaccount']} ";
	//	print "${item['sshpassword']} ";
	//	print "${item['dbaccount']} ";
	//	print "${item['dbpassword']} ";
	//	print "${item['authenticationpassword']} ";
	//	print "${item['licensenum']} ";
	//	print "${item['clientnum']}<BR>";
	//}
	// �� 2020.01.21 �ێ���@���ڒǉ�

	
	// MySQL �ؒf
	mysql_close($cn);

	print "Clear";
	//print "<P>�X�V������ɏI�����܂���</P>";

?>