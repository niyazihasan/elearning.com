<?php

namespace App\Http\Controllers;

use App\Models\{Discipline, Lecture};
use App\Models\Forms\{CreateLecturePage, UpdateLecture};

use Atk4\Ui\{Grid, View, Menu, Header, JsModal, HtmlTemplate};

use Illuminate\Http\Request;

class LectureController extends AtkController
{
    public function index(Request $request)
    {
        $discipline = new Discipline($this->atkDb);
        $discipline->addCondition('id', $request->discipline);

        $fields = ['name','description'];

        if (auth()->user()->isTeacher()) {
            
            $discipline->addCondition('teacher_id', auth()->user()->id)
                       ->tryLoadAny();

            $fields = ['name','description', 'created_at', 'updated_at'];
        }

        $discipline->tryLoadAny();

        if (!$discipline->loaded()) {
            abort(404);
        }

        $lectures = $discipline->ref('Lectures');
        $lectures->getField('created_at')->type = 'date';
        $lectures->getField('updated_at')->type = 'date';

        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, 'Лекции', 'icon' => Lecture::ICON,
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);

        $grid = $this->atk->add([Grid::class, 'ipp' => 20]);
        $grid->setModel($lectures, $fields);

         if (auth()->user()->isTeacher()) {

            $rm = $lm->addMenuRight();

            $lecturePage = new CreateLecturePage($lectures);

            $this->atk->add($lecturePage);

            $modal = new JsModal("Добави $lectures->caption", $lecturePage->getUrl('cut'));
            $modal->setOption('modalCss', 'tiny');

            $rm->addItem(["Добави $lectures->caption", 'icon' => 'plus'], $modal);

            $grid->addActionButton(['icon' => 'violet edit'], function ($js, $id) {
                
                return $this->atk->jsRedirect(route('lecture.edit', ['lecture' => $id]));
            });

            $grid->addActionButton(['icon' => 'green eye'], function ($js, $id) {
                
                return $this->atk->jsRedirect(route('lecture.show', ['lecture' => $id]));
            });
        } 
        
        if (auth()->user()->isStudent()) {
            
            $grid->addActionButton(['icon' => 'green eye'], function ($js, $id) {
                
                return $this->atk->jsRedirect(route('student.lecture.show', ['lecture' => $id]));
            });
        }

        return response($this->atk->run());
    }

    public function edit(Request $request)
    {
        $this->initLocalMenu($request);

        $model = new Lecture($this->atkDb);

        $form = new UpdateLecture();

        $this->atk->add($form);

        $form->setModel($model, []);

        return response($this->atk->run());
    }

    public function show(Request $request)
    {
        $model = new Lecture($this->atkDb);
        $model->addCondition('id', $request->lecture);

        if (auth()->user()->isTeacher()) {
            $model->addCondition('Discipline/teacher_id', auth()->user()->id);
        }

        $model->tryLoadAny();

        if (!$model->loaded()) {
            abort(404);
        }

        $discipline = $model->ref('Discipline');

        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, $model->caption, 'icon' => Lecture::ICON,
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);
        
        $rm = $lm->addMenuRight();
        
        if (auth()->user()->isTeacher()) {
            $rm->addItem(['Редактирай', 'icon' => 'edit'], [route('lecture.edit', ['lecture' => $request->lecture])]);
            $rm->addItem(['Обратно към списъка', 'icon' => 'arrow left'], [route('lecture.index', ['discipline' => $model->get('discipline_id')])]);
        }
        
        if(auth()->user()->isStudent()) {
            $rm->addItem(['Обратно към списъка', 'icon' => 'arrow left'], [route('student.lecture.index', ['discipline' => $model->get('discipline_id')])]);
        }

        $template = view('lectures.view', ['lecture' => $model])->render();
        
        View::addTo($this->atk, ['ui' => 'hidden divider']);
        
        $this->atk->add([View::class, 'template' => new HtmlTemplate($template)]);

        return response($this->atk->run());
    }

    private function initLocalMenu(Request $request)
    {
        $model = new Lecture($this->atkDb);
        $model->addCondition('id', $request->lecture)
              ->addCondition('Discipline/teacher_id', auth()->user()->id)
              ->tryLoadAny();

        if (!$model->loaded()) {
            abort(404);
        }

        $discipline = $model->ref('Discipline');

        $lm = $this->atk->add([Menu::class]);
        $lm->add([
            Header::class, $model->caption, 'icon' => Lecture::ICON,
            'subHeader' => 'Предмет ' . $discipline->get('name') . ', курс ' . $discipline->get('course') . ', семестър ' . $discipline->get('semester')
        ]);

        $rm = $lm->addMenuRight();
        $rm->addItem(['Обратно към списъка', 'icon' => 'arrow left'], [route('lecture.index', ['discipline' => $model->get('discipline_id')])]);
    }
}