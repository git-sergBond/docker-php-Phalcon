<?php

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Callback;

class Services extends SubjectsWithNotDeleted
{
    /**
     *
     * @var integer
     * @Primary
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $serviceid;

    /**
     *
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

    /**
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $datepublication;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pricemin;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $pricemax;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=true)
     */
    protected $regionid;


    /**
     * @var string
     * @Column(type="string", length=150, nullable=true)
     */
    protected $name;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $longitude;

    /**
     *
     * @var string
     * @Column(type="string", length=53, nullable=false)
     */
    protected $latitude;

    /**
     *
     * @var integer
     * @Column(type="integer", length=32, nullable=false)
     */
    protected $numberofdisplay;

    protected $rating;

    const publicColumns = ['serviceid', 'description', 'datepublication', 'pricemin', 'pricemax',
        'regionid', 'name', 'rating'];

    const publicColumnsInStr = 'serviceid, description, datepublication, pricemin, pricemax,
        regionid, name, rating';

    /**
     * Method to set the value of field serviceId
     *
     * @param integer $serviceid
     * @return $this
     */
    public function setServiceId($serviceid)
    {
        $this->serviceid = $serviceid;

        return $this;
    }

    /**
     * Method to set the value of field name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Method to set the value of field description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Method to set the value of field datePublication
     *
     * @param string $datepublication
     * @return $this
     */
    public function setDatePublication($datepublication)
    {
        $this->datepublication = $datepublication;

        return $this;
    }

    /**
     * Method to set the value of field priceMin
     *
     * @param integer $pricemin
     * @return $this
     */
    public function setPriceMin($pricemin)
    {
        $this->pricemin = $pricemin;

        return $this;
    }

    /**
     * Method to set the value of field priceMax
     *
     * @param integer $pricemax
     * @return $this
     */
    public function setPriceMax($pricemax)
    {
        $this->pricemax = $pricemax;

        return $this;
    }

    /**
     * Method to set the value of field regionId
     *
     * @param integer $regionid
     * @return $this
     */
    public function setRegionId($regionid)
    {
        $this->regionid = $regionid;

        return $this;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;

        return $this;
    }

    /**
     * Method to set the value of field longitude
     *
     * @param string $longitude
     * @return $this
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    /**
     * Method to set the value of field latitude
     *
     * @param string $latitude
     * @return $this
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Method to set the value of field numberOfDisplay
     *
     * @param integer $numberOfDisplay
     * @return $this
     */
    public function setNumberOfDisplay($numberOfDisplay)
    {
        $this->numberofdisplay = $numberOfDisplay;
        return $this;
    }

    /**
     * Returns the value of field regionId
     *
     * @return integer
     */
    public function getRegionId()
    {
        return $this->regionid;
    }

    /**
     * Returns the value of field serviceId
     *
     * @return integer
     */
    public function getServiceId()
    {
        return $this->serviceid;
    }

    /**
     * Returns the value of field description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the value of field datePublication
     *
     * @return string
     */
    public function getDatePublication()
    {
        return $this->datepublication;
    }

    /**
     * Returns the value of field priceMin
     *
     * @return integer
     */
    public function getPriceMin()
    {
        return $this->pricemin;
    }

    public function getRating()
    {
        return $this->rating;
    }

    /**
     * Returns the value of field priceMax
     *
     * @return integer
     */
    public function getPriceMax()
    {
        return $this->pricemax;
    }

    /**
     * Returns the value of field name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the value of field longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Returns the value of field latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Returns the value of field numberOfDisplay
     *
     * @return integer
     */
    public function getNumberOfDisplay()
    {
        return $this->numberofdisplay;
    }

