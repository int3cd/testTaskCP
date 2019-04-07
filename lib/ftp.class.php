<?
namespace FileTransfer;

class Ftp
{
    protected $connection,
              $connectionFlag = false;

    public function __construct()
    {
        if (!$this->connection && $this->connectionFlag == true)
            throw new \Exception('Соединение потеряно');
    }

    public function __call($method, $parameters)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $parameters);
        } else {
            throw new \Exception('Метода ' . $method . ' не существует');
        }
    }

    /**
     * @param string $user
     * @param string $password
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @return bool
     * @throws \Exception
     */
    public function init($user = '', $password = '', $host = '', $port = 21, $ssl = false)
    {
        $this->connect($host, $port, $ssl);
        $this->login($user, $password);
	    \ftp_pasv($this->connection, true);

        $this->connectionFlag = true;

        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @throws \Exception
     */
    private function connect($host = '', $port = 21, $ssl = false)
    {
        if ($ssl) {
            $this->connection = ftp_ssl_connect($host, $port);
        } else {
            $this->connection = ftp_connect($host, $port);
        }

        if (!$this->connection)
            throw new \Exception('Не удалось установить соединение: ' . $host);
    }

    /**
     * @param string $user
     * @param string $password
     * @throws \Exception
     */
    private function login($user = '', $password = '')
    {
        $result = ftp_login($this->connection, $user, $password);
        if ($result === false)
            throw new \Exception('Не удалось войти под именем: ' . $user);
    }

    /**
     * @throws \Exception
     */
    public function close()
    {
        ftp_close($this->connection);
        $this->streamFlag = false;
    }

    /**
     * @return string
     */
    public function pwd()
    {
        $result = ftp_pwd($this->connection);
        return $result;
    }

    /**
     * @param string $newDir
     * @return bool
     * @throws \Exception
     */
    public function cd($newDir = '')
    {
        if (!$newDir) throw new \Exception('Укажите новую директорию');

        $result = ftp_chdir($this->connection, $newDir);
        if ($result === false)
            throw new \Exception('Не удалось сменить директорию: ' . $newDir);

        return $result;
    }

	/**
	 * @param string $fileTo
	 * @param string $fileFrom
	 * @return $this
	 * @throws \Exception
	 */
    public function upload($fileFrom = '', $fileTo = '')
    {
        if (!$fileFrom) throw new \Exception('Укажите файл');
		if (!$fileTo) $fileTo = $fileFrom;

        $result = ftp_put($this->connection, $fileFrom,  $fileTo, FTP_ASCII);
        if ($result === false)
            throw new \Exception('Ошибка загрузки файла: ' . $fileTo);

        return $this;
    }

    /**
     * @param string $command
     * @return bool
     * @throws \Exception
     */
    public function exec($command = '')
    {
        if (!$command) throw new \Exception('Укажите комнду');

        //$result = \ftp_exec($this->stream, $command);
        $command = 'cd ' . $_SERVER['DOCUMENT_ROOT'] . $this->pwd() . ';' . $command;
        $result = shell_exec($command);
        if ($result === false)
            throw new \Exception('Не удалось выполнить команду: ' . $command);

        return $result;
    }
}