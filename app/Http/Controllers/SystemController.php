<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    public function checkDB()
    {
        try {
            DB::connection()->getPdo();
            $message = "Database connection is established.";
        } catch (\Exception $e) {
            $message = "Database connection could not be established: " . $e->getMessage();
        }
        return $message;
    }

    public function migrate()
    {
        try {
            Artisan::call('migrate');
            $message = "Migrations executed successfully.";
        } catch (\Exception $e) {
            $message = "Error occurred while running migrations: " . $e->getMessage();
        }
        return $message;
    }

    public function optimizeClear()
    {
        try {
            Artisan::call('optimize:clear');
            $message = "'optimize:clear' run successfully";
        } catch (\Exception $e) {
            $message = "'optimize:clear' failed to run: " . $e->getMessage();
        }
        return $message;
    }
}
