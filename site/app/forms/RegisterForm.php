<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class RegisterForm extends Form
{
    public function initialize($entity = null, $options = null)
    {

        $firstname = new Text('firstname', ['class' => 'form-control']);
        $firstname->setLabel('Имя:');
        //$phone->setFilters(['alpha']);
        $firstname->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести имя'
            ])
        ]);
        $this->add($firstname);

        $lastname = new Text('lastname', ['class' => 'form-control']);
        $lastname->setLabel('Фамилия:');
        //$phone->setFilters(['alpha']);
        $lastname->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести имя'
            ])
        ]);
        $this->add($lastname);

        $phone = new Text('phone', ['class' => 'form-control']);
        $phone->setLabel('Номер телефона:');
        //$phone->setFilters(['alpha']);
        $phone->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести номер телефона'
            ])
        ]);

        $this->add($phone);

        $email = new Text('email', ['class' => 'form-control']);
        $email->setLabel('E-Mail');
        $email->setFilters('email');
        $email->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести e-mail.'
            ]),
            new Email([
                'message' => 'Некорректный E-mail'
            ])
        ]);

        $this->add($email);

        $phone = new Text('phone', ['class' => 'form-control']);
        $phone->setLabel('Номер телефона:');
        //$phone->setFilters(['alpha']);
        $phone->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести номер телефона'
            ])
        ]);
        $this->add($phone);


        $male = new Select(
            "male",
            ['1'=>'Мужской','0'=>'Женский'],
            [
                "useEmpty"   => false,
                'class' => 'form-control'
            ]
        );
        $male->setLabel('Пол:');
        //$phone->setFilters(['alpha']);
        $male->addValidators([
            new PresenceOf([
                'message' => 'Необходимо указать пол'
            ])
        ]);

        $this->add($male);


        // Password
        $password = new Password('password',['class' => 'form-control']);
        $password->setLabel('Пароль:');
        $password->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести пароль'
            ])
        ]);
        $this->add($password);

        // Confirm Password
        $repeatPassword = new Password('repeatPassword',['class' => 'form-control']);
        $repeatPassword->setLabel('Повторите пароль:');
        $repeatPassword->addValidators([
            new PresenceOf([
                'message' => 'Повторите свой пароль'
            ])
        ]);
        $this->add($repeatPassword);

    }
}
