<?

namespace FileTransfer;

class Sftp
{
    protected $connection,
              $connectionFlag = false,
              $bufferSshDir;

    public function __construct()
    {
        if (!$this->connection && $this->connectionFlag == true)
            throw new \Exception('Соединение не найдено');
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
    public function init($user = '', $password = '', $host = '', $port = 22, $ssl = false)
    {
        $this->connect($host, $port);
        $this->login($user, $password);

        $this->connectionFlag = true;

        return $this;
    }

    /**
     * @param string $host
     * @param int $port
     * @throws \Exception
     */
    private function connect($host = '', $port = 22)
    {
        $this->connection = ssh2_connect($host, $port);

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
        $result = ssh2_auth_password($this->connection, $user, $password);
        if ($result === false)
            throw new \Exception('Не удалось войти под именем: ' . $user);
    }

    /**
     * @throws \Exception
     */
    public function close()
    {
        ssh2_disconnect($this->connection);
        $this->connectionFlag = false;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function pwd()
    {
        $addCommand = $this->bufferSshDir ? 'cd ' . $this->bufferSshDir . ';' : '';
        $result = $this->cmd($addCommand . 'pwd');
        return trim($result);
    }

    /**
     * @param string $newDir
     * @return $this
     * @throws \Exception
     */
    public function cd($newDir = '')
    {
        if (!$newDir) throw new \Exception('Укажите новую директорию');

        $this->cmd('cd ' . $newDir);

        $firstChar = mb_substr($newDir, 0, 1);
        if ($firstChar == '/') {
            $this->bufferSshDir = $newDir;
        } else {
            $this->bufferSshDir = $this->bufferSshDir . '/' . $newDir;
        }

        return $this;
    }

    /**
     * @param $command
     * @return string
     * @throws \Exception
     */
    private function cmd($command)
    {
        $stream = ssh2_exec($this->connection, $command);
        $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

        stream_set_blocking($errorStream, true);
        if ($error = stream_get_contents($errorStream)) throw new \Exception($error);

        stream_set_blocking($stream, true);
        $streamOut = ssh2_fetch_stream($stream, SSH2_STREAM_STDIO);

        $result = stream_get_contents($streamOut);

        return $result;
    }

    /**
     * @param string $remoteFile
     * @param string $localeFile
     * @return $this
     * @throws \Exception
     */
    public function download($remoteFile = '', $localeFile = '')
    {
        if (!$remoteFile) throw new \Exception('Укажите название файла');

        $localeFile = $localeFile ? $localeFile : $remoteFile;

        $curDir = $this->pwd();

        $patchRemoteFile = $curDir . '/' . $remoteFile;
        $patchLocaleFile = $_SERVER['DOCUMENT_ROOT'] . '/' . $localeFile;

        if (!is_file($patchRemoteFile)) throw new \Exception('Удаленный файл не найден');
        if (is_file($patchLocaleFile)) throw new \Exception('Локальный файл уже существует');

        ssh2_scp_recv($this->connection, $patchRemoteFile, $patchLocaleFile);

        return $this;
    }
}