<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;

class AtkController extends Controller 
{
    protected $atk;
    protected $atkDb;
    
    public function __construct() 
    {   
        $this->atkDb = app()->make(\App\Persistence\MySQL::class);

        $this->atk = app()->make(\App\Ui\App::class);
        
        $this->atk->title = config('app.name');
        
        $this->middleware('auth');
        
        $this->middleware(function ($request, $next) {
            
            $this->atk->initLayout([\Atk4\Ui\Layout\Admin::class, 'defaultTemplate' => resource_path('views/layouts/template.html')]);

            $this->initMenus($request);

            return $next($request);
        });
    }
    
    private function initMenus(Request $request)
    {   
        $layout = $this->atk->layout;
        
        $layout->isMenuLeftVisible = $request->session()->get('isMenuLeftVisible') ?? true;

        $layout->burger->on('click', function ($js, $arg1) use ($request) {
            
            $request->session()->put('isMenuLeftVisible', $arg1 === 'visible');
            $request->session()->save();
            
        }, [new \Atk4\Ui\JsExpression("$(\".ui, .left, .menu, .sidebar\").css('visibility')")]);
        
        $user = auth()->user();
        
        $atkUser = new \App\Models\AbstractUser($this->atkDb);
        $atkUser->tryLoad($user->id);
        
        $layout->menuRight->addItem(['', 'icon' => 'envelope'], [route('message.inbox')])->set($atkUser->ref('UnReadMessages')->action('count')->getOne());
        
        $layout->menu->addItem(['', 'icon' => 'refresh'], []);
        $layout->menu->addItem(['', 'icon' => 'home'], [url('/')]);
        
        $rm = $layout->menuRight->addMenu("$user->title $user->name $user->sname");
        $rm->addItem(['Моят профил', 'icon' => 'user'], [route('user.profile')]);
        $rm->addItem(['Отписване', 'icon' => 'lock'], [route('logout.get')]);

        if (Gate::allows('admin')) {
            
            $layout->menu->addItem(['Администраторска платформа', 'ui' => 'red active tiny header', 'icon' => \App\Models\Admin::ICON]);
            $layout->menu->addItem([date("l, d. M. Y")]);
            
            return (new \App\Ui\Menus\AdminMenu())->init($layout->menuLeft);
        }

        if (Gate::allows('teacher')) {
            
            $layout->menu->addItem(['Преподавателска платформа', 'ui' => 'orange active tiny header', 'icon' => \App\Models\Teacher::ICON]);
            $layout->menu->addItem([date("l, d. M. Y")]);
                    
            return (new \App\Ui\Menus\TeacherMenu())->init($layout->menuLeft, new \App\Models\Teacher($this->atkDb));
        }

        if (Gate::allows('student')) {
            
            $layout->menu->addItem(['Студентска платформа', 'ui' => 'blue active tiny header', 'icon' => \App\Models\Student::ICON]);
            $layout->menu->addItem([date("l, d. M. Y")]);
                    
            return (new \App\Ui\Menus\StudentMenu($this->atkDb))->init($layout->menuLeft, new \App\Models\Student($this->atkDb));
        }
    }
}