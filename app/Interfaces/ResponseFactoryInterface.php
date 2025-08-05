<?php
namespace App\Interfaces;

use Slim\Psr7\Response;
interface ResponseFactoryInterface
{
    public function createResponse(): Response;
}

?>