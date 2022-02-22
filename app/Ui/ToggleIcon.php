<?php

namespace App\Ui;

class ToggleIcon extends \Atk4\Ui\Table\Column\Link
{
    public $statusField;
    public $button;
    public $cb;

    public function __construct(string $statusField, $cb = null, $button = null)
    {
        $this->statusField = $statusField;
        $this->button = $button;
        $this->cb = $cb;
        
        parent::__construct();
    }

    protected function init(): void
    {
        parent::init();

        $this->vp = $this->table->add(new \Atk4\Ui\JsCallback());

        $this->vp->set($this->cb);
    }

    public function getDataCellTemplate(\Atk4\Data\Field $f = null)
    {
        $this->table->on('click', 'div.' . $this->short_name)->atkAjaxec([
            'uri' => $this->vp->getJSURL(),
            'uri_options' => ['id' => $this->table->jsRow()->data('id')],
        ]);

        $a = '<div id="{$_id}" class="ui {$_btn} ' . $this->short_name . '">';
        $a .= '<i id="{$_icon_id}" class="ui {$_icon_class' . $this->short_name . '} icon"></i>';
        $a .= '{Content}{/} {$_name}</div>';

        return $a;
    }

    public function getHTMLTags($row, $field) 
    {
        $icon = '';
        $btn = '';
        $name = '';

        if (array_key_exists('inactiveIcon', $this->button)){
            $icon = $this->button['inactiveIcon'];
            $btn = 'compact icon button';
        }
        
        if (!$row->get($this->statusField)) {
            $icon = $this->button['icon'];
            $btn = 'compact icon basic button';
            $name = $this->button['name'] ?? '';
        }

        return [
            '_icon_class' . $this->short_name => $icon,
            '_btn' => $btn,
            '_name' => $name
        ];
    }

}