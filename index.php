<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include 'lib/factory.class.php';

use FileTransfer as FT;

$factory = new FT\Factory();

try {
	$conn = $factory->getConnection('ssh', 'root', 'M37iVxbKLF', '37.143.8.243', 22)
		->cd('/home/test.in3cd.ru/www/uploads')
        ->download('somefile.txt')
        ->close();
} catch (Exception $e) {
	echo $e->getMessage();
}

try {
    $conn = $factory->getConnection('ftp', 'www-data', 'A123456bc', '37.143.8.243', 21, false);
    $conn->cd('new');
    echo $conn->pwd() . PHP_EOL;
    $conn->upload('somefile.txt');
    FT\Factory::printr($conn->exec('ls -al'));
    $conn->close();
} catch (Exception $e) {
    echo $e->getMessage();
}
