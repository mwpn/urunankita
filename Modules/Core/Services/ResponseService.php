<?php

namespace Modules\Core\Services;

use CodeIgniter\HTTP\ResponseInterface;

class ResponseService
{
    public function json($data = [], int $status = 200): ResponseInterface
    {
        return service('response')->setStatusCode($status)->setJSON([
            'status' => $status,
            'data' => $data,
        ]);
    }
}


