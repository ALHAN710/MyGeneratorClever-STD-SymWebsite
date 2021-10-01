<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use App\Entity\Zone;
use App\Form\ZoneType;
use App\Repository\ZoneRepository;
use App\Form\ZoneUserCollectionType;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/zone")
 */
class ZoneController extends ApplicationController
{
    /**
     * @Route("/{zone<\d+>?}", name="home_zone")
     * @IsGranted("ROLE_USER")
     */
    public function dashboard(Zone $zone, EntityManagerInterface $manager): Response
    {
        /*$smartMods = $manager->createQuery("SELECT sm.id AS Id
                            FROM App\Entity\SmartMod sm
                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                            AND sm.levelZone = 2 
                            AND sm.subType = 'Production'                                                                                                                                           
                            ")
            ->setParameters(array(
                'zoneId'     => $zone->getId()
            ))
            ->getResult();
        foreach ($smartMods as $smartMod) {
            $smartModsProduction[] = $smartMod['Id'];
        }*/
        // //dump($smartModsProduction);
        $climates = [];
        $climatesOut = [];
        $inTemp = 0.0;
        $inHum = 0.0;
        $outTemp = 0.0;
        $outHum = 0.0;
        $lastDate = 'Y-m-d HH:mm:ss';
        if ($zone->getType() === 'PUE Calculation') {

            foreach ($zone->getSmartMods() as $smartMod) {
                if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Indoor') $climates[] = $smartMod;
                else if ($smartMod->getModType() === 'Climate' && $smartMod->getSubType() === 'Outdoor') $climatesOut[] = $smartMod;
            }
            $LastIndoorData = $manager->createQuery("SELECT d.temperature AS temp, d.humidity AS hum, d.dateTime AS dt
                                                            FROM App\Entity\ClimateData d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (:smartMods)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\ClimateData d1 WHERE d1.dateTime LIKE :nowDate)
                                                                                                                                                                                                            
                                                            ")
                ->setParameters(array(
                    'smartMods'      => $climates,
                    //'nowDate'     => "2021-07-20%",
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
                $lastDate = $LastIndoorData[0]['dt']->format('Y-m-d H:m:s');
            }
            if (count($LastOutdoorData) > 0) {
                $outTemp = $LastOutdoorData[0]['temp'];
                $outHum = $LastOutdoorData[0]['hum'];
            }
        }
        return $this->render('zone/dashboard.html.twig', [
            'zone' => $zone,
            //'smartModsProduction' => $smartModsProduction,
            'alarms'    => $manager->getRepository('App:Alarm')->findBy(['type' => 'Load Meter']),
            'inTemp'    => $inTemp,
            'inHum'    => $inHum,
            'outTemp'    => $outTemp,
            'outHum'    => $outHum,
            'lastDate'  => $lastDate,
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="zone_show", methods={"GET"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and zone.getSite().getEnterprise() === user.getEnterprise() )" )
     */
    public function show(Zone $zone): Response
    {
        return $this->render('zone/show.html.twig', [
            'zone' => $zone,
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="zone_edit", methods={"GET","POST"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and zone.getSite().getEnterprise() === user.getEnterprise() )" )
     */
    public function edit(Request $request, Zone $zone): Response
    {
        $form = $this->createForm(ZoneType::class, $zone);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('zone_index');
        }

        return $this->render('zone/edit.html.twig', [
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/list", name="zone_admin_index", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function adminZoneIndex(EntityManagerInterface $manager): Response
    {
        //$manager = $this->getDoctrine()->getManager();
        $zones = [];
        $zones_ = $manager->createQuery("SELECT zn
                            FROM App\Entity\Zone zn
                            JOIN zn.site st
                            WHERE st.id IN (SELECT st_.id FROM App\Entity\Site st_ JOIN st_.enterprise ent WHERE ent.id = :entId)                                    
                            ")
            ->setParameters(array(
                'entId'     => $this->getUser()->getEnterprise()->getId()
            ))
            ->getResult();
        foreach ($zones_ as $zone) {
            $zones[] = $zone;
        }
        //dd($zones);
        return $this->render('zone/admin_index.html.twig', [
            'zones' => $zones,
        ]);
    }

    /**
     * @Route("/{zone<\d+>}/settings", name="zone_setting", methods={"GET","POST"})
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_ADMIN') and zone.getSite().getEnterprise() === user.getEnterprise() )" )
     */
    public function adminZoneSetting(Request $request, Zone $zone): Response
    {
        $form = $this->createForm(ZoneUserCollectionType::class, $zone, [
            'entId'   => $this->getUser()->getEnterprise()->getId(),
            'forZone' => true,
        ]);
        $form->handleRequest($request);
        // dump($zone);
        $manager = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($zone->getUsers() as $user) {

                //dump($user->getUserNam());
                // $zone->addUser($user);
                //$user->addZone($zone);
                //Je vérifie si le produit est déjà existant en BDD pour éviter les doublons 
                $user_ = $manager->getRepository('App:User')->findOneBy(['id' => $user->getUserNam()->getId()]);
                //dd($user_);
                // $user->addZone($zone);
                // $manager->persist($user);
                if (empty($user->getId())) {
                    if (empty($user_)) {
                        //$user->addZone($zone);
                        //$manager->persist($user);
                        $zone->removeUser($user);
                        // //dump('user dont exists ');
                    } else {
                        // //dump('user exists with id = ' . $user_->getId());
                        if (!$user_->getZones()->contains($zone)) {
                            // //dump("user don't have a zone " . $zone->getName());
                            $zone->removeUser($user);
                            $user = $user_;
                            $user->addZone($zone);
                            $zone->addUser($user);
                            if (!$zone->getSite()->getUsers()->contains($user)) {
                                $user->addSite($zone->getSite());
                                $zone->getSite()->addUser($user);
                                $manager->persist($zone->getSite());
                            }
                            $manager->persist($user);
                        }
                    }
                }
                // $manager->persist($zone);
                //$manager->persist($user);
            }
            dump($zone);
            dd($zone->getSite());
            $manager->persist($zone);
            //die();
            $manager->flush();

            $this->addFlash(
                'success',
                "The modifications of zone <strong> {$zone->getName()} </strong> have been saved !"
            );

            return $this->redirectToRoute('zone_admin_index');
        }

        return $this->render('zone/admin_settings.html.twig', [
            'zone' => $zone,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Permet de mettre à jour les graphes en cours liés aux données des modules load Meter d'une zone
     *
     * @Route("/update/overview/graphs/", name="update_zone_overview_graphs")
     * 
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateOverviewGraph(EntityManagerInterface $manager, Request $request): Response
    {
        //$smartModRepo = $this->getDoctrine()->getRepository(SmartModRepository::class);
        //$smartMod = $smartModRepo->find($id);
        // //dump($smartModRepo);
        // //dump($smartMod->getModType());
        //$temps = DateTime::createFromFormat("d-m-Y H:i:s", "120");
        // //dump($temps);
        //die();
        $date        = [];
        $EA_flow   = [];
        $ER_flow = [];
        $S = 0.00;
        $P = 0.00;
        $FP_flow = 0.00;
        $EA_month = 0.0;
        $kgCO2_month = 0.0;
        $p = [];
        $s = [];
        $fp = [];
        $IntervalPUE = 0;
        $InstantPUE = 0;
        $InstantTotal_AP = 0;
        $InstantIT_AP = 0;
        $instantpue = [];
        $intervalpue = [];
        $diffEnergy = [];
        $dateE       = [];
        $dateP = [];
        $datePSCosfi = [];
        $dateClimate   = [];
        $productionAP = [];
        $totalAP = [];
        $totalEA = [];
        $productionEA = [];

        //Climate data variables
        $inTemperature = [];
        $outTemperature = [];
        $inHumidity = [];
        $outHumidity = [];

        $xScale = 'day';

        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['id' => $paramJSON['id']]);
        $zone = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['zoneId']]);
        // $dateparam = $request->get('selectedDate'); // Ex : %2020-03-20%
        //$dateparam = $paramJSON['selectedDate']; // Ex : %2020-03-20%
        //$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['startDate']); // Ex : %2020-03-20%
        $startDate = new DateTime($paramJSON['startDate']); // Ex : %2020-03-20%
        //$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['endDate']); // Ex : %2020-03-20%
        $endDate = new DateTime($paramJSON['endDate']); // Ex : %2020-03-20%
        // dump($startDate->format('Y-m-d H:i:s'));
        // dump($endDate->format('Y-m-d H:i:s'));
        //$dat = "2020-02"; //'%' . $dat . '%'
        //$dat = substr($dateparam, 0, 8); // Ex : %2020-03
        // //dump($dat);
        //die();
        //$dat = $dat . '%';

        $dateparam = $request->get('selectedDate'); // Ex : %2020-03-20%
        //$dat = "2020-02"; //'%' . $dat . '%'
        $dat = substr($dateparam, 0, 8); // Ex : %2020-03
        // //dump($dat);
        //die();
        $dat = $dat . '%';

        if ($zone) {
            foreach ($zone->getSmartMods() as $smartMod) {
                if ($smartMod->getModType() === 'Load Meter' && $smartMod->getLevelZone() === 2) {
                    $EA_flow[$smartMod->getId()]   = 0.00;
                    $ER_flow[$smartMod->getId()] = 0.00;
                    // $P['' . $smartMod->getId()] = "0.00";
                    // $S['' . $smartMod->getId()] = "0.00";
                    // $FP_flow['' . $smartMod->getId()] = "0.00";
                }
            }
            //SUM( SQRT( (d.pmoy*d.pmoy) + (SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) ) AS kVA,
            $commonEnergyData = $manager->createQuery("SELECT sm.id AS ID, SUM(d.ea) AS kWh, SUM(d.er) AS kVAR
                                            FROM App\Entity\SmartMod sm
                                            JOIN sm.loadDataEnergies d 
                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                            AND d.dateTime BETWEEN :startDate AND :endDate
                                            AND sm.levelZone = 2
                                            GROUP BY ID
                                            ORDER BY ID ASC                                                                                                                                                
                                            ")
                ->setParameters(array(
                    //'selDate'      => $dat,
                    'startDate'  => $startDate->format('Y-m-d H:i:s'),
                    'endDate'    => $endDate->format('Y-m-d H:i:s'),
                    'zoneId'     => $zone->getId()
                ))
                ->getResult();

            // dump($commonData);

            //die();
            foreach ($commonEnergyData as $d) {
                //$dateE[] = $d['dt']->format('Y-m-d H:i:s');
                $EA_flow[$d['ID']]   = floatval(number_format((float) $d['kWh'], 2, '.', ''));
                $ER_flow[$d['ID']] = floatval(number_format((float) $d['kVAR'], 2, '.', ''));
                // $P['' . $d['ID']] = number_format((float) $d['P'], 2, '.', '');
                // $S['' . $d['ID']] = number_format((float) $d['S'], 2, '.', '');
                // $FP_flow['' . $d['ID']] = number_format((float) $d['PF'], 2, '.', '');
            }

            $commonCurrentMonthEnergyQuery = $manager->createQuery("SELECT SUM(d.ea) AS kWh, SUM(d.ea)*0.207 AS kgCO2
                                            FROM App\Entity\SmartMod sm
                                            JOIN sm.loadDataEnergies d 
                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                            AND d.dateTime LIKE :currentMonth
                                            AND sm.levelZone = 2                                                                                                                                          
                                            ")
                ->setParameters(array(
                    //'selDate'      => $dat,
                    'currentMonth' => date('Y-m') . '%',
                    //'endDate'    => $endDate->format('Y-m-d H:i:s'),
                    'zoneId'       => $zone->getId()
                ))
                ->getResult();

            // dump($commonData);

            //die();

            foreach ($commonCurrentMonthEnergyQuery as $d) {
                //$dateE[] = $d['dt']->format('Y-m-d H:i:s');
                $EA_month   = floatval(number_format((float) $d['kWh'], 1, '.', ''));
                $kgCO2_month = floatval(number_format((float) $d['kgCO2'], 1, '.', ''));
                // $P['' . $d['ID']] = number_format((float) $d['P'], 2, '.', '');
                // $S['' . $d['ID']] = number_format((float) $d['S'], 2, '.', '');
                // $FP_flow['' . $d['ID']] = number_format((float) $d['PF'], 2, '.', '');
            }
            /*$commonPowerData = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy)*1000 AS P, 
                                            SUM(d.ea)/SQRT( (SUM(d.ea)*SUM(d.ea)) + (SUM(d.er)*SUM(d.er)) ) AS PF, SQRT( (SUM(d.pmoy)*SUM(d.pmoy)) + (SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) )*1000 AS S
                                            FROM App\Entity\SmartMod sm
                                            JOIN sm.loadDataEnergies d 
                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                            AND d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\LoadDataEnergy d1 WHERE d1.dateTime LIKE :nowDate)
                                            AND sm.levelZone = 2
                                            GROUP BY dt
                                            ORDER BY dt ASC                                                                                                                                                
                                            ")
                ->setParameters(array(
                    //'selDate'      => $dat,
                    'nowDate'      => date("Y-m-d") . "%",
                    'zoneId'     => $zone->getId()
                ))
                ->getResult();

            //dump($commonData);

            //die();
            foreach ($commonPowerData as $d) {
                //$dateE[] = $d['dt']->format('Y-m-d H:i:s');
                // $EA_flow[$d['ID']]   = floatval(number_format((float) $d['kWh'], 2, '.', ''));
                // $ER_flow[$d['ID']] = floatval(number_format((float) $d['kVAR'], 2, '.', ''));
                $P = number_format((float) $d['P'], 2, '.', '');
                $S = number_format((float) $d['S'], 2, '.', '');
                $FP_flow = number_format((float) $d['PF'], 2, '.', '');
            }*/
            $lastRecord = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                       FROM App\Entity\SmartMod sm
                                       JOIN sm.loadDataEnergies d 
                                       WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                       AND d.dateTime LIKE :nowDate            
                                    ")
                ->setParameters(array(
                    'nowDate'      => date("Y-m-d") . "%",
                    'zoneId'     => $zone->getId()
                ))
                ->getResult();
            //dump($lastRecord);
            if ($zone->getType() === 'PUE Calculation') {
                $lastDatetimeForPUE = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($lastDatetimeForPUE[0]['dt']);
                $date = new DateTime($lastDatetimeForPUE[0]['dt'] ?? 'now');
                $date->sub(new DateInterval('PT2M'));
                //dump($date);
                $InstantProductionActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                            FROM App\Entity\LoadDataEnergy d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                            AND d.dateTime = :lastDate
                                                            AND sm.levelZone = 2
                                                            AND sm.subType = 'Production'                                                                                                                                                
                                                            ")
                    ->setParameters(array(
                        'lastDate'      => $date->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($InstantProductionEnergy);

                $InstantTotalActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND d.dateTime = :lastDate
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        'lastDate'      => $date->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($InstantTotalActivePower);
                $InstantTotal_AP = $InstantTotalActivePower[0]['kW'] ?? 0;
                $InstantIT_AP = $InstantProductionActivePower[0]['kW'] ?? 0;
                $InstantTotal_AP = number_format((float) $InstantTotal_AP, 2, '.', '');
                $InstantIT_AP = number_format((float) $InstantIT_AP, 2, '.', '');
                $InstantPUE = 0;
                if (count($InstantTotalActivePower) && count($InstantProductionActivePower)) {
                    $InstantPUE = $InstantProductionActivePower[0]['kW'] > 0 ? ($InstantTotalActivePower[0]['kW'] * 1.0) / $InstantProductionActivePower[0]['kW'] : 0;
                    $InstantPUE = number_format((float) $InstantPUE, 2, '.', '');
                }
                // dump('InstantPUE = ' . $InstantPUE);
                /*
                $IntervalTotalEnergy = $manager->createQuery("SELECT SUM(d.ea) AS kWh
                                                        FROM App\Entity\LoadDataEnergy d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.levelZone = 2
                                                                                                                                                                                                      
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($IntervalTotalEnergy);

                $IntervalProductionEnergy = $manager->createQuery("SELECT SUM(d.ea) AS kWh
                                                        FROM App\Entity\LoadDataEnergy d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.levelZone = 2 
                                                        AND sm.subType = 'Production'                                                                                                                                               
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($IntervalProductionEnergy);
                */

                $indoorClimateData = $manager->createQuery("SELECT d.dateTime AS dt, d.temperature AS temp, d.humidity AS hum
                                                        FROM App\Entity\ClimateData d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.modType = 'Climate' 
                                                        AND sm.subType = 'Indoor'  
                                                        GROUP BY dt
                                                        ORDER BY dt ASC                                                                                                                                             
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($indoorClimateData);
                foreach ($indoorClimateData as $d) {
                    $dateClimate[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    //$productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                    $inTemperature[]   = $d['temp'];
                    $inHumidity[]   = $d['hum'];
                }

                $outdoorClimateData = $manager->createQuery("SELECT d.dateTime AS dt, d.temperature AS temp, d.humidity AS hum
                                                        FROM App\Entity\ClimateData d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.modType = 'Climate' 
                                                        AND sm.subType = 'Outdoor'   
                                                        GROUP BY dt
                                                        ORDER BY dt ASC                                                                                                                                            
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($outdoorClimateData);
                foreach ($outdoorClimateData as $d) {
                    //$dateClimate[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    //$productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                    $outTemperature[]   = $d['temp'];
                    $outHumidity[]   = $d['hum'];
                }

                /*$IntervalPUE = 0;
                if (count($IntervalProductionEnergy) && count($IntervalTotalEnergy)) {
                    $IntervalPUE = $IntervalProductionEnergy[0]['kWh'] > 0 ? ($IntervalTotalEnergy[0]['kWh'] * 1.0) / $IntervalProductionEnergy[0]['kWh'] : 0;
                    $IntervalPUE = number_format((float) $IntervalPUE, 2, '.', '');
                }*/
                // dump('IntervalPUE = ' . $IntervalPUE);
                $dataProductionActivePower = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime LIKE :startDate 
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d') . '%',
                        //'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();

                $dataTotalActivePower = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime LIKE :startDate 
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d') . '%',
                        //'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();


                $dataProductionEnergy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,13) AS dt, SUM(d.ea) AS kWh
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate 
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        //'startDate'  => $startDate->format('Y-m-d') . '%',
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($dataProductionEnergy);
                $dataTotalEnergy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,13) AS dt, SUM(d.ea) AS kWh
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        //'startDate'  => $startDate->format('Y-m-d') . '%',
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();

                //dump($dataTotalEnergy);
                //die();
                foreach ($dataProductionActivePower as $d) {
                    $dateP[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    $productionAP[]   = number_format((float) $d['kW'], 2, '.', '');
                }
                foreach ($dataTotalActivePower as $d) {
                    //$dateE[] = $d['dt'];
                    $totalAP[]   = number_format((float) $d['kW'], 2, '.', '');
                }

                foreach ($dataProductionEnergy as $d) {
                    $dateE[] = $d['dt'] . ":00:00";
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    $productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                }
                // dump($dataTotalEnergy);
                //die();
                foreach ($dataTotalEnergy as $d) {
                    //$dateE[] = $d['dt'];
                    $totalEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                }
                // dump($dataTotalActivePower);
                //die();

                $instantpue =  array_map(function ($a, $b) {
                    return $b > 0 ? round($a / $b, 2) : 0;
                }, $totalAP, $productionAP);

                $intervalpue =  array_map(function ($a, $b) {
                    return $b > 0 ? round($a / $b, 2) : 0;
                }, $totalEA, $productionEA);
                // dump($intervalpue);


                /*$diffEnergy =  array_map(function ($a, $b) {
                    return number_format((float) ($a - $b), 2, '.', '');
                }, $totalEA, $productionEA);*/
                // dump($diffEnergy);
            } else {
                $data = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy)*1000 AS P, 
                                            SUM(d.ea)/SQRT( (SUM(d.ea)*SUM(d.ea)) + (SUM(d.er)*SUM(d.er)) ) AS PF, SQRT( (SUM(d.pmoy)*SUM(d.pmoy)) + (SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) )*1000 AS S
                                            FROM App\Entity\SmartMod sm
                                            JOIN sm.loadDataEnergies d 
                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                            AND d.dateTime BETWEEN :startDate AND :endDate
                                            AND sm.levelZone = 2
                                            GROUP BY dt
                                            ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();

                // dump($data);

                //die();
                foreach ($data as $d) {
                    $datePSCosfi[] = $d['dt']->format('Y-m-d H:i:s');
                    $p[] = number_format((float) $d['P'], 2, '.', '');
                    $s[] = number_format((float) $d['S'], 2, '.', '');
                    $fp[] = number_format((float) $d['PF'], 2, '.', '');
                }
            }

            return $this->json([
                'code'                => 200,
                'date'                => $dateE,
                'dateP'               => $dateP,
                'Date1'               => $lastRecord[0]['dt'] ?? '',
                'datePSCosfi'         => $datePSCosfi,
                'climateDate'         => $dateClimate,
                'xscale'              => $xScale,
                'InstantPUE'          => $InstantPUE,
                'InstantTotal_AP'     => $InstantTotal_AP,
                'InstantIT_AP'        => $InstantIT_AP,
                'IntervalPUE'         => $IntervalPUE,
                'PieActiveEnergy'     => $EA_flow,
                'PieReactiveEnergy'   => $ER_flow,
                'PUE'                 => [$productionAP, $totalAP, $instantpue],
                'MixedEnergy'         => [$totalEA, $productionEA, $intervalpue],
                'MixedClimate'        => [$inTemperature, $outTemperature, $inHumidity, $outHumidity],
                'MixedPSCosfi'        => [$s, $p, $fp],
                //'S'                   => end($s), //$S,
                'P'                   => end($p) ? end($p) : 0, //$P,
                //'FP'                  => end($fp), //$FP_flow,
                'EA'                  => $EA_month,
                'kgCO2'               => $kgCO2_month

            ], 200);
        }


        return $this->json([
            'code'         => 500,
        ], 500);
    }

    /**
     * Permet de mettre à jour l'historique des graphes liés aux données des modules load Meter d'une zone
     *
     * @Route("/update/zone/graphs/", name="update_zone_graphs")
     * 
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateGraph(EntityManagerInterface $manager, Request $request): Response
    {
        //$smartModRepo = $this->getDoctrine()->getRepository(SmartModRepository::class);
        //$smartMod = $smartModRepo->find($id);
        // //dump($smartModRepo);
        // //dump($smartMod->getModType());
        //$temps = DateTime::createFromFormat("d-m-Y H:i:s", "120");
        // //dump($temps);
        //die();
        $date        = [];
        $EA_flow   = [];
        $ER_flow = [];
        $Smax = [];
        $FP_flow = [];
        $IntervalPUE = 0;
        $InstantPUE = 0;
        $InstantTotal_AP = 0;
        $InstantIT_AP = 0;
        $instantpue = [];
        $intervalpue = [];
        $diffEnergy = [];
        $dateE       = [];
        $dateP = [];
        $dateClimate   = [];
        $productionAP = [];
        $totalAP = [];
        $totalEA = [];
        $productionEA = [];

        //Climate data variables
        $inTemperature = [];
        $outTemperature = [];
        $inHumidity = [];
        $outHumidity = [];

        $xScale = 'day';

        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['id' => $paramJSON['id']]);
        $zone = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['zoneId']]);
        // $dateparam = $request->get('selectedDate'); // Ex : %2020-03-20%
        //$dateparam = $paramJSON['selectedDate']; // Ex : %2020-03-20%
        //$startDate = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['startDate']); // Ex : %2020-03-20%
        $startDate = new DateTime($paramJSON['startDate']); // Ex : %2020-03-20%
        //$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['endDate']); // Ex : %2020-03-20%
        $endDate = new DateTime($paramJSON['endDate']); // Ex : %2020-03-20%
        // dump($startDate->format('Y-m-d H:i:s'));
        // dump($endDate->format('Y-m-d H:i:s'));
        //$dat = "2020-02"; //'%' . $dat . '%'
        //$dat = substr($dateparam, 0, 8); // Ex : %2020-03
        // //dump($dat);
        //die();
        //$dat = $dat . '%';

        $dateparam = $request->get('selectedDate'); // Ex : %2020-03-20%
        //$dat = "2020-02"; //'%' . $dat . '%'
        $dat = substr($dateparam, 0, 8); // Ex : %2020-03
        // //dump($dat);
        //die();
        $dat = $dat . '%';

        if ($zone) {
            foreach ($zone->getSmartMods() as $smartMod) {
                if ($smartMod->getModType() === 'Load Meter' && $smartMod->getLevelZone() === 2) {
                    $EA_flow[$smartMod->getId()]   = 0.00;
                    $ER_flow[$smartMod->getId()] = 0.00;
                    $Smax['' . $smartMod->getId()] = "0.00";
                    $FP_flow['' . $smartMod->getId()] = "0.00";
                }
            }
            //SUM( SQRT( (d.pmoy*d.pmoy) + (SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) ) AS kVA,
            $commonData = $manager->createQuery("SELECT sm.id AS ID, SUM(d.ea) AS kWh, SUM(d.er) AS kVAR, 
                                            SUM(d.ea)/SQRT( (SUM(d.ea)*SUM(d.ea)) + (SUM(d.er)*SUM(d.er)) ) AS PF, MAX(d.smoy) AS Smax
                                            FROM App\Entity\SmartMod sm
                                            JOIN sm.loadDataEnergies d 
                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                            AND d.dateTime BETWEEN :startDate AND :endDate
                                            AND sm.levelZone = 2
                                            GROUP BY ID
                                            ORDER BY ID ASC                                                                                                                                                
                                            ")
                ->setParameters(array(
                    //'selDate'      => $dat,
                    'startDate'  => $startDate->format('Y-m-d H:i:s'),
                    'endDate'    => $endDate->format('Y-m-d H:i:s'),
                    'zoneId'     => $zone->getId()
                ))
                ->getResult();

            // dump($commonData);

            //die();
            foreach ($commonData as $d) {
                //$dateE[] = $d['dt']->format('Y-m-d H:i:s');
                $EA_flow[$d['ID']]   = floatval(number_format((float) $d['kWh'], 2, '.', ''));
                $ER_flow[$d['ID']] = floatval(number_format((float) $d['kVAR'], 2, '.', ''));
                $Smax['' . $d['ID']] = number_format((float) $d['Smax'], 2, '.', '');
                $FP_flow['' . $d['ID']] = number_format((float) $d['PF'], 2, '.', '');
            }

            if ($zone->getType() === 'PUE Calculation') {
                /*$lastDatetimeForPUE = $manager->createQuery("SELECT MAX(d.dateTime) AS dt
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($lastDatetimeForPUE[0]['dt']);
                $date = new DateTime($lastDatetimeForPUE[0]['dt']);
                $date->sub(new DateInterval('PT2M'));
                //dump($date);
                $InstantProductionActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                            FROM App\Entity\LoadDataEnergy d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                            AND d.dateTime = :lastDate
                                                            AND sm.levelZone = 2
                                                            AND sm.subType = 'Production'                                                                                                                                                
                                                            ")
                    ->setParameters(array(
                        'lastDate'      => $date->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($InstantProductionEnergy);

                $InstantTotalActivePower = $manager->createQuery("SELECT SUM(d.pmoy) AS kW
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND d.dateTime = :lastDate
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        'lastDate'      => $date->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($InstantTotalActivePower);

                $InstantTotal_AP = $InstantTotalActivePower[0]['kW'] ?? 0;
                $InstantIT_AP = $InstantProductionActivePower[0]['kW'] ?? 0;
                $InstantTotal_AP = number_format((float) $InstantTotal_AP, 2, '.', '');
                $InstantIT_AP = number_format((float) $InstantIT_AP, 2, '.', '');
                $InstantPUE = 0;
                if (count($InstantTotalActivePower) && count($InstantProductionActivePower)) {
                    $InstantPUE = $InstantProductionActivePower[0]['kW'] > 0 ? ($InstantTotalActivePower[0]['kW'] * 1.0) / $InstantProductionActivePower[0]['kW'] : 0;
                    $InstantPUE = number_format((float) $InstantPUE, 2, '.', '');
                }*/

                // dump('InstantPUE = ' . $InstantPUE);
                /*
                $IntervalTotalEnergy = $manager->createQuery("SELECT SUM(d.ea) AS kWh
                                                        FROM App\Entity\LoadDataEnergy d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.levelZone = 2
                                                                                                                                                                                                      
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($IntervalTotalEnergy);

                $IntervalProductionEnergy = $manager->createQuery("SELECT SUM(d.ea) AS kWh
                                                        FROM App\Entity\LoadDataEnergy d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.levelZone = 2 
                                                        AND sm.subType = 'Production'                                                                                                                                               
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                // dump($IntervalProductionEnergy);
                */

                $indoorClimateData = $manager->createQuery("SELECT d.dateTime AS dt, d.temperature AS temp, d.humidity AS hum
                                                        FROM App\Entity\ClimateData d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.modType = 'Climate' 
                                                        AND sm.subType = 'Indoor'  
                                                        GROUP BY dt
                                                        ORDER BY dt ASC                                                                                                                                             
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($indoorClimateData);
                foreach ($indoorClimateData as $d) {
                    $dateClimate[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    //$productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                    $inTemperature[]   = $d['temp'];
                    $inHumidity[]   = $d['hum'];
                }

                $outdoorClimateData = $manager->createQuery("SELECT d.dateTime AS dt, d.temperature AS temp, d.humidity AS hum
                                                        FROM App\Entity\ClimateData d
                                                        JOIN d.smartMod sm 
                                                        WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                        AND d.dateTime BETWEEN :startDate AND :endDate
                                                        AND sm.modType = 'Climate' 
                                                        AND sm.subType = 'Outdoor'   
                                                        GROUP BY dt
                                                        ORDER BY dt ASC                                                                                                                                            
                                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                //dump($outdoorClimateData);
                foreach ($outdoorClimateData as $d) {
                    //$dateClimate[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    //$productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                    $outTemperature[]   = $d['temp'];
                    $outHumidity[]   = $d['hum'];
                }

                /*$IntervalPUE = 0;
                if (count($IntervalProductionEnergy) && count($IntervalTotalEnergy)) {
                    $IntervalPUE = $IntervalProductionEnergy[0]['kWh'] > 0 ? ($IntervalTotalEnergy[0]['kWh'] * 1.0) / $IntervalProductionEnergy[0]['kWh'] : 0;
                    $IntervalPUE = number_format((float) $IntervalPUE, 2, '.', '');
                }*/
                // dump('IntervalPUE = ' . $IntervalPUE);
                $dataProductionActivePower = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate 
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();

                $dataTotalActivePower = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate 
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();

                if ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
                    $xScale = 'month';
                    /*
                    $dataProductionActivePower = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,10) AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'zoneId'     => $zone->getId()
                        ))
                        ->getResult();

                    $dataTotalActivePower = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,10) AS dt, SUM(d.pmoy) AS kW
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'zoneId'     => $zone->getId()
                        ))
                        ->getResult();
                    */
                    $dataProductionEnergy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,10) AS dt, SUM(d.ea) AS kWh
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'zoneId'     => $zone->getId()
                        ))
                        ->getResult();

                    $dataTotalEnergy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,10) AS dt, SUM(d.ea) AS kWh
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'zoneId'     => $zone->getId()
                        ))
                        ->getResult();
                } else {
                    $dataProductionEnergy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,13) AS dt, SUM(d.ea) AS kWh
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                AND sm.subType = 'Production' 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            //'startDate'  => $startDate->format('Y-m-d') . '%',
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'zoneId'     => $zone->getId()
                        ))
                        ->getResult();

                    $dataTotalEnergy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,13) AS dt, SUM(d.ea) AS kWh
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2 
                                                GROUP BY dt
                                                ORDER BY dt ASC                                                                                                                                                
                                            ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            //'startDate'  => $startDate->format('Y-m-d') . '%',
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'zoneId'     => $zone->getId()
                        ))
                        ->getResult();
                }
                // dump($dataProductionEnergy);
                //die();
                foreach ($dataProductionActivePower as $d) {
                    $dateP[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    $productionAP[]   = number_format((float) $d['kW'], 2, '.', '');
                }

                // dump($dataTotalActivePower);
                //die();
                foreach ($dataTotalActivePower as $d) {
                    //$dateE[] = $d['dt'];
                    $totalAP[]   = number_format((float) $d['kW'], 2, '.', '');
                }

                foreach ($dataProductionEnergy as $d) {
                    $dateE[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    $productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                }
                // dump($dataTotalEnergy);
                //die();
                foreach ($dataTotalEnergy as $d) {
                    //$dateE[] = $d['dt'];
                    $totalEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                }

                $instantpue =  array_map(function ($a, $b) {
                    return $b > 0 ? round($a / $b, 2) : 0;
                }, $totalAP, $productionAP);

                $intervalpue =  array_map(function ($a, $b) {
                    return $b > 0 ? round($a / $b, 2) : 0;
                }, $totalEA, $productionEA);
                // dump($intervalpue);


                /*$diffEnergy =  array_map(function ($a, $b) {
                    return number_format((float) ($a - $b), 2, '.', '');
                }, $totalEA, $productionEA);*/
                // dump($diffEnergy);
            }

            return $this->json([
                'code'    => 200,
                'date'    => $dateE,
                'dateP'    => $dateP,
                'climateDate'   => $dateClimate,
                'xscale'    => $xScale,
                'InstantPUE' => $InstantPUE,
                'InstantTotal_AP'   => $InstantTotal_AP,
                'InstantIT_AP'  => $InstantIT_AP,
                'IntervalPUE' => $IntervalPUE,
                'PieActiveEnergy'      => $EA_flow,
                'PieReactiveEnergy'   => $ER_flow,
                'PUE'   => [$productionAP, $totalAP, $instantpue],
                'MixedEnergy'     => [$totalEA, $productionEA, $intervalpue],
                'MixedClimate'    => [$inTemperature, $outTemperature, $inHumidity, $outHumidity],
                'Smax'    => $Smax,
                'FP'    => $FP_flow,


            ], 200);
        }


        return $this->json([
            'code'         => 500,
        ], 500);
    }

    /**
     * Permet de mettre à jour la Facture d'un module load Meter ou d'une zone
     *
     * @Route("/update/bill/", name="update_bill")
     * 
     * @param [SmartMod] $smartMod
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateBill(EntityManagerInterface $manager, Request $request): Response
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$type = $paramJSON['type'];
        $smartMod = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['id']]);
        $zone = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['zoneId']]);
        $startDate = new DateTime($paramJSON['startDate']); // Ex : %2020-03-20%
        //$endDate = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['endDate']); // Ex : %2020-03-20%
        $endDate = new DateTime($paramJSON['endDate']); // Ex : %2020-03-20%
        // dump($zone);
        // dump($startDate->format('Y-m-d H:i:s'));
        // dump($endDate->format('Y-m-d H:i:s'));

        $interval = $endDate->diff($startDate);
        $nbDay = 1;
        $amountEAHP = 0;
        $amountEAP = 0;
        $amountEA = 0;
        //$interval = $this->periodofvalidity->diff($nowDate);
        if ($interval) {
            //return gettype($interval->format('d'));
            //return $interval->format('%R%a days'); // '+29 days'
            $nbDay += $interval->days; //Nombre de jour total de différence entre les dates
            //$nbDay = $interval->days; //Nombre de jour total de différence entre les dates
            // dump($nbDay);
            //return !$interval->invert; // 
            //return $this->isActivated;
        }
        $nbHours = 24 * $nbDay;
        //$nbHours = $nbDay > 0 ? 24 * $nbDay : 24;
        // dump($nbHours);

        if ($zone) {
            $niv1 = 0;
            if ($smartMod) { //Recherche de l'existance  d'un module de type LOAD de niv = 1
                if ($smartMod->getLevelZone() === 1) {
                    $niv1 = $smartMod->getId();
                }
            }
            if ($niv1 !== 0) {
                /*$Energy = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.kWh) AS kWh, SUM(d.kVarh) AS kVarh 
                                                    FROM App\Entity\DataMod d
                                                    JOIN d.zone sm 
                                                    WHERE d.dateTime BETWEEN :startDate AND :endDate
                                                    AND sm.id = :smartModId
                                                    GROUP BY dt
                                                    ORDER BY dt ASC
                                                                                            
                                                    ")
                        ->setParameters(array(
                            //'selDate'      => $dat,
                            'startDate'  => $startDate->format('Y-m-d H:i:s'),
                            'endDate'    => $endDate->format('Y-m-d H:i:s'),
                            'smartModId' => $niv1
                        ))
                        ->getResult();*/
            } else {
                /*$GensetParams = $manager->createQuery("SELECT MAX(d.totalRunningHours) - MIN(NULLIF(d.totalRunningHours, 0)) AS TRH, 
                                        MAX(d.totalEnergy) - MIN(NULLIF(d.totalEnergy, 0)) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime BETWEEN :startDate AND :endDate
                                        AND sm.id = :smartModId                
                                        ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'    => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'smartModId'   => $manager->getRepository('App:SmartMod')->findOneBy(['site' => $zone->getSite(), 'modType' => 'FUEL'])->getId()
                    ))
                    ->getResult();
                // dump($GensetParams);
                $NHU_FUEL = $GensetParams[0]['TRH'] ?? 0;
                $NHU_Grid = $nbHours - $NHU_FUEL;*/
                /*
                     SELECT  SUBSTRING(`date_time`,12,8) AS dt, SUM(`ea`) AS kWh, SUM(`pmoy`) AS kW 
                    FROM `load_data_energy` d
                    JOIN `smart_mod` sm 
                    WHERE `smart_mod_id` IN (SELECT `smart_mod_id` FROM `zone_smart_mod` WHERE `zone_id` = 1)
                    AND (CAST(`date_time`AS DATETIME) BETWEEN CAST('2021-03-01 00:00:00'AS DATETIME) AND CAST('2021-05-10 23:59:59'AS DATETIME))
                    AND sm.`level_zone` = 2
                    GROUP BY dt
                    ORDER BY dt ASC
                */
                $smartMods  = []; //$manager->getRepository('App:SmartMod')->findBy(['zones' => [$zone->getId()], 'modType' => 'Load Meter']);
                foreach ($zone->getSmartMods() as $smartMod) {
                    if ($smartMod->getModType() === 'Load Meter' && $smartMod->getLevelZone() === 2) $smartMods[] = $smartMod->getId();
                }
                /*
                $Energy = $manager->createQuery("SELECT SUM(CASE 
                                                                WHEN (d.dateTime BETWEEN CONCAT( SUBSTRING(d.dateTime,1,10), :hp1 ) AND CONCAT( SUBSTRING(d.dateTime,1,10), :hp2 ) )
                                                                    OR (d.dateTime BETWEEN CONCAT( SUBSTRING(d.dateTime,1,10), :hp3 ) AND CONCAT( SUBSTRING(d.dateTime,1,10), :hp4 ) ) THEN d.ea
                                                                ELSE 0
                                                            END) AS EAHP, 
                                                    SUM(CASE 
                                                            WHEN (d.dateTime BETWEEN CONCAT( SUBSTRING(d.dateTime,1,10), :p1 ) AND CONCAT( SUBSTRING(d.dateTime,1,10), :p2 ) ) THEN d.ea
                                                            ELSE 0
                                                        END) AS EAP, 
                                                    SUM(CASE 
                                                            WHEN (d.dateTime BETWEEN CONCAT( SUBSTRING(d.dateTime,1,10), :hp1 ) AND CONCAT( SUBSTRING(d.dateTime,1,10), :hp2 ) )
                                                                OR (d.dateTime BETWEEN CONCAT( SUBSTRING(d.dateTime,1,10), :hp3 ) AND CONCAT( SUBSTRING(d.dateTime,1,10), :hp4 ) ) THEN d.er
                                                            ELSE 0
                                                        END) AS ERHP, 
                                                    SUM(CASE 
                                                            WHEN (d.dateTime BETWEEN CONCAT( SUBSTRING(d.dateTime,1,10), :p1 ) AND CONCAT( SUBSTRING(d.dateTime,1,10), :p2 ) ) THEN d.er
                                                            ELSE 0
                                                        END) AS ERP 
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (:smartMods)
                                                    AND d.dateTime BETWEEN :startDate AND :endDate
                                                    AND sm.levelZone = 2
                                                                 
                                                    ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'hp1'   => ' 23:00:00',
                        'hp2'   => ' 23:59:59',
                        'hp3'   => ' 00:00:00',
                        'hp4'   => ' 17:59:59',
                        'p1'   => ' 18:59:59',
                        'p2'   => ' 22:00:00',
                        'smartMods'  => $smartMods,
                        //'zoneId'     => $zone->getId()
                    ))
                    ->getResult();*/
                // dump($Energy);
                //

                // dump($smartMods);
                //WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                //AND sm.levelZone = 2
                //AND sm.modType = 'Load Meter' 

                /*$Duration = $manager->createQuery("SELECT d.dateTime AS dt, 
                                                    CASE 
                                                        WHEN SQRT( (SUM(d.pmoy)*SUM(d.pmoy)) + (SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) <= :Ssous THEN 1
                                                        ELSE 0
                                                    END AS NHU_Psous, 
                                                    CASE 
                                                        WHEN SQRT( (SUM(d.pmoy)*SUM(d.pmoy)) + (SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) > :Ssous THEN 1
                                                        ELSE 0
                                                    END AS NHD_Psous
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (:smartMods)
                                                    AND d.dateTime BETWEEN :startDate AND :endDate
                                                    GROUP BY dt
                                                    ORDER BY dt ASC             
                                                    ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        //'zoneId'     => $zone->getId(),
                        'Ssous'      => $zone->getPowerSubscribed(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();*/
                $Duration = $manager->createQuery("SELECT d.dateTime AS dt, 
                                                    SQRT( (SUM(d.pmoy)*SUM(d.pmoy)) + (SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SUM( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) AS Smoy
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (:smartMods)
                                                    AND d.dateTime BETWEEN :startDate AND :endDate
                                                    GROUP BY dt
                                                    ORDER BY dt ASC             
                                                ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        //'zoneId'     => $zone->getId(),
                        //'Ssous'      => $zone->getPowerSubscribed(),
                        'smartMods'  => $smartMods,
                    ))
                    ->getResult();
                //dump($Duration);
                $NHU_Psous = 0;
                $NHD_Psous = 0;
                /*foreach ($Duration as $d) {
                    $NHU_Psous += intval($d['NHU_Psous']);
                    $NHD_Psous += intval($d['NHD_Psous']);
                }*/
                foreach ($Duration as $d) {
                    if ($d['Smoy'] <= $zone->getPowerSubscribed()) $NHU_Psous++;
                    else if ($d['Smoy'] > $zone->getPowerSubscribed()) $NHD_Psous++;
                }
                // dump($NHU_Psous);
                // dump($NHD_Psous);
                // $NHU_Psous = ($Duration[0]['NHU_Psous'] * 2.0) / 60.0;
                $NHU_Psous = ($NHU_Psous * 2.0) / 60.0;
                $NHU_Psous = number_format((float) $NHU_Psous, 2, '.', '');
                // $NHD_Psous = ($Duration[0]['NHD_Psous'] * 2.0) / 60.0;
                $NHD_Psous = ($NHD_Psous * 2.0) / 60.0;
                $NHD_Psous = number_format((float) $NHD_Psous, 2, '.', '');
                /*$tarifGridHP = $zone->getSite()->getTarification()->getTarifAcGridHP();
                $tarifGridP = $zone->getSite()->getTarification()->getTarifAcGridP();
                $tarifFuelHP = $zone->getSite()->getTarification()->getTarifAcFuelHP();
                $tarifFuelP = $zone->getSite()->getTarification()->getTarifAcFuelP();
                //die();
                foreach ($Energy as $d) {
                    //$dateE[] = $d['dt'];
                    $EAHP  = number_format((float) $d['EAHP'], 2, '.', '');
                    $EAP   = number_format((float) $d['EAP'], 2, '.', '');
                    $ERHP  = number_format((float) $d['ERHP'], 2, '.', '');
                    $ERP   = number_format((float) $d['ERP'], 2, '.', '');
                    $EATotal = floatval($d['EAHP']) + floatval($d['EAP']);
                    $ERTotal = floatval($d['ERHP']) + floatval($d['ERP']);
                    $FP = ($EATotal * 1.0) / sqrt(($EATotal * $EATotal) + ($ERTotal * $ERTotal));
                    $FP = number_format((float) $FP, 2, '.', '');
                    $amountEAHP = floatval($d['EAHP']) * (((($NHU_Grid * 1.0) / $nbHours) * $tarifGridHP) + ((($NHU_FUEL * 1.0) / $nbHours) * $tarifFuelHP));
                    $amountEAP = floatval($d['EAP']) * (((($NHU_Grid * 1.0) / $nbHours) * $tarifGridP) + ((($NHU_FUEL * 1.0) / $nbHours) * $tarifFuelP));
                    $amountEA = $amountEAHP + $amountEAP;
                    $amountEAHP = number_format((float) $amountEAHP, 2, '.', '');
                    $amountEAP = number_format((float) $amountEAP, 2, '.', '');
                    $amountEA = number_format((float) $amountEA, 2, '.', '');
                    $EATotal = number_format((float) $EATotal, 2, '.', '');
                }*/

                $PowerMax = $manager->createQuery("SELECT d.dateTime AS jour, SUM( SQRT( (d.pmoy*d.pmoy) + (SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) ) AS Smoy
                                                FROM App\Entity\LoadDataEnergy d
                                                JOIN d.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND d.dateTime BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2
                                                GROUP BY jour    
                                                ORDER BY Smoy DESC                                             
                                                ")
                    ->setParameters(array(
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'zoneId'     => $zone->getId(),
                        //'smartModId'   => $smartMod->getId()
                    ))
                    ->getResult();
                // dump($PowerMax[0]);
                $Smax = 0;
                if (count($PowerMax)) {
                    if (count($PowerMax[0])) $Smax = number_format((float) $PowerMax[0]['Smoy'], 2, '.', '');
                }
                /*$Energy = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 10) AS jour, SUM(d.kWh) AS kWh, SUM(d.kVarh) AS kVarh
                                                FROM App\Entity\DataMod d, App\Entity\SmartMod sm WHERE d.dateTime LIKE :selDate
                                                AND sm.id = :modId
                                                GROUP BY jour
                                                ORDER BY jour ASC
                                                                                        
                                                ")
                        ->setParameters(array(
                            'selDate' => $dateparam,
                            'modId'   => $id
                        ))
                        ->getResult();*/
                // //dump($data);
                /*foreach ($data as $d) {
                        $date[]    = $d['dat']->format('Y-m-d H:i:s');
                        $VA[]      = $d['va'];
                        $VB[]      = $d['vb'];
                        $VC[]      = $d['vc'];
                        $SA[]      = $d['sa'];
                        $SB[]      = $d['sb'];
                        $SC[]      = $d['sc'];
                        $S3ph[]    = $d['s3ph'];
                        // $Id[]      = $d['idmoy'];
                        // $Io[]      = $d['iomoy'];
                        // $Vd[]      = $d['vdmoy'];
                        // $Vo[]      = $d['vomoy'];
                        // $THDiA[]   = $d['thdia'];
                        // $THDiB[]   = $d['thdib'];
                        // $THDiC[]   = $d['thdic'];
                        // $THDi3ph[] = $d['thdi3ph'];
                        // $idc[]     = number_format((float) $d['IDC'], 2, '.', '');
                        // $idd[]     = number_format((float) $d['IDD'], 2, '.', '');
                        //$kWh[]   = $d['kWh'];
                        //$kVarh[] = $d['kVarh'];
                    }*/

                /*foreach ($idc as $i) {
                        $idcRef[] = 20;
                    }
                    foreach ($idd as $i) {
                        $iddRef[] = 20;
                    }*/

                /*foreach ($Energy as $d) {
                        $dateE[] = $d['jour'];
                        $kWh[]   = $d['kWh'];
                        $kVarh[] = $d['kVarh'];
                    }*/

                // //dump($Energy);
                //die();

                return $this->json([
                    'code'    => 200,
                    //'EAHP'  => $EAHP,
                    //'EAP'  => $EAP,
                    //'EATotal'  => $EATotal,
                    //'ERHP'  => $ERHP,
                    //'ERP'  => $ERP,
                    //'FP'  => $FP,
                    //'amountEAHP'  => $amountEAHP,
                    //'amountEAP'  => $amountEAP,
                    //'amountEA'  => $amountEA,
                    //'NHU_Grid'  => $NHU_Grid,
                    //'NHU_FUEL'  => $NHU_FUEL,
                    'Smax' => $Smax,
                    'NHU_Psous'  => $NHU_Psous,
                    'NHD_Psous'  => $NHD_Psous
                ], 200);
            }
        }

        return $this->json([
            'code'         => 200,
        ], 500);
    }
}
