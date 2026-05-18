<?php

    class Database {
        private ?PDO $connection = null;
        private array $env = []; //Guarda las variables de la base de datos

        public function __construct() //Funcion que se ejecuta al crear una instancia de la clase
        {
            $this->loadEnv(); //Carga las variables de la base de datos antes de intentar la conexion
        }

        public function getConnection(): PDO //Funcion que devuelve la conexion a la base de datos
        {
            if ($this->connection !== null) { //Si la conexion ya existe, la devuelve
                return $this->connection; //Devuelve la conexion a la base de datos
            }

            $host = getenv('DB_HOST') ?: ($this->env['DB_HOST'] ?? 'localhost'); //Host de la base de datos
            $dbName = getenv('DB_NAME') ?: ($this->env['DB_NAME'] ?? ''); //Nombre de la base de datos
            $user = getenv('DB_USER') ?: ($this->env['DB_USER'] ?? ''); //Usuario de la base de datos
            $pass = getenv('DB_PASS') ?: ($this->env['DB_PASS'] ?? ''); //Contraseña de la base de datos
            //DSN (Data Source Name) es una cadena de caracteres que contiene la informacion necesaria para conectar a la base de datos
            $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4"; 

            //PDO (PHP Data Objects) es una clase que permite conectar a la base de datos
            $this->connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //Si hay un error, se lanza una excepcion
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //Devuelve los resultados como un array asociativo
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4", //Establece el conjunto de caracteres de la conexion
            ]);

            return $this->connection; //Devuelve la conexion a la base de datos
        }

        private function loadEnv(): void //Funcion que carga las variables de la base de datos
        {
            $envPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env'; //Ruta del archivo .env

            if (!file_exists($envPath)) { //Si el archivo .env no existe, devuelve null
                return; //Devuelve null
            }

            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES); //Lee el archivo .env y devuelve un array con las lineas

            if ($lines === false) { //Si el array es false, devuelve null
                return;
            }

            foreach ($lines as $line) { //Recorre el array y asigna las variables de la base de datos
                $line = trim($line); //Elimina los espacios en blanco al principio y al final de la linea

                if ($line === '' || str_starts_with($line, '#')) { //Si la linea esta vacia o comienza con #, continua
                    continue;
                }

                $parts = explode('=', $line, 2); //Separa la linea en dos partes, la clave y el valor

                if (count($parts) !== 2) { //Si el array no tiene dos partes, continua
                    continue;
                }

                $key = trim($parts[0]); //Elimina los espacios en blanco al principio y al final de la clave
                $value = trim($parts[1]); //Elimina los espacios en blanco al principio y al final del valor

                $value = trim($value, "\"'"); //Elimina las comillas al principio y al final del valor

                $this->env[$key] = $value; //Asigna la clave y el valor al array $env
                $_ENV[$key] = $value; //Asigna la clave y el valor a la variable $_ENV
                putenv($key . '=' . $value); //Asigna la clave y el valor a la variable de entorno
            }
        }
    }
?>