    /**
     * Validations and business logic
     *
     * @return boolean
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add(
            'pricemin',
            new Callback(
                [
                    "message" => "Минимальная цена должна быть меньше (или равна) максимальной",
                    "callback" => function ($service) {
                        if (!SupportClass::checkPositiveInteger($service->getPriceMin())
                            || !SupportClass::checkPositiveInteger($service->getPriceMax()))
                            return false;
                        if ($service->getPriceMin() <= $service->getPriceMax())
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            'regionid',
            new Callback(
                [
                    "message" => "Для услуги должен быть указан регион",
                    "callback" => function ($service) {
                        $region = Regions::findFirstByRegionid($service->getRegionId());
                        if ($region)
                            return true;
                        return false;
                    }
                ]
            )
        );

        $validator->add(
            "datepublication",
            new PresenceOf(
                [
                    "message" => "Не указана дата опубликования услуги",
                ]
            )
        );

        if ($this->getLongitude() != null) {
            $validator->add(
                'latitude',
                new Callback(
                    [
                        "message" => "Не указана широта для услуги",
                        "callback" => function ($service) {
                            if ($service->getLatitude() != null && SupportClass::checkDouble($service->getLatitude()))
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        if ($this->getLatitude() != null) {
            $validator->add(
                'longitude',
                new Callback(
                    [
                        "message" => "Не указана долгота для услуги",
                        "callback" => function ($service) {
                            if ($service->getLongitude() != null && SupportClass::checkDouble($service->getLongitude()))
                                return true;
                            return false;
                        }
                    ]
                )
            );
        }

        return $this->validate($validator) && parent::validation();
    }

    public function delete($delete = false, $deletedCascade = false, $data = null, $whiteList = null)
    {
        if ($delete) {
            $images = ImagesServices::findByServiceid($this->getServiceId());

            foreach ($images as $image) {
                if (!$image->delete()) {
                    return false;
                };
            }
        }
        $result = parent::delete($delete, $deletedCascade, $data, $whiteList);

        return $result;
    }

    /**
     * Initialize method for model.
     */
    public function initialize()
    {
        //$this->setSchema("public");
        $this->setSource("services");
        $this->hasMany('serviceid', 'ServicesPoints', 'serviceid', ['alias' => 'ServicesPoints']);
        $this->belongsTo('regionid', '\Regions', 'regionid', ['alias' => 'Regions']);
    }

    /**
     * Returns table name mapped in the model.
     *
     * @return string
     */
    public function getSource()
    {
        return 'services';
    }

