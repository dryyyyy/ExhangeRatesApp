<?php

namespace App\Controller;

use App\Entity\ExchangeRate;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    /**
     * @Route("/api/{date}", name="item")
     * @param $date
     * @return JsonResponse
     */
    public function showItem($date)
    {
        $result = $this->getDoctrine()->getRepository(ExchangeRate::class)->findOneBy(['date' => $date]);
        return $this->json($result);
    }
}