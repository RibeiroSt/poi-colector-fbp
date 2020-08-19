<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 14/01/2019
 * Time: 16:48
 */

class Coordinate
{
    private $facade_dao;

    public function __construct()
    {
        $this->facade_dao = new FacadeDAO();
    }

    public function generateInitialCoordinates()
    {
        if (!$this->facade_dao->isEmptyTable()) {

            $msg = "The system is trying to generate the initial coordinates, but the coordinates table is not empty.";
            Utils::printError($msg);
        }
        // right superior limit
        $lim_right_sup_lat = Params::BORDER_COORDS['right']['sup']['lat'];
        $lim_right_sup_lon = Params::BORDER_COORDS['right']['sup']['lon'];

        // left inferior limit
        $lim_left_inf_lat = Params::BORDER_COORDS['left']['inf']['lat'];
        $lim_left_inf_lon = Params::BORDER_COORDS['left']['inf']['lon'];
        $latitude_base = $lim_left_inf_lat;

        $i = 0;
        while ($lim_left_inf_lon <= $lim_right_sup_lon) {

            while ($lim_left_inf_lat <= $lim_right_sup_lat) {

                $lim_left_inf_lat = $this->calcLatitude($lim_left_inf_lat, Params::DEFAULT_DISTANCE);
                $this->facade_dao->addCoord($lim_left_inf_lat, $lim_left_inf_lon);
            }
            $lim_left_inf_lon = $this->calcLongitude($lim_left_inf_lat, $lim_left_inf_lon, (Params::DEFAULT_DISTANCE / 2));

            if (($i % 2) == 0) {

                $lim_left_inf_lat = $this->calcLatitude($latitude_base, (Params::DEFAULT_DISTANCE / 2));
            } else {

                $lim_left_inf_lat = $latitude_base;
            }
            $i ++;
        }
        $this->facade_dao->persistCoords();
    }

    private function calcLatitude($latitude, $distance)
    {
        $earth = 6378.137;  //radius of the earth in kilometer
        $pi = M_PI;
        $m = (1 / ((2 * $pi / 360) * $earth)) / 1000;   //1 meter in degree

        return $latitude + ($distance * $m);
    }

    private function calcLongitude($latitude, $longitude, $distance)
    {
        $earth = 6378.137;  //radius of the earth in kilometer
        $pi = M_PI;
        $m = (1 / ((2 * $pi / 360) * $earth)) / 1000;  //1 meter in degree

        return $longitude + ($distance * $m) / cos($latitude * ($pi / 180));
    }
}