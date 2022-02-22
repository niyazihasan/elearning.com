<?php

namespace App\Ui\Menus;

class TeacherMenu
{
    public function init(\Atk4\Ui\Menu $menu, \App\Models\Teacher $teacher) 
    {
        $teacher->tryLoad(auth()->user()->id);

        if (!$teacher->loaded()) {
            abort(404);
        }

        $menu->addItem(['Табло', 'icon' => 'chart line', 'active' => $this->isThatUrl('dashboard')], [route('dashboard')]);
        
        foreach ($teacher->ref('Disciplines') as $discipline) {

            $disciplineGroup = $menu->addGroup($discipline->get('name'));

            $disciplineGroup->addItem([
                    'Лекции', 'icon' => \App\Models\Lecture::ICON, 'active' => $this->isThatUrl('lecture.index', ['discipline' => $discipline->getId()])
                ], [route('lecture.index', ['discipline' => $discipline->getId()])
            ]);

            $disciplineGroup->addItem([
                'Курсови & Домашни работи', 'icon' => \App\Models\Task::ICON, 'active' => $this->isThatUrl('task.index', ['discipline' => $discipline->getId()])
                ], [route('task.index', ['discipline' => $discipline->getId()])
            ]);
            
            $disciplineGroup->addItem([
                'Контрол на присъствие', 'icon' => \App\Models\Presence::ICON, 'active' => $this->isThatUrl('presence.index', ['discipline' => $discipline->getId()])
                ], [route('presence.index', ['discipline' => $discipline->getId()])
            ]);
        }
    }
    
    private function isThatUrl(string $name, array $param = null) 
    {
        return request()->url() === route($name, $param);
    }

}