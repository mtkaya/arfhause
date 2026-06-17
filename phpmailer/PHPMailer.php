<?php
/**
 * PHPMailer - Minimal version for SMTP sending
 */

namespace PHPMailer\PHPMailer;

class PHPMailer
{
    public $Host = '';
    public $Port = 587;
    public $SMTPAuth = true;
    public $Username = '';
    public $Password = '';
    public $SMTPSecure = 'tls';
    public $From = '';
    public $FromName = '';
    public $CharSet = 'UTF-8';
    public $Subject = '';
    public $Body = '';
    public $AltBody = '';
    public $isHTML = false;

    protected $to = [];
    protected $replyTo = [];
    protected $ErrorInfo = '';

    public function isSMTP()
    {
        return true;
    }

    public function setFrom($email, $name = '')
    {
        $this->From = $email;
        $this->FromName = $name;
    }

    public function addAddress($email, $name = '')
    {
        $this->to[] = ['email' => $email, 'name' => $name];
    }

    public function addReplyTo($email, $name = '')
    {
        $this->replyTo[] = ['email' => $email, 'name' => $name];
    }

    public function isHTML($isHtml = true)
    {
        $this->isHTML = $isHtml;
    }

    public function send()
    {
        $errno = 0;
        $errstr = '';

        $secure = ($this->SMTPSecure === 'ssl') ? 'ssl://' : '';
        $host = $secure . $this->Host;

        $socket = @fsockopen($host, $this->Port, $errno, $errstr, 30);

        if (!$socket) {
            $this->ErrorInfo = "Connection failed: $errstr ($errno)";
            return false;
        }

        $this->getResponse($socket);

        // EHLO
        if ($this->SMTPSecure === 'tls') {
            fwrite($socket, "EHLO " . $this->Host . "\r\n");
            $this->getResponse($socket);

            fwrite($socket, "STARTTLS\r\n");
            $this->getResponse($socket);

            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        }

        fwrite($socket, "EHLO " . $this->Host . "\r\n");
        $this->getResponse($socket);

        // AUTH LOGIN
        fwrite($socket, "AUTH LOGIN\r\n");
        $this->getResponse($socket);

        fwrite($socket, base64_encode($this->Username) . "\r\n");
        $this->getResponse($socket);

        fwrite($socket, base64_encode($this->Password) . "\r\n");
        $response = $this->getResponse($socket);

        if (strpos($response, '235') === false) {
            $this->ErrorInfo = "Authentication failed: $response";
            fclose($socket);
            return false;
        }

        // MAIL FROM
        fwrite($socket, "MAIL FROM:<{$this->From}>\r\n");
        $this->getResponse($socket);

        // RCPT TO
        foreach ($this->to as $recipient) {
            fwrite($socket, "RCPT TO:<{$recipient['email']}>\r\n");
            $this->getResponse($socket);
        }

        // DATA
        fwrite($socket, "DATA\r\n");
        $this->getResponse($socket);

        // Headers
        $headers = "From: {$this->FromName} <{$this->From}>\r\n";
        $headers .= "To: {$this->to[0]['email']}\r\n";
        $headers .= "Subject: {$this->Subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        if (!empty($this->replyTo)) {
            $headers .= "Reply-To: {$this->replyTo[0]['email']}\r\n";
        }

        if ($this->isHTML) {
            $headers .= "Content-Type: text/html; charset={$this->CharSet}\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset={$this->CharSet}\r\n";
        }

        $headers .= "\r\n";

        // Body
        $message = $headers . $this->Body . "\r\n.\r\n";
        fwrite($socket, $message);
        $this->getResponse($socket);

        // QUIT
        fwrite($socket, "QUIT\r\n");
        fclose($socket);

        return true;
    }

    protected function getResponse($socket)
    {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (substr($line, 3, 1) == ' ') break;
        }
        return $response;
    }

    public function getErrorInfo()
    {
        return $this->ErrorInfo;
    }
}

class Exception extends \Exception {}
class SMTP {}
