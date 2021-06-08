<?php

namespace App\Controller;

use DateTime;
use App\Entity\Zone;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ZoneController extends ApplicationController
{
    /**
     * @Route("/zone/{zone<\d+>?}", name="home_zone")
     */
    public function index(Zone $zone, EntityManagerInterface $manager): Response
    {
        $smartMods = $manager->createQuery("SELECT sm.id AS Id
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
        }
        //dump($smartModsProduction);
        return $this->render('zone/index.html.twig', [
            'zone' => $zone,
            'smartModsProduction' => $smartModsProduction,
            'alarms'    => $manager->getRepository('App:Alarm')->findBy(['type' => 'Load Meter']),
        ]);
    }

    /**
     * Permet de mettre à jour les graphes liés aux données d'un module load Meter
     *
     * @Route("/update/zone/mod/graphs/", name="update_zone_graphs")
     * 
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateGraph(EntityManagerInterface $manager, Request $request): Response
    {
        //$smartModRepo = $this->getDoctrine()->getRepository(SmartModRepository::class);
        //$smartMod = $smartModRepo->find($id);
        //dump($smartModRepo);
        //dump($smartMod->getModType());
        //$temps = DateTime::createFromFormat("d-m-Y H:i:s", "120");
        //dump($temps);
        //die();
        $date        = [];
        $EA_flow   = [];
        $ER_flow = [];
        $Smax = [];
        $FP_flow = [];
        $IntervalPUE = 0;
        $InstantPUE = 0;
        $pue = [];
        $diffEnergy = [];
        $dateE       = [];


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
        dump($startDate->format('Y-m-d H:i:s'));
        dump($endDate->format('Y-m-d H:i:s'));
        //$dat = "2020-02"; //'%' . $dat . '%'
        //$dat = substr($dateparam, 0, 8); // Ex : %2020-03
        //dump($dat);
        //die();
        //$dat = $dat . '%';

        $dateparam = $request->get('selectedDate'); // Ex : %2020-03-20%
        //$dat = "2020-02"; //'%' . $dat . '%'
        $dat = substr($dateparam, 0, 8); // Ex : %2020-03
        //dump($dat);
        //die();
        $dat = $dat . '%';

        if ($zone) {
            //SUM( SQRT( (d.pmoy*d.pmoy) + (SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) ) AS kVA,
            $commonData = $manager->createQuery("SELECT SUM(d.ea) AS kWh, SUM(d.er) AS kVAR, sm.id AS ID,
                                            SUM(d.ea)/SQRT( (SUM(d.ea)*SUM(d.ea)) + (SUM(d.er)*SUM(d.er)) ) AS PF, MAX(d.smoy) AS Smax
                                            FROM App\Entity\LoadDataEnergy d
                                            JOIN d.smartMod sm 
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

            dump($commonData);

            //die();
            foreach ($commonData as $d) {
                //$dateE[] = $d['dt']->format('Y-m-d H:i:s');
                $EA_flow[$d['ID']]   = floatval(number_format((float) $d['kWh'], 2, '.', ''));
                $ER_flow[$d['ID']] = floatval(number_format((float) $d['kVAR'], 2, '.', ''));
                $Smax['' . $d['ID']] = number_format((float) $d['Smax'], 2, '.', '');
                $FP_flow['' . $d['ID']] = number_format((float) $d['PF'], 2, '.', '');
            }

            if ($zone->getType() === 'PUE Calculation') {
                $InstantProductionEnergy = $manager->createQuery("SELECT SUM(d.ea) AS kW
                                                            FROM App\Entity\LoadDataEnergy d
                                                            JOIN d.smartMod sm 
                                                            WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                            AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\LoadDataEnergy d1 JOIN d1.smartMod sm1 JOIN sm1.zones zn1 WHERE zn1.id = :zoneId)
                                                            AND sm.levelZone = 2
                                                            AND sm.subType = 'Production'                                                                                                                                                
                                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                dump($InstantProductionEnergy);

                $InstantTotalEnergy = $manager->createQuery("SELECT SUM(d.ea) AS kW
                                                    FROM App\Entity\LoadDataEnergy d
                                                    JOIN d.smartMod sm 
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                    AND d.dateTime = (SELECT MAX(d1.dateTime) FROM App\Entity\LoadDataEnergy d1 JOIN d1.smartMod sm1 JOIN sm1.zones zn1 WHERE zn1.id = :zoneId)
                                                    AND sm.levelZone = 2                                                                                                                                               
                                                    ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                dump($InstantTotalEnergy);
                $InstantPUE = 0;
                if (count($InstantTotalEnergy) && count($InstantProductionEnergy)) {
                    $InstantPUE = $InstantProductionEnergy[0]['kW'] > 0 ? ($InstantTotalEnergy[0]['kW'] * 1.0) / $InstantProductionEnergy[0]['kW'] : 0;
                    $InstantPUE = number_format((float) $InstantPUE, 2, '.', '');
                }
                dump('InstantPUE = ' . $InstantPUE);

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
                dump($IntervalTotalEnergy);

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
                dump($IntervalProductionEnergy);

                $IntervalPUE = 0;
                if (count($IntervalProductionEnergy) && count($IntervalTotalEnergy)) {
                    $IntervalPUE = $IntervalProductionEnergy[0]['kWh'] > 0 ? ($IntervalTotalEnergy[0]['kWh'] * 1.0) / $IntervalProductionEnergy[0]['kWh'] : 0;
                    $IntervalPUE = number_format((float) $IntervalPUE, 2, '.', '');
                }
                dump('IntervalPUE = ' . $IntervalPUE);

                if ($startDate->format('Y-m-d') !== $endDate->format('Y-m-d')) {
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
                    $dataProductionEnergy = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.ea) AS kWh
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

                    $dataTotalEnergy = $manager->createQuery("SELECT d.dateTime AS dt, SUM(d.ea) AS kWh
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
                }
                dump($dataProductionEnergy);
                //die();
                foreach ($dataProductionEnergy as $d) {
                    $dateE[] = $d['dt'];
                    //$dateE[] = DateTime::createFromFormat('Y-m-d H:i:s', $d['dt']);
                    $productionEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                }
                dump($dataTotalEnergy);
                //die();
                foreach ($dataTotalEnergy as $d) {
                    //$dateE[] = $d['dt'];
                    $totalEA[]   = number_format((float) $d['kWh'], 2, '.', '');
                }

                $pue =  array_map(function ($a, $b) {
                    return $b > 0 ? round($a / $b, 2) : 0;
                }, $totalEA, $productionEA);
                dump($pue);

                $diffEnergy =  array_map(function ($a, $b) {
                    return number_format((float) ($a - $b), 2, '.', '');
                }, $totalEA, $productionEA);
                dump($diffEnergy);
            }

            return $this->json([
                'code'    => 200,
                'date'    => $dateE,
                'InstantPUE' => $InstantPUE,
                'IntervalPUE' => $IntervalPUE,
                'PieActiveEnergy'      => $EA_flow,
                'PieReactiveEnergy'   => $ER_flow,
                'PUE'   => $pue,
                'MixedEnergy'     => [$totalEA, $productionEA, $diffEnergy],
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
        dump($zone);
        dump($startDate->format('Y-m-d H:i:s'));
        dump($endDate->format('Y-m-d H:i:s'));

        $interval = $endDate->diff($startDate);
        $nbDay = 0;
        $amountEAHP = 0;
        $amountEAP = 0;
        $amountEA = 0;
        //$interval = $this->periodofvalidity->diff($nowDate);
        if ($interval) {
            //return gettype($interval->format('d'));
            //return $interval->format('%R%a days'); // '+29 days'
            $nbDay = $interval->days; //Nombre de jour total de différence entre les dates
            dump($nbDay);
            //return !$interval->invert; // 
            //return $this->isActivated;
        }
        $nbHours = 24 * $nbDay;
        dump($nbHours);
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
                $GensetParams = $manager->createQuery("SELECT MAX(d.totalRunningHours) - MIN(d.totalRunningHours) AS TRH, 
                                        MAX(d.totalEnergy) - MIN(d.totalEnergy) AS TEP
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
                dump($GensetParams);
                $NHU_FUEL = $GensetParams[0]['TRH'] ?? 0;
                $NHU_Grid = $nbHours - $NHU_FUEL;
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
                                                    WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
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
                        'zoneId'     => $zone->getId()
                    ))
                    ->getResult();
                dump($Energy);
                $Duration = $manager->createQuery("SELECT SUM(CASE 
                                                            WHEN d.smoy <= :Ssous THEN 1
                                                            ELSE 0
                                                            END) AS NHU_Psous, 
                                                    SUM(CASE 
                                                        WHEN d.smoy > :Ssous THEN 1
                                                        ELSE 0
                                                        END) AS NHD_Psous
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
                        'zoneId'     => $zone->getId(),
                        'Ssous'      => $zone->getPowerSubscribed(),
                    ))
                    ->getResult();
                dump($Duration);
                $NHU_Psous = ($Duration[0]['NHU_Psous'] * 10.0) / 60.0;
                $NHU_Psous = number_format((float) $NHU_Psous, 2, '.', '');
                $NHD_Psous = ($Duration[0]['NHD_Psous'] * 10.0) / 60.0;
                $NHD_Psous = number_format((float) $NHD_Psous, 2, '.', '');

                $tarifGridHP = $zone->getSite()->getTarification()->getTarifAcGridHP();
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
                    $EATotal = $d['EAHP'] + $d['EAP'];
                    $ERTotal = $d['ERHP'] + $d['ERP'];
                    $FP = ($EATotal * 1.0) / sqrt(($EATotal * $EATotal) + ($ERTotal * $ERTotal));
                    $FP = number_format((float) $FP, 2, '.', '');
                    $amountEAHP = $d['EAHP'] * ((($NHU_Grid * 1.0) / $nbHours) * $tarifGridHP + (($NHU_FUEL * 1.0) / $nbHours) * $tarifFuelHP);
                    $amountEAP = $d['EAP'] * ((($NHU_Grid * 1.0) / $nbHours) * $tarifGridP + (($NHU_FUEL * 1.0) / $nbHours) * $tarifFuelP);
                    $amountEA = $amountEAHP + $amountEAP;
                    $amountEAHP = number_format((float) $amountEAHP, 2, '.', '');
                    $amountEAP = number_format((float) $amountEAP, 2, '.', '');
                    $amountEA = number_format((float) $amountEA, 2, '.', '');
                    $EATotal = number_format((float) $EATotal, 2, '.', '');
                }

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
                dump($PowerMax[0]);
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
                //dump($data);
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

                //dump($Energy);
                //die();

                return $this->json([
                    'code'    => 200,
                    'EAHP'  => $EAHP,
                    'EAP'  => $EAP,
                    'EATotal'  => $EATotal,
                    'ERHP'  => $ERHP,
                    'ERP'  => $ERP,
                    'FP'  => $FP,
                    'amountEAHP'  => $amountEAHP,
                    'amountEAP'  => $amountEAP,
                    'amountEA'  => $amountEA,
                    'NHU_Grid'  => $NHU_Grid,
                    'NHU_FUEL'  => $NHU_FUEL,
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
