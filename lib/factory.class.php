<?

namespace FileTransfer;

class Factory
{
    /**
     * @param string $type
     * @param string $user
     * @param string $password
     * @param string $host
     * @param int $port
     * @param bool $ssl
     * @return bool|string
     * @throws \Exception
     */
    public function getConnection($type = '', $user = '', $password = '', $host = '', $port = 21, $ssl = false)
    {
        switch ($type) {
            case 'ftp':
                include 'ftp.class.php';

                $factory = new \FileTransfer\Ftp();
                $conn = $factory->init($user, $password, $host, $port, $ssl);
                break;
            case 'ssh':
                include 'sftp.class.php';

                $factory = new \FileTransfer\Sftp();
                $conn = $factory->init($user, $password, $host, $port);
                break;
            case 'default':
                $conn = '';
        }

        if (!$factory)
            throw new \Exception('Тип соединение не определен. Пожалуйста, укажите тип соединения. (ftp || ssh)');

        return $conn;
    }

    /**
     * @param array $array
     */
    public static function printr($array = [])
    {
        echo '<pre>';
        print_r($array);
        echo '</pre>';
    }
}