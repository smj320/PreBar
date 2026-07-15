<?php
/*
 * Form template with hydrator
 */
namespace App\Form;

use Laminas\Form\Form;
use Laminas\Form\Element;
use Laminas\Hydrator\ReflectionHydrator;

class ArticleForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('message-form');

        // Hydratorの設定
        $this->setHydrator(new ReflectionHydrator());

        $this->add([
            'name' => 'content',
            'type' => Element\Textarea::class,
            'options' => ['label' => 'メッセージ'],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Button::class,
            'options' => ['label' => '送信'],
            'attributes' => ['type' => 'submit'],
        ]);
    }
}