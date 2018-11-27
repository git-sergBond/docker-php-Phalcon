<?php

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Http\Response;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\Dispatcher\Exception as DispatcherException;
use Phalcon\Mvc\Dispatcher;

/**
 * Контроллер для работы с категориями.
 * Здесь методы для получения категорий и для работы с подписками
 * пользователей на категории.
 *
 */
class CategoriesAPIController extends Controller
{
    /**
     * Index action
     */
    public function indexAction()
    {
        if ($this->request->isPost() || $this->request->isGet()) {
            $categories = Categories::find();
            return json_encode($categories);
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает категории
     *
     * @method GET
     *
     * @return string - json array с категориями
     */
    public function getCategoriesAction()
    {
        if ($this->request->isGet()) {

            $categories = Categories::find(['categoryid > 20','order' => 'parentid DESC']);

            $response = new Response();
            $response->setJsonContent([
                'status' => STATUS_OK,
                'categories' => $categories
            ]);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Подписывает текущего пользователя на указанную категорию.
     *
     * @method POST
     * @params categoryId, radius
     * @return string - json array Status
     */
    public function setFavouriteAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $categoryId = $this->request->getPost('categoryId');

            $fav = FavoriteCategories::findByIds($userId, $categoryId);

            if (!$fav) {
                $fav = new FavoriteCategories();
                $fav->setCategoryId($categoryId);
                $fav->setUserId($userId);
                $fav->setRadius($this->request->getPost('radius'));

                if (!$fav->save()) {
                    $errors = [];
                    foreach ($fav->getMessages() as $message) {
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
                        "status" => STATUS_OK,
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_ALREADY_EXISTS,
                    "errors" => ["Пользователь уже подписан"]
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Меняет радиус на получение уведомлений для подписки на категорию
     * @method PUT
     * @params radius, categoryId
     * @return string - json array Status
     */
    public function editRadiusInFavouriteAction()
    {
        if ($this->request->isPut()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $categoryId = $this->request->getPut('categoryId');

            $fav = FavoriteCategories::findByIds($userId, $categoryId);

            if (!$fav) {
                $response->setJsonContent(
                    [
                        "status" => STATUS_WRONG,
                        "errors" => ["Пользователь не подписан"]
                    ]
                );
                return $response;
            }

            $fav->setRadius($this->request->getPut('radius'));

            if (!$fav->update()) {
                $errors = [];
                foreach ($fav->getMessages() as $message) {
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
                    "status" => STATUS_OK,
                ]
            );
            return $response;


        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Отписывает текущего пользователя от категории
     * @method DELETE
     * @param $categoryId
     * @return string - json array Status
     */
    public function deleteFavouriteAction($categoryId)
    {
        if ($this->request->isDelete()) {
            $response = new Response();
            $auth = $this->session->get('auth');
            $userId = $auth['id'];

            $fav = FavoriteCategories::findByIds($userId, $categoryId);

            if ($fav) {
                if (!$fav->delete()) {
                    $errors = [];
                    foreach ($fav->getMessages() as $message) {
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
                        "status" => STATUS_OK,
                    ]
                );
                return $response;
            }

            $response->setJsonContent(
                [
                    "status" => STATUS_WRONG,
                    "errors" => ["Пользователь не подписан на категорию"]
                ]
            );

            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает все подписки пользователя на категории
     * @GET
     * @return string - json array - подписки пользователя
     */
    public function getFavouritesAction()
    {
        if ($this->request->isGet()) {
            $auth = $this->session->get('auth');
            $userId = $auth['id'];
            $response = new Response();
            $fav = FavoriteCategories::findByUserid($userId);
            $response->setJsonContent($fav);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    /**
     * Возвращает категории в удобном для сайта виде
     *
     * @method GET
     *
     * @return string - json array с категориями
     */
    public function getCategoriesForSiteAction()
    {
        if ($this->request->isGet()) {

            $categories = Categories::find(['categoryid > 20','order' => 'parentid DESC']);

            $categories2 = [];
            foreach ($categories as $category) {
                /*if ($category->getParentId() == null) {
                    $categories2[$category->getCategoryId()] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                        'description' => $category->getDescription(), 'img' => $category->getImg(),
                        'child' => []];
                } else{
                    $categories2[$category->getParentId()]['child'][] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                        'description' => $category->getDescription(), 'img' => $category->getImg(),
                        'child' => []];
                }*/
                if ($category->getParentId() == null) {
                    $categories2[] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                        'description' => $category->getDescription(), 'img' => $category->getImg(),
                        'child' => []];
                } else {
                    for ($i = 0; $i < count($categories2); $i++)
                        if ($categories2[$i]['id'] == $category->getParentId()) {
                            $categories2[$i]['child'][] = ['id' => $category->getCategoryId(), 'name' => $category->getCategoryName(),
                                'description' => $category->getDescription(), 'img' => $category->getImg(),
                                'child' => [], 'check' => false];
                            break;
                        }
                }
            }
            $response = new Response();
            $response->setJsonContent([
                'status' => STATUS_OK,
                'categories' => $categories2]);
            return $response;
        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function editCategoryAction()
    {
        if ($this->request->isPost()) {

            $category = Categories::findFirstByCategoryid($this->request->getPost('categoryId'));

            $category->setDescription($this->request->getPost('description'));
            $category->setImg($this->request->getPost('img'));
            $category->setParentId($this->request->getPost('parentId'));
            $category->setCategoryName($this->request->getPost('categoryName'));

            $category->save();
            return $category->save();

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addCategoryAction()
    {
        if ($this->request->isPost()) {

            $category = new Categories();

            $category->setDescription($this->request->getPost('description'));
            $category->setImg($this->request->getPost('img'));
            $category->setParentId($this->request->getPost('parentId'));
            $category->setCategoryName($this->request->getPost('categoryName'));

            $category->save();
            return $category->save();

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }

    public function addSomeCategoriesAction()
    {
        if ($this->request->isPost()) {
            $response = new Response();

            $categories = [['name' => 'Питание', 'child' => ['Рестораны', 'Бары, пабы', 'Столовые','Кофейни','Кондитерские, торты на заказ',
                'Быстрое питание', 'Доставка еды, воды', 'Кейтеринг', 'Другое'],'img' => '/images/categories/питание.jpg'],
                ['name' => 'Развлечения и отдых', 'child' => [], 'img' => '/images/categories/развлечения-и-отдых.jpg'],
                ['name' => 'Авто и перевозки', 'child' => [], 'img' => '/images/categories/авто-и-перевозки.jpg'],
                ['name' => 'Красота', 'child' => [], 'img' => '/images/categories/красота.jpg'],
                ['name' =>'Спорт','child' => [],'img' => '/images/categories/спорт.jpg'],
                ['name' =>'Медицина','child' => [],'img' => '/images/categories/медицина.jpg'],
                ['name' =>'Недвижимость','child' => [],'img' => '/images/categories/недвижимость.jpg'],
                ['name' =>'Ремонт и строительство','child' => [],'img' => '/images/categories/ремонт-и-строительство.jpg'],
                ['name' =>'IT, интернет, телеком','child' => [],'img' => '/images/categories/интернет-и-it.jpg'],
                ['name' =>'Деловые услуги','child' => [],'img' => '/images/categories/деловые услуги.jpg'],
                ['name' =>'Курьерские поручения','child' => ['Курьерские услуги', 'Почтовые услуги', 'Доставка цветов',
                    'Другое'],'img' => '/images/categories/курьерские-поручения.jpg'],
                ['name' =>'Бытовые услуги','child' => [],'img' => '/images/categories/бытовые услуги.jpg'],
                ['name' =>'Клининг','child' => [],'img' => '/images/categories/клининг.jpg'],
                ['name' =>'Обучение','child' => [],'img' => '/images/categories/обучение.jpg'],
                ['name' =>'Праздники, мероприятия','child' => [],'img' => '/images/categories/праздники.jpg'],
                ['name' =>'Животные','child' => [],'img' => '/images/categories/животные.jpg'],
                ['name' =>'Реклама, полиграфия','child' => [],'img' => '/images/categories/реклама.jpg'],
                ['name' =>'Сад, благоустройство','child' => [],'img' => '/images/categories/сад.jpg'],
                ['name' =>'Охрана, безопасность','child' => [],'img' => '/images/categories/охрана.jpg'],
                ['name' =>'Патронажн, уход','child' => [],'img' => '/images/categories/уход.jpg'],
                ['name' =>'Друг на час','child' => [],'img' => '/images/categories/друг-на-час.jpg'],
                ['name' =>'Благотворительность','child' => [],'img' => '/images/categories/благотвортельность.jpg'],
                ['name' =>'Ритуальные услуги','child' => [],'img' => '/images/categories/ритуальные-услуги.jpg'],
            ];

            $this->db->begin();
            foreach ($categories as $category){
                $categoryObj = new Categories();
                $categoryObj->setCategoryName($category['name']);
                $categoryObj->setImg($category['img']);

                if(!$categoryObj->save()){
                    $this->db->rollback();
                    $errors = [];
                    foreach ($categoryObj->getMessages() as $message) {
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

                foreach($category['child'] as $child){
                    $categoryObj2 = new Categories();
                    $categoryObj2->setCategoryName($child);
                    $categoryObj2->setParentId($categoryObj->getCategoryId());

                    if(!$categoryObj2->save()){
                        $this->db->rollback();
                        $errors = [];
                        foreach ($categoryObj2->getMessages() as $message) {
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

            $this->db->commit();

            $response->setJsonContent(
                [
                    "status" => STATUS_OK,
                ]
            );
            return $response;

        } else {
            $exception = new DispatcherException("Ничего не найдено", Dispatcher::EXCEPTION_HANDLER_NOT_FOUND);
            throw $exception;
        }
    }
}
