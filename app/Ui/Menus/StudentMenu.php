<?php

namespace App\Ui\Menus;

class StudentMenu
{
    public function init(\Atk4\Ui\Menu $menu, \App\Models\Student $student) 
    {
        $student->tryLoad(auth()->user()->id);
        
        if (!$student->loaded()) {
            abort(404);
        }

        $menu->addItem(['Табло', 'icon' => 'chart line', 'active' => $this->isThatUrl('dashboard')], [route('dashboard')]);
        
        $group = $student->ref('Group');
        
        foreach ($group->ref('specialty_id')->ref('Disciplines')->ref('discipline_id')->addCondition('course', '<=', $group->get('course')) as $discipline) {

            $disciplineGroup = $menu->addGroup($discipline->get('name'));

            $disciplineGroup->addItem([
                    'Лекции', 'icon' => \App\Models\Lecture::ICON, 'active' => $this->isThatUrl('student.lecture.index', ['discipline' => $discipline->getId()])
                ], [route('student.lecture.index', ['discipline' => $discipline->getId()])
            ]);

            $disciplineGroup->addItem([
                'Курсови & Домашни работи', 'icon' => \App\Models\Task::ICON, 'active' => $this->isThatUrl('student.task.show', ['discipline' => $discipline->getId()])
                ], [route('student.task.show', ['discipline' => $discipline->getId()])
            ]);
            
            $disciplineGroup->addItem([
                'Контрол на присъствие', 'icon' => \App\Models\Presence::ICON, 'active' => $this->isThatUrl('student.presence.show', ['discipline' => $discipline->getId()])
                ], [route('student.presence.show', ['discipline' => $discipline->getId()])
            ]);
        }
    }
    
    private function isThatUrl(string $name, array $param = null) 
    {
        return request()->url() === route($name, $param);
    }

}
