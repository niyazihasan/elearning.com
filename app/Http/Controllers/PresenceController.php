<?php

namespace App\Http\Controllers;

use App\Models\{Discipline, Presence, Student};

use Atk4\Ui\{Accordion, Button, View, Form, Grid, Menu, Header, JsReload, JsExpression};

use Illuminate\Http\Request;

class PresenceController extends AtkController
{
    public function index(Request $request)
    {
        $discipline = new Discipline($this->atkDb);
        $discipline->addCondition('id', $request->discipline)
                   ->addCondition('teacher_id', auth()->user()->id)
                   ->tryLoadAny();

        if (!$discipline->loaded()) {
            abort(404);
        }

        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, 'Контрол на присъствие', 'icon' => Presence::ICON,
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);
        
        View::addTo($this->atk, ['ui' => 'hidden divider']);
        
        $accordion = $this->atk->add([Accordion::class], ['type' => ['styled', 'fluid']]);

        foreach ($discipline->ref('Specialties') as $specialty) {

            foreach ($specialty->ref('specialty_id')->ref('Groups')->addCondition('course', '>=', $discipline->get('course')) as $group) {

                $sectionName = $group->ref('specialty_id')->get('name') . ' / Група ' . $group->get('name') . ' / Курс ' . $group->get('course');
                $section = $accordion->addSection($sectionName);

                $form = Form::addTo($section, ['buttonSave'=> false]);

                $control = $form->addControl('date', [
                    \Atk4\Ui\Form\Control\Calendar::class,
                    'caption' => ' ',
                    'options' => ['clickOpens' => false]
                ]);
                $control->addAction(['Днес', 'icon' => 'calendar day'])->on('click', $control->getJsInstance()->setDate(date('M d, Y')));
                $control->addAction(['Избери...', 'icon' => 'calendar'])->on('click', $control->getJsInstance()->open());
                $control->addAction(['Изчисти', 'icon' => 'times red'])->on('click', $control->getJsInstance()->clear());

                $formButton = $section->add(new Button(['Запиши', 'primary']));
                $formButton->addStyle('margin-top', '1rem');

                $grid = $section->add([Grid::class, 'paginator' => false]);
                $grid->table->onHook(\Atk4\Ui\Table\Column::HOOK_GET_HEADER_CELL_HTML, function ($table, $column){});

                $students = $group->ref('Students');

                $dateHtml = '<tr><th></th><th></th><th></th>';

                $date_arr = array_map('unserialize', array_unique(array_map('serialize', array_column($students->ref('Presences')->addCondition('discipline_id', $discipline->getId())->export(), 'date'))));
                
                foreach ($date_arr as $date) {
                    $dateHtml .= '<th>' . $date->format('M d, Y') . '</th>';
                }

                $grid->table->template->dangerouslyAppendHtml('SubHead', $dateHtml . '</tr>');

                $students->addField('html', ['never_persist' => true, 'caption' => '']);
                $students->addField('full_name', ['never_persist' => true, 'caption' => '']);

                $students->onHook(\Atk4\Data\Model::HOOK_AFTER_LOAD, function ($m) use ($discipline) {
                    
                    $color = $m->get('active') ? 'green' : 'red';
                    $m->set('full_name', "<i class='$color user icon'></i>" . $m->get('name') . ' ' . $m->get('fname') . ' ' . $m->get('sname'));

                    $html = "";

                    foreach ($m->ref('Presences')->addCondition('discipline_id', $discipline->getId()) as $presence) {
                        
                        if ($presence->get('name')) {
                            $html .= "<td class='positive single line'><i class='icon checkmark'></i></td>";
                        } else {
                            $html .= "<td class='negative single line'><i class='icon close'></i></td>";
                        }
                    }

                    $m->set('html', $html);
                });

                $grid->setModel($students, ['full_name', 'html']);

                $grid->table->onHook(\Atk4\Ui\Table\Column::HOOK_GET_HTML_TAGS, function ($table, \Atk4\Data\Model $row) {
                    return [
                        'html' => $row->get('html'),
                        'full_name' => $row->get('full_name')
                    ];
                });

                $selection = $grid->addSelection();

                $formButton->on('click', function ($js, $arg1, $arg2) use ($students, $grid, $request, $control) {
                    
                    if ($arg2 == "") {
                        return;
                    }

                    $ids = explode(',', $arg1);

                    foreach ($students as $student) {

                        $presence = new Presence($this->atkDb);

                        $presence->set('date', $arg2);
                        $presence->set('teacher_id', auth()->user()->id);
                        $presence->set('discipline_id', $request->discipline);
                        $presence->set('student_id', $student->getId());

                        if (in_array($student->getId(), $ids)) {
                            $presence->set('name', 1);
                        } else {
                            $presence->set('name', 0);
                        }

                        $presence->save();
                    }
                    
                    return [new JsReload($grid), new JsReload($control)];

                }, ['args'=> [new JsExpression('[]', [$selection->jsChecked()]), $control->jsInput()->val()]]);
            }
        }

        return response($this->atk->run());
    }
    
    public function show(Request $request)
    {
        $discipline = new Discipline($this->atkDb);
        $discipline->tryLoad($request->discipline);

        if (!$discipline->loaded()) {
            abort(404);
        }

        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, 'Контрол на присъствие', 'icon' => Presence::ICON,
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);
        
        $model = (new Student($this->atkDb))->tryLoad(auth()->user()->id)->ref('Presences')->addCondition('discipline_id', $request->discipline);
        
        $grid = $this->atk->add([Grid::class, 'paginator' => false, 'table' => ['header' => false]]);
        $grid->setModel($model, ['date', 'name']);
        
        return response($this->atk->run());
    }
}