<?php

use Phalcon\Mvc\User\Component;

/**
 * Elements
 *
 * Helps to build UI elements for the application
 */
class Elements extends Component
{
    private $_headerMenu = [
        'navbar-left' => [

            'Userinfo' => [
                'caption' => 'Профиль',
                'action' => 'index'
            ],
            'tasks' => [
                'caption' => 'Задания',
                'action' => 'mytasks/'
            ],
            'auctions' => [
                'caption' => 'Тендеры',
                'action' => 'index'
            ],
        ],
        'navbar-right' => [
            'users' => [
                'caption' => 'Модерация',
                'action' => 'index'
            ],
            'register' => [
                'caption' => 'Зарегистрироваться',
                'action' => 'index'
            ],
            'session' => [
                'caption' => 'Войти',
                'action' => 'index'
            ]
        ]
    ];

    private $_tabs = [
        'Пользователи' => [
            'controller' => 'users',
            'action' => 'index',
        ],
        'Задания' => [
            'controller' => 'tasksModer',
            'action' => 'index',
        ],
        'Тендеры' => [
            'controller' => 'auctionsModer',
            'action' => 'index',
        ],
        'Предложения' => [
            'controller' => 'offers',
            'action' => 'index',
        ],
        'Логи' => [
            'controller' => 'logs',
            'action' => 'index',
        ],
        'Категории' => [
            'controller' => 'categories',
            'action' => 'index',
        ],
        'Сообщения' => [
            'controller' => 'messages',
            'action' => 'index',
        ]
    ];

    /**
     * Builds header menu with left and right items
     *
     * @return string
     */

