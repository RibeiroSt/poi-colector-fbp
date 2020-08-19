<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 10/01/2019
 * Time: 16:42
 */

class CoordsDAO
{
    private $pdo = null;
    private $coords_to_add = '';
    private $table_name = '';
    private $control_field_name = '';
    private $pristine_field_name = '';
    private $last_update_field_name = '';
    private $seq_name = '';

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->table_name = Params::DB_PARAMS[Params::COORDS_KEY]['table_name'];
        $this->seq_name = Params::DB_PARAMS[Params::COORDS_KEY]['seq_name'];
        $this->control_field_name = Params::CONTROL_FIELD_KEY;
        $this->pristine_field_name = Params::PRISTINE_KEY;
        $this->last_update_field_name = Params::LAST_UPDATE_KEY;
    }

    public function isEmptyTable()
    {
        $sql = 'SELECT COUNT(0) AS qtd FROM ' . $this->table_name;
        $res = $this->execSQL($sql, true);

        return ($res['qtd'] == 0);
    }

    public function getNextCoord()
    {
        if ($this->isEmptyTable()) {

            Utils::printError("Coordinates table is empty.");
        }
        $sql  = ' SELECT ' . $this->getTableFilds() . ' FROM ' . $this->table_name;
        $sql .= ' WHERE ' . $this->pristine_field_name . ' = true ';
        $sql .= ' AND ' . $this->control_field_name . ' = false ';
        $sql .= ' ORDER BY id ASC ';
        $sql .= ' LIMIT 1 ';

        $res = $this->execSQL($sql, true);

        if (empty($res[Params::LATITUDE_KEY]) || empty($res[Params::LONGITUDE_KEY])
            || empty($res[Params::GEOM_POINT_KEY]) || empty($res[Params::COORDS_ID_KEY])) {

            Utils::printError("An empty result was returned when trying to get the coordinates from database.", 'Error', false);
        }

        if(!$this->updateCoordinates(
            $res[Params::LATITUDE_KEY],
            $res[Params::LONGITUDE_KEY],
            $res[Params::GEOM_POINT_KEY],
            $res[Params::COORDS_ID_KEY])) {

            Utils::printError("The system has failed when trying to block the coordinates into database.");
        }

        $coords = [
            Params::LATITUDE_KEY => $res[Params::LATITUDE_KEY],
            Params::LONGITUDE_KEY => $res[Params::LONGITUDE_KEY],
            Params::GEOM_POINT_KEY => $res[Params::GEOM_POINT_KEY],
            Params::COORDS_ID_KEY => $res[Params::COORDS_ID_KEY],
        ];
        Registry::setCoords($coords);
        Utils::printInfo($coords);
        return $coords;
    }

    public function updateCoordinates($latitude, $longitude, $geom, $id, $block = true, $pristine = null)
    {
        $status_var = ':' . $this->control_field_name;
        $lat_var = ':' . Params::LATITUDE_KEY;
        $long_var = ':' . Params::LONGITUDE_KEY;
        $geom_var = ':' . Params::GEOM_POINT_KEY;
        $id_var = ':' . Params::COORDS_ID_KEY;
        $params = [
            $status_var => (int) $block,
            $lat_var => (double) $latitude,
            $long_var => (double) $longitude,
            $geom_var => $geom,
            $id_var => $id,
        ];

        $upd  = ' UPDATE ' . $this->table_name;
        $upd .= ' SET ' . $this->control_field_name . ' = ' . $status_var;
        $upd .= ' , ' . $this->last_update_field_name . ' = \'' . $this->getDateTimeAsString() . '\'';
        $wre  = ' WHERE ' . Params::LATITUDE_KEY . ' = ' . $lat_var;
        $wre .= ' AND ' . Params::LONGITUDE_KEY . ' = ' . $long_var;
        $wre .= ' AND ' . Params::GEOM_POINT_KEY . ' = ' . $geom_var;
        $wre .= ' AND ' . Params::COORDS_ID_KEY . ' = ' . $id_var;
        // $wre .= ' AND ' . $this->control_field_name . ' <> ' . $status_var;

        if ($pristine === true || $pristine === false) {

            $pristine_var = ':' . Params::PRISTINE_KEY;
            $upd .= ', ' . Params::PRISTINE_KEY . ' = ' . $pristine_var;
            // $wre .= ' AND ' . $this->pristine_field_name . ' <> ' . $pristine_var;

            $params[$pristine_var] = (int) $pristine;
        }
        $sql = $upd . $wre;
        $this->execSQL($sql, false, $params);

        return $this->isCoordinatesOk($latitude, $longitude, $geom, $id, $block, $pristine);
    }

    private function isCoordinatesOk($latitude, $longitude, $geom, $id, $block = true, $pristine = null)
    {
        $lat_var = ':' . Params::LATITUDE_KEY;
        $long_var = ':' . Params::LONGITUDE_KEY;
        $geom_var = ':' . Params::GEOM_POINT_KEY;
        $id_var = ':' . Params::COORDS_ID_KEY;

        $sql  = ' SELECT ' . $this->control_field_name . ', ' . $this->pristine_field_name . ' FROM ' . $this->table_name;
        $sql .= ' WHERE ' . Params::LATITUDE_KEY . ' = ' . $lat_var;
        $sql .= ' AND ' . Params::LONGITUDE_KEY . ' = ' . $long_var;
        $sql .= ' AND ' . Params::GEOM_POINT_KEY . ' = ' . $geom_var;
        $sql .= ' AND ' . Params::COORDS_ID_KEY . ' = ' . $id_var;
        $sql .= ' LIMIT 1 ';
        $params = [
            $lat_var => $latitude,
            $long_var => $longitude,
            $geom_var => $geom,
            $id_var => $id,
        ];
        $res = $this->execSQL($sql, true, $params);

        $isOk = ($pristine === true || $pristine === false)
                 ? (($res[$this->pristine_field_name] == $pristine) && ($res[$this->control_field_name] == $block))
                 : ($res[$this->control_field_name] == $block);

        return $isOk;
    }

    public function persist()
    {
        $this->coords_to_add .= ';';
        $this->execSQL($this->coords_to_add, false, [], true);
    }

    private function execSQL($sql, $fetch = false, $params = [], $print_info = false)
    {
        try {
            return Utils::execSQL(
                $this->pdo,
                $sql,
                $this->table_name,
                $this->seq_name,
                $fetch,
                $params,
                $print_info,
                true,
                true
            );

        } catch (PDOException $e) {

            $error  = $e->getMessage();
            $error .= $e->getTraceAsString();
            $error .= $e->getFile();
            $error .= PHP_EOL;
            $error .= 'SQL: ' . PHP_EOL . $sql . PHP_EOL;
            $error .= 'PARAMS ' . PHP_EOL . print_r($params, true) . PHP_EOL;

            Utils::printError($error, 'Error', true);
        }
    }

    public function addCoord($lat, $long)
    {
        if ($this->coords_to_add === '') {

            $table_name = $seq_name = Params::DB_PARAMS[Params::COORDS_KEY]['table_name'];
            $table_fields = $this->getTableFilds();

            $this->coords_to_add .= 'INSERT INTO ' . $table_name .'(' . $table_fields . ') VALUES ' . PHP_EOL;
            $this->coords_to_add .= '(' . $lat . ',' . $long .')';
        } else {

            $this->coords_to_add .= ', ' . PHP_EOL . '(' . $lat . ',' . $long .')';
        }
    }

    private function getTableFilds()
    {
        return implode(', ', Params::DB_PARAMS[Params::COORDS_KEY]['table_fields']);
    }

    public function getCoordsToAdd()
    {
        return $this->coords_to_add;
    }

    private function getDateTimeAsString()
    {
        return (new DateTime("now", new DateTimeZone('Europe/Lisbon')))->format('Y-m-d H:i:s');
    }
}