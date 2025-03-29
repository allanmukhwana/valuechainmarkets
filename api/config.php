<?php
require_once __DIR__ . '/../vendor/autoload.php';

//Display Errors
ini_set('display_errors', 1);



$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$config = [
    'db' => [
        'host' => $_ENV['DB_HOST'],
        'name' => $_ENV['DB_NAME'],
        'user' => $_ENV['DB_USER'],
        'pass' => $_ENV['DB_PASS']
    ],
    'azure' => [
        'endpoint' => $_ENV['AZURE_OPENAI_ENDPOINT'],
        'key' => $_ENV['AZURE_OPENAI_KEY'],
        'deployment' => $_ENV['AZURE_OPENAI_DEPLOYMENT'],
        'api_version' => $_ENV['AZURE_OPENAI_API_VERSION']
    ]
];

function getDb() {
    global $config;
    
    try {
        $pdo = new PDO(
            "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
            $config['db']['user'],
            $config['db']['pass'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        throw new Exception("Database connection failed: " . $e->getMessage());
    }
}
