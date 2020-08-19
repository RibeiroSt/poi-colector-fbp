<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 07/01/2019
 * Time: 16:43
 */

class PlacesDAO
{
    private $pdo = null;
    private $table_fields = [];
    private $table_name = '';
    private $seq_name = '';
    private $control_fields = [];
    private $error = 'Error: ';

    public function __construct($pdo)
    {
        $this->pdo =$pdo;
        $this->table_name = Params::DB_PARAMS[Params::PLACE_SEARCH_KEY]['table_name'];
        $this->seq_name = Params::DB_PARAMS[Params::PLACE_SEARCH_KEY]['seq_name'];
    }

    public function insert(array $data, array $control_fields)
    {
        $this->control_fields = $control_fields;

        $dvalues = $this->normalizeData($data);
        $sql = $this->generateInsertSQL($dvalues);

        try {
            Utils::execSQL(
                $this->pdo,
                $sql,
                $this->table_name,
                $this->seq_name,
                false,
                [],
                true,
                false
            );
            return true;
        } catch (PDOException $e) {

            $error  = $e->getMessage();
            $error .= $e->getTraceAsString();
            $error .= $e->getFile();
            $error .= PHP_EOL;
            $error .= 'SQL: ' . PHP_EOL . $sql . PHP_EOL;

            $this->setError($error);
            return false;
        }
    }

    private function generateInsertSQL($dvalues)
    {
        $sql_main_row = $this->generateMainSQL();
        $sql_rows = $this->generateInsertSqlRows($dvalues);

        return $sql_main_row . $sql_rows . ';';
    }

    private function generateMainSQL()
    {
        $str_table_fields = $this->getTableFields();
        $str_table_name = Params::DB_PARAMS[Params::PLACE_SEARCH_KEY]['table_name'];

        return 'INSERT INTO ' . $str_table_name . '(' . $str_table_fields . ') VALUES ' . PHP_EOL;
    }

    private function generateInsertSqlRows($data)
    {
        $lines = [];
        foreach ($data as $value) {

            $line = [];
            foreach ($this->table_fields as $field) {

                $str = str_replace('\'', '"', $value[$field]);
                $line[] = (!empty($value[$field])) ? ('\'' . $str . '\'') : 'null';
            }
            $lines[] = '(' . implode(', ', $line) . ')';
        }
        return implode(', ' . PHP_EOL, $lines);
    }

    private function normalizeData($data)
    {
        $places = [];
        foreach ($data as $key => $value) {

            $place = [];
            foreach ($value as $pkey => $pinfo) {

                if (!is_array($pinfo)) {

                    $place[$pkey] = $pinfo;
                } else {

                    switch ($pkey) {
                        case Params::ESPEC_FLD_HOURS:
                            $place[Params::ESPEC_FLD_HOURS] = json_encode($pinfo);
                            break;

                        case Params::ESPEC_FLD_CATEGORY_LIST:
                            $place[Params::ESPEC_FLD_CATEGORY_LIST] = json_encode($pinfo);
                            break;

                        case Params::ESPEC_FLD_RESTAURANT_SPECIALITIES:
                            $place[Params::ESPEC_FLD_RESTAURANT_SPECIALITIES] = json_encode($pinfo);
                            break;

                        case Params::ESPEC_FLD_LOCATION || Params::ESPEC_FLD_RESTAURANT_SERVICES:
                            foreach ($pinfo as $ikey => $info) {
                                $index = 'espec_fld_' . $ikey;
                                $place[$index] = $info;
                            }
                            break;
                    }
                }
            }
            // WARNING:: order is essential here!
            $places[] = array_merge($place, $this->control_fields);
        }
        return $places;
    }

    private function getTableFields()
    {
        return implode(', ', $this->getTableFieldsArray());
    }

    private function getTableFieldsArray()
    {
        $main_fields = Params::REQUEST_PARAMS[Params::PLACE_SEARCH_KEY][Params::FIELDS_KEY];

        foreach ($main_fields as $key => $main_field) {

            if (in_array($main_field, array_keys(Params::ESPECIAL_FIELDS))) {
                unset($main_fields[$key]);
            }
        }
        $this->table_fields = array_values(
           array_merge(
               $main_fields,
               Params::ESPECIAL_FIELDS[Params::ESPEC_FLD_LOCATION],
               Params::ESPECIAL_FIELDS[Params::ESPEC_FLD_RESTAURANT_SERVICES],
               Params::ESPECIAL_FIELDS[Params::ESPEC_FLD_RESTAURANT_SPECIALITIES],
               array_keys($this->control_fields)
           )
        );
        return $this->table_fields;
    }


    public function setError($error)
    {
        $this->error .= PHP_EOL . $error;
    }

    public function getError()
    {
        return $this->error;
    }
}