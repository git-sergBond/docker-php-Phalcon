<?php

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Password;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Email;

class AuthorizedForm extends Form
{
    public function initialize($entity = null, $options = null)
    {

        $email = new Text('email', ['class' => 'form-control']);
        $email->setLabel('E-Mail или телефон');
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

        // Password
        $password = new Password('password',['class' => 'form-control']);
        $password->setLabel('Пароль:');
        $password->addValidators([
            new PresenceOf([
                'message' => 'Необходимо ввести пароль'
            ])
        ]);
        $this->add($password);
    }
}