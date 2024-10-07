<?php 

namespace App\Controllers;
use Symfony\Component\HttpFoundation\Request;

use App\Models\User;
use App\Models\General;

class SiteController {
    public function index(Request $request) {
        return [
            'content' => 'Some nice content',
            'method' => $request->getMethod()
        ];
    }

    public function users(Request $request) {
        $user = User::where('id', 1)->first();
        return [
            'user' => $user,
        ];
    }

    public function generals(Request $request) {
        $generals = General::all();
        return [
            'generals' => $generals,
        ];
    }
} 