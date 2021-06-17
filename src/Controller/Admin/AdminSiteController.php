<?php

namespace App\Controller\Admin;

use App\Entity\Site;
use App\Form\AdminSiteType;
use App\Repository\SiteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminSiteController extends AbstractController
{
    /**
     * @Route("/admin/site", name="admin_site_index")
     */
    public function index(SiteRepository $siteRepository): Response
    {
        return $this->render('admin/site/index.html.twig', [
            'sites' => $siteRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/site/new", name="admin_site_new", methods={"GET","POST"})
     *
     */
    public function new(Request $request): Response
    {
        $site = new Site();
        $form = $this->createForm(AdminSiteType::class, $site, [
            'isEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($site);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "The <strong>\"{$site->getName()}\"</strong> site was created successfully !"
            );

            return $this->redirectToRoute('admin_site_index');
        }

        return $this->render('admin/site/new.html.twig', [
            //'isEdit' => false,
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/site/{id<\d+>}", name="admin_site_show", methods={"GET"})
     * 
     */
    public function show(Site $site): Response
    {
        return $this->render('admin/site/show.html.twig', [
            'site' => $site,
        ]);
    }

    /**
     * @Route("/admin/site/{id<\d+>}/edit", name="admin_site_edit", methods={"GET","POST"})
     * 
     */
    public function edit(Request $request, Site $site): Response
    {
        $form = $this->createForm(AdminSiteType::class, $site, [
            'isEdit' => true,
            'forSite' => true,
            'entId' => $site->getEnterprise()->getId(),
        ]);
        $form->handleRequest($request);
        $manager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($site->getSmartMods() as $smartMod) {

                // //dump($smartMod->getsmartModName());
                // $site->addsmartMod($smartMod);
                //$smartMod->addsite($site);
                //Je vérifie si le produit est déjà existant en BDD pour éviter les doublons 
                $smartMod_ = $manager->getRepository('App:SmartMod')->findOneBy(['id' => $smartMod->getSmartModName()->getId()]);
                //dd($smartMod_);
                // $smartMod->addsite($site);
                // $manager->persist($smartMod);

                if (empty($smartMod_)) {
                    //$smartMod->addsite($site);
                    //$manager->persist($smartMod);
                    $site->removeSmartMod($smartMod);
                    // //dump('smartMod dont exists ');
                } else {
                    // //dump('smartMod exists with id = ' . $smartMod_->getId());
                    if (!$site->getSmartMods()->contains($smartMod_)) {
                        // //dump("smartMod don't have a site " . $site->getName());
                        $site->removeSmartMod($smartMod);
                        $smartMod = $smartMod_;
                        $smartMod->setSite($site);
                        $site->addSmartMod($smartMod);
                        $manager->persist($smartMod);
                    }
                }
                // $manager->persist($site);
                //$manager->persist($smartMod);
            }
            $manager->persist($site);
            //die();
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of site <strong> {$site->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('admin_site_index');
        }

        return $this->render('admin/site/edit.html.twig', [
            //'isEdit' => true,
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/site/{id<\d+>}/delete", name="admin_site_delete", methods={"POST"})
     */
    public function delete(Request $request, Site $site): Response
    {
        if ($this->isCsrfTokenValid('delete' . $site->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($site);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_site_index');
    }
}
