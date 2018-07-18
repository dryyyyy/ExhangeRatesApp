<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    /**
     * @Route("/api/{date}", name="item")
     */
    public function showItem($date){
        $rates = $this->get('App\Service\ExRatesService');

        try{
            $result = $rates->fetch($date);
            $statusCode = 200;
        }catch (\Exception $ex) {
            $result = ['error' => $ex->getMessage()];
            $statusCode = 500;
        }

        $response = new JsonResponse($result, $statusCode, [], true);

        return $response;
    }
}