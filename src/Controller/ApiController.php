<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    /**
     * @Route("/api/{date}", name="item")
     */
    public function showItem($date){
        $entityManager = $this->getDoctrine()->getManager();
        $rates = $this->get('App\Service\ExRatesService');
        $response = new Response($rates->fetch($entityManager, $date));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}