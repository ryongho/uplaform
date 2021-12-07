<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Wish;
use App\Models\Goods;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\Comparaison;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MailController extends Controller
{
    public function send(Request $request)
    {
        $result = mail($request->email, $request->title, $request->content, '', '-pm@dnsolution.kr');

        dd($result);
    }

    



}
