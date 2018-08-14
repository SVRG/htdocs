<?php
/**
 * Created by PhpStorm.
 * User: svrg
 * Date: 16/08/17
 * Time: 09:08
 */
include_once 'PHPMailer/PHPMailerAutoload.php';
include_once "class_config.php";

class Mail
{
    public $err = ""; // Ошибки
    private $gmail_login = "";
    private $gmail_pass = "";
    private $from_adress = "";
    private $from_name = "";
    private $to_adress = array();
    public $yandex_login = "";
    public $yandex_pass = "";

    /**
     * Mail constructor.
     */
    public function __construct()
    {
        $this->gmail_login = config::$gmail_login;
        $this->gmail_pass = config::$gmail_pass;
        $this->yandex_login = config::$yandex_login;
        $this->yandex_pass = config::$yandex_pass;
        $this->from_adress = config::$from_address;
        $this->from_name = config::$from_name;
        $this->to_adress = config::$to_adress;
    }
//----------------------------------------------------------------------------------------------------------------------

    /**
     * Отправка писем
     * @param string $Body
     * @param string $subject
     * @return bool
     * @throws phpmailerException
     */
    public function send_mail($Body = '', $subject = "НВС Навигационные Технологии")
    {
        if (isset($this->to_adress)) {
            $cnt = count($this->to_adress);
            if ($cnt == 0) {
                $this->err = "Error: Не удалось получить список адресатов. Проверьте settings.ini";
                return false;
            }
        } else {
            $this->err = "Error: Не удалось получить список адресатов. Проверьте settings.ini";
            return false;
        }

        //Create a new PHPMailer instance
        $mail = new PHPMailer;

        // Set UTF-8!
        $mail->CharSet = 'UTF-8';

        //Tell PHPMailer to use SMTP
        $mail->isSMTP();

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;

        //Ask for HTML-friendly debug output
        $mail->Debugoutput = 'html';

        //Set the hostname of the mail server
        //$mail->Host = 'smtp.gmail.com';
        $mail->Host = 'smtp.yandex.ru';


        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 465;

        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = 'ssl';

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        //$mail->Username = $this->gmail_login;
        $mail->Username = $this->yandex_login;

        //Password to use for SMTP authentication
        //$mail->Password = $this->gmail_pass;
        $mail->Password = $this->yandex_pass;

        //Set who the message is to be sent from
        try {
            $mail->setFrom($this->from_adress, $this->from_name);
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }

        //Set who the message is to be sent to
        for ($i = 0; $i < $cnt; $i++)
            $mail->addAddress($this->to_adress[$i]);

        //Set the subject line
        $mail->Subject = $subject;

        //Set an HTML message body
        $mail->msgHTML("<html><body>$Body</body></html>");

        //Replace the plain text body with one created manually
        $mail->AltBody = 'This is a plain-text message body';

        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');

        //send the message, check for errors
        if (!$mail->send()) {
            $this->err = "Mailer Error: " . $mail->ErrorInfo;
            $err_log = $this->err;
            $user = func::user();
            // Запись ошибок в log
            $db = new Db();
            $err_log = $db->real_escape_string($err_log);
            $db->query(/** @lang MySQL */
                "INSERT INTO log (log, user) VALUES ('$err_log','$user')");
            return false;
        } else {
            return true;
        }
    }
}