<?php

namespace App\Controller;

use DateTime;
use App\Entity\AirConditionerData;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AirConditionerController extends ApplicationController
{
    /**
     * @Route("/air/conditioner", name="air_conditioner")
     */
    public function index(): Response
    {
        return $this->render('air_conditioner/index.html.twig', [
            'controller_name' => 'AirConditionerController',
        ]);
    }

    /**
     * Permet de surcharger les données des modules soft air conditioner dans la BDD
     *
     * @Route("/air-conditioner-data/mod/{modId<[a-zA-Z0-9]+>}/add", name="airConditionerData_add") 
     * 
     * @param SmartMod $smartMod
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return void
     */
    public function airConditionerData_add($modId, EntityManagerInterface $manager, Request $request)
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());
        // //dump($paramJSON);
        // //dump($content);
        //die();

        $datetimeACData = new AirConditionerData();

        //Recherche du module dans la BDD
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);


        if ($smartMod != null) { // Test si le module existe dans notre BDD
            //data:{"date": "2020-03-20 12:15:00", "sa": 1.2, "sb": 0.7, "sc": 0.85, "va": 225, "vb": 230, "vc": 231, "s3ph": 2.75, "kWh": 1.02, "kvar": 0.4}
            // //dump($smartMod);//Affiche le module
            //die();

            //$date = new DateTime($paramJSON['date']);

            // //dump($date);
            //die();

            if ($smartMod->getModType() == 'Air Conditioner') {
                //Paramétrage des champs de la nouvelle LoadDataEnergy aux valeurs contenues dans la requête du module
                if (array_key_exists("date", $paramJSON)) {
                    //Récupération de la date dans la requête et transformation en object de type Date au format date SQL
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                    $datetimeACData->setDateTime($date)
                        ->setSmartMod($smartMod);
                    if (array_key_exists("temperature", $paramJSON)) {
                        $datetimeACData->setReturnAirTemp($paramJSON['temperature']);
                    }
                    if (array_key_exists("humidity", $paramJSON)) {
                        $datetimeACData->setReturnAirHum($paramJSON['humidity']);
                    }
                    if (array_key_exists("fanSpeed1", $paramJSON)) {
                        $datetimeACData->setFanSpeed1($paramJSON['fanSpeed1']);
                    }

                    $manager->persist($datetimeACData);
                    $manager->flush();
                    // //dump($datetimeACData);
                    //die();
                    //Insertion de la nouvelle datetimeACData dans la BDD

                    return $this->json([
                        'code' => 200,
                        'received' => $paramJSON

                    ], 200);
                }
            }
        }
        return $this->json([
            'code' => 403,
            'message' => "SmartMod don't exist",
            'received' => $paramJSON

        ], 403);
    }
}
