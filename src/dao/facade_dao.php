<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 14/01/2019
 * Time: 16:54
 */

class FacadeDAO
{
    private $placesdao;
    private $coordsdao;
    private $logdao;
    private $pdo;
    private $error;

    public function __construct()
    {
        $this->pdo = Registry::getConnection();
        $this->placesdao = new PlacesDAO($this->pdo);
        $this->coordsdao = new CoordsDAO($this->pdo);
        $this->logdao = new LogDAO($this->pdo);
    }

    public function insertPlaces(array $data, array $control_fields)
    {
        list($latitude, $longitude, $geom, $point_id) = explode(',', $control_fields['coords']);

        if (empty($data)) {

            $error  = 'There are no results to save. Coords: ' . print_r($control_fields['coords'], true);
            Utils::printError($error);

            $this->unblockCoordinates($latitude, $longitude, $geom, $point_id);
            return;
        }
        $this->pdo->beginTransaction();

        if ($this->unblockCoordinates($latitude, $longitude, $geom, $point_id)) {

            $ok = true;
            foreach ($data as $places) {

                $ok = $ok && $this->placesdao->insert($places, $control_fields);
            }
            if (!$ok) {

                $this->pdo->rollback();

                Utils::printError($this->placesdao->getError(), 'Error', true);
                $this->placesdao->setError('');
            } else {

                $this->pdo->commit();
            }
        } else {

            $this->pdo->rollback();
            Utils::printError('Error releasing the coordinates ' . $control_fields['coords'], 'Error',true);
        }
        $this->persistLog();
    }

    public function unblockCoordinates($latitude, $longitude, $geom, $point_id)
    {
        return $this->coordsdao->updateCoordinates($latitude, $longitude, $geom, $point_id, false, false);
    }

    public function getNextCoordinate()
    {
        return $this->coordsdao->getNextCoord();
    }

    public function isEmptyTable()
    {
        return $this->coordsdao->isEmptyTable();
    }

    public function addCoord($lat, $long)
    {
        $this->coordsdao->addCoord($lat, $long);
    }

    public function persistCoords()
    {
        $this->coordsdao->persist();
    }

    private function persistLog()
    {
        $this->logdao->persist();

        Log::clearLog();
        Registry::setCoords([]);
    }
}