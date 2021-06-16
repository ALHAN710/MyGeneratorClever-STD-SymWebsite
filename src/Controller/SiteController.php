<?php

namespace App\Controller;

use App\Entity\Site;
use App\Form\SiteType;
use App\Repository\SiteRepository;
use App\Form\SiteUserCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/site")
 */
class SiteController extends AbstractController
{
    /**
     * @Route("/", name="site_index", methods={"GET"})
     */
    public function index(SiteRepository $siteRepository): Response
    {
        return $this->render('site/index.html.twig', [
            'sites' => $siteRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="site_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(Request $request): Response
    {
        $site = new Site();
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($site);
            $entityManager->flush();

            return $this->redirectToRoute('site_index');
        }

        return $this->render('site/new.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="site_show", methods={"GET"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and site.getEnterprise() === user.getEnterprise() )" )
     */
    public function show(Site $site): Response
    {
        return $this->render('site/show.html.twig', [
            'site' => $site,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="site_edit", methods={"GET","POST"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and site().getEnterprise() === user.getEnterprise() )" )
     */
    public function edit(Request $request, Site $site): Response
    {
        $form = $this->createForm(SiteType::class, $site);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('site_index');
        }

        return $this->render('site/edit.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="site_delete", methods={"POST"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and site.getEnterprise() === user.getEnterprise() )" )
     */
    public function delete(Request $request, Site $site): Response
    {
        if ($this->isCsrfTokenValid('delete' . $site->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($site);
            $entityManager->flush();
        }

        return $this->redirectToRoute('site_index');
    }

    /**
     * @Route("/list", name="site_admin_index", methods={"GET","POST"})
     * @Security( "is_granted('ROLE_ADMIN')" )
     */
    public function adminSiteIndex(EntityManagerInterface $manager): Response
    {
        //$manager = $this->getDoctrine()->getManager();
        $sites = [];
        $sites_ = $manager->createQuery("SELECT st
                            FROM App\Entity\Site st
                            JOIN st.enterprise ent
                            WHERE ent.id = :entId                                    
                            ")
            ->setParameters(array(
                'entId'     => $this->getUser()->getEnterprise()->getId()
            ))
            ->getResult();
        foreach ($sites_ as $site) {
            $sites[] = $site;
        }
        //dd($sites);
        return $this->render('site/admin_index.html.twig', [
            'sites' => $sites,
        ]);
    }

    /**
     * @Route("/{site<\d+>}/settings", name="site_setting", methods={"GET","POST"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and site.getEnterprise() === user.getEnterprise() )" )
     */
    public function adminSiteSetting(Request $request, Site $site): Response
    {
        $form = $this->createForm(SiteUserCollectionType::class, $site, [
            'entId'   => $this->getUser()->getEnterprise()->getId(),
            'forSite' => true,
        ]);
        $form->handleRequest($request);
        // dump($site);
        $manager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($site->getUsers() as $user) {

                // //dump($user->getUserName());
                // $site->addUser($user);
                //$user->addSitee($site);
                //Je vérifie si le produit est déjà existant en BDD pour éviter les doublons 
                $user_ = $manager->getRepository('App:User')->findOneBy(['id' => $user->getUserNam()->getId()]);
                //dd($user_);
                // $user->addSite($site);
                // $manager->persist($user);

                if (empty($user_)) {
                    //$user->addsite($site);
                    //$manager->persist($user);
                    $site->removeUser($user);
                    // //dump('user dont exists ');
                } else {
                    // //dump('user exists with id = ' . $user_->getId());
                    if (!$user_->getSites()->contains($site)) {
                        // //dump("user don't have a site " . $site->getName());
                        $site->removeUser($user);
                        $user = $user_;
                        $user->addSite($site);
                        $site->addUser($user);
                        $manager->persist($user);
                    }
                }
                // $manager->persist($site);
                //$manager->persist($user);
            }
            $manager->persist($site);
            //die();
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of site <strong> {$site->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('site_admin_index');
        }

        return $this->render('site/admin_settings.html.twig', [
            'site' => $site,
            'form' => $form->createView(),
        ]);
    }
}
