<?php

namespace App\Controller\Admin;

use App\Entity\SmartMod;
use App\Form\SmartModType;
use App\Repository\SmartModRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSmartModController extends AbstractController
{
    /**
     * @Route("/admin/smartMod", name="admin_smartMod_index")
     */
    public function index(SmartModRepository $smartModRepository): Response
    {
        return $this->render('admin/smart_mod/index.html.twig', [
            'smartMods' => $smartModRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/smartMod/new", name="admin_smartMod_new", methods={"GET","POST"})
     *
     */
    public function new(Request $request): Response
    {
        $smartMod = new SmartMod();
        $form = $this->createForm(SmartModType::class, $smartMod);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($smartMod);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "The smartMod <strong>\"{$smartMod->getName()}\"</strong> was created successfully !"
            );
            return $this->redirectToRoute('admin_smartMod_index');
        }

        return $this->render('admin/smart_mod/new.html.twig', [
            //'isEdit' => false,
            'smartMod' => $smartMod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/smartMod/{id<\d+>}", name="admin_smartMod_show", methods={"GET"})
     * 
     */
    public function show(SmartMod $smartMod): Response
    {
        return $this->render('admin/smart_mod/show.html.twig', [
            'smartMod' => $smartMod,
        ]);
    }

    /**
     * @Route("/admin/smartMod/{id<\d+>}/edit", name="admin_smartMod_edit", methods={"GET","POST"})
     * 
     */
    public function edit(Request $request, SmartMod $smartMod): Response
    {
        $form = $this->createForm(SmartModType::class, $smartMod);
        $form->handleRequest($request);
        $manager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->persist($smartMod);
            //die();
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of Smart Module <strong> {$smartMod->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('admin_smartMod_index');
        }

        return $this->render('admin/smart_mod/edit.html.twig', [
            'isEdit' => true,
            'smartMod' => $smartMod,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/smartMod/{id<\d+>}/delete", name="admin_smartMod_delete", methods={"POST"})
     */
    public function delete(Request $request, SmartMod $smartMod): Response
    {
        if ($this->isCsrfTokenValid('delete' . $smartMod->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($smartMod);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_smartMod_index');
    }
}