    public function getMenu()
    {
        $auth = $this->session->get('auth');
        if ($auth) {
            $this->_headerMenu['navbar-right']['session'] = [
                'caption' => 'Выйти',
                'action' => 'end'
            ];

            $this->_headerMenu['navbar-left']['tasks']['action'].=$auth['id'];

            unset($this->_headerMenu['navbar-right']['register']);
            if($auth['role']!= "Moderator") {
                unset($this->_headerMenu['navbar-right']['users']);
            }
        } else {
            unset($this->_headerMenu['navbar-right']['users']);
            unset($this->_headerMenu['navbar-left']['Userinfo']);
            unset($this->_headerMenu['navbar-left']['tasks']);
        }

        $controllerName = $this->view->getControllerName();
        foreach ($this->_headerMenu as $position => $menu) {
            echo '<div class="collapse navbar-collapse">';
            echo '<ul class="nav navbar-nav ', $position, '">';
            foreach ($menu as $controller => $option) {
                if ($controllerName == $controller) {
                    echo '<li class="active">';
                } else {
                    echo '<li>';
                }
                echo $this->tag->linkTo($controller . '/' . $option['action'], $option['caption']);
                echo '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }

    }

    /**
     * Returns menu tabs
     */
    public function getTabs()
    {
        $controllerName = $this->view->getControllerName();
        $actionName = $this->view->getActionName();
        echo '<ul class="nav nav-tabs">';
        foreach ($this->_tabs as $caption => $option) {
            if ($option['controller'] == $controllerName && ($option['action'] == $actionName)) {
                echo '<li class="active">';
            } else {
                echo '<li>';
            }
            echo $this->tag->linkTo($option['controller'] . '/' . $option['action'], $caption), '</li>';
        }
        echo '</ul>';
    }


    private $_tasks=[

    ];
    private $_leftMenu=[
        'index'=>[
            'caption' => 'Главная',
            'action' => ''
        ],
        'tasks' => [
            'caption' => 'Задания',
            'action' => 'mytasks',
            'childs'=>[
                [
                    'caption' => 'Создать задание',
                    'action' => 'new'
                ],
                [
                    'caption' => 'Мои задания',
                    'action' => 'mytasks'
                ],
                [
                    'caption' => 'Мне выполняют задания',
                    'action' => 'doingtasks'
                ],
                [
                    'caption' => 'Я выполняю задания',
                    'action' => 'workingtasks'
                ],
                ]
        ],
        'Userinfo' => [
            'caption' => 'Профиль',
            'action' => 'index'
        ],

        'auctions' => [
            'caption' => 'Тендеры',
            'action' => 'index'
        ],
    ];
    private $_rightMenu=[

        'session' => [
            'caption' => 'Войти',
            'action' => 'index'
        ],
        'register' => [
            'caption' => 'Зарегистрироваться',
            'action' => 'index'
        ],
        'users' => [
            'caption' => 'Модерация',
            'action' => 'index'
        ]

    ];
    public function getLeftMenu()
    {

        $auth = $this->session->get('auth');
        if ($auth) {
           $this->_rightMenu['session']=[
               'caption' => 'Выйти',
               'action' => 'end'
           ];
            unset($this->_rightMenu['register']);
            if($auth['role']!= "Moderator")
                unset($this->_rightMenu['users']);
        }
        else
        {
            unset($this->_leftMenu['tasks']);
            unset($this->_leftMenu['Userinfo']);
            unset($this->_rightMenu['users']);
        }
            $controllerName = $this->view->getControllerName();
            foreach ($this->_leftMenu as $controller=>$option)
            {
                $class='';
                $f=false;
                if(isset($option['childs'])) {
                    $class = 'tr-dropdown ';
                    $f=true;
                }
                if ($controllerName == $controller)
                    $class=$class.'active';

                if($controller=='auctions'||$controller=='index')
                    $link=$this->tag->linkTo($controller . '/' . $option['action'],$option['caption']);
                else
                    $link=$this->tag->linkTo($controller . '/' . $option['action'].'/'.$auth['id'],$option['caption']);


                echo '<li class="'.$class.'">'.$link;
                if($f)
                {
                    echo '<ul class="tr-dropdown-menu tr-list fadeInUp" role="menu">';
                    foreach ($option['childs'] as $child=>$fields)
                    {
                        if($fields['action']!='new')
                            $sublink=$this->tag->linkTo($controller . '/' . $fields['action'].'/'.$auth['id'],$fields['caption']);
                        else
                            $sublink=$this->tag->linkTo($controller . '/' . $fields['action'],$fields['caption']);
                        echo '<li>'.$sublink.'</li>';
                    }
                    echo '</ul>';
                }
                echo '</li>';

            }
        }


    public function getRightMenu()
    {
        foreach ($this->_rightMenu as $controller=>$option)
        {
            $link=$this->tag->linkTo($controller . '/' . $option['action'],$option['caption']);
            echo '<li>'.$link.'</li>';
        }
    }

    public function getFooter()
    {
        echo '<div class="footer">
		<div class="footer-top section-padding">
			<div class="container">
				<div class="row">
					<div class="col-sm-3">
						<div class="footer-widget">
							<h3>О нас</h3>
							<ul class="tr-list">
								<li><a href="#">О подработке</a></li>
								<li><a href="#">Правила и соглашения</a></li>
								<li><a href="#">Партнеры</a></li>
								<li><a href="#">Обратная связь</a></li>
								<li><a href="#">Контакты</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="footer-widget">
							<h3>Заказчики</h3>
							<ul class="tr-list">
								<li><a href="#">Создание задания и тендера</a></li>
								<li><a href="#">Статьи</a></li>
								<li><a href="#">FAQ</a></li>
								<li><a href="#">Видео инструкции</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="footer-widget">
							<h3>Исполнители</h3>
							<ul class="tr-list">
								<li><a href="#">Вступление в тендер и создание предложения</a></li>
								<li><a href="#">Статьи</a></li>
								<li><a href="#">FAQ</a></li>
								<li><a href="#">Видео инструкции</a></li>
							</ul>
						</div>
					</div>
					<div class="col-sm-3">
						<div class="footer-widget">
							<h3>Новостная рассылка</h3>
							<p>Подпишитесь на новостную рассылку, чтобы первым узнавать о новых возможностях podrabotka.ru</p>
							<form class="contact-form" method="post" action="#">
								<div class="form-group">
								    <input type="email" class="form-control" placeholder="Ваш email">
								</div>             
							    <div class="form-group">
							        <button type="button" class="btn btn-primary">Подписаться</button>
							    </div>
							</form>
						</div>
					</div>
				</div><!-- /.row -->
			</div><!-- /.container -->
		</div><!-- /.footer-top -->
		<div class="footer-bottom">
			<div class="container">
				<div class="copyright">
					<p>Темчишен Андрей и Титов Герман &copy; Company 2017 <a href="#">Podrabotka.ru.</a> All rights reserved.</p>
				</div>
				<div class="footer-social pull-right">
					<ul class="tr-list">
						<li><a href="#" title="Facebook"><i class="fa fa-facebook"></i></a></li>
						<li><a href="#" title="Twitter"><i class="fa fa-twitter"></i></a></li>
						<li><a href="#" title="Google Plus"><i class="fa fa-google-plus"></i></a></li>
						<li><a href="#" title="Youtube"><i class="fa fa-youtube"></i></a></li>
					</ul>
				</div>
			</div>
		</div><!-- /.footer-bottom -->
	</div><!-- /.footer -->';
    }

    public function getRating($podpis, $rating)
    {
        echo '<div class="rating">';
        for($i=10;$i>0;$i--)
        {
            $class='';
            if($i%2==0)
                $class='full';
            else
                $class='half';

            if($i!=$rating)
            {
                echo "<input type=\"radio\" disabled id=\"star".$podpis.$i."\"/><label class = \"".$class."\" for=\"star".$podpis.$i."\"></label>";
            }
            else
            {
                echo "<input type=\"radio\" disabled checked id=\"star".$podpis.$i."\"/><label class = \"".$class."\" for=\"star".$podpis.$i."\"></label>";
            }
        }
        echo '</div>';
    }
}
