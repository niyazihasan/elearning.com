<?php

namespace App\Http\Controllers;

use App\Models\{Discipline, Task, Student};
use App\Models\Forms\{AddTaskPage, RatingPage, AddSolutionPage};

use Atk4\Ui\{Accordion, View, Menu, Header, Grid, JsModal, JsToast};

use Illuminate\Http\Request;

class TaskController extends AtkController
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
            Header::class, 'Курсови & Домашни работи', 'icon' => 'tasks',
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);
        
        View::addTo($this->atk->layout, ['ui' => 'hidden divider']);
        
        $accordion = $this->atk->add([Accordion::class], ['type' => ['styled', 'fluid']]);

        foreach ($discipline->ref('Specialties') as $specialty) {

            foreach ($specialty->ref('specialty_id')->ref('Groups')->addCondition('course', '>=', $discipline->get('course')) as $group) {

                $sectionName = $group->ref('specialty_id')->get('name') . ' / Група ' . $group->get('name') . ' / Курс ' . $group->get('course');
                $section = $accordion->addSection($sectionName);

                $index = 0;

                foreach ($group->ref('Students') as $student) {

                    $tasks = $section->add([Grid::class, 'paginator' => false]);

                    $studentTasks = $student->ref('Tasks')->addCondition('discipline_id', $discipline->getId());
             
                    $studentTasks->addField('files_html', ['never_persist' => true, 'caption' => '']);

                    $studentTasks->onHook(\Atk4\Data\Model::HOOK_AFTER_LOAD, function ($m) {
                        
                        $solution_html = '';
                        $assignment_html = '';

                        if ($m->ref('solution_id')->get('id')) {
                            $solution_html = '<a target="_blank" href=' . $m->ref('solution_id')->getNonCdnUrl() . '>'
                                    . '<button class="tiny ui icon button">'
                                    . '<i class="external link square alternate icon">'
                                    . '</i> Решение</button></a>';
                        }

                        if ($m->ref('assignment_id')->get('id')) {
                            $assignment_html = '<a target="_blank" href=' . $m->ref('assignment_id')->getNonCdnUrl() . '>'
                                    . '<button class="tiny ui icon button">'
                                    . '<i class="external link square alternate icon">'
                                    . '</i> Задание</button></a>';
                        }

                        $m->set('files_html', "$assignment_html $solution_html");
                    });

                    $tasks->setModel(clone $studentTasks, ['name', 'type_name', 'solution_upload_period', 'rating', 'files_html']);

                    $tasks->table->onHook(\Atk4\Ui\Table\Column::HOOK_GET_HTML_TAGS, function ($table, \Atk4\Data\Model $row) {
                        return [
                            'files_html' => $row->get('files_html')
                        ];
                    });

                    $ratingPage = new RatingPage($request, $studentTasks, $tasks);

                    $this->atk->add($ratingPage);

                    $tasks->addActionButton(['icon' => 'violet edit'], function ($js, $id) use ($ratingPage, $studentTasks) {
                        
                        if ($studentTasks->tryLoad($id)->get('solution_id')) {

                            $modal = new JsModal('Оцени Задача', $ratingPage->getURL('cut'), ['task_id' => $id]);
                            $modal->setOption('modalCss', 'mini');

                            return $modal;
                        }

                        return new JsToast(['message' => 'Няма решение!', 'class' => 'info']);
                    });

                    $tasks->menu->addItem([
                        ++$index . '. ' . $student->get('name') . ' ' . $student->get('fname') . ' ' . $student->get('sname'),
                        'icon' => $student->get('active') ? 'green user' : 'red user'
                    ], null);

                    $addTask = new AddTaskPage($studentTasks, $tasks);

                    $this->atk->add($addTask);

                    $modalTask = new JsModal("Добави Задача", $addTask->getURL('cut'), ['student_id' => $student->getId()]);
                    $modalTask->setOption('modalCss', 'tiny');

                    $tasks->menu->addItem(["Добави $studentTasks->caption", 'icon' => 'plus'], $modalTask);
                }
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
            Header::class, 'Курсови & Домашни работи', 'icon' => Task::ICON,
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);

        $tasks = (new Student($this->atkDb))->tryLoad(auth()->user()->id)->ref('Tasks')->addCondition('discipline_id', $request->discipline);

        $tasks->addField('files_html', ['never_persist' => true, 'caption' => '']);

        $tasks->onHook(\Atk4\Data\Model::HOOK_AFTER_LOAD, function ($m) {
            
            $solution_html = '';
            $assignment_html = '';

            if ($m->ref('solution_id')->get('id')) {
                $solution_html = '<a target="_blank" href=' . $m->ref('solution_id')->getNonCdnUrl() . '>'
                        . '<button class="tiny ui icon button">'
                        . '<i class="external link square alternate icon">'
                        . '</i> Решение</button></a>';
            }

            if ($m->ref('assignment_id')->get('id')) {
                $assignment_html = '<a target="_blank" href=' . $m->ref('assignment_id')->getNonCdnUrl() . '>'
                        . '<button class="tiny ui icon button">'
                        . '<i class="external link square alternate icon">'
                        . '</i> Задание</button></a>';
            }

            $m->set('files_html', "$assignment_html $solution_html");
        });

        $grid = $this->atk->add([Grid::class, 'paginator' => false]);
        $grid->setModel(clone $tasks, ['name', 'type_name', 'solution_upload_period', 'rating', 'files_html']);

        $grid->table->onHook(\Atk4\Ui\Table\Column::HOOK_GET_HTML_TAGS, function ($table, \Atk4\Data\Model $row) {
            return [
                'files_html' => $row->get('files_html')
            ];
        });

        $solutionPage = new AddSolutionPage($request, $tasks, $grid);

        $this->atk->add($solutionPage);
        
        $grid->addColumn(null, new \App\Ui\ToggleIcon('solution_id', function ($js) use ($solutionPage, $tasks) {
            
            $m = $tasks->tryLoad(intval($_POST['id']));
            
            if(!$m->get('solution_id')) {
                
                $modal = new JsModal("Качи Решение", $solutionPage->getURL('cut'), ['task_id' => $m->getId()]);
                $modal->setOption('modalCss', 'mini');

                return $modal;
            }
                
        }, ['icon' => 'violet upload', 'name' => 'Качи Решение']));

        return response($this->atk->run());
    }

}