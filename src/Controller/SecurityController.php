<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('homepage');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * Page d'accueil de l'application
     * 
     * @Route("/", name="homepage")
     * 
     */
    public function home(EntityManagerInterface $manager)
    {

        $user = $this->getUser();
        // $error = $utils->getLastAuthenticationError();
        // $username = $utils->getLastUsername();
        //dump($user->getRoles()[0]);
        if ($user !== NULL) {
            if ($user->getRoles()[0] === 'ROLE_SUPER_ADMIN') return $this->redirectToRoute('admin_enterprises_index');
            else if ($user->getRoles()[0] === 'ROLE_CUSTOMER' || $user->getRoles()[0] === 'ROLE_MANAGER') {
                $sites = $user->getSites();
                if (count($sites) > 0) {
                    $zones = $sites[0]->getZones();
                    if (count($zones) > 0) {
                        $smartMod = count($zones[0]->getSmartMods()) > 0 ? $zones[0]->getSmartMods()[0] : null;
                        if ($smartMod !== null) return $this->redirectToRoute('load_meter', ['smartMod' => $smartMod->getId(), 'zone' => $zones[0]->getId()]);
                        throw $this->createNotFoundException('No modules found');
                    }
                    throw $this->createNotFoundException('No zones found');
                }
                throw $this->createNotFoundException('No sites found');
            } else {
                $fuelMods = $manager->getRepository('App:SmartMod')->findBy(['modType' => 'FUEL']);
                if (count($fuelMods) > 0) return $this->redirectToRoute('genset_home', ['id' => $fuelMods[0]->getId()]);
            }
        } else {
            return $this->redirectToRoute('app_login');
        }
    }
}
