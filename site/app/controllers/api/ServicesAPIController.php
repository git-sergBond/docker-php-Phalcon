<?php

use Phalcon\Http\Response;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Model\Query;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Class ServicesAPIController
 * Контроллер для работы с услугами.
 * Содержит методы для поиска услуг, CRUD для услуг.
 * Методы для связывания/отвязывания услуг и точек оказания услуг.
 */
class ServicesAPIController extends Controller
{
    /**
     * Возвращает все услуги заданной компании
     *
     * @method GET
     *
     * @param $subjectId
     * @param $subjectType
     * @return string -  массив услуг в виде:
     *      [{serviceid, description, datepublication, pricemin, pricemax,
     *      regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
     *      {Userinfo или Company} }].
     */
    public function getServicesForSubjectAction($subjectId, $subjectType)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $services = Services::getServicesForSubject($subjectId, $subjectType);
            $response->setJsonContent($services);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Возвращает все услуги данного юзера (или его компании)
     *
     * @method GET
     *
     * @params $companyId - если не указан, то будут возвращены
     *
     * @return string -  массив услуг в виде:
     *      [{serviceid, description, datepublication, pricemin, pricemax,
     *      regionid, name, rating, [Categories], [images (массив строк)] {TradePoint}, [Tags],
     *      {Userinfo или Company} }].
     */
    public function getOwnServicesAction($companyId = null)
    {
        if ($this->request->isGet()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            if($companyId == null){
                $services = Services::getServicesForSubject($userId, 0);
            } else{
                if(!SubjectsWithNotDeleted::checkUserHavePermission($userId,$companyId,1,'getServices')){
                    $response->setJsonContent([
                        'status' => STATUS_WRONG,
                        'errors' => ['permission denied']
                    ]);
                    return $response;
                }
                $services = Services::getServicesForSubject($companyId, 1);
            }
            $response->setJsonContent($services);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает услуги. Во-первых, принимает тип запроса в параметре typeQuery:
     * 0 - принимает строку userQuery, центральную точку для поиска - center => [longitude => ..., latitude =>  ...],
     * крайнюю точку для определения радиуса - diagonal => [longitude => ..., latitude =>  ...],
     * массив регионов (id-шников) (regionsId). возвращает
     * список услуг и всего им соответствующего;
     * 1 - запрос на получение элементов интеллектуального поиска. Принимает те же данные, что и в 0-вом запросе.
     * Возвращает массив с типом элемента (строкой - 'company', 'service' и 'category'), id элемента и его названием для отображения в строке
     *  ([{type : ..., id : ..., name : ...}, {...}]);
     * 2 - еще один запрос на получение услуг. Принимает id элемента и тип строкой (type), как отдавалось в запрос 1.
     * Возвращает массив услуг, как в 0-вом запросе.
     * 3 - запрос на получение услуг по категориям. Принимает массив категорий categoriesId, центральную и крайнюю точку
     * и массив регионов, как в 0-вом запросе. Возвращает массив услуг, как везде.
     * 4 - запрос для поиска по области. Центральная точка, крайняя точка, массив регионов, которые попадут в область.
     * Возвращает массив услуг, как везде.
     * 5 - запрос для поиска с фильтрами. Принимает центральную, диагональные точки, массив категорий,
     * минимальную и максимальную цены (priceMin, priceMax) и минимальный рейтинг (ratingMin)
     *
     * @access public
     *
     * @method POST
     *
     * @params int typeQuery (обязательно)
     * @params array center (необязательно) [longitude, latiitude]
     * @params array diagonal (необязательно) [longitude, latiitude]
     * @params string type (необязательно) 'company', 'service', 'category'.
     * @params int id (необязательно)
     * @params string userQuery (необязательно)
     * @params array regionsId (необязательно) массив регионов,
     * @params array categoriesId (необязательно) массив категорий,
     * @params priceMin
     * @params priceMax
     * @params ratingMin
     *
     * @return string json массив [status, service, company/userinfo,[categories],[tradepoints],[images]] или
     *   json массив [status, [{type : ..., id : ..., name : ...}, {...}]].
     */
    public function getServicesAction()
    {
        if ($this->request->isPost() || $this->request->isGet()) {
            $response = new Response();

            if ($this->request->getPost('typeQuery') == 0) {
                if (strlen($this->request->getPost('userQuery')) < 3) {
                    $response->setJsonContent([
                        'status' => STATUS_WRONG,
                        'errors' => ['Слишком маленькая длина запроса']
                    ]);
                    return $response;
                }

                $result = Services::getServicesByQuery($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));


                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;

            } elseif ($this->request->getPost('typeQuery') == 1) {
                $results = Services::getAutocompleteByQuery($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'autocomplete' => $results,
                ]);
                return $response;
            } elseif ($this->request->getPost('typeQuery') == 2) {

                if ($this->request->getPost('type') == 'category') {
                    $categoriesId = $this->request->getPost('id');

                    if(is_array($categoriesId)) {
                        $allCategories = [];
                        foreach ($categoriesId as $categoryId) {
                            $allCategories[] = $categoryId;
                            $childCategories = Categories::findByParentid($categoryId);
                            foreach ($childCategories as $childCategory) {
                                $allCategories[] = $childCategory->getCategoryId();
                            }
                        }
                    } else{
                        $allCategories[] = $categoriesId;
                        $childCategories = Categories::findByParentid($categoriesId);
                        foreach ($childCategories as $childCategory) {
                            $allCategories[] = $childCategory->getCategoryId();
                        }
                    }

                    $result = Services::getServicesByElement($this->request->getPost('type'),
                        $allCategories,
                        $this->request->getPost('center'), $this->request->getPost('diagonal'),
                        $this->request->getPost('regionsId'));

                } else {
                    $result = Services::getServicesByElement($this->request->getPost('type'),
                        array($this->request->getPost('id')),
                        $this->request->getPost('center'), $this->request->getPost('diagonal'),
                        $this->request->getPost('regionsId'));
                }

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;

            } elseif ($this->request->getPost('typeQuery') == 3) {

                $categoriesId = $this->request->getPost('categoriesId');

                if(is_array($categoriesId)) {
                    $allCategories = [];
                    foreach ($categoriesId as $categoryId) {
                        $allCategories[] = $categoryId;
                        $childCategories = Categories::findByParentid($categoryId);
                        foreach ($childCategories as $childCategory) {
                            $allCategories[] = $childCategory->getCategoryId();
                        }
                    }
                } else{
                    $allCategories[] = $categoriesId;
                    $childCategories = Categories::findByParentid($categoriesId);
                    foreach ($childCategories as $childCategory) {
                        $allCategories[] = $childCategory->getCategoryId();
                    }
                }

                $result = Services::getServicesByElement('category',
                    $allCategories,
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;
            } elseif ($this->request->getPost('typeQuery') == 4) {
                $result = Services::getServicesByQuery($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));

                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;
            } elseif($this->request->getPost('typeQuery') == 5){

                $categoriesId = $this->request->getPost('categoriesId');

                if(is_array($categoriesId)) {
                    $allCategories = [];
                    foreach ($categoriesId as $categoryId) {
                        $allCategories[] = $categoryId;
                        $childCategories = Categories::findByParentid($categoryId);
                        foreach ($childCategories as $childCategory) {
                            $allCategories[] = $childCategory->getCategoryId();
                        }
                    }
                } else{
                    $allCategories[] = $categoriesId;
                    $childCategories = Categories::findByParentid($categoriesId);
                    foreach ($childCategories as $childCategory) {
                        $allCategories[] = $childCategory->getCategoryId();
                    }
                }

                $result = Services::getServicesWithFilters($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'),$categoriesId,$this->request->getPost('priceMin'),
                    $this->request->getPost('priceMax'),$this->request->getPost('ratingMin'));
                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;
            } elseif($this->request->getPost('typeQuery') == 6){
                if (strlen($this->request->getPost('userQuery')) < 3) {
                    $response->setJsonContent([
                        'status' => STATUS_WRONG,
                        'errors' => ['Слишком маленькая длина запроса']
                    ]);
                    return $response;
                }

                $result = Services::getServicesByQueryByTags($this->request->getPost('userQuery'),
                    $this->request->getPost('center'), $this->request->getPost('diagonal'),
                    $this->request->getPost('regionsId'));


                $response->setJsonContent([
                    'status' => STATUS_OK,
                    'services' => $result
                ]);
                return $response;
            }

            $response->setJsonContent([
                'status' => STATUS_WRONG,
                'errors' => ['Неправильно указан тип запроса']
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Удаляет указанную услугу
     * @access private
     *
     * @method DELETE
     *
     * @param $serviceId
     * @return Response - с json массивом в формате Status
     */
    public function deleteServiceAction($serviceId)
    {
        if ($this->request->isDelete()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'deleteService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if (!$service->delete()) {
                $errors = [];
                foreach ($service->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Редактирует указанную услугу
     * @access private
     *
     * @method PUT
     *
     * @params serviceId
     * @params description
     * @params name
     * @params priceMin, priceMax (или же вместо них просто price)
     * @params regionId
     * @params deletedTags - массив int-ов - id удаленных тегов
     * @params addedTags - массив строк
     * @params (необязательные) companyId или userId.
     * @return Response - с json массивом в формате Status
     */
    public function editServiceAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPut("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($this->request->getPut("companyId")) {
                $service->setSubjectId($this->request->getPut("companyId"));
                $service->setSubjectType(1);
            } else if ($this->request->getPost("userId")) {
                $service->setSubjectId($this->request->getPut("userId"));
                $service->setSubjectType(0);
            }

            $service->setDescription($this->request->getPut("description"));
            $service->setName($this->request->getPut("name"));

            if ($this->request->getPut("price")) {
                $service->setPriceMin($this->request->getPut("price"));
                $service->setPriceMax($this->request->getPut("price"));
            } else {
                $service->setPriceMin($this->request->getPut("priceMin"));
                $service->setPriceMax($this->request->getPut("priceMax"));
            }

            $service->setRegionId($this->request->getPut("regionId"));

            $this->db->begin();
            if (!$service->save()) {
                $this->db->rollback();
                return SupportClass::getResponseWithErrors($service);
            }

            $deletedTags = $this->request->getPut("deletedTags");

            foreach ($deletedTags as $tagId){
                $serviceTag = ServicesTags::findByIds($service->getServiceId(), $tagId);
                if(!$serviceTag) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrorsFromArray(
                        ["Услуга не связана с указанным якобы удаляемым тегом."]);
                }

                if(!$serviceTag->delete()){
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($serviceTag);
                }
            }

            $addedTags = $this->request->getPut("addedTags");

            foreach($addedTags as $tag){
                $tagObject = new Tags();
                $tagObject->setTag($tag);

                if(!$tagObject->save()){
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($tagObject);
                }

                $serviceTag = new ServicesTags();
                $serviceTag->setServiceId($service->getServiceId());
                $serviceTag->setTagId($tagObject->getTagId());

                if(!$serviceTag->save()){
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($serviceTag);
                }
            }

            $this->db->commit();
            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     *
     */
    /*public function editImageServiceAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPost("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $result = $this->addImageHandler($service->getServiceId());

            $result = json_decode($result->getContent());

            if($result->status != STATUS_OK){
                $response->setJsonContent(
                    [
                        "status" => $result->status,
                        "errors" => $result->errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }*/

    /**
     * Добавляет новую услугу к субъекту. Если не указана компания, можно добавить категории.
     *
     * @method POST
     *
     * @params (необязательные) массив oldPoints - массив id tradePoint-ов,
     * (необязательные) массив newPoints - массив объектов TradePoints
     * @params (необязательные) companyId, description, name, priceMin, priceMax (или же вместо них просто price)
     *           (обязательно) regionId,
     *           (необязательно) longitude, latitude
     *           (необязательно) если не указана компания, можно указать id категорий в массиве categories.
     * @params массив строк tags с тегами.
     * @params прикрепленные изображения. Именование роли не играет.
     *
     * @return string - json array. Если все успешно - [status, serviceId], иначе [status, errors => <массив ошибок>].
     */
    public function addServiceAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $service = new Services();

            if ($this->request->getPost("companyId")) {
                if (!Companies::checkUserHavePermission($userId, $this->request->getPost("companyId"),
                    'addService')) {
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => ['permission error']
                        ]
                    );
                    return $response;
                }

                $service->setSubjectId($this->request->getPost("companyId"));
                $service->setSubjectType(1);

            } else {
                $service->setSubjectId($userId);
                $service->setSubjectType(0);
            }
            $description = $this->request->getPost("description");

            if ($this->request->getPost("video"))
                $description .= "\n\rВидео: " . $this->request->getPost("video");

            $service->setDescription($description);
            $service->setName($this->request->getPost("name"));

            if ($this->request->getPost("price")) {
                $service->setPriceMin($this->request->getPost("price"));
                $service->setPriceMax($this->request->getPost("price"));
            } else {
                $service->setPriceMin($this->request->getPost("priceMin"));
                $service->setPriceMax($this->request->getPost("priceMax"));
            }

            $service->setLongitude($this->request->getPost("longitude"));
            $service->setLatitude($this->request->getPost("latitude"));

            $service->setDatePublication(date('Y-m-d H:i:s'));

            if (!$this->request->getPost("regionId") &&
                !($this->request->getPost("oldPoints") && count($this->request->getPost("oldPoints")) != 0)
                && !($this->request->getPost("newPoints") && count($this->request->getPost("newPoints")) != 0)) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Услуга должна быть связана либо с регионом, либо с точками оказания услуг']
                    ]
                );
                return $response;
            }

            $service->setRegionId($this->request->getPost("regionId"));
            //$service->setRegionId(1);
            $this->db->begin();

            if (!$service->save()) {
                $this->db->rollback();
                $errors = [];
                foreach ($service->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            //
            if ($this->request->getPost("oldPoints")) {
                $points = $this->request->getPost("oldPoints");
                foreach ($points as $point) {
                    $servicePoint = new ServicesPoints();
                    $servicePoint->setServiceId($service->getServiceId());
                    $servicePoint->setPointId($point);

                    if (!$servicePoint->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($servicePoint->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                }
            }

            if ($this->request->getPost("newPoints")) {
                $points = $this->request->getPost("newPoints");

                foreach ($points as $point) {
                    $result = $this->TradePointsAPI->addTradePoint($point);
                    $result = json_decode($result->getContent());

                    if ($result->status != STATUS_OK) {
                        $this->db->rollback();
                        $response->setJsonContent($result);
                        return $response;
                    }
                    foreach ($point->newPhones as $phone) {
                        $_POST['phone'] = $phone;
                        $_POST['pointId'] = $result->pointId;
                        $result2 = $this->PhonesAPI->addPhoneToTradePointAction();
                        $result2 = json_decode($result2->getContent());

                        if ($result2->status != STATUS_OK) {
                            $this->db->rollback();
                            $response->setJsonContent($result2);
                            return $response;
                        }
                    }

                    $servicePoint = new ServicesPoints();
                    $servicePoint->setServiceId($service->getServiceId());
                    $servicePoint->setPointId($result->pointId);

                    if (!$servicePoint->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($servicePoint->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                }
            }

            if (!$this->request->getPost("companyId")) {
                $categories = $this->request->getPost("categories");

                foreach ($categories as $categoryId) {
                    $userCategory = new UsersCategories();
                    $userCategory->setUserId($userId);
                    $userCategory->setCategoryId($categoryId);

                    if (!$userCategory->save()) {
                        $this->db->rollback();
                        $errors = [];
                        foreach ($userCategory->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                }
            }

            //С точками разобрались, теперь надо добавить теги
            $tags = $this->request->getPost("tags");

            foreach($tags as $tag){
                $tagObject = new Tags();
                $tagObject->setTag($tag);

                if(!$tagObject->save()){
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($tagObject);
                }

                $serviceTag = new ServicesTags();
                $serviceTag->setServiceId($service->getServiceId());
                $serviceTag->setTagId($tagObject->getTagId());

                if(!$serviceTag->save()){
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($serviceTag);
                }
            }

            //Добавление изображений
            if ($this->request->hasFiles()) {
                $result = $this->addImagesHandler($service->getServiceId());

                $resultContent = json_decode($result->getContent(), true);
                if($resultContent['status'] != STATUS_OK){
                    //$service->delete(true);
                    $this->db->rollback();
                } else{
                    $this->db->commit();
                    $resultContent['serviceId'] = $service->getServiceId();
                    $result->setJsonContent($resultContent);
                }
                return $result;
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                    'serviceId' => $service->getServiceId()
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет картинки к услуге
     *
     * @method POST
     *
     * @params (обязательно) serviceId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function addImagesAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPost('serviceId'));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $result = $this->addImagesHandler($service->getServiceId());

            return $result;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Удаляет картинку из списка картинок услуги
     *
     * @method DELETE
     *
     * @param $imageId integer id изображения
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageAction($imageId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $image = ImagesServices::findFirstByImageid($imageId);

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор картинки'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $service = Services::findFirstByServiceid($image->getServiceId());

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                    $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Удаляет картинку из списка картинок услуги
     *
     * @method DELETE
     *
     * @param $serviceId - id услуги
     * @param $imageName - название изображения с расширением
     *
     * @return string - json array в формате Status - результат операции
     */
    public function deleteImageByNameAction($serviceId, $imageName)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                    $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "errors" => ['permission error'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $image = ImagesServices::findFirstByImagepath(
                ImageLoader::formFullImagePathFromImageName('services', $serviceId, $imageName));

            if (!$image) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверное название изображения'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            if (!$image->delete()) {
                $errors = [];
                foreach ($image->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Связывает услугу с точкой оказания услуг
     *
     * @method POST
     *
     * @params (обязательные) serviceId, pointId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function linkServiceWithPointAction()
    {
        if ($this->request->isPost() && $this->session->get('auth')) {

            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($this->request->getPost("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(),
                'linkServiceWithPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $servicePoint = new ServicesPoints();
            $servicePoint->setPointId($this->request->getPost("pointId"));
            $servicePoint->setServiceId($this->request->getPost("serviceId"));

            if (!$servicePoint->save()) {
                $errors = [];
                foreach ($servicePoint->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Убирает связь услуги и точки оказания услуг
     *
     * @method DELETE
     *
     * @param $serviceId
     * @param $pointId
     *
     * @return string - json array в формате Status - результат операции
     */
    public function unlinkServiceAndPointAction($serviceId, $pointId)
    {
        if ($this->request->isDelete() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            if (!SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(),
                'unlinkServiceWithPoint')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $servicePoint = ServicesPoints::findByIds($serviceId, $pointId);

            if (!$servicePoint) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Услуга не связана с точкой оказания услуг']
                    ]
                );
                return $response;
            }

            if (!$servicePoint->delete()) {
                $errors = [];
                foreach ($servicePoint->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Подтверждает выполнить заявку на оказание услуги
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return Response - с json массивом в формате Status
     */
    public function confirmRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя подтвердить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTING);

            if (!$request->save()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Предоставляющий услугу субъект утверждает, что выполнил заявку
     *
     * @method PUT
     *
     * @params requestId
     *
     * @return Response - с json массивом в формате Status
     */
    public function performRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_EXECUTING) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя завершить заказ на данном этапе']
                    ]
                );
                return $response;
            }

            $request->setStatus(STATUS_EXECUTED_EXECUTOR);

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Заказчик отменяет заявку.
     *
     * @method PUT
     *
     * @param requestId
     *
     * @return string - json array в формате Status
     */
    public
    function rejectRequestAction()
    {
        if ($this->request->isPut() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $request = Requests::findFirstByRequestid($this->request->getPut("requestId"));

            $service = $request->services;

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(), $service->getSubjectType(), 'editService')) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() != STATUS_WAITING_CONFIRM &&
                $request->getStatus() != STATUS_EXECUTING &&
                $request->getStatus() != STATUS_EXECUTED_EXECUTOR &&
                $request->getStatus() != STATUS_EXECUTED_CLIENT) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Нельзя отказаться от заказа на данном этапе']
                    ]
                );
                return $response;
            }

            if ($request->getStatus() == STATUS_WAITING_CONFIRM)
                $request->setStatus(STATUS_NOT_CONFIRMED);
            else {
                $request->setStatus(STATUS_NOT_EXECUTED);
            }

            if (!$request->update()) {
                $errors = [];
                foreach ($request->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Добавляет картинку к услуге.
     * @param $serviceId
     * @return Response с json массивом типа Status
     */
    /*public function addImagesHandler($serviceId)
    {
        $response = new Response();
        include(APP_PATH . '/library/SimpleImage.php');
        // Проверяем установлен ли массив файлов и массив с переданными данными
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $filenames = [];

            $images = ImagesServices::findByServiceid($serviceId);
            $countImages = count($images);

            $this->db->begin();

            foreach($files as $file) {


                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $format = $imageFormat;
                if ($imageFormat == 'jpeg' || 'jpg')
                    $imageFormat = IMAGETYPE_JPEG;
                elseif ($imageFormat == 'png')
                    $imageFormat = IMAGETYPE_PNG;
                elseif ($imageFormat == 'gif')
                    $imageFormat = IMAGETYPE_GIF;
                else {
                    $this->db->rollback();
                    $response->setJsonContent(
                        [
                            "error" => ['Данный формат не поддерживается'],
                            "status" => STATUS_WRONG
                        ]
                    );
                    return $response;
                }

                $filename = BASE_PATH . '/img/services/' . hash('crc32', $service->getServiceId()) . '_'
                    . $countImages . '.' . $format;

                $imageFullName = str_replace(BASE_PATH, '', $filename);

                $newimage = new ImagesServices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath($imageFullName);

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }
                $filenames[] = ['name' => $filename, 'format' => $imageFormat, 'tempname' => $file->getTempName()];
                $countImages+=1;
            }

            foreach($filenames as $filename){
                $image = new SimpleImage();
                $image->load($filename['tempname']);
                $image->resizeToWidth(200);
                $image->save($filename['name'], $filename['format']);
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );
        return $response;
    }*/

    /**
     * Добавляет все отправленные файлы изображений к услуге. Общее количество
     * фотографий для одной услуги на данный момент не более 10.
     *
     * @param $serviceId
     * @return Response с json массивом типа Status
     */
    public function addImagesHandler($serviceId)
    {
        include(APP_PATH . '/library/SimpleImage.php');
        $response = new Response();
        if ($this->request->hasFiles()) {
            $files = $this->request->getUploadedFiles();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent(
                    [
                        "errors" => ['Неверный идентификатор услуги'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $filenames = [];

            $images = ImagesServices::findByServiceid($serviceId);
            $countImages = count($images);

            if (($countImages + count($files)) > ImagesServices::MAX_IMAGES) {
                $response->setJsonContent(
                    [
                        "errors" => ['Слишком много изображений для услуги. 
                        Можно сохранить для одной услуги не более чем ' . ImagesServices::MAX_IMAGES . ' изображений'],
                        "status" => STATUS_WRONG
                    ]
                );
                return $response;
            }

            $this->db->begin();
            $imagesIds = [];

            foreach ($files as $file) {

                $newimage = new ImagesServices();
                $newimage->setServiceId($serviceId);
                $newimage->setImagePath("");

                if (!$newimage->save()) {
                    $errors = [];
                    $this->db->rollback();
                    foreach ($newimage->getMessages() as $message) {
                        $errors[] = $message->getMessage();
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => $errors
                        ]
                    );
                    return $response;
                }

                $imagesIds[] = $newimage->getImageId();

                $imageFormat = pathinfo($file->getName(), PATHINFO_EXTENSION);

                $filename = ImageLoader::formFullImageName('services', $imageFormat, $serviceId, $newimage->getImageId());

                $newimage->setImagePath($filename);

                if (!$newimage->update()) {
                    $this->db->rollback();
                    return SupportClass::getResponseWithErrors($newimage);
                }
            }
            $i = 0;
            foreach ($files as $file) {
                $result = ImageLoader::loadService($file->getTempName(), $file->getName(), $serviceId, $imagesIds[$i]);
                $i++;
                if ($result != ImageLoader::RESULT_ALL_OK || $result === null) {
                    if ($result == ImageLoader::RESULT_ERROR_FORMAT_NOT_SUPPORTED) {
                        $error = 'Формат одного из изображений не поддерживается';
                    } elseif ($result == ImageLoader::RESULT_ERROR_NOT_SAVED) {
                        $error = 'Не удалось сохранить изображение';
                    } else {
                        $error = 'Ошибка при загрузке изображения';
                    }
                    $response->setJsonContent(
                        [
                            "status" => STATUS_WRONG,
                            "errors" => [$error]
                        ]
                    );
                    return $response;
                }
            }

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;
        }
        $response->setJsonContent(
            [
                "status" => STATUS_OK
            ]
        );
        return $response;
    }

    /**
     * Увеличивает на 1 счетчик числа просмотров услуги.
     * @method PUT
     * @params $serviceId
     * @return string - json array в формате Status
     */
    public
    function incrementNumberOfDisplayForServiceAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();

            $service = Services::findFirstByServiceid($this->request->getPut("serviceId"));

            if (!$service) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['Такая услуга не существует']
                    ]
                );
                return $response;
            }

            $service->setNumberOfDisplay($service->getNumberOfDisplay() + 1);

            if (!$service->update()) {
                $errors = [];
                foreach ($service->getMessages() as $message) {
                    $errors[] = $message->getMessage();
                }
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => $errors
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_OK
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает все заказы, которые могут быть связаны с данной услугой.
     * На самом деле нет, конечно же. Логики того, как это будет делаться нет.
     *
     * @method GET
     *
     * @param $serviceId
     * @return string - json array tasks
     */
    public
    function getTasksForService($serviceId)
    {
        if ($this->request->isGet() && $this->session->get('auth')) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $service = Services::findFirstByServiceid($serviceId);

            if (!$service || !SubjectsWithNotDeleted::checkUserHavePermission($userId, $service->getSubjectId(),
                    $service->getSubjectType(), 'getTasksForSubject')) {

                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ['permission error']
                    ]
                );
                return $response;
            }

            $result = Services::getTasksForService($serviceId);

            $response->setJsonContent([
                'status' => STATUS_OK,
                'services' => $result
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    /**
     * Возвращает публичную информацию об услуге.
     * Публичный доступ.
     *
     * @method GET
     *
     * @param $serviceId
     *
     * @return string - json array {status, service, [points => {point, [phones]}], reviews (до двух)}
     */
    public
    function getServiceInfoAction($serviceId)
    {
        if ($this->request->isGet()) {
            $response = new Response();

            $service = Services::findFirstByServiceid($serviceId);

            if (!$service) {
                $response->setJsonContent([
                    'status' => STATUS_WRONG,
                    'errors' => ['Услуга не существует']
                ]);

                return $response;
            }

            $service = $service->clipToPublic();

            $points = Services::getPointsForService($serviceId);

            $images = ImagesServices::findByServiceid($serviceId);

            $points2 = [];
            foreach ($points as $point) {
                $points2[] = ['point' => $point->clipToPublic(),
                    'phones' => PhonesPoints::getPhonesForPoint($point->getPointId())];
            }

            $reviews = Reviews::getReviewsForService2($serviceId, 2);

            //$reviews = Reviews::getReviewsForService2($serviceId);

            $reviews2_ar = [];
            foreach ($reviews as $review) {
                $reviews2['review'] = json_decode($review['review'], true);

                unset($reviews2['review']['deleted']);
                unset($reviews2['review']['deletedcascade']);
                unset($reviews2['review']['fake']);
                unset($reviews2['review']['subjectid']);
                unset($reviews2['review']['subjecttype']);
                unset($reviews2['review']['objectid']);
                unset($reviews2['review']['objecttype']);

                unset($reviews2['review']['userid']);
                $subject = json_decode($review['subject'], true);
                if (isset($subject['reviewid'])) {
                    $userinfo = new Userinfo();
                    $userinfo->setFirstname($reviews2['review']['fakename']);
                    //$reviews2['userinfo'] = $userinfo;
                    $reviews2['userinfo']['firstname'] = $userinfo->getFirstname();
                    $reviews2['userinfo']['lastname'] = $userinfo->getLastname();
                    $reviews2['userinfo']['pathtophoto'] = $userinfo->getPathToPhoto();
                    $reviews2['userinfo']['userid'] = $userinfo->getUserId();
                } else if (isset($subject['companyid'])) {
                    //$reviews2['company'] = $subject;
                    /*unset($reviews2['company']['deleted']);
                    unset($reviews2['company']['deletedcascade']);
                    unset($reviews2['company']['ismaster']);
                    unset($reviews2['company']['yandexMapPages']);*/

                    $reviews2['company']['name'] = $subject['name'];
                    $reviews2['company']['fullname'] = $subject['fullname'];
                    $reviews2['company']['logotype'] = $subject['logotype'];
                    $reviews2['company']['companyid'] = $subject['companyid'];
                } else {
                    //$reviews2['userinfo'] = $subject;
                    $reviews2['userinfo']['firstname'] = $subject['firstname'];
                    $reviews2['userinfo']['lastname'] = $subject['lastname'];
                    $reviews2['userinfo']['pathtophoto'] = $subject['pathtophoto'];
                    $reviews2['userinfo']['userid'] = $subject['userid'];
                }
                unset($reviews2['review']['fakename']);

                $reviews2_ar[] = $reviews2;
            }

            if ($service['subjecttype'] == 1) {
                $str = 'company';
                $binder = Companies::findFirstByCompanyid($service['subjectid']);
            } else {
                $str = 'user';
                $binder = Userinfo::findFirstByUserid($service['subjectid']);
            }
            //test
            /*$str = 'user';
            $binder = Userinfo::findFirstByUserid(6);*/

            $response->setJsonContent([
                'status' => STATUS_OK,
                'service' => $service,
                'points' => $points2,
                'images' => $images,
                'reviews' => $reviews2_ar,
                $str => $binder
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }

    public
    function addImagesToAllServicesAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $services = Services::find();

            foreach ($services as $service) {
                $randnumber = rand(0, 3);

                if ($randnumber > 0) {
                    $imageserv = new ImagesServices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/desert.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                    $randnumber--;
                }

                if ($randnumber > 0) {
                    $imageserv = new ImagesServices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/butterfly.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                    $randnumber--;
                }
                if ($randnumber > 0) {
                    $imageserv = new ImagesServices();
                    $imageserv->setServiceId($service->getServiceId());
                    $imageserv->setImagePath('/images/services/flower.jpg');
                    if (!$imageserv->save()) {
                        $errors = [];
                        foreach ($imageserv->getMessages() as $message) {
                            $errors[] = $message->getMessage();
                        }
                        $response->setJsonContent(
                            [
                                "status" => STATUS_WRONG,
                                "errors" => $errors
                            ]
                        );
                        return $response;
                    }
                }
            }

            $response->setJsonContent([
                'status' => STATUS_OK,
            ]);

            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);

            throw $exception;
        }
    }
}