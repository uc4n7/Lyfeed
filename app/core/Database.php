<?php
// One shared PDO connection for all models.
class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $cfg = require BASE_PATH . '/config/database.php';
            $db  = $cfg['db'];
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";

            try {
                self::$pdo = new PDO($dsn, $db['user'], $db['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

                // Keep MySQL timestamps in the same timezone as PHP.
                $tzOffset = (new DateTime('now', new DateTimeZone(config('app.timezone', 'Asia/Manila'))))->format('P');
                $tzStmt = self::$pdo->prepare("SET time_zone = ?");
                $tzStmt->execute([$tzOffset]);
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Database connection failed. Check config/database.php and make sure the social_app database is imported.');
            }
        }
        return self::$pdo;
    }
}
