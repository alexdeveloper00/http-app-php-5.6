<?php 
namespace App\Middlewares;

use Http\MiddlewareInterface;
use Symfony\Component\HttpFoundation\Request;

class BearerAuthMiddleware implements MiddlewareInterface {
    private $m_header = 'Authorization';
    private $m_pattern = '/^Bearer\s+(.*?)$/';

    public function handle(Request $request) {
        $header = $request->headers->get($this->m_header);

        if (!$header) {
            return [
                'message' => 'Missing authorization header',
                'status' => 400,
            ];
        }

        if (!preg_match($this->m_pattern, $header, $match)) {
            return [
                'message' => 'Invalid protocol',
                'status' => 403,
            ];
        }
    }
}