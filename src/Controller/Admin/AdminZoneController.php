<?php

namespace App\Controller\Admin;

use App\Entity\Zone;
use App\Form\AdminZoneType;
use App\Repository\ZoneRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminZoneController extends AbstractController
{
    /**
     * @Route("/admin/zone", name="admin_zone_index")
     */
    public function index(ZoneRepository $zoneRepository): Response
    {
        return $this->render('admin/zone/index.html.twig', [
            'zones' => $zoneRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/zone/new", name="admin_zone_new", methods={"GET","POST"})
     *
     */
    public function new(Request $request): Response
    {
        $zone = new Zone();
        $form = $this->createForm(AdminZoneType::class, $zone, [
            'isEdit' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($zone);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "The <strong>\"{$zone->getName()}\"</strong> zone was created successfully !"
            );

            return $this->redirectToRoute('admin_zone_index');
        }

        return $this->render('admin/zone/new.html.twig', [
            //'isEdit' => false,
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/zone/{id<\d+>}", name="admin_zone_show", methods={"GET"})
     * 
     */
    public function show(Zone $zone): Response
    {
        return $this->render('admin/zone/show.html.twig', [
            'zone' => $zone,
        ]);
    }

    /**
     * @Route("/admin/zone/{id<\d+>}/edit", name="admin_zone_edit", methods={"GET","POST"})
     * 
     */
    public function edit(Request $request, Zone $zone): Response
    {
        $form = $this->createForm(AdminZoneType::class, $zone, [
            'isEdit' => true,
            'forZone' => true,
            'entId' => $zone->getSite()->getEnterprise()->getId(),
        ]);
        $form->handleRequest($request);
        $manager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($zone->getSmartMods() as $smartMod) {

                // //dump($smartMod->getsmartModName());
                // $zone->addsmartMod($smartMod);
                //$smartMod->addZone($zone);
                //Je vérifie si le produit est déjà existant en BDD pour éviter les doublons 
                $smartMod_ = $manager->getRepository('App:SmartMod')->findOneBy(['id' => $smartMod->getSmartModName()->getId()]);
                //dd($smartMod_);
                // $smartMod->addZone($zone);
                // $manager->persist($smartMod);

                if (empty($smartMod_)) {
                    //$smartMod->addZone($zone);
                    //$manager->persist($smartMod);
                    $zone->removeSmartMod($smartMod);
                    // //dump('smartMod dont exists ');
                } else {
                    // //dump('smartMod exists with id = ' . $smartMod_->getId());
                    if (!$smartMod_->getZones()->contains($zone)) {
                        // //dump("smartMod don't have a zone " . $zone->getName());
                        $zone->removeSmartMod($smartMod);
                        $smartMod = $smartMod_;
                        $smartMod->addZone($zone);
                        $zone->addSmartMod($smartMod);
                        $manager->persist($smartMod);
                    }
                }
                // $manager->persist($zone);
                //$manager->persist($smartMod);
            }
            $manager->persist($zone);
            //die();
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of zone <strong> {$zone->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('admin_zone_index');
        }

        return $this->render('admin/zone/edit.html.twig', [
            'isEdit' => true,
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/zone/{id<\d+>}/delete", name="admin_zone_delete", methods={"POST"})
     */
    public function delete(Request $request, Zone $zone): Response
    {
        if ($this->isCsrfTokenValid('delete' . $zone->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($zone);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_zone_index');
    }
}
