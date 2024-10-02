<?php 

namespace App\Controllers;
use Symfony\Component\HttpFoundation\Request;

class SiteController {
    public function index(Request $request) {
        return [
            'content' => 'Some nice content',
            'method' => $request->getMethod()
        ];
    }
}