<?php
/**
 * Created by PhpStorm.
 * User: Дима
 * Date: 04.07.2018
 * Time: 21:03
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class mainController extends Controller
{
    /**
     * @Route("/", name="home")
     */
     public function index(){
         $entityManager = $this->getDoctrine()->getManager();
         $rates = $this->get('app.exRatesService');
         $response = new Response();
         $response->headers->set('Content-Type', 'application/json');
         $rates->putToDB($entityManager);

         return $response;
     }

    /**
     * @Route("/{date}", name="item")
     */
    public function showItem($date){
        $entityManager = $this->getDoctrine()->getManager();
        $rates = $this->get('app.exRatesService');
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        return new Response($rates->fetch($entityManager, $date));
    }
}