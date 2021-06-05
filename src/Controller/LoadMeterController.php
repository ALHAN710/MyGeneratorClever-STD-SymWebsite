<?php

namespace App\Controller;

use Faker;
use DateTime;
use App\Entity\Zone;
use App\Entity\SmartMod;
use App\Entity\LoadDataEnergy;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoadMeterController extends ApplicationController
{
    /**
     * @Route("/load/meter/{smartMod<\d+>}/{zone<\d+>}", name="load_meter")
     */
    public function index(SmartMod $smartMod, Zone $zone, EntityManagerInterface $manager): Response
    {
        //dump($id);

        return $this->render('load_meter/index.html.twig', [
            'zone' => $zone,
            'smartMod' => $smartMod,
        ]);
    }

    /**
     * Permet de surcharger les données LoadDataEnergy des modules load dans la BDD
     *
     * @Route("/load-data-energy/mod/{modId<[a-zA-Z0-9]+>}/add", name="loadDataEnergy_add") 
     * 
     * @param SmartMod $smartMod
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return void
     */
    public function loadDataEnergy_add($modId, EntityManagerInterface $manager, Request $request)
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());
        //dump($paramJSON);
        //dump($content);
        //die();

        $datetimeData = new LoadDataEnergy();

        //Recherche du module dans la BDD
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);


        if ($smartMod != null) { // Test si le module existe dans notre BDD
            //data:{"date": "2020-03-20 12:15:00", "sa": 1.2, "sb": 0.7, "sc": 0.85, "va": 225, "vb": 230, "vc": 231, "s3ph": 2.75, "kWh": 1.02, "kvar": 0.4}
            //dump($smartMod);//Affiche le module
            //die();

            //$date = new DateTime($paramJSON['date']);

            //Récupération de la date dans la requête et transformation en object de type Date au format date SQL
            $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
            //dump($date);
            //die();

            if ($smartMod->getModType() == 'Load') {
                //Paramétrage des champs de la nouvelle LoadDataEnergy aux valeurs contenues dans la requête du module
                $datetimeData->setDateTime($date)
                    ->setVamoy($paramJSON['Va'])
                    ->setVbmoy($paramJSON['Vb'])
                    ->setVcmoy($paramJSON['Vc'])
                    ->setPamoy($paramJSON['Pa'])
                    ->setPbmoy($paramJSON['Pb'])
                    ->setPcmoy($paramJSON['Pc'])
                    ->setPmoy($paramJSON['P'])
                    ->setSamoy($paramJSON['Sa'])
                    ->setSbmoy($paramJSON['Sb'])
                    ->setScmoy($paramJSON['Sc'])
                    ->setSmoy($paramJSON['S'])
                    ->setCosfia($paramJSON['Cosfia'])
                    ->setCosfib($paramJSON['Cosfib'])
                    ->setCosfic($paramJSON['Cosfic'])
                    ->setCosfi($paramJSON['Cosfi'])
                    ->setEaa($paramJSON['Eaa'])
                    ->setEab($paramJSON['Eab'])
                    ->setEac($paramJSON['Eac'])
                    ->setEa($paramJSON['Ea'])
                    ->setEra($paramJSON['Era'])
                    ->setErb($paramJSON['Erb'])
                    ->setErc($paramJSON['Erc'])
                    ->setEr($paramJSON['Er'])
                    ->setSmartMod($smartMod);
            }

            //dump($datetimeData);
            //die();
            //Insertion de la nouvelle datetimeData dans la BDD
            $manager->persist($datetimeData);
            $manager->flush();

            return $this->json([
                'code' => 200,
                'received' => $paramJSON

            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => "SmartMod don't exist",
            'received' => $paramJSON

        ], 403);
    }

    /**
     * Permet de mettre à jour les graphes liés aux données d'un module load Meter
     *
     * @Route("/update/load-meter/mod/graphs/", name="update_load_meter_graphs")
     * 
     * @param [SmartMod] $smartMod
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
        $VAmoy          = [];
        $VBmoy          = [];
        $VCmoy          = [];
        $PAmoy          = [];
        $PBmoy          = [];
        $PCmoy          = [];
        $Pmoy        = [];
        $SA          = [];
        $SB          = [];
        $SC          = [];
        $Smoy        = [];
        $CosfiA          = [];
        $CosfiB          = [];
        $CosfiC          = [];
        $Cosfimoy        = [];
        $EAA          = [];
        $EAB          = [];
        $EAC          = [];
        $EA        = [];
        $ERA          = [];
        $ERB          = [];
        $ERC          = [];
        $ER        = [];
        $FP        = [];
        // $Vo          = [];
        // $Vd          = [];
        // $Io          = [];
        // $Id          = [];
        // $THDiA       = [];
        // $THDiB       = [];
        // $THDiC       = [];
        // $THDi3ph     = [];
        $dateE       = [];
        // $idc         = [];
        // $idd         = [];
        // $idcRef      = [];
        // $iddRef      = [];

        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$type = $paramJSON['type'];
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['id' => $paramJSON['id']]);
        //$zone = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['zoneId']]);
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


        if ($smartMod) {
            //SUM( SQRT( (d.pmoy*d.pmoy) + (SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) )*SQRT( (d.smoy*d.smoy) - (d.pmoy*d.pmoy) ) ) ) ) AS kVA,
            $Energy = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,10) AS dt, SUM(d.ea) AS kWh, SUM(d.er) AS kVAR
                                            FROM App\Entity\LoadDataEnergy d
                                            JOIN d.smartMod sm 
                                            WHERE sm.id = :smartModId
                                            AND d.dateTime BETWEEN :startDate AND :endDate
                                            GROUP BY dt
                                            ORDER BY dt ASC                                                                                                                                                
                                            ")
                ->setParameters(array(
                    //'selDate'      => $dat,
                    'startDate'  => $startDate->format('Y-m-d H:i:s'),
                    'endDate'    => $endDate->format('Y-m-d H:i:s'),
                    'smartModId'     => $smartMod->getId()
                ))
                ->getResult();
            dump($Energy);

            //die();
            foreach ($Energy as $d) {
                $dateE[] = $d['dt'];
                $EA[]   = number_format((float) $d['kWh'], 2, '.', '');
                $ER[] = number_format((float) $d['kVAR'], 2, '.', '');
            }

            if ($smartMod->getNbPhases() === 1) {
                $data = $manager->createQuery("SELECT d.dateTime AS dt, d.smoy AS kVA, d.pmoy AS kW, d.vamoy AS Volt, d.cosfi AS Cosfi
                                            FROM App\Entity\LoadDataEnergy d
                                            JOIN d.smartMod sm 
                                            WHERE sm.id = :smartModId
                                            AND d.dateTime BETWEEN :startDate AND :endDate
                                            GROUP BY dt
                                            ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'smartModId'     => $smartMod->getId()
                    ))
                    ->getResult();
                dump($data);
                //die();
                foreach ($data as $d) {
                    $date[] = $d['dt']->format('Y-m-d H:i:s');
                    //$EA[]   = number_format((float) $d['kWh'], 2, '.', '');
                    $Smoy[] = number_format((float) $d['kVA'], 2, '.', '');
                    $Pmoy[] = number_format((float) $d['kW'], 2, '.', '');
                    $VAmoy[] = number_format((float) $d['Volt'], 2, '.', '');
                    $Cosfimoy[] = number_format((float) $d['Cosfi'], 2, '.', '');
                    //$FP[] = number_format((float) $d['PF'], 2, '.', '');
                }
            } else if ($smartMod->getNbPhases() === 3) {
                $data = $manager->createQuery("SELECT d.dateTime AS dt, d.pamoy AS PA, d.pbmoy AS PB, d.pcmoy AS PC, d.vamoy AS VA,d.vbmoy AS VB, d.vcmoy AS VC, 
                                            d.cosfia AS CosfiA, d.cosfib AS CosfiB, d.cosfic AS CosfiC
                                            FROM App\Entity\LoadDataEnergy d
                                            JOIN d.smartMod sm 
                                            WHERE sm.id = :smartModId
                                            AND d.dateTime BETWEEN :startDate AND :endDate
                                            GROUP BY dt
                                            ORDER BY dt ASC                                                                                                                                                
                                            ")
                    ->setParameters(array(
                        //'selDate'      => $dat,
                        'startDate'  => $startDate->format('Y-m-d H:i:s'),
                        'endDate'    => $endDate->format('Y-m-d H:i:s'),
                        'smartModId'     => $smartMod->getId()
                    ))
                    ->getResult();
                dump($data);
                //die();
                foreach ($data as $d) {
                    $date[] = $d['dt']->format('Y-m-d H:i:s');
                    //$EA[]   = number_format((float) $d['kWh'], 2, '.', '');
                    //$Smoy[] = number_format((float) $d['kVA'], 2, '.', '');
                    $PAmoy[] = number_format((float) $d['PA'], 2, '.', '');
                    $PBmoy[] = number_format((float) $d['PB'], 2, '.', '');
                    $PCmoy[] = number_format((float) $d['PC'], 2, '.', '');
                    $VAmoy[] = number_format((float) $d['VA'], 2, '.', '');
                    $VBmoy[] = number_format((float) $d['VB'], 2, '.', '');
                    $VCmoy[] = number_format((float) $d['VC'], 2, '.', '');
                    $CosfiA[] = number_format((float) $d['CosfiA'], 2, '.', '');
                    $CosfiB[] = number_format((float) $d['CosfiB'], 2, '.', '');
                    $CosfiC[] = number_format((float) $d['CosfiC'], 2, '.', '');

                    //$FP[] = number_format((float) $d['PF'], 2, '.', '');
                }
            }
            return $this->json([
                'code'    => 200,
                'date'    => $date,
                'Voltage'      => $VAmoy,
                'MixedPSCos'   => [$Pmoy, $Smoy, $Cosfimoy],
                'dateE'   => $dateE,
                'MixedEnergy'     => [$EA, $ER],
                'MixedActivePower'    => [$PAmoy, $PBmoy, $PCmoy],
                'MixedCosfi'    => [$CosfiA, $CosfiB, $CosfiC],
                'MixedVoltage'    => [$VAmoy, $VBmoy, $VCmoy],

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

    /**
     * Permet de générer et surcharger les fausses données DatetimeData des modules Load dans la BDD
     *
     * @Route("/fixtures/datetimedata/{id}/add", name="fixtures_datetimeData_add") 
     * 
     * @return void
     */
    public function fixDatetimeData_add($id, EntityManagerInterface $manager)
    {
        $faker = Faker\Factory::create('fr_FR');

        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $id]);
        //dd($smartMod);
        // Génération de fausses données pour chaque module sur une année
        $Year = 2021;
        $month = 3;
        $day = 1;
        $nbDay = 10;
        $nbYear = 1;

        $date_array = [];
        /*$date = new DateTime($Year . '-' . $month . '-' . $day . ' 00:00:00');
        $date = new DateTime('2020-02-01 00:00:00');
        $date->format('Y-m-d H:i:s');
        $dat = new DateTime();
        $date_array = [];
        */

        //$date_array = $dateTimeGenerator->getArrayDateTime();
        for ($i = 0; $i < 3; $i++) {
            $months = $month + $i;
            for ($j = 1; $j <= $nbDay; $j++) {
                for ($h = 0; $h < 24; $h++) {
                    for ($m = 0; $m < 60; $m += 15) { //'P0DT0H15M0S'
                        $date = new DateTime($Year . '-' . $months . '-' . $j . ' ' . $h . ':' . $m . ':00');
                        $date->format('Y-m-d H:i:s');

                        $date_array[] = $date;
                    }
                }
            }
        }

        foreach ($date_array as $dat) {

            $dataMod = new LoadDataEnergy();
            /*$date = new DateTime($faker->unique()
                                ->dateTimeBetween($startDate = '2020-02-01 00:00:00', $endDate = '2020-02-11 23:59:59', $timezone = 'Africa/Douala')
                                ->format('Y-m-d H:i:s'));
                $date = new DateTime(
                    $faker->dateTimeInInterval($startDate = '-1 month', $interval = '+ 1 days', $timezone = 'Africa/Douala')
                        ->format('Y-m-d H:i:s')
                );*/
            /*$date = $dat;
                            $date->add(new DateInterval('PT15M0S'))
                                ->format('Y-m-d H:i:s');*/
            //$dat = new DateTime($faker->unique()->randomElement($date_array)->format('Y-m-d H:i:s'));
            //$dat = new DateTime($faker->randomElement($date_array)->format('Y-m-d H:i:s'));

            // $va = $faker->randomFloat($nbMaxDecimals = 2, $min = 190, $max = 240);
            // $vb = $faker->randomFloat($nbMaxDecimals = 2, $min = 200, $max = 240);
            // $vc = $faker->randomFloat($nbMaxDecimals = 2, $min = 180, $max = 240);

            // $pa = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $pb = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $pc = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            //$pmoy = $pa + $pb + $pc;
            $pmoy = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 4);

            // $sa = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $sb = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $sc = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $smoy = $sa + $sb + $sc;
            $smoy = $pmoy == 0 ? $pmoy : $faker->randomFloat($nbMaxDecimals = 2, $min = $pmoy + 1.2, $max = $pmoy + 1.5);

            // $cosfia = $faker->randomFloat($nbMaxDecimals = 2, $min = 0.7, $max = 0.88);
            // $cosfib = $faker->randomFloat($nbMaxDecimals = 2, $min = 0.7, $max = 0.88);
            // $cosfic = $faker->randomFloat($nbMaxDecimals = 2, $min = 0.7, $max = 0.88);
            $cosfi = $faker->randomFloat($nbMaxDecimals = 2, $min = 0.7, $max = 0.88);

            // $eaa = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $eab = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $eac = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            $ea = $pmoy == 0 ? $pmoy : $faker->randomFloat($nbMaxDecimals = 2, $min = $pmoy, $max = $pmoy + 0.3);

            // $era = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $erb = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            // $erc = $faker->randomFloat($nbMaxDecimals = 2, $min = 0, $max = 2);
            $er = $ea == 0 ? $ea : $faker->randomFloat($nbMaxDecimals = 2, $min = abs($ea - 0.8), $max = abs($ea - 0.2));

            $dataMod->setDateTime($dat)
                // ->setVamoy($va)
                // ->setVbmoy($vb)
                // ->setVcmoy($vc)
                // ->setSamoy($sa)
                // ->setSbmoy($sb)
                // ->setScmoy($sc)
                ->setSmoy($smoy)
                ->setSmartMod($smartMod)
                // ->setPamoy($pa)
                // ->setPbmoy($pb)
                // ->setPcmoy($pc)
                ->setPmoy($pmoy)
                // ->setCosfia($cosfia)
                // ->setCosfib($cosfib)
                // ->setCosfic($cosfic)
                ->setCosfi($cosfi)
                // ->setEaa($eaa)
                // ->setEab($eab)
                // ->setEac($eac)
                ->setEa($ea)
                // ->setEra($era)
                // ->setErb($erb)
                // ->setErc($erc)
                ->setEr($er);

            $manager->persist($dataMod);
        }

        $manager->flush();
        return $this->json([
            'code' => 200,
        ], 200);
    }
}
