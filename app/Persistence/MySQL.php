<?php

namespace App\Persistence;

use Illuminate\Support\Facades\DB;

use Atk4\Data\Persistence\Sql;
use Atk4\Dsql\Connection;

class MySQL extends Sql
{
    public function __construct()
    {
        $pdo = DB::connection()->getPdo();
        
        $connect = Connection::connect($pdo);

        parent::__construct($connect);
    }
}