<?php

namespace Modules\Core\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;

class BaseController extends Controller
{
    protected $helpers = [];

    /**
     * Called automatically when controller is instantiated
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
        
        // Call initialize if it exists
        if (method_exists($this, 'initialize')) {
            $this->initialize();
        }
    }

    protected function initialize(): void
    {
        helper(['Modules\\Core\\Helpers\\auth', 'Modules\\Core\\Helpers\\format', 'Modules\\Core\\Helpers\\string', 'Modules\\Core\\Helpers\\date', 'Modules\\Core\\Helpers\\system']);
    }

    protected function authService()
    {
        return Services::modulesCoreAuth();
    }

    protected function tenantService()
    {
        return Services::modulesCoreTenant();
    }

    protected function responseService()
    {
        return Services::modulesCoreResponse();
    }
}


