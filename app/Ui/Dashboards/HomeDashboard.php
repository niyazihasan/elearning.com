<?php

namespace App\Ui\Dashboards;

use Atk4\Ui\{View, Header, Columns};

class HomeDashboard extends View
{
    public function boot()
    {
        $columns = $this->add([Columns::class]);

        $col1= $columns->addColumn(16);
        
        Header::addTo($col1, ['< tu-elearning />', 'aligned' => 'center', 'subHeader' => 'Home, ' . date("l, d. M. Y")]);
    }
}
