<?php

namespace App\Http\Controllers;
<<<<<<< HEAD

abstract class Controller
{
    //
=======
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
>>>>>>> 48ceca89c80cd3cb95c2541bb2833327718bb572
}
