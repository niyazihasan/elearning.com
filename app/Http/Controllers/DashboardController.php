<?php

namespace App\Http\Controllers;

class DashboardController extends AtkController
{
    public function index()
    {
        $dashboard = $this->atk->add(new \App\Ui\Dashboards\HomeDashboard());

        $dashboard->boot();
   
        return response($this->atk->run());
    }
}
