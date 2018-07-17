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
        $response = new JsonResponse($rates->fetch($date), 200, [], true);

        return $response;
    }
}