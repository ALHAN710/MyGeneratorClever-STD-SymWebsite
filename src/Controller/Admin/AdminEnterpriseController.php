<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminEnterpriseController extends AbstractController
{
    /**
     * @Route("/admin/enterprise", name="admin_enterprise_index")
     */
    public function index(): Response
    {
        return $this->render('admin/enterprise/index.html.twig', [
            'controller_name' => 'AdminEnterpriseController',
        ]);
    }
}
