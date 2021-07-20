<?php

namespace App\Controller;

use DateTime;
use App\Entity\ClimateData;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ClimateSensorController extends ApplicationController
{
    /**
     * @Route("/climate/sensor", name="climate_sensor")
     */
    public function index(): Response
    {
        return $this->render('climate_sensor/index.html.twig', [
            'controller_name' => 'ClimateSensorController',
        ]);
    }

    /**
     * Permet de surcharger les données des modules climate dans la BDD
     *
     * @Route("/climate-data/mod/{modId<[a-zA-Z0-9]+>}/add", name="climateData_add") 
     * 
     * @param SmartMod $smartMod
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return void
     */
    public function climateData_add($modId, EntityManagerInterface $manager, Request $request)
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());
        // //dump($paramJSON);
        // //dump($content);
        //die();

        $datetimeData = new ClimateData();

        //Recherche du module dans la BDD
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);


        if ($smartMod != null) { // Test si le module existe dans notre BDD
            //data:{"date": "2020-03-20 12:15:00", "sa": 1.2, "sb": 0.7, "sc": 0.85, "va": 225, "vb": 230, "vc": 231, "s3ph": 2.75, "kWh": 1.02, "kvar": 0.4}
            // //dump($smartMod);//Affiche le module
            //die();

            //$date = new DateTime($paramJSON['date']);

            // //dump($date);
            //die();

            if ($smartMod->getModType() == 'Climate') {
                //Paramétrage des champs de la nouvelle LoadDataEnergy aux valeurs contenues dans la requête du module
                if (array_key_exists("date", $paramJSON)) {
                    //Récupération de la date dans la requête et transformation en object de type Date au format date SQL
                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                    $datetimeData->setDateTime($date)
                        ->setSmartMod($smartMod);
                    if (array_key_exists("temperature", $paramJSON)) {
                        $datetimeData->setTemperature($paramJSON['temperature']);
                    }
                    if (array_key_exists("humidity", $paramJSON)) {
                        $datetimeData->setHumidity($paramJSON['humidity']);
                    }
                    if (array_key_exists("pressure", $paramJSON)) {
                        $datetimeData->setPressure($paramJSON['pressure']);
                    }

                    $manager->persist($datetimeData);
                    $manager->flush();
                    // //dump($datetimeData);
                    //die();
                    //Insertion de la nouvelle datetimeData dans la BDD

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
