<?

//header('Content-Type: text/plain;');
//error_reporting(E_ALL ^ E_WARNING);
ob_implicit_flush();

class SendMail extends MNG{
	var $address;
	var $port;
	var $login;
	var $pwd;
	var $socket;
	var $condata;
	var $logs;
	function SendMail($condata='info'){
		$this->inc();
	}
	
	function qSend($to, $from, $subject, $message){
		$this->Connect();
		$this->smtpSend($to, $from, $subject, $message);
		$this->Disconnect();
	}
	
	function Connect($p=NULL){
		if($p){
			if(is_array($p))$p_arr = $p;
			else $p_arr = $this->CFG->AuthMail[$p];
		} else {
			$p_arr = $this->CFG->AuthMail['info'];
		}
		
		$this->address 	= $p_arr['address'];	// адрес smtp-сервера
		$this->port 	= $p_arr['port'];		// порт (стандартный smtp - 25)
		$this->login 	= $p_arr['login'];		// логин к ящику
		$this->pwd 		= $p_arr['pwd'];		// пароль к ящику
		
		try {
			// Создаем сокет
			$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			if ($socket < 0) {
				throw new Exception('socket_create() failed: '.socket_strerror(socket_last_error())."\n");
			}
	
			// Соединяем сокет к серверу
			$this->logs.= 'Connect to \''.$this->address.':'.$this->port.'\' ... ';
			$result = socket_connect($this->socket, $this->address, $this->port);
			if ($result === false) {
				throw new Exception('socket_connect() failed: '.socket_strerror(socket_last_error())."\n");
			} else {
				$this->logs.= "OK\n";
			}
		   
			// Читаем информацию о сервере
			$this->read_smtp_answer($this->socket);
		   
			// Приветствуем сервер

			$this->write_smtp_response($this->socket, 'EHLO '.$this->address);
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			$this->logs.= 'Authentication ... ';
			   
			// Делаем запрос авторизации
			$this->write_smtp_response($this->socket, 'AUTH LOGIN');
			$this->read_smtp_answer($this->socket); // ответ сервера
			
			// Отравляем логин
			$this->write_smtp_response($this->socket, base64_encode($this->login));
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			// Отравляем пароль
			$this->write_smtp_response($this->socket, base64_encode($this->pwd));
			$this->read_smtp_answer($this->socket); // ответ сервера
			$this->logs.= "OK\n";
		} catch (Exception $e) {
			$this->logs.= "\nError: ".$e->getMessage();
		}
		
	}
	
	function Disconnect(){
		if (isset($this->socket)) {
			$this->logs.= 'Close connection ... ';
		   
			// Отсоединяемся от сервера
			$this->write_smtp_response($this->socket, 'QUIT');
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			$this->logs.= "OK\n";
			
			socket_close($this->socket);			
		}
	}
	
	
	function smtpSend($to, $from, $subject, $message){
		//print "$to, $from, $subject, $message";
		//if(!isset($this->socket))$this->Connect();
		try {

			$this->logs.= "Check sender address ... ";
		   
			// Задаем адрес отправителя
			$this->write_smtp_response($this->socket, 'MAIL FROM:<'.$from.'>');
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			$this->logs.= "OK\n";
			$this->logs.= "Check recipient address ... ";
		   
			// Задаем адрес получателя
			$this->write_smtp_response($this->socket, 'RCPT TO:<'.$to.'>');
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			$this->logs.= "OK\n";
			$this->logs.= "Send message text ... ";
		   
			// Готовим сервер к приему данных
			$this->write_smtp_response($this->socket, 'DATA');
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			// Отправляем данные
			$message = "To: $to\r\n".$message; // добавляем заголовок сообщения "адрес получателя"
			$message = "Subject: $subject\r\n".$message; // заголовок "тема сообщения"
			$message = "From: $from\r\n".$message; // заголовок "тема сообщения"
			$message = "Content-Type:text/html; charset=utf-8\r\n".$message; // заголовок "тема сообщения"
			
			//Content-Type:text/html; charset=windows-1251\r\n
			
			$this->write_smtp_response($this->socket, $message."\r\n.");
			$this->read_smtp_answer($this->socket); // ответ сервера
		   
			$this->logs.= "OK\n";
			
		   
		} catch (Exception $e) {
			$this->logs.= "\nError: ".$e->getMessage();
		}
	    //if(isset($this->socket))$this->Disconnect();
	   
	}

    // Функция для чтения ответа сервера. Выбрасывает исключение в случае ошибки
    function read_smtp_answer($socket) {
        $read = socket_read($socket, 1024);
		
        if ($read{0} != '2' && $read{0} != '3') {
            if (!empty($read)) {
                throw new Exception('SMTP failed: '.$read."\n");
            } else {
                throw new Exception('Unknown error'."\n");
            }
        }
    }
   
    // Функция для отправки запроса серверу
    function write_smtp_response($socket, $msg) {
		//$this->logs.= "\n[$msg]\n";
        $msg = $msg."\r\n";
		//print "$socket, $msg, ".strlen($msg);
        socket_write($socket, $msg, strlen($msg));
    }
}

//$m = new SendMail();
//$m->qSend('oleksadesign@gmail.com', 'forum@djerelo.info', 'You have a message in Djerelo Forum', 'this is test message');

?>