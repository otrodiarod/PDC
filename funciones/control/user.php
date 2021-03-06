<?php 
	include '../generic/Clsconnection.php';
    include '../model/Modeluser.php';

	if($_POST['action']){
        $dataUser = $_POST['data'];
        $objUser = new Modeluser();
        
		switch ($_POST['action']) {
            case 'login':                
                $result = $objUser->login($dataUser);				
                print_r(json_encode($result));				
                break;
			case 'recordUser':
                $pass = password_hash($dataUser['pwd'],PASSWORD_DEFAULT);
                $miIp = getRealIP();
                $result = $objUser->recordUser($dataUser,$miIp,$pass);
                                
                $bodyMail = 'Gracias por registrarte con nosotros';
                $mail = sendEmail($result['data']['email'],$result['data']['name'],'Registro exitoso',$bodyMail);
                
                print_r(json_encode($result));
                                
				break;
            case 'forgotpass':
                $result = $objUser->forgotPass($dataUser);
                
                $bodyMail = 'Su nueva contraseña es: '.$result['newPass'];
                $mail = sendEmail($dataUser['emailPass'],'','Recordar contraseña',$bodyMail);
                
                print_r(json_encode($result));
                break;
            case 'saveData':
                $result = $objUser->saveData($dataUser,$_POST['info']);
                print_r(json_encode($result));
                break;
            case 'getDataPages':
                $result = $objUser->getData($dataUser);
                print_r(json_encode($result));
                break;
            case 'saveImg':
                $dataUser = json_decode($_POST['data']);
                                
                $temp = explode(".", $_FILES["file"]["name"]);
                
                $newfilename = round(microtime(true)) . '.' . end($temp);                
                
                $uploaddir = __DIR__.'/../../img/img_users/';
                $uploadfile = $uploaddir.basename($newfilename);
                                
                if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)){
                    $result = $objUser->saveImage($newfilename,$dataUser,$_POST['field']);
                    print_r(json_encode($result));
                }else{
                    print_r(json_encode(array('data'=>false,msg=>'img not upload')));
                }                
                break;                
		}
	}

	function sendEmail($email,$name,$subject,$body){
		require_once('../phpmailer/PHPMailerAutoload.php');
		//include("class.smtp.php"); // optional, gets called from within class.phpmailer.php if not already loaded

		$mail = new PHPMailer;
		//$mail->SMTPDebug = 3;                               // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'smtp.arkix.com';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'aplicaciones@arkixmailer.com';                 // SMTP username
		$mail->Password = 'D4b4rk2019$$%!';                           // SMTP password
		//$mail->Port = 26;                                    // TCP port to connect to 

		$mail->setFrom('davinson.anaya@arkix.com', 'Información Puntos de contacto');
		$mail->addAddress($email, $name);     // Add a recipient
		
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->CharSet = 'UTF-8';
		$mail->Subject = $subject;
		$mail->Body    = $body;
		//$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

		if(!$mail->send()) {
			return array('Error'=> $mail->ErrorInfo,'status'=>0);		    
		} else {
		    return array('status'=>1);
		}
	}

	function getRealIP() {
		if (!empty($_SERVER['HTTP_CLIENT_IP']))
			return $_SERVER['HTTP_CLIENT_IP'];
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		return $_SERVER['REMOTE_ADDR'];
	}
?>