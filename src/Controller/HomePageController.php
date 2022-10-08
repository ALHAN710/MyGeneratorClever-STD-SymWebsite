<?php

namespace App\Controller;

use App\Service\GensetModService;
use DateTime;
use DateInterval;
use App\Entity\NoDatetimeData;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomePageController extends ApplicationController
{
    private $gensetModService;

    /**
     * @param $gensetModService
     */
    public function __construct(GensetModService $gensetModService)
    {
        $this->gensetModService = $gensetModService;
    }

    /**
     * @Route("/home/page/{siteId<d+>?0}", name="home_page")
     * @IsGranted("ROLE_USER")
     */
    public function index($siteId, EntityManagerInterface $manager): Response
    {
        $climates = [];
        $climatesOut = [];
        $gensetFL = [];
        $inTemp = 0.0;
        $inHum = 0.0;
        $outTemp = 0.0;
        $outHum = 0.0;
        $site = $manager->getRepository('App:Site')->find(['id' => $siteId]);
        if ($site) {
            $zonesPUE = $manager->getRepository('App:Zone')->findBy(['site' => $site, 'type' => 'PUE Calculation']);
            $zonesGENSET = $manager->getRepository('App:Zone')->findBy(['site' => $site, 'type' => 'Genset']);
            $gens = $manager->getRepository('App:SmartMod')->findBy(['site' => $site, 'modType' => 'FUEL']);
            //dump($zonesGENSET);
            //$climates = [];
            if (count($zonesPUE) > 0) {
                foreach ($zonesPUE[0]->getSmartMods() as $smartMod) {
                    if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Indoor') $climates[] = $smartMod;
                    else if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Outdoor') $climatesOut[] = $smartMod;
                }

                $LastIndoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)
                                                                                                                                                                                                            
                                                            ")
                    ->setParameters(array(
                        'smartMods'      => $climates,
                        'nowDate'     => date("Y-m-d") . "%",
                    ))
                    ->getResult();
                $LastOutdoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)
                                                                                                                                                                                                            
                                                            ")
                    ->setParameters(array(
                        'smartMods'      => $climatesOut,
                        'nowDate'     => date("Y-m-d") . "%",
                    ))
                    ->getResult();

                if (count($LastIndoorData) > 0) {
                    $inTemp = $LastIndoorData[0]['temp'];
                    $inHum = $LastIndoorData[0]['hum'];
                }
                if (count($LastOutdoorData) > 0) {
                    $outTemp = $LastOutdoorData[0]['temp'];
                    $outHum = $LastOutdoorData[0]['hum'];
                }
            }
            /*if (count($zonesGENSET) > 0) {
                foreach ($zonesPUE[0]->getSmartMods() as $smartMod) {
                    if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Indoor') $climates[] = $smartMod;
                    else if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Outdoor') $climatesOut[] = $smartMod;
                }

                $LastIndoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)

                                                            ")
                    ->setParameters(array(
                        'smartMods'      => $climates,
                        'nowDate'     => date("Y-m-d") . "%",
                    ))
                    ->getResult();
                $LastOutdoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)

                                                            ")
                    ->setParameters(array(
                        'smartMods'      => $climatesOut,
                        'nowDate'     => date("Y-m-d") . "%",
                    ))
                    ->getResult();

                if (count($LastIndoorData) > 0) {
                    $inTemp = $LastIndoorData[0]['temp'];
                    $inHum = $LastIndoorData[0]['hum'];
                }
                if (count($LastOutdoorData) > 0) {
                    $outTemp = $LastOutdoorData[0]['temp'];
                    $outHum = $LastOutdoorData[0]['hum'];
                }
            }*/
            return $this->render('home_page/homepage2.html.twig', [
                'site' => $site,
                'zone' => count($zonesPUE) > 0 ? $zonesPUE[0]->getId() : 0,
                'gen'   => count($gens) > 0 ? $gens[0]->getId() : 0,
                'climate'   => count($climates) > 0 ? $climates[0] : 0,
                'inTemp'    => $inTemp,
                'inHum'    => $inHum,
                'outTemp'    => $outTemp,
                'outHum'    => $outHum,
            ]);
        } else {
            $sites = $manager->getRepository('App:Site')->findBy(['enterprise' => $this->getUser()->getEnterprise(), 'isPublic' => true]);
            //dump($sites);
            if (count($sites) === 1) {
                $gens = $manager->getRepository('App:SmartMod')->findBy(['site' => $sites[0], 'modType' => 'FUEL']);
                $zonesGENSET = $manager->getRepository('App:Zone')->findBy(['site' => $sites[0], 'type' => 'Genset']);
//                dump($zonesGENSET);
                $zonesPUE = $manager->getRepository('App:Zone')->findBy(['site' => $sites[0], 'type' => 'PUE Calculation']);
                //$climates = [];
                if (count($zonesPUE) > 0) {
                    foreach ($zonesPUE[0]->getSmartMods() as $smartMod) {
                        if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Indoor') $climates[] = $smartMod;
                        else if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Outdoor') $climatesOut[] = $smartMod;
                    }

                    $LastIndoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum 
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)
                                                                                                                                                                                                            
                                                            ")
                        ->setParameters(array(
                            'smartMods'      => $climates,
                            'nowDate'     => date("Y-m-d") . "%",
                        ))
                        ->getResult();
                    $LastOutdoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum 
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)
                                                                                                                                                                                                            
                                                            ")
                        ->setParameters(array(
                            'smartMods'      => $climatesOut,
                            'nowDate'     => date("Y-m-d") . "%",
                        ))
                        ->getResult();

                    if (count($LastIndoorData) > 0) {
                        $inTemp = $LastIndoorData[0]['temp'];
                        $inHum = $LastIndoorData[0]['hum'];
                    }
                    if (count($LastOutdoorData) > 0) {
                        $outTemp = $LastOutdoorData[0]['temp'];
                        $outHum = $LastOutdoorData[0]['hum'];
                    }
                }
                if (count($zonesGENSET) > 0) {
                    foreach ($zonesGENSET as $zone){
                        foreach ($zone->getSmartMods() as $smartMod) {
                            if ($smartMod->getModType() === 'FUEL' && $smartMod->getSubType() === 'FL') {
                                // ######## Récupération des données temps réel du module Genset
                                $noDatetimeData = $manager->getRepository(NoDatetimeData::class)->findOneBy(['smartMod' => $smartMod->getId()]) ?? new NoDatetimeData();
                                $gensetFL[] = [
                                    'id' => $smartMod->getId(),
                                    'FL' => $noDatetimeData->getFuelLevel(),
                                    'GR' => $noDatetimeData->getGensetRunning(),
                                    'name' => $zone->getName(),
                                ];
                            }
                        }
                    }

                }
