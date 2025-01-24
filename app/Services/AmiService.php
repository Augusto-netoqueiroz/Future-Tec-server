<?php

namespace App\Services;

class AmiService
{
    private $host;
    private $port;
    private $username;
    private $password;
    private $socket;

    public function __construct($host = '127.0.0.1', $port = 5038, $username = 'admin', $password = 'MKsx2377!@')
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function connect()
    {
        $this->socket = fsockopen($this->host, $this->port, $errno, $errstr, 30);

        if (!$this->socket) {
            throw new \Exception("Erro ao conectar ao AMI: $errstr ($errno)");
        }

        $this->login();
    }

    private function login()
    {
        $this->send("Action: Login\r\nUsername: {$this->username}\r\nSecret: {$this->password}\r\n\r\n");

        $response = $this->read();
        if (strpos($response, 'Success') === false) {
            throw new \Exception("Erro ao fazer login no AMI: $response");
        }
    }

    public function sendCommand($command)
    {
        $this->send("Action: Command\r\nCommand: {$command}\r\n\r\n");
        return $this->read();
    }

    private function send($data)
    {
        fwrite($this->socket, $data);
    }

    private function read()
    {
        $response = '';
        while (!feof($this->socket)) {
            $line = fgets($this->socket, 4096);
            $response .= $line;

            if (trim($line) === '') {
                break;
            }
        }

        return $response;
    }

    public function disconnect()
    {
        $this->send("Action: Logoff\r\n\r\n");
        fclose($this->socket);
    }
}
