<?php
/**
 * Created by PhpStorm.
 * User: Renato
 * Date: 11/12/2018
 * Time: 18:38
 */

class Params {

    const APP_PARAMS = [
        self::APP_ID_STR => 'APP_ID',
        self::APP_SECRET_STR => 'APP_SECRET',
        self::APP_DEFAULT_VERSION_STR => 'v3.2',
    ];

    const FBPLACES_REDIRECT_URL = 'https://www.fbplaces.com/getplaces.php';

    const REDIRECT_URL = self::FBPLACES_REDIRECT_URL;

    const FBPLACES_BASE_URL = 'https://www.fbplaces.com/index.php';

    const BASE_URL = self::FBPLACES_BASE_URL;

    const APP_ID_STR = 'app_id';
    const APP_SECRET_STR = 'app_secret';
    const APP_DEFAULT_VERSION_STR = 'default_graph_version';

    const PLACE_SEARCH_KEY = 'place_search';
    const FIELDS_KEY = 'fields';
    const CENTER_KEY = 'center';
    const TYPE_KEY = 'type';
    const DISTANCE_KEY = 'distance';
    const LIMIT_KEY = 'limit';

    const MIN_SLEEP_TIME = 18; // in seconds
    const MAX_SLEEP_TIME = 72; // in seconds

    /* Coordinates table keys */
    const COORDS_KEY = 'coordinate';
    const LATITUDE_KEY = 'latitude';
    const LONGITUDE_KEY = 'longitude';
    const PRISTINE_KEY = 'pristine';
    const CONTROL_FIELD_KEY = 'in_progress';
    const GEOM_POINT_KEY = 'geom_point';
    const COORDS_ID_KEY = 'id';
    const LAST_UPDATE_KEY = 'last_update';

    /* Log table keys */
    const LOG_KEY = 'log';
    const INFO_FIELD_KEY = 'info';
    const TITLE_FIELD_KEY = 'title';
    const TYPE_FIELD_KEY = 'type';
    const DATETIME_FIELD_KEY = 'datetime';

    const PERMISSIONS = [];
    const DEFAULT_DISTANCE = 2000;
    const DEFAULT_LIMIT = 100;
    const DEFAULT_TYPE = 'place';

    //const CR_CHARACTER      = PHP_EOL;
    const CR_CHARACTER      = '<br/>';

    /**
     * Especial Fields
     */
    const ESPEC_FLD_HOURS = 'hours';
    const ESPEC_FLD_LOCATION = 'location';
    const ESPEC_FLD_CATEGORY_LIST = 'category_list';
    const ESPEC_FLD_RESTAURANT_SERVICES = 'restaurant_services';
    const ESPEC_FLD_RESTAURANT_SPECIALITIES = 'restaurant_specialties';

    const ESPECIAL_FIELDS = [
        self::ESPEC_FLD_LOCATION => [
            'espec_fld_city',
            'espec_fld_city_id',
            'espec_fld_country',
            'espec_fld_country_code',
            'espec_fld_located_in',
            'espec_fld_latitude',
            'espec_fld_longitude',
            'espec_fld_name',
            'espec_fld_region',
            'espec_fld_region_id',
            'espec_fld_state',
            'espec_fld_street',
            'espec_fld_zip',
        ],
        self::ESPEC_FLD_RESTAURANT_SERVICES => [
            'espec_fld_delivery',
            'espec_fld_catering',
            'espec_fld_groups',
            'espec_fld_kids',
            'espec_fld_outdoor',
            'espec_fld_pickup',
            'espec_fld_reserve',
            'espec_fld_takeout',
            'espec_fld_waiter',
            'espec_fld_walkins',
        ],
        self::ESPEC_FLD_RESTAURANT_SPECIALITIES => [
            'espec_fld_breakfast',
            'espec_fld_coffee',
            'espec_fld_dinner',
            'espec_fld_drinks',
            'espec_fld_lunch',
        ],
    ];

    const URL_LIST = [
        self::PLACE_SEARCH_KEY => 'search',
    ];

    const REQUEST_PARAMS = [
        self::PLACE_SEARCH_KEY => [
            self::CENTER_KEY   => '',
            self::TYPE_KEY     => self::DEFAULT_TYPE,
            self::DISTANCE_KEY => self::DEFAULT_DISTANCE,
            self::LIMIT_KEY    => self::DEFAULT_LIMIT,
            self::FIELDS_KEY   => [
                'id',
                'name',
                'about',
                'checkins',
                'description',
                'is_permanently_closed',
                'is_verified',
                'link',
                'single_line_address',
                'website',
                self::ESPEC_FLD_RESTAURANT_SPECIALITIES,
                self::ESPEC_FLD_CATEGORY_LIST,
                self::ESPEC_FLD_HOURS,
                self::ESPEC_FLD_LOCATION,
                self::ESPEC_FLD_RESTAURANT_SERVICES,
            ],
        ]
    ];

    const VALID_URL_LIST = [
        self::PLACE_SEARCH_KEY,
    ];

    const DB_PARAMS = [
        self::PLACE_SEARCH_KEY => [
            'table_name' => 'poi_db.poi.fbplaces',
            'seq_name' => 'poi_db.poi.fbplaces_id_seq',
            'request_fields' => self::REQUEST_PARAMS[self::PLACE_SEARCH_KEY][self::FIELDS_KEY],
        ],
        self::COORDS_KEY => [
            'table_name' => 'poi_db.poi.coordinates',
            'seq_name' => 'poi_db.poi.coordinates_id_seq',
            'table_fields' => [
                self::LATITUDE_KEY,
                self::LONGITUDE_KEY,
                self::GEOM_POINT_KEY,
                self::COORDS_ID_KEY,
            ],
        ],
        self::LOG_KEY => [
            'table_name' => 'poi_db.poi.log',
            'seq_name' => 'poi_db.poi.log_id_seq',
            'table_fields' => [
                self::INFO_FIELD_KEY,
                self::TITLE_FIELD_KEY,
                self::TYPE_FIELD_KEY,
                self::DATETIME_FIELD_KEY,
            ],
        ],
    ];

    const BORDER_COORDS = [
        'left' => [
            'sup' => [
                'lat' => 42.259886,
                'lon' => -9.574114,
            ],
            'inf' => [
                'lat' => 36.788878,
                'lon' => -9.574114,
            ],
        ],
        'right' => [
            'sup' => [
                'lat' => 42.259886,
                'lon' => -5.906718,
            ],
            'inf' => [
                'lat' => 36.788878,
                'lon' => -5.906718,
            ],
        ],
    ];
}