    public static function getServices($categoriesId = null, $serviceid = null, $companyid = null)
    {
        $db = Phalcon\DI::getDefault()->getDb();
        if ($serviceid == null) {
            if ($companyid == null) {
                $query = $db->prepare("SELECT * FROM (SELECT row_to_json(serv) as \"service\",
                row_to_json(comp) as \"company\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.\"companiesCategories\" compcat ON (compcat.categoryid = cat.categoryid)
                                       WHERE comp.companyid = compcat.companyid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
               array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\"                        
              FROM public.companies as comp
              INNER JOIN public.services as serv ON (serv.subjectid = comp.companyid AND serv.subjecttype = 1
              AND serv.deleted = false AND comp.deleted = false))) foo
              ");

                $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.userscategories uc ON(uc.categoryid = cat.categoryid)
                                       WHERE uc.userid = us.userid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
              array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\" 
              FROM public.userinfo as us
              INNER JOIN public.services as serv ON (serv.subjectid = us.userid AND serv.subjecttype = 0
              AND serv.deleted = false) 
              INNER JOIN public.users ON (us.userid = public.users.userid))
              ) foo LIMIT 100");
                $query->execute();
                $query2->execute();
            } else {
                $query = $db->prepare("SELECT * FROM (SELECT row_to_json(serv) as \"service\",
                row_to_json(comp) as \"company\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.\"companiesCategories\" compcat ON (compcat.categoryid = cat.categoryid)
                                       WHERE comp.companyid = compcat.companyid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
               array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\"                        
              FROM public.companies as comp
              INNER JOIN public.services as serv ON (serv.subjectid = comp.companyid AND serv.subjecttype = 1
              AND serv.deleted = false AND comp.deleted = false)
              WHERE comp.companyid = :companyId 
              ) foo");

                $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.userscategories uc ON(uc.categoryid = cat.categoryid)
                                       WHERE uc.userid = us.userid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
              array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\" 
              FROM public.userinfo as us
              INNER JOIN public.services as serv ON (serv.subjectid = us.userid AND serv.subjecttype = 0
              AND serv.deleted = false) 
              INNER JOIN public.users ON (us.userid = public.users.userid) 
              WHERE false) 
              ) foo");
                $query->execute(['companyId' => $companyid]);
                $query2->execute(/*['companyId'=> $companyid]*/);
            }
        } else {
            $query = $db->prepare("SELECT * FROM (SELECT row_to_json(serv) as \"service\",
                row_to_json(comp) as \"company\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.\"companiesCategories\" compcat ON (compcat.categoryid = cat.categoryid)
                                       WHERE comp.companyid = compcat.companyid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
               array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\"                        
              FROM public.companies as comp
              INNER JOIN public.services as serv ON (serv.subjectid = comp.companyid AND serv.subjecttype = 1
              AND serv.deleted = false AND comp.deleted = false)
              WHERE serv.serviceid = :serviceId 
              ) foo");

            $query2 = $db->prepare("SELECT * FROM ((SELECT row_to_json(serv) as \"service\",
                row_to_json(us) as \"userinfo\",
               array(SELECT row_to_json(cat.*) FROM public.categories as cat INNER JOIN
                              public.userscategories uc ON(uc.categoryid = cat.categoryid)
                                       WHERE uc.userid = us.userid) as \"categories\",
               array(SELECT row_to_json(points) FROM public.\"tradePoints\" as points INNER JOIN
                              public.\"servicesPoints\" servpoint ON (servpoint.pointid = points.pointid
                              AND points.deleted = false)
                                       WHERE servpoint.serviceid = serv.serviceid) as \"points\",
              array(SELECT row_to_json(images) FROM public.imagesservices as images 
                                       WHERE images.serviceid = serv.serviceid) as \"images\" 
              FROM public.userinfo as us
              INNER JOIN public.services as serv ON (serv.subjectid = us.userid AND serv.subjecttype = 0
              AND serv.deleted = false) 
              INNER JOIN public.users ON (us.userid = public.users.userid) 
              WHERE serv.serviceid = :serviceId) 
              ) foo");
            $query->execute(['serviceId' => $serviceid]);
            $query2->execute(['serviceId' => $serviceid]);
        }


        $services = $query->fetchAll(\PDO::FETCH_ASSOC);
        $servicesusers = $query2->fetchAll(\PDO::FETCH_ASSOC);
        $reviews2 = [];
        foreach ($services as $review) {
            $review2 = [];
            $review2['service'] = json_decode($review['service']);

            $review2['company'] = json_decode($review['company']);


            $review['categories'][0] = '[';
            $review['categories'][strlen($review['categories']) - 1] = ']';

            $review['categories'] = str_replace('"{', '{', $review['categories']);
            $review['categories'] = str_replace('}"', '}', $review['categories']);
            $review['categories'] = stripslashes($review['categories']);
            $review2['categories'] = json_decode($review['categories']);

            $review2['images'] = json_decode($review['images']);
            $review['images'][0] = '[';
            $review['images'][strlen($review['images']) - 1] = ']';

            $review['images'] = str_replace('"{', '{', $review['images']);
            $review['images'] = str_replace('}"', '}', $review['images']);
            $review['images'] = stripslashes($review['images']);
            $review2['images'] = json_decode($review['images']);

            $review['points'][0] = '[';
            $review['points'][strlen($review['points']) - 1] = ']';

            $review['points'] = str_replace('"{', '{', $review['points']);
            $review['points'] = str_replace('}"', '}', $review['points']);
            $review['points'] = stripslashes($review['points']);
            $review2['ratingcount'] = 45;

            $review2['points'] = json_decode($review['points'], true);

            for ($i = 0; $i < count($review2['points']); $i++) {
                $review2['points'][$i]['phones'] = [];
                $pps = PhonesPoints::findByPointid($review2['points'][$i]['pointid']);
                foreach ($pps as $pp)
                    $review2['points'][$i]['phones'][] = $pp->phones->getPhone();
            }

            //$review2['points'] = json_decode($review2['points']);

            if ($categoriesId != null) {
                $flag = false;
                foreach ($categoriesId as $categoryId) {
                    foreach ($review2['categories'] as $category) {
                        if ($category->categoryid == $categoryId) {
                            $flag = true;
                            break;
                        }
                    }
                    if ($flag) {
                        $reviews2[] = $review2;
                        break;
                    }
                }
            } else {
                $reviews2[] = $review2;
            }
        }

        foreach ($servicesusers as $review) {
            $review2 = [];
            $review2['service'] = json_decode($review['service']);
            $review2['Userinfo'] = json_decode($review['userinfo']);

            $review['categories'][0] = '[';
            $review['categories'][strlen($review['categories']) - 1] = ']';

            $review['categories'] = str_replace('"{', '{', $review['categories']);
            $review['categories'] = str_replace('}"', '}', $review['categories']);
            $review['categories'] = stripslashes($review['categories']);
            $review2['categories'] = json_decode($review['categories']);

            $review['images'][0] = '[';
            $review['images'][strlen($review['images']) - 1] = ']';

            $review['images'] = str_replace('"{', '{', $review['images']);
            $review['images'] = str_replace('}"', '}', $review['images']);
            $review['images'] = stripslashes($review['images']);
            $review2['images'] = json_decode($review['images']);
            $review2['ratingcount'] = 45;


            $review['points'][0] = '[';
            $review['points'][strlen($review['points']) - 1] = ']';

            $review['points'] = str_replace('"{', '{', $review['points']);
            $review['points'] = str_replace('}"', '}', $review['points']);
            $review['points'] = stripslashes($review['points']);
            $review2['points'] = json_decode($review['points'], true);

            for ($i = 0; $i < count($review2['points']); $i++) {
                $review2['points'][$i]['phones'] = [];
                $pps = PhonesPoints::findByPointid($review2['points'][$i]['pointid']);
                foreach ($pps as $pp)
                    $review2['points'][$i]['phones'][] = $pp->phones->getPhone();
            }

            if ($categoriesId != null) {
                $flag = false;
                foreach ($categoriesId as $categoryId) {
                    foreach ($review2['categories'] as $category) {
                        if ($category->categoryid == $categoryId) {
                            $flag = true;
                            break;
                        }
                    }
                    if ($flag) {
                        $reviews2[] = $review2;
                        break;
                    }
                }
            } else {
                $reviews2[] = $review2;
            }
        }

        return $reviews2;
    }

    private function sortFunction($a, $b)
    {
        return ($a['weight'] < $b['weight']) ? -1 : 1;
    }

    function cmp($a, $b)
    {
        if ($a['weight'] == $b['weight']) {
            return 0;
        }
        return ($a['weight'] < $b['weight']) ? -1 : 1;
    }

    /**
     * @param $query
     * @param $center
     * @param $diagonal
     * @param null $regions
     * @return array
     */
    public static function getServicesByQuery($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        //$cl->SetMatchMode(SPH_MATCH_ANY);
        if (trim($query) == '')
            $cl->SetMatchMode(SPH_MATCH_ALL);
        else
            $cl->SetMatchMode(SPH_MATCH_ANY);

        $cl->SetLimits(0, 10000, 50);
        $cl->SetFieldWeights(['name' => 100, 'description' => 10]);
        $cl->SetRankingMode(SPH_RANK_SPH04);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
            $cl->AddQuery($query, 'bro4you_small_index');
            $cl->ResetFilters();
        }
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'bro4you_small_index');

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allmatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        foreach ($allmatches as $match) {
            $service['service'] = json_decode($match['attrs']['service'], true);
            //$service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);
            if (count($match['attrs']['pointid']) > 0) {
                $str = '';

                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($str == '')
                        $str .= 'pointid IN (' . $pointid;
                    else {
                        $str .= ', ' . $pointid;
                    }
                }
                $str .= ')';

                $points = TradePoints::find([$str, 'columns' => TradePoints::publicColumns]);

                $service['points'] = $points;
            }

            if ($service['service']['subjecttype'] == 1) {
                $service['companies'] = Companies::findFirst([
                    'companyid = :companyId:',
                    'bind' => ['companyId' => $service['service']['subjectid']],
                    'columns' => Companies::publicColumns
                ]);

                $service['categories'] = CompaniesCategories::getCategoriesByCompany($service['service']['subjectid']);
            } elseif ($service['service']['subjecttype'] == 0) {
                $service['userinfo'] = Userinfo::findFirst([
                    'userid = :userId:',
                    'bind' => ['userId' => $service['service']['subjectid']],
                    'columns' => Userinfo::publicColumns
                ]);

                $service['categories'] = UsersCategories::getCategoriesByUser($service['service']['subjectid']);
            }

            $service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);

            if (count($service['images']) == 0) {
                $image = new ImagesServices();
                $image->setImagePath('/images/no_image.jpg');
                $image->setServiceId($service['service']['serviceid']);
                $service['images'] = [$image];
            }

            $service['ratingcount'] = count(Reviews::getReviewsForService($service['service']['serviceid']));
            $services[] = $service;
        }


        return $services;
    }

    public static function getAutocompleteByQuery($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_ANY);
        $cl->SetRankingMode(SPH_RANK_SPH04);
        $cl->SetLimits(0, 10000, 40);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);
        $cl->SetFieldWeights(['name2' => 100, 'description2' => 10]);

        //Сначала поиск по компаниям
        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
        }

        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        //$cl->SetGeoAnchor('latitude', 'longitude', deg2rad(39.023), deg2rad(54.032));
        //$cl->SetFilterFloatRange("@geodist", 0, 50000000, false);

        $cl->AddQuery($query, 'companies_min_index');
        //$cl->ResetFilters();
        $cl->AddQuery($query, 'services_min_index');

        $cl->ResetFilters();
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }
        $cl->AddQuery('@*' . $query . '*'/*$query*/, 'categories_min_index');

        $results = $cl->RunQueries();

        /*var_dump($results);
        die;*/

        $allMatches = [];

        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allMatches = array_merge($allMatches, $result['matches']);
            }
        }

        $res = usort($allMatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        $output = [];

        for ($i = 0; $i < 10 && $i < count($allMatches); $i++) {
            $result = $allMatches[$i];
            $output[] = ['id' => $result['attrs']['elementid'], 'name' => $result['attrs']['name'],
                'type' => $result['attrs']['type'],
            ];
        }

        return $output;
    }

    public static function getServicesByElement($type, $elementIds, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        $cl->SetLimits(0, 10000, 50);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            if ($type == 'service') {
                $cl->setFilter('regionid', $regions, false);
                $cl->setFilter('servid', $elementIds, false);
                $cl->AddQuery('', 'bro4you_small_index');
                $cl->ResetFilters();
            } elseif ($type == 'company') {
                $cl->setFilter('regionid', $regions, false);
                $cl->setFilter('companyid', $elementIds, false);
                $cl->AddQuery('', 'services_with_company_index');
                $cl->ResetFilters();
            } elseif ($type == 'category') {
                $cl->setFilter('regionid', $regions, false);
                $cl->setFilter('categoryid', $elementIds, false);
                $cl->AddQuery('', 'services_with_category_index');
                $cl->ResetFilters();
            }
        }

        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        if ($type == 'service') {
            $cl->setFilter('servid', $elementIds, false);
            $cl->AddQuery('', 'bro4you_small_index');
        } elseif ($type == 'company') {
            $cl->setFilter('companyid', $elementIds, false);
            $cl->AddQuery('', 'services_with_company_index');
        } elseif ($type == 'category') {
            $cl->setFilter('categoryid', $elementIds, false);
            $cl->AddQuery('', 'services_with_category_index');
        }

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allMatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        foreach ($allmatches as $match) {
            $service['service'] = json_decode($match['attrs']['service'], true);
            //$service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);
            if (count($match['attrs']['pointid']) > 0) {
                $str = '';

                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($str == '')
                        $str .= 'pointid IN (' . $pointid;
                    else {
                        $str .= ', ' . $pointid;
                    }
                }
                $str .= ')';

                $points = TradePoints::find([$str, 'columns' => TradePoints::publicColumns]);

                $service['points'] = $points;
            }

            if ($service['service']['subjecttype'] == 1) {
                $service['companies'] = Companies::findFirst([
                    'companyid = :companyId:',
                    'bind' => ['companyId' => $service['service']['subjectid']],
                    'columns' => Companies::publicColumns
                ]);

                $service['categories'] = CompaniesCategories::getCategoriesByCompany($service['service']['subjectid']);
            } elseif ($service['service']['subjecttype'] == 0) {
                $service['userinfo'] = Userinfo::findFirst([
                    'userid = :userId:',
                    'bind' => ['userId' => $service['service']['subjectid']],
                    'columns' => Userinfo::publicColumns
                ]);

                $service['categories'] = UsersCategories::getCategoriesByUser($service['service']['subjectid']);
            }
            $service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);

            if (count($service['images']) == 0) {
                $image = new ImagesServices();
                $image->setImagePath('/images/no_image.jpg');
                $image->setServiceId($service['service']['serviceid']);
                $service['images'] = [$image];
            }
            $reviews = Reviews::getReviewsForService($service['service']['serviceid']);
            $service['ratingcount'] = count($reviews);
            $services[] = $service;
        }


        return $services;
    }

    public static function getServicesWithFilters($query, $center, $diagonal, $regions = null,
                                                  $categories = null, $priceMin = null, $priceMax = null, $ratingMin = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        $cl->SetLimits(0, 10000, 50);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if($regions!= null)
            $cl->setFilter('regionid', $regions, false);
        if($categories!=null)
            $cl->setFilter('categoryid', $categories, false);

        if($priceMin!=null)
            $cl->setFilterFloatRange('pricemin', $priceMin,9223372036854775807, false);

        if($priceMax!=null)
            $cl->setFilterFloatRange('pricemax', 0,$priceMax, false);

        if($ratingMin!=null)
            $cl->setFilterFloatRange('rating', $ratingMin,100.0, false);

        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));
            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);
            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'services_with_filters_index');
        $results = $cl->RunQueries();

        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allMatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        foreach ($allmatches as $match) {
            $service['service'] = json_decode($match['attrs']['service'], true);
            //$service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);
            if (count($match['attrs']['pointid']) > 0) {
                $str = '';

                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($str == '')
                        $str .= 'pointid IN (' . $pointid;
                    else {
                        $str .= ', ' . $pointid;
                    }
                }
                $str .= ')';

                $points = TradePoints::find([$str, 'columns' => TradePoints::publicColumns]);

                $service['points'] = $points;
            }

            if ($service['service']['subjecttype'] == 1) {
                $service['companies'] = Companies::findFirst([
                    'companyid = :companyId:',
                    'bind' => ['companyId' => $service['service']['subjectid']],
                    'columns' => Companies::publicColumns
                ]);

                $service['categories'] = CompaniesCategories::getCategoriesByCompany($service['service']['subjectid']);
            } elseif ($service['service']['subjecttype'] == 0) {
                $service['userinfo'] = Userinfo::findFirst([
                    'userid = :userId:',
                    'bind' => ['userId' => $service['service']['subjectid']],
                    'columns' => Userinfo::publicColumns
                ]);

                $service['categories'] = UsersCategories::getCategoriesByUser($service['service']['subjectid']);
            }
            $service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);

            if (count($service['images']) == 0) {
                $image = new ImagesServices();
                $image->setImagePath('/images/no_image.jpg');
                $image->setServiceId($service['service']['serviceid']);
                $service['images'] = [$image];
            }
            $reviews = Reviews::getReviewsForService($service['service']['serviceid']);
            $service['ratingcount'] = count($reviews);
            $services[] = $service;
        }


        return $services;
    }

    public static function getServicesByQuery2($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        $cl->SetMatchMode(SPH_MATCH_EXTENDED2);
        $cl->SetLimits(0, 10000, 50);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
            $cl->AddQuery($query, 'bro4you_index');
            $cl->ResetFilters();
        }
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'bro4you_index');

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allmatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        foreach ($allmatches as $match) {
            $service['service'] = json_decode($match['attrs']['service'], true);
            $subject = json_decode($match['attrs']['subject'], true);
            if ($service['service']['subjecttype'] == 1)
                $service['company'] = $subject;
            else {
                $service['userinfo'] = $subject;
            }

            $service['categories'] = SupportClass::translateInPhpArrFromPostgreArr($match['attrs']['categories']);

            $service['images'] = SupportClass::translateInPhpArrFromPostgreArr($match['attrs']['images']);
            $points = SupportClass::translateInPhpArrFromPostgreArr($match['attrs']['points']);

            foreach ($points as $point) {
                $f = false;
                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($point['pointid'] == $pointid) {
                        $f = true;
                        break;
                    }
                }
                if ($f)
                    $service['points'][] = $point;
            }

            $services[] = $service;
        }

        return $services;
    }

    public static function getServicesByQueryByTags($query, $center, $diagonal, $regions = null)
    {
        require(APP_PATH . '/library/sphinxapi.php');
        $cl = new SphinxClient();
        $cl->setServer('127.0.0.1', 9312);
        //$cl->SetMatchMode(SPH_MATCH_ANY);
        if (trim($query) == '')
            $cl->SetMatchMode(SPH_MATCH_ALL);
        else
            $cl->SetMatchMode(SPH_MATCH_ANY);

        $cl->SetLimits(0, 10000, 50);
        $cl->SetRankingMode(SPH_RANK_SPH04);
        $cl->SetSortMode(SPH_SORT_RELEVANCE);

        if ($regions != null) {
            $cl->setFilter('regionid', $regions, false);
            $cl->AddQuery($query, 'bro4you_small_tags_index');
            $cl->ResetFilters();
        }
        if ($center != null && $diagonal != null) {
            $cl->SetGeoAnchor('latitude', 'longitude', deg2rad($center['latitude']), deg2rad($center['longitude']));

            $radius = SupportClass::codexworldGetDistanceOpt($center['latitude'], $center['longitude'],
                $diagonal['latitude'], $diagonal['longitude']);

            $cl->SetFilterFloatRange("@geodist", 0, $radius, false);
        }

        $cl->AddQuery($query, 'bro4you_small_tags_index');

        $results = $cl->RunQueries();
        $services = [];
        $allmatches = [];
        foreach ($results as $result) {
            if ($result['total'] > 0) {
                $allmatches = array_merge($allmatches, $result['matches']);
            }
        }

        $res = usort($allmatches, function ($a, $b) {
            if ($a['weight'] == $b['weight']) {
                return 0;
            }
            return ($a['weight'] > $b['weight']) ? -1 : 1;
        });

        foreach ($allmatches as $match) {
            $service['service'] = json_decode($match['attrs']['service'], true);
            //$service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);
            if (count($match['attrs']['pointid']) > 0) {
                $str = '';

                foreach ($match['attrs']['pointid'] as $pointid) {
                    if ($str == '')
                        $str .= 'pointid IN (' . $pointid;
                    else {
                        $str .= ', ' . $pointid;
                    }
                }
                $str .= ')';

                $points = TradePoints::find([$str, 'columns' => TradePoints::publicColumns]);

                $service['points'] = $points;
            }

            if ($service['service']['subjecttype'] == 1) {
                $service['companies'] = Companies::findFirst([
                    'companyid = :companyId:',
                    'bind' => ['companyId' => $service['service']['subjectid']],
                    'columns' => Companies::publicColumns
                ]);

                $service['categories'] = CompaniesCategories::getCategoriesByCompany($service['service']['subjectid']);
            } elseif ($service['service']['subjecttype'] == 0) {
                $service['userinfo'] = Userinfo::findFirst([
                    'userid = :userId:',
                    'bind' => ['userId' => $service['service']['subjectid']],
                    'columns' => Userinfo::publicColumns
                ]);

                $service['categories'] = UsersCategories::getCategoriesByUser($service['service']['subjectid']);
            }

            $service['images'] = ImagesServices::findByServiceid($service['service']['serviceid']);

            if (count($service['images']) == 0) {
                $image = new ImagesServices();
                $image->setImagePath('/images/no_image.jpg');
                $image->setServiceId($service['service']['serviceid']);
                $service['images'] = [$image];
            }

            $service['ratingcount'] = count(Reviews::getReviewsForService($service['service']['serviceid']));
            $services[] = $service;
        }


        return $services;
    }
    /**
     * @param $subjectId
     * @param $subjectType
     * @return Возвращает массив услуг в виде:
     *      [{serviceid, description, datepublication, pricemin, pricemax,
            regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
            [Userinfo или Company]}]
     */
    public static function getServicesForSubject($subjectId, $subjectType){
        $db = Phalcon\DI::getDefault()->getDb();

        $services = Services::findBySubject($subjectId,$subjectType,'datepublication desc',Services::publicColumnsInStr);

        $servicesArr = json_encode($services);
        $servicesArr = json_decode($servicesArr,true);
        $servicesAll = [];

        if($subjectType == 0){
            $categories = UsersCategories::getCategoriesByUser($subjectId);
        } else{
            $categories = CompaniesCategories::getCategoriesByCompany($subjectId);
        }

        foreach ($servicesArr as $service) {
            $serviceAll = $service;
            $serviceAll['categories'] = $categories;
            $images = ImagesServices::findByServiceid($service['serviceid']);
            $serviceAll['images'] = [];
            foreach ($images as $image){
                $serviceAll['images'][] = $image->getImagePath();
            }
            $points = Services::getPointsForService($service['serviceid']);
            $serviceAll['point'] = count($points)>0?
                $points[0]:[];

            $tags = Services::getTagsForService($service['serviceid']);
            $serviceAll['tags'] = count($tags)>0?
                $tags:[];

            if ($subjectType == 0) {
                $user = Userinfo::findFirst(
                    ['conditions' => 'userid = :subjectid:',
                        'columns' => Userinfo::publicColumnsInStr,
                        'bind' => ['subjectid' => $subjectId]]);

                $user = json_encode($user);
                $user = json_decode($user, true);
                $serviceAll['publisherUser'] = $user;
                $phones = PhonesUserinfo::getUserPhones($subjectId);
                //$newsWithAllElement['publisherUser']->setPhones($phones);
                $serviceAll['publisherUser']['phones'] = $phones;
            } else {
                $company = Companies::findFirst(
                    ['conditions' => 'companyid = :subjectid:',
                        'columns' => Companies::publicColumnsInStr,
                        'bind' => ['subjectid' => $subjectId]]);

                $company = json_encode($company);
                $company = json_decode($company, true);

                $serviceAll['publisherCompany'] = $company;
                $phones = PhonesCompanies::getCompanyPhones($serviceAll['publisherCompany']['companyid']);
                $serviceAll['publisherCompany']['phones'] = $phones;
            }

            $servicesAll[] =$serviceAll;
        }

        return $servicesAll;
    }

    public static function getTasksForService($serviceId)
    {
        $db = Phalcon\DI::getDefault()->getDb();
        return [];
    }

    public static function getPointsForService($serviceId)
    {
        $modelsManager = Phalcon\DI::getDefault()->get('modelsManager');
        $columns = [];
        foreach(TradePoints::publicColumns as $publicColumn){
            $columns[] = 'p.'.$publicColumn;
        }
        $result = $modelsManager->createBuilder()
            ->columns($columns)
            ->from(["p" => "TradePoints"])
            ->join('ServicesPoints', 'p.pointid = sp.pointid', 'sp')
            ->join('Services', 'sp.serviceid = s.serviceid', 's')
            ->where('s.serviceid = :serviceId:', ['serviceId' => $serviceId])
            ->getQuery()
            ->execute();

        return $result;
    }

    public static function getTagsForService($serviceId)
    {
        $modelsManager = Phalcon\DI::getDefault()->get('modelsManager');

        $result = $modelsManager->createBuilder()
            ->from(["t" => "Tags"])
            ->join('ServicesTags', 't.tagid = st.tagid', 'st')
            ->join('Services', 'st.serviceid = s.serviceid', 's')
            ->where('s.serviceid = :serviceId:', ['serviceId' => $serviceId])
            ->getQuery()
            ->execute();

        return $result;
    }

    public function clipToPublic()
    {
        $service = $this;
        $service = json_encode($service);
        $service = json_decode($service, true);
        unset($service['deleted']);
        unset($service['deletedcascade']);
        unset($service['numberofdisplay']);
        return $service;
    }
}