//                dump($gensetFL);
                return $this->render('home_page/homepage2.html.twig', [
                    'site' => $sites[0],
                    'zone' => count($zonesPUE) > 0 ? $zonesPUE[0]->getId() : 0,
                    'gen'   => count($gens) > 0 ? $gens[0]->getId() : 0,
                    'climate'   => count($climates) > 0 ? $climates[0] : 0,
                    'inTemp'    => $inTemp,
                    'inHum'    => $inHum,
                    'outTemp'    => $outTemp,
                    'outHum'    => $outHum,
                    'gensets'   => $gensetFL,
                ]);
            } else if (count($sites) > 0) {
                return $this->render('home_page/homepage1.html.twig', [
                    'sites' => $sites
                ]);
            }
        }
    }

    /**
     * @Route("/app/home/page", name="app_home_page")
     * @IsGranted("ROLE_USER")
     */
    public function appHome(EntityManagerInterface $manager): Response
    {
        $user = $this->getUser();
        if ($user->getRoles()[0] === 'ROLE_CUSTOMER') {
            // $sites = $user->getSites();
            // if (count($sites) > 0) {
            $zones = $user->getZones();
            if (count($zones) > 0) {
                return $this->redirectToRoute('home_zone', ['smartMod' => $zones[0]->getId(), 'zone' => $zones[0]->getId()]);
                /*$smartMod = count($zones[0]->getSmartMods()) > 0 ? $zones[0]->getSmartMods()[0] : null;
                    if ($smartMod !== null) return $this->redirectToRoute('load_meter', ['smartMod' => $smartMod->getId(), 'zone' => $zones[0]->getId()]);
                    throw $this->createNotFoundException('No modules found');*/
            }
            throw $this->createNotFoundException('No zones found');
            // }
            throw $this->createNotFoundException('No sites found');
        } else if ($user->getRoles()[0] === 'ROLE_NOC_SUPERVISOR') {
            $sites = $user->getSites();
            if (count($sites) > 0) {
                $fuelMods = $manager->getRepository('App:SmartMod')->findBy(['modType' => 'FUEL', 'site' => $sites[0]]);
                if (count($fuelMods) > 0) return $this->redirectToRoute('genset_home', ['id' => $fuelMods[0]->getId()]);
                throw $this->createNotFoundException('No genset found');
            }
            throw $this->createNotFoundException('No sites found');
        } else {
            $fuelMods = $manager->getRepository('App:SmartMod')->findBy(['modType' => 'FUEL', 'enterprise' => $user->getEnterprise()]);
            if (count($fuelMods) > 0) return $this->redirectToRoute('genset_home', ['id' => $fuelMods[0]->getId()]);
            throw $this->createNotFoundException('No genset found');
        }
    }

    /**
     * Permet de mettre à jour les graphes en cours liés aux données des modules load Meter d'une zone
     *
     * @Route("/update/site/overview/graphs/", name="update_site_overview_graphs")
     * 
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateSiteOverviewGraph(EntityManagerInterface $manager, Request $request): Response
    {
        //$smartModRepo = $this->getDoctrine()->getRepository(SmartModRepository::class);
        //$smartMod = $smartModRepo->find($id);
        // //dump($smartModRepo);
        // //dump($smartMod->getModType());
        //$temps = DateTime::createFromFormat("d-m-Y H:i:s", "120");
        // //dump($temps);
        //die();
        $InstantPUE = 0;
        $InstantTotal_AP = 0;
        $InstantIT_AP = 0;

        $instantpue = [];
        $datePue = [];
        $productionAP = [];
        $totalAP = [];
        $gensetFL = [];

        $Intemp = 0;
        $Inhum = 0;
        $Outtemp = 0;
        $Outhum = 0;
        $ClimDate = "";

        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['id' => $paramJSON['id']]);
        $zone = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['zoneId']]);

        if ($zone) {
            $smartMods = $zone->getSmartMods();
            //dump($smartMods);
            /*$lastRecord = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                       FROM App\Entity\SmartMod sm
                                       JOIN sm.loadDataEnergies d 
                                       WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                       AND d.dateTime LIKE :nowDate            
                                    ")
                ->setParameters(array(
                    'nowDate'    => date("Y-m") . "%",
                    'zoneId'     => $zone->getId()
                ))
                ->getResult();*/
            $lastRecord = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                       FROM App\Entity\LoadDataEnergy d
                                       JOIN d.smartMod sm 
                                       WHERE sm.id IN (:smartMods)
                                       AND d.dateTime LIKE :nowDate            
                                    ")
                ->setParameters(array(
                    'nowDate'    => date("Y-m") . "%",
                    //'zoneId'     => $zone->getId(),
                    'smartMods'  => $smartMods,
                ))
                ->getResult();
            //dump($lastRecord);
            if ($zone->getType() === 'PUE Calculation') {

                /*$lastDatetimeForPUE = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")*/
                $lastDatetimeForPUE = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (:smartMods)
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        //'zoneId'     => $zone->getId(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();
                //dump($lastDatetimeForPUE[0]['dt']);
                $date = new DateTime($lastDatetimeForPUE[0]['dt']);
                $date->sub(new DateInterval('PT2M'));
                //dump($date);
                /*$InstantProductionActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                            FROM App\Entity\LoadDataEnergy d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                            AND d.dateTime = :lastDate
                                                            AND sm.levelZone = 2
                                                            AND sm.subType = 'Production'                                                                                                                                                
                                                            ")*/
                $InstantProductionActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                            FROM App\Entity\LoadDataEnergy d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime LIKE :lastDate
                                                            AND sm.levelZone = 2
                                                            AND sm.subType = 'Production'                                                                                                                                                
                                                            ")
                    ->setParameters(array(
                        'lastDate'   => $date->format('Y-m-d H:i') . '%',
                        //'zoneId'     => $zone->getId(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();
//                dump($InstantProductionActivePower);

                /*$InstantTotalActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND d.dateTime = :lastDate
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")*/
                $InstantTotalActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (:smartMods)
                                                    AND d.dateTime LIKE :lastDate
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        'lastDate'   => $date->format('Y-m-d H:i') . '%',
                        //'zoneId'     => $zone->getId(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();
//                dump($InstantTotalActivePower);
                $InstantTotal_AP = $InstantTotalActivePower[0]['kW'] ?? 0;
                $InstantIT_AP = $InstantProductionActivePower[0]['kW'] ?? 0;
                $InstantTotal_AP = number_format((float) $InstantTotal_AP, 2, '.', '');
                $InstantIT_AP = number_format((float) $InstantIT_AP, 2, '.', '');
                //$InstantPUE = 0;
                if (count($InstantTotalActivePower) && count($InstantProductionActivePower)) {
                    $InstantPUE = $InstantProductionActivePower[0]['kW'] > 0 ? ($InstantTotalActivePower[0]['kW'] * 1.0) / $InstantProductionActivePower[0]['kW'] : 0;
                    $InstantPUE = number_format((float) $InstantPUE, 2, '.', '');
                }

                /*$dataProductionActivePower = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime LIKE :nowDate 
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")*/
                $dataProductionActivePower = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 16) AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (:smartMods)
                                                AND d.dateTime LIKE :nowDate 
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
//                        'nowDate'    => '2022-03-05%',
                        'nowDate'    => date('Y-m-d') . '%',
                        //'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        //'zoneId'     => $zone->getId(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();

                /*$dataTotalActivePower = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime LIKE :nowDate 
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")*/
                $dataTotalActivePower = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 16) AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (:smartMods)
                                                AND d.dateTime LIKE :nowDate 
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
//                        'nowDate'    => '2022-03-05%',
                        'nowDate'    => date('Y-m-d') . '%',
                        //'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        //'zoneId'     => $zone->getId(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();

                foreach ($dataProductionActivePower as $d) {
                    $datePue[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    $productionAP[]   = number_format((float) $d['kW'], 2, '.', '');
                }

                //dump($dataTotalActivePower);
                //die();
                foreach ($dataTotalActivePower as $d) {
                    //$dateE[] = $d['dt'];
                    $totalAP[]   = number_format((float) $d['kW'], 2, '.', '');
                }

                $instantpue =  array_map(function ($a, $b) {
                    return $b > 0 ? round($a / $b, 2) : 0;
                }, $totalAP, $productionAP);

                $indoorClimateData = $manager->createQuery("SELECT d.dateTime AS dt, d.temperature AS temp, d.humidity AS hum
                                                        FROM App\Entity\ClimateData d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime = (SELECT MAX(dd.dateTime) AS ddt FROM App\Entity\ClimateData dd WHERE dd.dateTime LIKE :nowDate)
                                                        AND sm.modType = 'Climate' 
                                                        AND sm.subType = 'Indoor'                                                                                                                                  
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'nowDate'    => date("Y-m-d") . "%",
//                        'nowDate'    => '2021-10-28%',
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
//                dump($indoorClimateData);
                if(count($indoorClimateData)){
                    $ClimDate = $indoorClimateData[0]['dt']->format('Y-m-d H:i:s');
                    $Intemp = $indoorClimateData[0]['temp'];
                    $Inhum = $indoorClimateData[0]['hum'];
                }

                $outdoorClimateData = $manager->createQuery("SELECT d.dateTime AS dt, d.temperature AS temp, d.humidity AS hum
                                                        FROM App\Entity\ClimateData d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime = (SELECT MAX(dd.dateTime) AS ddt FROM App\Entity\ClimateData dd WHERE dd.dateTime LIKE :nowDate)
                                                        AND sm.modType = 'Climate' 
                                                        AND sm.subType = 'Outdoor'                                                                                                                                 
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'nowDate'    => date("Y-m-d") . "%",
//                        'nowDate'    => '2021-10-28%',
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
//                dump($outdoorClimateData);
                if(count($outdoorClimateData)){
//                    $ClimDate = $outdoorClimateData[0]['dt']->format('Y-m-d H:i:s');
                    $Outtemp = $outdoorClimateData[0]['temp'];
                    $Outhum = $outdoorClimateData[0]['hum'];
                }

            }

            $noDatetimeData = $manager->getRepository('App:NoDatetimeData')->findOneBy(['id' => $paramJSON['genId']]) ?? new NoDatetimeData();
//            dump($paramJSON['gensets']);
            foreach ($paramJSON['gensets'] as $id){
//                dump($id);
                // ######## Récupération des données temps réel du module Genset
                $noDatetimeData = $manager->getRepository(NoDatetimeData::class)->findOneBy(['smartMod' => $id]) ?? new NoDatetimeData();
                $gensetFL[$id . ''] = [
                    'id' => $id,
                    'FL' => $noDatetimeData->getFuelLevel() ?? 0,
                    'GR' => $noDatetimeData->getGensetRunning() ?? 0,
                    'Date1' => $noDatetimeData->getDateTime() ?? '',
                ];
            }
            return $this->json([
                'code'     => 200,
                'datePue'  => $datePue,
                'Date1'    => $lastRecord[0]['dt'] ?? '',
                'Vcg'      => [$noDatetimeData->getL12G() ?? 0, $noDatetimeData->getL13G() ?? 0, $noDatetimeData->getL23G() ?? 0],
                //'Vsg'     => [$noDatetimeData->getL1N() ?? 0, $noDatetimeData->getL2N() ?? 0, $noDatetimeData->getL3N() ?? 0],
                'Vcm'     => [$noDatetimeData->getL12M() ?? 0, $noDatetimeData->getL13M() ?? 0, $noDatetimeData->getL23M() ?? 0],
                'InstantPUE' => $InstantPUE,
                'InstantTotal_AP'   => $InstantTotal_AP,
                'InstantIT_AP'  => $InstantIT_AP,
                'PUE'   => $instantpue,
                'CGCR'       => [$noDatetimeData->getCg() ?? 0, $noDatetimeData->getCr() ?? 0],
                'Gensetrunning' => $noDatetimeData->getGensetRunning() ?? 0,
                'MainsPresence' => $noDatetimeData->getMainsPresence() ?? 0,
                'gensets'   => $gensetFL,
                "Intemp"    => number_format((float)$Intemp, 2, '.', ' '),
                "Inhum"     => number_format((float)$Inhum, 2, '.', ' '),
                "Outtemp"   => number_format((float)$Outtemp, 2, '.', ' '),
                "Outhum"    => number_format((float)$Outhum, 2, '.', ' '),
                "ClimDate"  => $ClimDate,

            ], 200);
        }
        return $this->json([
            'code'         => 500,
        ], 500);
    }
}
