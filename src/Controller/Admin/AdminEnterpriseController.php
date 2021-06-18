<?php

namespace App\Controller\Admin;

use App\Entity\Enterprise;
use Cocur\Slugify\Slugify;
use App\Form\AdminEnterpriseType;
use App\Repository\EnterpriseRepository;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class AdminEnterpriseController extends AbstractController
{
    /**
     * @Route("/admin/enterprise", name="admin_enterprise_index")
     */
    public function index(EnterpriseRepository $enterpriseRepository): Response
    {
        return $this->render('admin/enterprise/index.html.twig', [
            'enterprises' => $enterpriseRepository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/enterprise/new", name="admin_enterprise_new", methods={"GET","POST"})
     *
     */
    public function new(Request $request): Response
    {
        $enterprise = new Enterprise();

        $lastLogo = $enterprise->getLogo();
        $filesystem = new Filesystem();
        $slugify = new Slugify();

        $form = $this->createForm(AdminEnterpriseType::class, $enterprise);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // @var UploadedFile $logoFile 
            $logoFile = $form->get('logo')->getData();

            // this condition is needed because the 'logo' field is not required
            // so the Image file must be processed only when a file is uploaded
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                // Move the file to the directory where logos are stored
                try {
                    $logoFile->move(
                        $this->getParameter('logo_directory'),
                        $newFilename
                    );
                    $path = $this->getParameter('logo_directory') . '/' . $lastLogo;
                    if ($lastLogo != NULL) $filesystem->remove($path);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $enterprise->setLogo($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($enterprise);
            $entityManager->flush();

            $this->addFlash(
                'success',
                "The enterprise <strong>\"{$enterprise->getSocialReason()}\"</strong> was created successfully !"
            );

            return $this->redirectToRoute('admin_enterprise_index');
        }

        return $this->render('admin/enterprise/new.html.twig', [
            //'isEdit' => false,
            'enterprise' => $enterprise,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/enterprise/{id<\d+>}", name="admin_enterprise_show", methods={"GET"})
     * 
     */
    public function show(Enterprise $enterprise): Response
    {
        return $this->render('admin/enterprise/show.html.twig', [
            'enterprise' => $enterprise,
        ]);
    }

    /**
     * @Route("/admin/enterprise/{id<\d+>}/edit", name="admin_enterprise_edit", methods={"GET","POST"})
     * 
     */
    public function edit(Request $request, Enterprise $enterprise): Response
    {
        $lastLogo = $enterprise->getLogo();
        $filesystem = new Filesystem();
        $slugify = new Slugify();

        $form = $this->createForm(AdminEnterpriseType::class, $enterprise);
        $form->handleRequest($request);
        $manager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            // @var UploadedFile $logoFile 
            $logoFile = $form->get('logo')->getData();

            // this condition is needed because the 'logo' field is not required
            // so the Image file must be processed only when a file is uploaded
            if ($logoFile) {
                $originalFilename = pathinfo($logoFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugify->slugify($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $logoFile->guessExtension();

                // Move the file to the directory where logos are stored
                try {
                    $logoFile->move(
                        $this->getParameter('logo_directory'),
                        $newFilename
                    );
                    $path = $this->getParameter('logo_directory') . '/' . $lastLogo;
                    if ($lastLogo != NULL) $filesystem->remove($path);
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }
                $enterprise->setLogo($newFilename);
            }
            $manager->persist($enterprise);
            //die();
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of enterprise <strong> {$enterprise->getSocialReason()} </strong> have been saved !"
            );

            return $this->redirectToRoute('admin_enterprise_index');
        }

        return $this->render('admin/enterprise/edit.html.twig', [
            //'isEdit' => true,
            'enterprise' => $enterprise,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/enterprise/{id<\d+>}/delete", name="admin_enterprise_delete", methods={"POST"})
     */
    public function delete(Request $request, Enterprise $enterprise): Response
    {
        if ($this->isCsrfTokenValid('delete' . $enterprise->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($enterprise);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_enterprise_index');
    }
}
