<?php

namespace App\Ui\Menus;

class AdminMenu
{
    public function init(\Atk4\Ui\Menu $menu) 
    {
        $menu->addItem(['Табло', 'icon' => 'chart line', 'active' => $this->isThatRoute('dashboard')], [route('dashboard')]);

        $menu->addItem(['Администратори', 'icon' => \App\Models\Admin::ICON, 'active' => $this->isThatRoute('admin.index')], [route('admin.index')]);

        $menu->addItem(['Преподаватели', 'icon' => \App\Models\Teacher::ICON, 'active' => $this->isThatRoute('teacher.index')], [route('teacher.index')]);

        $menu->addItem(['Предмети', 'icon' => \App\Models\Discipline::ICON, 'active' => $this->isThatRoute('discipline.index')], [route('discipline.index')]);

        $menu->addItem(['Групи', 'icon' => \App\Models\Group::ICON, 'active' => $this->isThatRoute('group.index')], [route('group.index')]);

        $menu->addItem(['Специалности', 'icon' => \App\Models\Specialty::ICON, 'active' => $this->isThatRoute('specialty.index')], [route('specialty.index')]);
    }
    
    private function isThatRoute(string $route)
    {
        return request()->route()->getName() === $route;
    }

}