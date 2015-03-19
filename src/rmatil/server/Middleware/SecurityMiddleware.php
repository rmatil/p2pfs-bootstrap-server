<?php

namespace rmatil\server\Middleware;

use Slim\Middleware;
use rmatil\server\Constants\HttpStatusCodes;

/**
 * Note: This is not really a security improvement. 
 * 
 * @author rmatil 
 */
class SecurityMiddleware extends Middleware {
    
    public function call() {

        $token = $this->app->request->params('token');

        // Stupid, i know
        if (null === $token || $token !== 'tabequals4') {
            $this->app->response->setStatus(HttpStatusCodes::UNAUTHORIZED);
            return;
        }

        $this->next->call();
    }
}
