<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerApp
{
    /**
     * @var PHPMailer
     */
    private $mail;

    private $config;

    private $currMessage;

    private $to;

    private $subject;

    public function __construct($config)
    {
        $this->config = $config;
        $this->mail = new PHPMailer(true);
    }

    public function createMessageFromView($path,$action,$params){
        $view = Phalcon\DI::getDefault()->getView();
        //$this->currMessage = $view->render($path, $params);
        ob_start();
        $view->partial($path, $params);
        $this->currMessage = ob_get_clean();
        return $this;
    }

    public function getMessage(){
        return $this->currMessage;
    }

    public function to($to){
        $this->to = $to;
        return $this;
    }

    public function subject($subject){
        $this->subject = $subject;
        return $this;
    }

    public function send(){
        try {
            //Server settings
            $this->mail->SMTPDebug = 2;                                 // Enable verbose debug output
            $this->mail->isSMTP();                                      // Set mailer to use SMTP
            $this->mail->Host = $this->config['host'];  // Specify main and backup SMTP servers
            $this->mail->SMTPAuth = true;                               // Enable SMTP authentication
            $this->mail->Username = $this->config['username'];                 // SMTP username
            $this->mail->Password = $this->config['password'];                           // SMTP password
            $this->mail->SMTPSecure = $this->config['encryption'];                            // Enable TLS encryption, `ssl` also accepted
            $this->mail->Port = $this->config['port'];

            //Recipients
            $this->mail->setFrom($this->config['from']['email'], $this->config['from']['name']);
            $this->mail->addAddress($this->to);

            $this->mail->isHTML(true);
            $this->mail->Subject =  $this->subject;
            $this->mail->Body    = $this->currMessage;
            $this->mail->AltBody = $this->currMessage;

            $this->mail->send();

            return true;
        } catch (Exception $e) {
            return 'Message could not be sent. Mailer Error: '. $this->mail->ErrorInfo;
        }
    }
}