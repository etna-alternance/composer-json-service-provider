<?php

namespace TestApp\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * @Route("/", methods={"POST"}, name="home")
     */
    public function home(Request $req)
    {
        return new JsonResponse($req->request->all(), 200);
    }
}
