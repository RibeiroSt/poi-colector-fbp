<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 15/01/2019
 * Time: 16:28
 */

class LogDAO
{
    private $pdo;
    private $table_name;
    private $seq_name;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->table_name = Params::DB_PARAMS[Params::LOG_KEY]['table_name'];
        $this->seq_name = Params::DB_PARAMS[Params::LOG_KEY]['seq_name'];
    }

    public function persist()
    {
        $log = Log::getLog();
        $main_sql = $this->generateMainSQL();
        $lines_sql = $this->generateRowsSQL($log);

        $sql = $main_sql . PHP_EOL . $lines_sql;
        $this->execSQL($sql);
    }

    private function getTableFilds()
    {
        return implode(', ', Params::DB_PARAMS[Params::LOG_KEY]['table_fields']);
    }

    private function generateMainSQL()
    {
        $table_fields = $this->getTableFilds();

        return 'INSERT INTO ' . $this->table_name . '(' . $table_fields . ') VALUES ';
    }

    private function generateRowsSQL($log)
    {
        $fields_pattern = Params::DB_PARAMS[Params::LOG_KEY]['table_fields'];

        $lines = [];
        foreach ($log as $data) {

            $values = [];
            foreach ($fields_pattern as $field) {

                $values[] = $this->sanitizeValue($data, $field, $values);
            }
            $lines[] = '(' . implode(', ', $values) . ')';
        }
        return implode(', ' . PHP_EOL, $lines);
    }

    private function execSQL($sql)
    {
        try {
            Utils::execSQL(
                $this->pdo,
                $sql,
                $this->table_name,
                $this->seq_name,
                false,
                [],
                false,
                false,
                false
            );
        } catch (PDOException $e) {

            $error = $e->getMessage();
            $error .= $e->getTraceAsString();
            $error .= $e->getFile();
            $error .= PHP_EOL;
            $error .= 'SQL: ' . PHP_EOL . $sql . PHP_EOL;

            Utils::printError($error, 'Error', false);
        }
    }

    private function sanitizeValue($data, $field, $values)
    {
        $val = (!empty($data[$field])) ? $data[$field] : 'null';
        return ('\'' . str_replace('\'', '', $val) . '\'');
    }
}