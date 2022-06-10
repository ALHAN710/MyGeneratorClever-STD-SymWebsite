<?php

namespace App\Controller;

use Faker;
use DateTime;
use DateInterval;
use App\Entity\SmartMod;
use App\Entity\DatetimeData;
use App\Entity\AlarmReporting;
use App\Entity\NoDatetimeData;
use App\Message\UserNotificationMessage;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GensetController extends ApplicationController
{ //
    /**
     * @Route("/genset/{id}", name="genset_home")
     * 
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_NOC_SUPERVISOR') and id.getSite().getEnterprise() === user.getEnterprise() )" )
     * 
     */
    public function index(SmartMod $id): Response
    {

        return $this->render('genset/home.html.twig', [
            'smartMod' => $id,
        ]);
    }

    /**
     * Permet de mettre à jour l'affichage des données temps réel d'un module genset
     *
     * @Route("/update/genset/mod/{id<\d+>}/display/",name="update_genset_display_data")
     * 
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_NOC_SUPERVISOR') and id.getSite().getEnterprise() === user.getEnterprise() )" )
     * 
     * @param [interger] $id
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateDisplayNoDatetimeData(SmartMod $id, EntityManagerInterface $manager, Request $request): Response
    {
        /*SELECT * 
            FROM `datetime_data` 
            WHERE `id` = (SELECT max(`id`) FROM `datetime_data` WHERE `date_time` LIKE '2021-05-21%')*/

        // //dump($date);
        $date = [];
        $S = [];
        $P = [];
        $Cosfi = [];

        /*$lastRecord = $manager->createQuery("SELECT d.p AS P, d.q AS Q, d.s AS S, d.cosfi AS Cosfi, d.totalRunningHours AS TRH,
                                        d.totalEnergy AS TEP, d.fuelInstConsumption AS FC, d.dateTime
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/

        $data = $manager->createQuery("SELECT d.dateTime as dat, d.p, (d.s*100.0)/:genpower as s, d.cosfi
                                        FROM App\Entity\DatetimeData d 
                                        JOIN d.smartMod sm
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId
                                        ORDER BY dat ASC
                                        
                                        ")
            ->setParameters(array(
                //'selDate'      => $dateparam,
                'nowDate'     => date("Y-m-d") . "%",
                'genpower'  => $id->getPower(),
                'smartModId'  => $id->getId()
            ))
            ->getResult();


        // dump($data);
        foreach ($data as $d) {
            $date[]    = $d['dat']->format('Y-m-d H:i:s');
            //$P[]       = number_format((float) $d['p'], 2, '.', '');
            $S[]    = number_format((float) $d['s'], 2, '.', '');
            //$Cosfi[]   = number_format((float) $d['cosfi'], 2, '.', '');
        }

        $NMIDay = $manager->createQuery("SELECT SUM(d.nbMainsInterruption) AS NMID
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // //dump($NMIDay);
        $NMIMonth = $manager->createQuery("SELECT SUM(d.nbMainsInterruption) AS NMIM
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // //dump($NMIMonth);
        $NMIYear = $manager->createQuery("SELECT SUM(d.nbMainsInterruption) AS NMIY
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // //dump($NMIYear);

        /*$firstDatetimeDataDayRecord = $manager->createQuery("SELECT d.totalRunningHours AS TRH, d.totalEnergy AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT min(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        /*+$firstDatetimeDataDayRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        // // dump($firstDatetimeDataDayRecord);
        /*$lastDatetimeDataDayRecord = $manager->createQuery("SELECT d.totalRunningHours AS TRH, d.totalEnergy AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        /*+$lastDatetimeDataDayRecord = $manager->createQuery("SELECT MAX(d.totalRunningHours) AS TRH, MAX(d.totalEnergy) AS TEP,
                                        MAX(d.nbPerformedStartUps) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        // // dump($lastDatetimeDataDayRecord);

        $diffDatadayRecordQuery = $manager->createQuery("SELECT MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MAX(NULLIF(d.nbPerformedStartUps,0)) - MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId  
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m-d") . "%",
                // 'nowDate'      => "2022-02-06%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // dump($diffDatadayRecordQuery);
        $npsd = 0;
        $trhd = 0;
        $tepd = 0;
        // if (count($firstDatetimeDataDayRecord) && count($lastDatetimeDataDayRecord)) {
        if (count($diffDatadayRecordQuery) > 0) {
            $npsd = intval($diffDatadayRecordQuery[0]['NPS'] ?? 0);
            $trhd = intval($diffDatadayRecordQuery[0]['TRH'] ?? 0);
            $tepd = intval($diffDatadayRecordQuery[0]['TEP'] ?? 0);
            // $npsd = intval($lastDatetimeDataDayRecord[0]['NPS']) - intval($firstDatetimeDataDayRecord[0]['NPS']);
            // $trhd = intval($lastDatetimeDataDayRecord[0]['TRH']) - intval($firstDatetimeDataDayRecord[0]['TRH']);
            // $tepd = intval($lastDatetimeDataDayRecord[0]['TEP']) - intval($firstDatetimeDataDayRecord[0]['TEP']);
            // dump($npsd);
            // dump($trhd);
            // dump($tepd);
        }

        /*$firstDatetimeDataMonthRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        // // dump($firstDatetimeDataMonthRecord);

        /*$lastDatetimeDataMonthRecord = $manager->createQuery("SELECT d.totalRunningHours AS TRH, d.totalEnergy AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        /*$lastDatetimeDataMonthRecord = $manager->createQuery("SELECT MAX(d.totalRunningHours) AS TRH, MAX(d.totalEnergy) AS TEP,
                                        MAX(d.nbPerformedStartUps) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/

        // // dump($lastDatetimeDataMonthRecord);

        $diffDataMonthRecordQuery = $manager->createQuery("SELECT MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MAX(NULLIF(d.nbPerformedStartUps,0)) - MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId  
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // dump($diffDataMonthRecordQuery);
        $npsm = 0;
        $trhm = 0;
        $tepm = 0;
        if (count($diffDataMonthRecordQuery) > 0) {
            // if (count($firstDatetimeDataMonthRecord) && count($lastDatetimeDataMonthRecord)) {
            $npsm = intval($diffDataMonthRecordQuery[0]['NPS'] ?? 0);
            $trhm = intval($diffDataMonthRecordQuery[0]['TRH'] ?? 0);
            $tepm = intval($diffDataMonthRecordQuery[0]['TEP'] ?? 0);
            // $npsm = intval($lastDatetimeDataMonthRecord[0]['NPS']) - intval($firstDatetimeDataMonthRecord[0]['NPS']);
            // $trhm = intval($lastDatetimeDataMonthRecord[0]['TRH']) - intval($firstDatetimeDataMonthRecord[0]['TRH']);
            // $tepm = intval($lastDatetimeDataMonthRecord[0]['TEP']) - intval($firstDatetimeDataMonthRecord[0]['TEP']);
            // dump($npsm);
            // dump($trhm);
            // dump($tepm);
        }

        /*$firstDatetimeDataYearRecord = $manager->createQuery("SELECT d.totalRunningHours AS TRH, d.totalEnergy AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT min(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        /*+$firstDatetimeDataYearRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/

        // //dump($firstDatetimeDataYearRecord);
        /*$lastDatetimeDataYearRecord = $manager->createQuery("SELECT d.totalRunningHours AS TRH, d.totalEnergy AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        /*+$lastDatetimeDataYearRecord = $manager->createQuery("SELECT MAX(d.totalRunningHours) AS TRH, MAX(d.totalEnergy) AS TEP,
                                        MAX(d.nbPerformedStartUps) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/
        // //dump($lastDatetimeDataYearRecord);

        $diffDataYearRecordQuery = $manager->createQuery("SELECT MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MAX(NULLIF(d.nbPerformedStartUps,0)) - MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId  
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        ")
            ->setParameters(array(
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // dump($diffDataYearRecordQuery);

        $npsy = 0;
        $trhy = 0;
        $tepy = 0;
        if (count($diffDataYearRecordQuery) > 0) {
            $npsy = intval($diffDataYearRecordQuery[0]['NPS'] ?? 0);
            $trhy = intval($diffDataYearRecordQuery[0]['TRH'] ?? 0);
            $tepy = intval($diffDataYearRecordQuery[0]['TEP'] ?? 0);
            // $npsy = intval($lastDatetimeDataYearRecord[0]['NPS']) - intval($firstDatetimeDataYearRecord[0]['NPS']);
            // $trhy = intval($lastDatetimeDataYearRecord[0]['TRH']) - intval($firstDatetimeDataYearRecord[0]['TRH']);
            // $tepy = intval($lastDatetimeDataYearRecord[0]['TEP']) - intval($firstDatetimeDataYearRecord[0]['TEP']);
            // dump($npsy);
            // dump($trhy);
            // dump($tepy);
        }
        // //dump($lastRecord);

        $poe = [];
        /*+$FCD = $manager->createQuery("SELECT AVG(NULLIF(COALESCE(d.fuelInstConsumption,0), 0)) AS FC
                                    FROM App\Entity\DatetimeData d
                                    JOIN d.smartMod sm 
                                    WHERE d.dateTime LIKE :nowDate
                                    AND sm.id = :smartModId                 
                                    ")
            ->setParameters(array(
                //'selDate'      => $dat,
                'nowDate'      => date("Y-m-d") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // //dump($FCD);
        if ($tepd > 0) $poe[] = ($FCD[0]['FC'] * 1.0) / $tepd;
        else $poe[] = 0;
        */
        $startDate = new DateTime(date('Y-m-d') . ' 00:00:00');
        $endDate = new DateTime(date('Y-m-d') . ' 23:59:59');
        $FCD = $this->getConsoFuelData($manager, $id, $startDate, $endDate);
        // dump($FCD);
        $gensetCapacity = 800;
        if ($tepd > 0) $poe[] = ($FCD['currentConsoFuel'] * $gensetCapacity * 0.01) / $tepd;
        else $poe[] = 0;

        /*$FCM = $manager->createQuery("SELECT AVG(NULLIF(COALESCE(d.fuelInstConsumption,0), 0)) AS FC
                                    FROM App\Entity\DatetimeData d
                                    JOIN d.smartMod sm 
                                    WHERE d.dateTime LIKE :nowDate
                                    AND sm.id = :smartModId                 
                                    ")
            ->setParameters(array(
                //'selDate'      => $dat,
                'nowDate'      => date("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // // dump($FCM);
        if ($tepm > 0) $poe[] = ($FCM[0]['FC'] * 1.0) / $tepm;
        else $poe[] = 0;
        */
        $startDate = new DateTime(date('Y-m-01') . ' 00:00:00');
        // dump($startDate);
        $endDate = new DateTime(date('Y-m-t') . ' 23:59:59');
        // dump($endDate);
        $FCM = $this->getConsoFuelData($manager, $id, $startDate, $endDate);
        // dump($FCM);

        if ($tepm > 0) $poe[] = ($FCM['currentConsoFuel'] * $gensetCapacity * 0.01) / $tepm;
        else $poe[] = 0;

        $FCY = $manager->createQuery("SELECT AVG(NULLIF(d.fuelInstConsumption, 0)) AS FC
                                    FROM App\Entity\DatetimeData d
                                    JOIN d.smartMod sm 
                                    WHERE d.dateTime LIKE :nowDate
                                    AND sm.id = :smartModId                 
                                    ")
            ->setParameters(array(
                //'selDate'      => $dat,
                'nowDate'      => date("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // // dump($FCY);
        if ($tepy > 0) $poe[] = ($FCY[0]['FC'] * 1.0) / $tepy;
        else $poe[] = 0;

        $startDate = new DateTime(date('Y-01-01') . ' 00:00:00');
        // dump($startDate);
        $endDate = new DateTime(date('Y-12-31') . ' 23:59:59');
        // dump($endDate);
        $FCY = $this->getConsoFuelData($manager, $id, $startDate, $endDate);
        // dump($FCY);

        if ($tepy > 0) $poe[] = ($FCY['currentConsoFuel'] * $gensetCapacity * 0.01) / $tepy;
        else $poe[] = 0;

        $noDatetimeData = $manager->getRepository('App:NoDatetimeData')->findOneBy(['id' => $id->getId()]) ?? new NoDatetimeData();
        $yesterday = new DateTime('now');
        $interval = new DateInterval('P1D'); //P10D P1M
        $yesterday->sub($interval);
        // dump($yesterday);
        $lastMonth = new DateTime('now');
        $interval = new DateInterval('P1M'); //P10D P1M
        $lastMonth->sub($interval);
        // dump($lastMonth);
        $lastYear = new DateTime('now');
        $interval = new DateInterval('P1Y'); //P10D P1M
        $lastYear->sub($interval);
        // dump($lastYear);
        /*$precDayLastTEPRecord = $manager->createQuery("SELECT d.totalEnergy AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $yesterday->format('Y-m-d') . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $precDayFirstTEPRecord = $manager->createQuery("SELECT d.totalRunningHours AS TRH, d.totalEnergy AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT min(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $yesterday->format('Y-m-d') . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $prevMonthFirstTEPRecord = $manager->createQuery("SELECT d.totalEnergy AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT min(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastMonth->format("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $prevMonthLastTEPRecord = $manager->createQuery("SELECT d.totalEnergy AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastMonth->format("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $prevYearFirstTEPRecord = $manager->createQuery("SELECT d.totalEnergy AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT min(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastYear->format("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $prevYearLastTEPRecord = $manager->createQuery("SELECT d.totalEnergy AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime =  (SELECT max(d1.dateTime) FROM App\Entity\DatetimeData d1 WHERE d1.dateTime LIKE :nowDate)
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastYear->format("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/


        /*$precDayLastTEPRecord = $manager->createQuery("SELECT MAX(d.totalEnergy) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $yesterday->format('Y-m-d') . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $precDayFirstTEPRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        d.nbPerformedStartUps AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $yesterday->format('Y-m-d') . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/

        $diffprecDayTEPQuery = $manager->createQuery("SELECT MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MAX(NULLIF(d.nbPerformedStartUps,0)) - MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId  
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        ")
            ->setParameters(array(
                'nowDate'      => $yesterday->format('Y-m-d') . "%",
                // 'nowDate'      => "2022-02-06%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // dump($diffprecDayTEPQuery);

        /*$prevMonthFirstTEPRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalEnergy,0)) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastMonth->format("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $prevMonthLastTEPRecord = $manager->createQuery("SELECT MAX(d.totalEnergy) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastMonth->format("Y-m") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/

        $diffprecMonthTEPQuery = $manager->createQuery("SELECT MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MAX(NULLIF(d.nbPerformedStartUps,0)) - MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId  
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastMonth->format('Y-m-d') . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // dump($diffprecMonthTEPQuery);

        /*$prevYearFirstTEPRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalEnergy,0)) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastYear->format("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        $prevYearLastTEPRecord = $manager->createQuery("SELECT MAX(d.totalEnergy) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId                   
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastYear->format("Y") . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();*/

        $diffprecYearTEPQuery = $manager->createQuery("SELECT MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP,
                                        MAX(NULLIF(d.nbPerformedStartUps,0)) - MIN(NULLIF(d.nbPerformedStartUps,0)) AS NPS
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime LIKE :nowDate
                                        AND sm.id = :smartModId  
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        ")
            ->setParameters(array(
                'nowDate'      => $lastYear->format('Y-m-d') . "%",
                'smartModId'   => $id->getId()
            ))
            ->getResult();
        // dump($diffprecYearTEPQuery);

        $prev_tepd = 0;
        // if (count($precDayLastTEPRecord) && count($precDayFirstTEPRecord)) {
        if (count($diffprecDayTEPQuery) > 0) {
            $prev_tepd = intval($diffprecDayTEPQuery[0]['TEP'] ?? 0);
            // $prev_tepd = intval($precDayFirstTEPRecord[0]['TEP']) - intval($precDayLastTEPRecord[0]['TEP']);
            // dump($prev_tepd);
        }
        $prev_tepm = 0;
        // if (count($prevMonthFirstTEPRecord) && count($prevMonthLastTEPRecord)) {
        if (count($diffprecMonthTEPQuery) > 0) {
            $prev_tepm = intval($diffprecMonthTEPQuery[0]['TEP'] ?? 0);
            // $prev_tepm = intval($prevMonthLastTEPRecord[0]['TEP']) - intval($prevMonthFirstTEPRecord[0]['TEP']);
            // dump($prev_tepm);
        }
        $prev_tepy = 0;
        // if (count($prevYearFirstTEPRecord) && count($prevYearLastTEPRecord)) {
        if (count($diffprecYearTEPQuery) > 0) {
            $prev_tepy = intval($diffprecYearTEPQuery[0]['TEP'] ?? 0);
            // $prev_tepy = intval($prevYearLastTEPRecord[0]['TEP']) - intval($prevYearFirstTEPRecord[0]['TEP']);
            // dump($prev_tepy);
        }

        $startDate = new DateTime($yesterday->format('Y-m-d') . ' 00:00:00');
        // dump($startDate);
        $endDate = new DateTime($yesterday->format('Y-m-d') . ' 23:59:59');
        // dump($endDate);
        $prevFCD = $this->getConsoFuelData($manager, $id, $startDate, $endDate);
        // dump($prevFCD);

        $startDate = new DateTime($lastMonth->format('Y-m-01') . ' 00:00:00');
        // dump($startDate);
        $endDate = new DateTime($lastMonth->format('Y-m-t') . ' 23:59:59');
        // dump($endDate);
        $prevFCM = $this->getConsoFuelData($manager, $id, $startDate, $endDate);
        // dump($prevFCM);

        $startDate = new DateTime($lastYear->format('Y-01-01') . ' 00:00:00');
        // dump($startDate);
        $endDate = new DateTime($lastYear->format('Y-12-31') . ' 23:59:59');
        // dump($endDate);
        $prevFCY = $this->getConsoFuelData($manager, $id, $startDate, $endDate);
        // dump($prevFCY);

        $prev_poe = [];
        /*if ($prev_tepd > 0) $prev_poe[] = ($FCD[0]['FC'] * 1.0) / $prev_tepd;
        else $prev_poe[] = 0;
        if ($prev_tepm > 0) $prev_poe[] = ($FCM[0]['FC'] * 1.0) / $prev_tepm;
        else $prev_poe[] = 0;
        if ($prev_tepy > 0) $prev_poe[] = ($FCY[0]['FC'] * 1.0) / $prev_tepy;
        else $prev_poe[] = 0;*/
        if ($prev_tepd > 0) $prev_poe[] = ($FCD['currentConsoFuel'] * $gensetCapacity * 0.01) / $prev_tepd;
        else $prev_poe[] = 0;
        if ($prev_tepm > 0) $prev_poe[] = ($FCM['currentConsoFuel'] * $gensetCapacity * 0.01) / $prev_tepm;
        else $prev_poe[] = 0;
        if ($prev_tepy > 0) $prev_poe[] = ($FCY['currentConsoFuel'] * $gensetCapacity * 0.01) / $prev_tepy;
        else $prev_poe[] = 0;
        // dump($prev_poe);

        return $this->json([
            'code'    => 200,
            'Vcg'     => [$noDatetimeData->getL12G() ?? 0, $noDatetimeData->getL13G() ?? 0, $noDatetimeData->getL23G() ?? 0],
            //'Vsg'     => [$noDatetimeData->getL1N() ?? 0, $noDatetimeData->getL2N() ?? 0, $noDatetimeData->getL3N() ?? 0],
            'Vcm'     => [$noDatetimeData->getL12M() ?? 0, $noDatetimeData->getL13M() ?? 0, $noDatetimeData->getL23M() ?? 0],
            //'I'       => [$noDatetimeData->getI1() ?? 0, $noDatetimeData->getI2() ?? 0, $noDatetimeData->getI3() ?? 0],
            //'Power'   => [$lastRecord[0]['P'] ?? 0, $lastRecord[0]['Q'] ?? 0, $lastRecord[0]['S'] ?? 0],
            //'Cosfi'    => $lastRecord[0]['Cosfi'] ?? 0,
            'NMI'   => [$NMIDay[0]['NMID'] ?? 0, $NMIMonth[0]['NMIM'] ?? 0, $NMIYear[0]['NMIY'] ?? 0],
            'NPS'   => [$npsd, $npsm, $npsy],
            'TEP'    => [$tepd, $tepm, $tepy],
            'TRH'    => [$trhd, $trhm, $trhy],
            'FC'    => [$FCD['currentConsoFuel'] * $gensetCapacity * 0.01 ?? 0, $FCM['currentConsoFuel'] * $gensetCapacity * 0.01 ?? 0, $FCY['currentConsoFuel'] * $gensetCapacity * 0.01 ?? 0],
            'POE'   => $poe,
            'prevPOE' => $prev_poe,
            //'Freq'    => $noDatetimeData->getFreq() ?? 0,
            //'Idiff'   => $noDatetimeData->getIDiff() ?? 0,
            'Level'       => [$noDatetimeData->getFuelLevel() ?? 0, $noDatetimeData->getWaterLevel() ?? 0, $noDatetimeData->getOilLevel() ?? 0],
            //'Pressure'       => [$noDatetimeData->getAirPressure() ?? 0, $noDatetimeData->getOilPressure() ?? 0],
            'Temp'       => [$noDatetimeData->getWaterTemperature() ?? 0, $noDatetimeData->getCoolerTemperature() ?? 0],
            //'EngineSpeed' => $noDatetimeData->getEngineSpeed() ?? 0,
            'BattVolt' => $noDatetimeData->getBattVoltage() ?? 0,
            'HTM' => $noDatetimeData->getHoursToMaintenance() ?? 0,
            'CGCR'       => [$noDatetimeData->getCg() ?? 0, $noDatetimeData->getCr() ?? 0],
            'Gensetrunning' => $noDatetimeData->getGensetRunning() ?? 0,
            'MainsPresence' => $noDatetimeData->getMainsPresence() ?? 0,
            'MaintenanceRequest' => $noDatetimeData->getMaintenanceRequest() ?? 0,
            'LowFuel' => $noDatetimeData->getLowFuel() ?? 0,
            'PresenceWaterInFuel' => $noDatetimeData->getPresenceWaterInFuel() ?? 0,
            'Overspeed' => $noDatetimeData->getOverspeed() ?? 0,
            'FreqAlarm'       => [$noDatetimeData->getMaxFreq() ?? 0, $noDatetimeData->getMinFreq() ?? 0],
            'VoltAlarm'       => [$noDatetimeData->getMaxVolt() ?? 0, $noDatetimeData->getMinVolt() ?? 0],
            'BattVoltAlarm'       => [$noDatetimeData->getMaxBattVolt() ?? 0, $noDatetimeData->getMinBattVolt() ?? 0],
            'Overload' => $noDatetimeData->getOverload() ?? 0,
            'ShortCircuit' => $noDatetimeData->getShortCircuit() ?? 0,
            'IncSeq'       => [$noDatetimeData->getMainsIncSeq() ?? 0, $noDatetimeData->getGensetIncSeq() ?? 0],
            'DifferentialIntervention' => $noDatetimeData->getDifferentialIntervention() ?? 0,
            'Date1' => $noDatetimeData->getDateTime() ?? '',
            'date' => $date,
            //'Mix_PSCosfi'            => [$S, $P, $Cosfi],
            'Load_Level'    => $S
            // 'ActivePower'            => $P,
            // 'Apparent Power'         => $S,
            // 'Cosfi'            => $Cosfi,

        ], 200);
    }

    /**
     * Permet de mettre à jour les graphes liés aux données d'un module genset
     *
     * @Route("/update/genset/mod/{smartMod<\d+>}/graphs/", name="update_genset_graphs")
     * 
     * @Security( "is_granted('ROLE_SUPER_ADMIN') or ( is_granted('ROLE_NOC_SUPERVISOR') and smartMod.getSite().getEnterprise() === user.getEnterprise() )" )
     * 
     * @param [SmartMod] $smartMod
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateGraph(SmartMod $smartMod, EntityManagerInterface $manager, Request $request): Response
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$smartModRepo = $this->getDoctrine()->getRepository(SmartModRepository::class);
        //$smartMod = $smartModRepo->find($id);
        // //dump($smartModRepo);
        // //dump($smartMod->getModType());
        //$temps = DateTime::createFromFormat("d-m-Y H:i:s", "120");
        // //dump($temps);
        //die();
        $date       = [];
        $P          = [];
        $S          = [];
        $Cosfi      = [];
        $TRH        = [];
        $TEP        = [];
        $FC         = [];
        $dateE      = [];

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

        /*$Energy = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 10) AS jour, MAX(d.totalRunningHours) - MIN(NULLIF(d.totalRunningHours, 0)) AS TRH, 
                                        MAX(d.totalEnergy) - MIN(NULLIF(d.totalEnergy, 0)) AS TEP, AVG(NULLIF(d.fuelInstConsumption, 0))*( MAX(d.totalRunningHours) - MIN(NULLIF(d.totalRunningHours, 0)) ) AS FC
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime BETWEEN :startDate AND :endDate
                                        AND sm.id = :smartModId
                                        GROUP BY jour
                                        ORDER BY jour ASC                       
                                        ")
            ->setParameters(array(
                //'selDate'      => $dat,
                'startDate'    => $startDate->format('Y-m-d H:i:s'),
                'endDate'    => $endDate->format('Y-m-d H:i:s'),
                'smartModId'   => $smartMod->getId()
            ))
            ->getResult();*/
        $Energy = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 10) AS jour, MAX(NULLIF(d.totalRunningHours,0)) - MIN(NULLIF(d.totalRunningHours,0)) AS TRH, 
                                        MAX(NULLIF(d.totalEnergy,0)) - MIN(NULLIF(d.totalEnergy,0)) AS TEP
                                        FROM App\Entity\DatetimeData d
                                        JOIN d.smartMod sm 
                                        WHERE d.dateTime BETWEEN :startDate AND :endDate
                                        AND sm.id = :smartModId
                                        AND d.totalRunningHours <> 853                 
                                        AND d.totalEnergy <> 33370                 
                                        AND d.nbPerformedStartUps <> 637                 
                                        GROUP BY jour
                                        ORDER BY jour ASC                       
                                        ")
            ->setParameters(array(
                //'selDate'      => $dat,
                'startDate'    => $startDate->format('Y-m-d H:i:s'),
                'endDate'    => $endDate->format('Y-m-d H:i:s'),
                'smartModId'   => $smartMod->getId()
            ))
            ->getResult();
        // dump($Energy);
        //die();
        foreach ($Energy as $d) {
            $dateE[] = $d['jour'];
            $TRH[] = number_format((float) $d['TRH'], 2, '.', '');
            $TEP[] = number_format((float) $d['TEP'], 2, '.', '');
            //$FC[] = number_format((float) $d['FC'], 2, '.', '') ?? 0;
        }


        /*
        SELECT d.dateTime as dat, d.va, d.vb, d.vc, d.sa, d.sb, d.sc, d.s3ph
                                                FROM App\Entity\DataMod d, App\Entity\SmartMod sm 
                                                WHERE d.dateTime LIKE :selDate
                                                AND sm.id = :modId
                                                ORDER BY dat ASC
        */

        $data = $manager->createQuery("SELECT d.dateTime as dat, d.p, (d.s*100.0)/:genpower as s, d.cosfi
                                        FROM App\Entity\DatetimeData d 
                                        JOIN d.smartMod sm
                                        WHERE d.dateTime BETWEEN :startDate AND :endDate
                                        AND sm.id = :smartModId
                                        ORDER BY dat ASC
                                        
                                        ")
            ->setParameters(array(
                //'selDate'      => $dateparam,
                'startDate'   => $startDate,
                'endDate'     => $endDate,
                'genpower'    => $smartMod->getPower(),
                'smartModId'  => $smartMod->getId()
            ))
            ->getResult();


        // dump($data);
        foreach ($data as $d) {
            $date[]    = $d['dat']->format('Y-m-d H:i:s');
            //$P[]       = number_format((float) $d['p'], 2, '.', '');
            $S[]    = number_format((float) $d['s'], 2, '.', '');
            //$Cosfi[]   = number_format((float) $d['cosfi'], 2, '.', '');
        }

        $FC = $this->getConsoFuelData($manager, $smartMod, $startDate, $endDate);

        // dump($FC);

        return $this->json([
            'code'         => 200,
            //'startDate'    => $startDate,
            //'endDate'      => $endDate,
            'date'         => $date,
            'Mix1'            => [$TRH, $TEP],
            'dateFuelConso'   => $FC['dayBydayConsoData']['dateConso'],
            'Mix2'            => $FC['dayBydayConsoData']['consoFuel'],
            //'Mix2'            => [$S, $P, $Cosfi],
            'Load_Level'    => $S,
            // 'S3ph'         => $S3ph,
            'dateE'           => $dateE,
            // 'kWh'          => $kWh,
            // 'kVarh'        => $kVarh,
        ], 200);
    }

    /**
     * Permet de mettre à jour la BDD des NoDatetimeData
     *
     * @Route("/update/mod/{modId<[a-zA-Z0-9]+>}/nodatetime/data",name="update_nodatetimedata")
     * 
     * @param [interger] $id
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateNoDatetimeData($modId, EntityManagerInterface $manager, Request $request): Response
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());
        // //dump($paramJSON);
        // //dump($content);
        //die();

        //Recherche du module dans la BDD
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);


        if ($smartMod != null) { // Test si le module existe dans notre BDD

            //Paramétrage des champs de la nouvelle dataMod aux valeurs contenues dans la requête du module
            //$dataMod->setVamin($paramJSON['Va'][0]);

            if ($smartMod->getModType() == 'FUEL') {
                $isNew = false;
                $oldData = null;
                $mess = "";
                //$response = new ResponseInterface();
                $dataMod = $smartMod->getNoDatetimeData();
                if (!$dataMod) {
                    $dataMod = new NoDatetimeData();
                    $dataMod->setSmartMod($smartMod);
                    $isNew = true;
                } else {
                    $oldData = clone $smartMod->getNoDatetimeData();
                }
                //$date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date1']);
                $date = new DateTime('now');

                /*$dataMod->setL12G($paramJSON['L12G'])
                    ->setL13G($paramJSON['L13G'])
                    ->setL23G($paramJSON['L23G'])
                    ->setL1N($paramJSON['L1N'])
                    ->setL2N($paramJSON['L2N'])
                    ->setL3N($paramJSON['L3N'])
                    ->setL12M($paramJSON['L12M'])
                    ->setL13M($paramJSON['L13M'])
                    ->setL23M($paramJSON['L23M'])
                    ->setI1($paramJSON['I1N'])
                    ->setI2($paramJSON['I2N'])
                    ->setI3($paramJSON['I3N'])
                    ->setFreq($paramJSON['Freq'])
                    ->setIDiff($paramJSON['Idiff'])
                    ->setFuelLevel($paramJSON['FuelLevel'])
                    ->setWaterLevel($paramJSON['WaterLevel'])
                    ->setOilLevel($paramJSON['OilLevel'])
                    ->setAirPressure($paramJSON['AirPressure'])
                    ->setOilPressure($paramJSON['OilPressure'])
                    ->setWaterTemperature($paramJSON['WaterTemperature'])
                    ->setCoolerTemperature($paramJSON['CoolerTemperature'])
                    ->setEngineSpeed($paramJSON['EngineSpeed'])
                    ->setBattVoltage($paramJSON['BattVoltage'])
                    ->setHoursToMaintenance($paramJSON['HTM'])
                    ->setCg($paramJSON['CG'])
                    ->setCr($paramJSON['CR'])
                    ->setGensetRunning($paramJSON['GensetRun'])
                    ->setMainsPresence($paramJSON['MainsPresence'])
                    ->setPresenceWaterInFuel($paramJSON['PresenceWaterInFuel'])
                    ->setMaintenanceRequest($paramJSON['MaintenanceRequest'])
                    ->setLowFuel($paramJSON['LowFuel'])
                    ->setOverspeed($paramJSON['Overspeed'])
                    ->setMaxFreq($paramJSON['MaxFreq'])
                    ->setMinFreq($paramJSON['MinFreq'])
                    ->setMaxVolt($paramJSON['MaxVolt'])
                    ->setMinVolt($paramJSON['MinVolt'])
                    ->setMaxBattVolt($paramJSON['MaxBattVolt'])
                    ->setMinBattVolt($paramJSON['MinBattVolt'])
                    ->setOverload($paramJSON['Overload'])
                    ->setShortCircuit($paramJSON['ShortCircuit'])
                    ->setMainsIncSeq($paramJSON['MainsIncSeq'])
                    ->setGensetIncSeq($paramJSON['GensetIncSeq'])
                    ->setDifferentialIntervention($paramJSON['DiffIntervention'])
                    //->setSmartMod($smartMod)
                ;*/

                if (array_key_exists("L12", $paramJSON)) {
                    $dataMod->setL12G($paramJSON['L12']);
                }

                $dataMod->setDateTime($date);

                if (array_key_exists("L13", $paramJSON)) {
                    $dataMod->setL13G($paramJSON['L13']);
                }


                if (array_key_exists("L23", $paramJSON)) {
                    $dataMod->setL23G($paramJSON['L23']);
                }


                if (array_key_exists("L1", $paramJSON)) {
                    $dataMod->setL1N($paramJSON['L1']);
                }


                if (array_key_exists("L2", $paramJSON)) {
                    $dataMod->setL2N($paramJSON['L2']);
                }


                if (array_key_exists("L3", $paramJSON)) {
                    $dataMod->setL3N($paramJSON['L3']);
                }


                if (array_key_exists("L12M", $paramJSON)) {
                    $dataMod->setL12M($paramJSON['L12M']);
                }


                if (array_key_exists("L13M", $paramJSON)) {
                    $dataMod->setL13M($paramJSON['L13M']);
                }


                if (array_key_exists("L23M", $paramJSON)) {
                    $dataMod->setL23M($paramJSON['L23M']);
                }


                if (array_key_exists("I1", $paramJSON)) {
                    $dataMod->setI1($paramJSON['I1']);
                }


                if (array_key_exists("I2", $paramJSON)) {
                    $dataMod->setI2($paramJSON['I2']);
                }


                if (array_key_exists("I3", $paramJSON)) {
                    $dataMod->setI3($paramJSON['I3']);
                }


                if (array_key_exists("Fr", $paramJSON)) {
                    $dataMod->setFreq($paramJSON['Fr']);
                }

                if (array_key_exists("Id", $paramJSON)) {
                    $dataMod->setIDiff($paramJSON['Id']);
                }

                if (array_key_exists("FL", $paramJSON)) {
                    $dataMod->setFuelLevel($paramJSON['FL']);
                }

                if (array_key_exists("WL", $paramJSON)) {
                    $dataMod->setWaterLevel($paramJSON['WL']);
                }

                if (array_key_exists("OL", $paramJSON)) {
                    $dataMod->setOilLevel($paramJSON['OL']);
                }

                if (array_key_exists("AP", $paramJSON)) {
                    $dataMod->setAirPressure($paramJSON['AP']);
                }

                if (array_key_exists("OP", $paramJSON)) {
                    $dataMod->setOilPressure($paramJSON['OP']);
                }

                if (array_key_exists("WT", $paramJSON)) {
                    $dataMod->setWaterTemperature($paramJSON['WT']);
                }

                if (array_key_exists("CT", $paramJSON)) {
                    $dataMod->setCoolerTemperature($paramJSON['CT']);
                }

                if (array_key_exists("ESD", $paramJSON)) {
                    $dataMod->setEngineSpeed($paramJSON['ESD']);
                }

                if (array_key_exists("BV", $paramJSON)) {
                    $dataMod->setBattVoltage($paramJSON['BV']);
                }

                if (array_key_exists("HTM", $paramJSON)) {
                    $dataMod->setHoursToMaintenance($paramJSON['HTM']);
                }


                if (array_key_exists("CG", $paramJSON)) {
                    $dataMod->setCg($paramJSON['CG']);
                }


                if (array_key_exists("CR", $paramJSON)) {
                    $dataMod->setCr($paramJSON['CR']);
                }


                if (array_key_exists("GenRun", $paramJSON)) {
                    $dataMod->setGensetRunning($paramJSON['GenRun']);
                }


                if (array_key_exists("MainsPresence", $paramJSON)) {
                    $dataMod->setMainsPresence($paramJSON['MainsPresence']);
                }


                if (array_key_exists("PWF", $paramJSON)) {
                    $dataMod->setPresenceWaterInFuel($paramJSON['PWF']);
                }

                if (array_key_exists("MRqst", $paramJSON)) {
                    $dataMod->setMaintenanceRequest($paramJSON['MRqst']);
                }

                if (array_key_exists("LowFuel", $paramJSON)) {
                    $dataMod->setLowFuel($paramJSON['LowFuel']);
                }

                if (array_key_exists("Overspeed", $paramJSON)) {
                    $dataMod->setOverspeed($paramJSON['Overspeed']);
                }

                if (array_key_exists("MaxFr", $paramJSON)) {
                    $dataMod->setMaxFreq($paramJSON['MaxFr']);
                }

                if (array_key_exists("MinFr", $paramJSON)) {
                    $dataMod->setMinFreq($paramJSON['MinFr']);
                }

                if (array_key_exists("MaxVolt", $paramJSON)) {
                    $dataMod->setMaxVolt($paramJSON['MaxVolt']);
                }

                if (array_key_exists("MinVolt", $paramJSON)) {
                    $dataMod->setMinVolt($paramJSON['MinVolt']);
                }

                if (array_key_exists("MaxBV", $paramJSON)) {
                    $dataMod->setMaxBattVolt($paramJSON['MaxBV']);
                }

                if (array_key_exists("MinBV", $paramJSON)) {
                    $dataMod->setMinBattVolt($paramJSON['MinBV']);
                }


                if (array_key_exists("Overload", $paramJSON)) {
                    $dataMod->setOverload($paramJSON['Overload']);
                }


                if (array_key_exists("SC", $paramJSON)) {
                    $dataMod->setShortCircuit($paramJSON['SC']);
                }


                if (array_key_exists("MIS", $paramJSON)) {
                    $dataMod->setMainsIncSeq($paramJSON['MIS']);
                }


                if (array_key_exists("GIS", $paramJSON)) {
                    $dataMod->setGensetIncSeq($paramJSON['GIS']);
                }


                if (array_key_exists("DIT", $paramJSON)) {
                    $dataMod->setDifferentialIntervention($paramJSON['DIT']);
                }


                $dataMod->setSmartMod($smartMod);;


                if (!$isNew) {
                    $BATT = "MINB"; // 0
                    $MAINAB = "MAIAB"; // 1
                    $MAINPR = "MAIPR"; // 1
                    $SPEED = "OVSPD"; // 2
                    $LOAD = "OVLOD"; // 3
                    $VOLT = "MINV"; // 4
                    $FREQ = "MINF"; // 5
                    $GENRUN = "GENR"; // 6
                    $GENST = "GENST"; // 6
                    $GOTL = "GOTL"; // 6
                    $GNOTL = "GNOTL"; // 6
                    $FUEL = "LOFL"; // 7
                    $DIFFC = "DIFFC"; // 8
                    $WATFL = "WATFL"; // 9
                    $SFL50 = "SFL50"; // 11
                    $SFL20 = "SFL20"; // 12

                    if (array_key_exists("MinBV", $paramJSON)) {
                        if ($oldData->getMinBattVolt() === 0 && $paramJSON['MinBV'] === 1) {
                            $mess = "{\"code\":\"{$BATT}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$BATT}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                    }

                    if (array_key_exists("CR", $paramJSON)) {
                        if ($oldData->getCr() === 1 && $paramJSON['CR'] === 0) {
                            $mess = "{\"code\":\"{$MAINAB}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$MAINAB}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                        if ($oldData->getCr() === 0 && $paramJSON['CR'] === 1) {
                            $mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                    }

                    if (array_key_exists("Overspeed", $paramJSON)) {
                        if ($oldData->getOverspeed() === 0 && $paramJSON['Overspeed'] === 1) {
                            $mess = "{\"code\":\"{$SPEED}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$SPEED}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                    }

                    if (array_key_exists("Overload", $paramJSON)) {
                        if ($oldData->getOverload() === 0 && $paramJSON['Overload'] === 1) {
                            $mess = "{\"code\":\"{$LOAD}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$LOAD}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                    }

                    if (array_key_exists("MinVolt", $paramJSON)) {
                        if ($oldData->getMinVolt() === 0 && $paramJSON['MinVolt'] === 1) {
                            $mess = "{\"code\":\"{$VOLT}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$VOLT}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                    }

                    if (array_key_exists("MinFr", $paramJSON)) {
                        if ($oldData->getMinFreq() === 0 && $paramJSON['MinFr'] === 1) {
                            $mess = "{\"code\":\"{$FREQ}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$FREQ}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward(
                                'App\Controller\GensetController::sendToAlarmController',
                                [
                                    'mess' => $mess,
                                    'modId' => $smartMod->getModuleId(),
                                ]
                            );
                        }
                    }

                    if (array_key_exists("CG", $paramJSON)) {
                        if (($oldData->getCg() === 0 && $paramJSON['CG'] === 1)) {
                            $mess = "{\"code\":\"{$GOTL}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$GENRUN}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess'   => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]);
                        }
                        if (($oldData->getCg() === 1 && $paramJSON['CG'] === 0)) {
                            $mess = "{\"code\":\"{$GNOTL}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$GENRUN}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess'   => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]);
                        }
                    }

                    if (array_key_exists("GenRun", $paramJSON)) {
                        if ($oldData->getGensetRunning() === 0 && $paramJSON['GenRun'] === 1) {
                            $mess = "{\"code\":\"{$GENRUN}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$GENRUN}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess' => $mess,
                                'modId' => $smartMod->getModuleId(),
                            ]);
                        }
                        if (($oldData->getGensetRunning() === 1 && $paramJSON['GenRun'] === 0)) {
                            $mess = "{\"code\":\"{$GENST}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$GENST}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess'   => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]);
                        }
                    }

                    if (array_key_exists("LowFuel", $paramJSON)) {
                        if ($oldData->getLowFuel() === 0 && $paramJSON['LowFuel'] === 1) {
                            $mess = "{\"code\":\"{$FUEL}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$FUEL}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess' => $mess,
                                'modId' => $smartMod->getModuleId(),
                            ]);
                        }
                    }

                    if (array_key_exists("FL", $paramJSON)) {
                        if ($oldData->getFuelLevel() > 50 && $paramJSON['FL'] <= 50) {
                            $mess = "{\"code\":\"{$SFL50}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$SFL50}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess' => $mess,
                                'modId' => $smartMod->getModuleId(),
                            ]);
                        }
                        if ($oldData->getFuelLevel() > 20 && $paramJSON['FL'] <= 20) {
                            $mess = "{\"code\":\"{$SFL20}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$SFL20}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess' => $mess,
                                'modId' => $smartMod->getModuleId(),
                            ]);
                        }
                    }

                    if (array_key_exists("DIT", $paramJSON)) {
                        if ($oldData->getDifferentialIntervention() === 0 && $paramJSON['DIT'] === 1) {
                            $mess = "{\"code\":\"{$DIFFC}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$DIFFC}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess' => $mess,
                                'modId' => $smartMod->getModuleId(),
                            ]);
                        }
                    }

                    if (array_key_exists("PWF", $paramJSON)) {
                        if ($oldData->getPresenceWaterInFuel() === 0 && $paramJSON['PWF'] === 1) {
                            $mess = "{\"code\":\"{$WATFL}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                            //$mess = "{\"code\":\"{$WATFL}\",\"date\":\"{$paramJSON['date1']}\"}";

                            $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                                'mess' => $mess,
                                'modId' => $smartMod->getModuleId(),
                            ]);
                        }
                    }
                }
            }

            // //dump($dataMod);
            //die();
            //Insertion de la nouvelle dataMod dans la BDD
            $manager->persist($dataMod);
            $manager->flush();

            return $this->json([
                'code' => 200,
                //'received' => $paramJSON,
                'date'  => $oldData->getDateTime()
                // 'status'   => $response->getStatusCode(),
                // 'content' => $response->getContent(),
                //'contentType' => $response->getHeaders()['content-type'][0],
                //'old'   => $oldData->getGensetRunning(),
                //'new'   => $paramJSON['GenRun'],
                //'Url'   => "http://127.0.0.1/index.php/alarm/notification/{$smartMod->getModuleId()}",
                //'mess'   => $mess

            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => "SmartMod don't exist",
            'received' => $paramJSON

        ], 403);
    }

    /**
     * Permet de surcharger les données DatetimeData des modules FUEL dans la BDD
     *
     * @Route("/datetimedata/mod/{modId<[a-zA-Z0-9]+>}/add", name="datetimeData_add") 
     * 
     * @param SmartMod $smartMod
     * @param EntityManagerInterface $manager
     * @param Request $request
     * @return void
     */
    public function datetimeData_add($modId, EntityManagerInterface $manager, Request $request)
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());
        // //dump($paramJSON);
        // //dump($content);
        //die();

        $datetimeData = new DatetimeData();

        //Recherche du module dans la BDD
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);

        if ($smartMod != null) { // Test si le module existe dans notre BDD
            //data:{"date": "2020-03-20 12:15:00", "sa": 1.2, "sb": 0.7, "sc": 0.85, "va": 225, "vb": 230, "vc": 231, "s3ph": 2.75, "kWh": 1.02, "kvar": 0.4}
            // //dump($smartMod);//Affiche le module
            //die();

            //$date = new DateTime($paramJSON['date']);
            if (array_key_exists("date", $paramJSON)) {
                //Récupération de la date dans la requête et transformation en object de type Date au format date SQL
                //$date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                if (array_key_exists("EL", $paramJSON) && array_key_exists("TRH", $paramJSON) && array_key_exists("NPS", $paramJSON)) {
                    if ((intval($paramJSON["EL"]) !== 33370) && (intval($paramJSON["TRH"]) !== 853) && (intval($paramJSON["NPS"]) !== 637)) {
                        $date = new DateTime('now');
                        $minute = intval($date->format('i'));
                        // //dump($date);
                        //die();
                        // $isTrue = 'No';

                        if ($minute % 2 == 0) {
                            // $isTrue = 'Yes';
                            if ($smartMod->getModType() == 'FUEL') {
                                //Paramétrage des champs de la nouvelle DatetimeData aux valeurs contenues dans la requête du module
                                $datetimeData->setDateTime($date)
                                    ->setSmartMod($smartMod);
                                if (array_key_exists("P3ph", $paramJSON)) {
                                    //$datetimeData->setPmax3ph($paramJSON['P3ph'][0])
                                }
                                if (array_key_exists("P", $paramJSON)) {
                                    $datetimeData->setP($paramJSON['P']);
                                }
                                if (array_key_exists("Q3ph", $paramJSON)) {
                                    //$datetimeData->setQmax3ph($paramJSON['Q3ph'][0])
                                }
                                if (array_key_exists("Q", $paramJSON)) {
                                    $datetimeData->setQ($paramJSON['Q']);
                                }
                                if (array_key_exists("S", $paramJSON)) {
                                    $datetimeData->setS($paramJSON['S']);
                                }
                                if (array_key_exists("Cosfi", $paramJSON)) {
                                    $datetimeData->setCosfi($paramJSON['Cosfi']);
                                }
                                if (array_key_exists("EL", $paramJSON)) {
                                    $datetimeData->setTotalEnergy($paramJSON['EL']);
                                }
                                if (array_key_exists("FuelInstConsumption", $paramJSON)) {
                                    $datetimeData->setFuelInstConsumption($paramJSON['FuelInstConsumption'] / 256.0);
                                }
                                if (array_key_exists("NPS", $paramJSON)) {
                                    $datetimeData->setNbPerformedStartUps($paramJSON['NPS']);
                                }
                                if (array_key_exists("NMI", $paramJSON)) {
                                    $datetimeData->setNbMainsInterruption($paramJSON['NMI']);
                                }
                                if (array_key_exists("TRH", $paramJSON)) {
                                    $datetimeData->setTotalRunningHours($paramJSON['TRH']);
                                }

                                $fuelLevel = 0.0;
                                $noDatetimeData = $smartMod->getNoDatetimeData();
                                if ($noDatetimeData) {
                                    $fuelLevel = $noDatetimeData->getFuelLevel() ?? 0;
                                }
                                $datetimeData->setFuelLevel($fuelLevel);
                            }
                            // //dump($datetimeData);
                            //die();
                            //Insertion de la nouvelle datetimeData dans la BDD
                            $manager->persist($datetimeData);
                            $manager->flush();
                        }
                    }
                } else {
                    if ($smartMod->getModType() == 'FUEL') {
                        if($paramJSON['date'] !== '2000-01-01 00:00:00') $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                        else $date = new DateTime('now');

                        //Paramétrage des champs de la nouvelle DatetimeData aux valeurs contenues dans la requête du module
                        $datetimeData->setDateTime($date)
                            ->setSmartMod($smartMod);
                        if (array_key_exists("P3ph", $paramJSON)) {
                            //$datetimeData->setPmax3ph($paramJSON['P3ph'][0])
                        }
                        if (array_key_exists("P", $paramJSON)) {
                            $datetimeData->setP($paramJSON['P']);
                        }
                        if (array_key_exists("Q3ph", $paramJSON)) {
                            //$datetimeData->setQmax3ph($paramJSON['Q3ph'][0])
                        }
                        if (array_key_exists("Q", $paramJSON)) {
                            $datetimeData->setQ($paramJSON['Q']);
                        }
                        if (array_key_exists("S", $paramJSON)) {
                            $datetimeData->setS($paramJSON['S']);
                        }
                        if (array_key_exists("Cosfi", $paramJSON)) {
                            $datetimeData->setCosfi($paramJSON['Cosfi']);
                        }
                        if (array_key_exists("EL", $paramJSON)) {
                            $datetimeData->setTotalEnergy($paramJSON['EL']);
                        }
                        if (array_key_exists("FuelInstConsumption", $paramJSON)) {
                            $datetimeData->setFuelInstConsumption($paramJSON['FuelInstConsumption'] / 256.0);
                        }
                        if (array_key_exists("NPS", $paramJSON)) {
                            $datetimeData->setNbPerformedStartUps($paramJSON['NPS']);
                        }
                        if (array_key_exists("NMI", $paramJSON)) {
                            $datetimeData->setNbMainsInterruption($paramJSON['NMI']);
                        }
                        if (array_key_exists("TRH", $paramJSON)) {
                            $datetimeData->setTotalRunningHours($paramJSON['TRH']);
                        }

                        $fuelLevel = 0.0;
                        $noDatetimeData = $smartMod->getNoDatetimeData();
                        if ($noDatetimeData) {
                            $fuelLevel = $noDatetimeData->getFuelLevel() ?? 0;
                        }
                        $datetimeData->setFuelLevel($fuelLevel);
                    }
                    // //dump($datetimeData);
                    //die();
                    //Insertion de la nouvelle datetimeData dans la BDD
                    $manager->persist($datetimeData);
                    $manager->flush();
                }

                // MOD(MINUTE(`date_time`), 2) <> 0
            }

            return $this->json([
                'code' => 200,
                'received' => $paramJSON,
                // 'isTrue'   => $isTrue
            ], 200);
        }
        return $this->json([
            'code' => 403,
            'message' => "SmartMod don't exist",
            'received' => $paramJSON

        ], 403);
    }

    public function sendToAlarmController($mess, $modId, EntityManagerInterface $manager, HttpClientInterface $client, MessageBusInterface $messageBus)
    {
        /*return $this->json([
            'mess'    => $mess,
            'modId' => $modId,
        ], 200);*/
        $paramJSON = $this->getJSONRequest($mess);
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);
        if ($smartMod) {
            $alarmCode = $manager->getRepository('App:Alarm')->findOneBy(['code' => $paramJSON['code']]);
            if ($alarmCode) {
                //$date = new DateTime('now');
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']) !== false ? DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']) : new DateTime('now');
                //$date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                $alarmReporting = new AlarmReporting();
                $alarmReporting->setSmartMod($smartMod)
                    ->setAlarm($alarmCode)
                    ->setCreatedAt($date);

                $site = null;
                $installationName = "";

                if ($smartMod->getSite()) {
                    $site = $smartMod->getSite();
                    $installationName = ' du site ' . $site->getName();
                }
                else {
                    foreach ($smartMod->getZones() as $zone) {
                        $site = $zone->getSite();
                        if ($site) {
                            $installationName = $smartMod->getName() . ' du site ' . $site->getName();
                            break;
                        }
                    }
                }

                $message = "";

                if ($alarmCode->getType() !== 'FUEL') $message = $alarmCode->getLabel() . ' détecté(e) par <<' . $smartMod->getName() . '>> du site ' . $site->getName() . ' le ' . $date->format('d/m/Y à H:i:s');
                else if ($alarmCode->getType() === 'FUEL') {
                    $data = clone $smartMod->getNoDatetimeData();
                    $fuelStr = $data->getFuelLevel() != null ? ' avec un niveau de Fuel de ' . $data->getFuelLevel() . '%' : '';
                    if ($alarmCode->getCode() === 'GENR') $message = $alarmCode->getLabel() . $installationName . ' survenu(e) le ' . $date->format('d/m/Y à H:i:s') . ' avec un niveau de Fuel de ' . $data->getFuelLevel() . '%';
                    else if ($alarmCode->getCode() === 'GENST') {
                        $message = $alarmCode->getLabel() . $installationName . " survenu le " . $date->format('d/m/Y à H:i:s') . $fuelStr;
                    } else if ($alarmCode->getCode() === 'SFL50' ) {
                        $message = $alarmCode->getLabel() . " dans le réservoir du groupe électrogène " . $installationName . " détecté le " . $date->format('d/m/Y à H:i:s') . ". Nous vous prions de bien vouloir effectuer une opération de ravitaillement. Niveau de Fuel Actuel : " . $data->getFuelLevel() . '%';
                    } else if ($alarmCode->getCode() === 'SFL20') {
                        $message = $alarmCode->getLabel() . " dans le réservoir du groupe électrogène " . $installationName . " détecté le " . $date->format('d/m/Y à H:i:s') . '. Niveau de Fuel de ' . $data->getFuelLevel() . '%';
                    } else if ($alarmCode->getCode() === 'GOTL') {
                        if ($smartMod->getSite()) $message = $alarmCode->getLabel() . ' ' . $site->getName() . " depuis le " . $date->format('d/m/Y à H:i:s') . $fuelStr;
                        else $message = 'Le Groupe électrogène ' . $smartMod->getName() . " débite depuis le " . $date->format('d/m/Y à H:i:s') . $fuelStr;
                    } else if ($alarmCode->getCode() === 'GNOTL') {
                        $message = $alarmCode->getLabel() . $installationName . " survenue le " . $date->format('d/m/Y à H:i:s');
                    } /*else if ($alarmCode->getCode() === 'SFL50' || $alarmCode->getCode() === 'SFL20') {
                        $message = $alarmCode->getLabel() . " dans le réservoir du groupe électrogène " . $installationName . " détecté le " . $date->format('d/m/Y à H:i:s') . ".
Niveau de Fuel actuel : " . $data->getFuelLevel() . '%';
                    }*/ else $message = $alarmCode->getLabel() . $installationName . ' survenu(e) le ' . $date->format('d/m/Y à H:i:s');
                }

                foreach ($site->getContacts() as $contact) {
                    $messageBus->dispatch(new UserNotificationMessage($contact->getId(), $message, $alarmCode->getMedia(), $alarmCode->getAlerte()));
                    //$messageBus->dispatch(new UserNotificationMessage($contact->getId(), $message, 'SMS', ''));
                }

                //$adminUsers = [];
                $Users = $manager->getRepository('App:User')->findAll();
                foreach ($Users as $user) {
                    if ($user->getRoles()[0] === 'ROLE_SUPER_ADMIN') {
                        //$adminUsers[] = $user;
                        $messageBus->dispatch(new UserNotificationMessage($user->getId(), $message, 'Email', $alarmCode->getAlerte()));
                    }
                }
                //$messageBus->dispatch(new UserNotificationMessage(1, $message, 'Email', $alarmCode->getAlerte()));
                //$messageBus->dispatch(new UserNotificationMessage(2, $message, 'Email', $alarmCode->getAlerte()));
                $manager->persist($alarmReporting);
                $manager->flush();
                return $this->json([
                    'code'    => 200,
                    'alarmCode'  => "{$alarmCode->getMedia()}",
                    'date'  => $date->format('d F Y H:i:s')
                ], 200);
            }
            return $this->json([
                'code'    => 200,
                'smartMod'  => "{$smartMod->getModuleId()}",
                //'date'  => $date->format('d F Y H:i:s')
            ], 200);
        }

        return $this->json([
            'code'         => 500,
        ], 500);
    }
    // stdigital.powermon.alerts@gmail.com
    public function getConsoFuelData(EntityManagerInterface $manager, $smartMod, $startDate, $endDate)
    {
        $lastStartDate = new DateTime($startDate->format('Y-m-d H:i:s'));
        $lastStartDate->sub(new DateInterval('P1M'));

        $lastEndDate = new DateTime($endDate->format('Y-m-d H:i:s'));
        $lastEndDate->sub(new DateInterval('P1M'));

        // ========= Détermination de la longueur de la datetime =========
        $length = 10; //Si endDate > startDate => regoupement des données par jour de la fenêtre de date
        if ($endDate->format('Y-m-d') == $startDate->format('Y-m-d')) $length = 13; //Si endDate == startDate => regoupement des données par heure du jour choisi

        // ######## Récupération des données de courbe pour le mois en cours ########
        $dataQuery = $manager->createQuery("SELECT d.dateTime as dat, d.fuelLevel as FL
                                        FROM App\Entity\DatetimeData d 
                                        JOIN d.smartMod sm
                                        WHERE d.dateTime BETWEEN :startDate AND :endDate
                                        AND sm.id = :smartModId
                                        ORDER BY dat ASC
                                        ")
            ->setParameters(array(
                //'selDate'      => $dateparam,
                'startDate'  => $startDate->format('Y-m-d H:i:s'),
                'endDate'    => $endDate->format('Y-m-d H:i:s'),
                'smartModId' => $smartMod->getId()
            ))
            ->getResult();
        // dump($data);
        // $FL   = [];
        // $TRH  = [];
        // $date = [];
        $data = [];
        foreach ($dataQuery as $d) {
            // $date[]    = $d['dat']->format('Y-m-d H:i:s');
            // $FL[]      = $d['FL'];
            // $TRH[]     = $d['TRH'];
            $data[$d['dat']->format('Y-m-d H:i:s')] = [
                'FL'    => $d['FL'],
                //'TRH'   => $d['TRH']
            ];
            //$Cosfi[]   = number_format((float) $d['cosfi'], 2, '.', '');
        }
        //dump($data);
        $dayRecord = $manager->createQuery("SELECT SUBSTRING(d.dateTime,1,:length_) as dat
                                        FROM App\Entity\DatetimeData d 
                                        JOIN d.smartMod sm
                                        WHERE d.dateTime BETWEEN :startDate AND :endDate
                                        AND sm.id = :smartModId
                                        GROUP BY dat
                                        ORDER BY dat ASC
                                        ")
            ->setParameters(array(
                'length_'    => $length,
                'startDate'  => $startDate->format('Y-m-d H:i:s'),
                'endDate'    => $endDate->format('Y-m-d H:i:s'),
                'smartModId' => $smartMod->getId(),
            ))
            ->getResult();

        // dump($dayRecord);
        $day = [];
        foreach ($dayRecord as $d) {
            $day[]    = $d['dat'];
        }

        $dataOrderByDay = []; //Tableau des valeurs jour après jour
        foreach ($data as $key => $value) {
            // dump($key);
            foreach ($day as $index => $val) {
                //dump($val);
                if (strpos($key, $val) !== false) { // On vérifie si le la sous-chaîne du jour est contenue dans la date
                    $dataOrderByDay[$val]['FL'][]  = $value['FL'];
                    //$dataOrderByDay[$val]['TRH'][] = $value['TRH'];
                }
            }
        }

        $currentConsoFuel = 0;
        $currentApproFuel = 0;

        $consoFuelDayByDay = [];
        $approFuelDayByDay = [];

        $dureeDayByDay = [];

        foreach ($dataOrderByDay as $key => $value) {
            $consoFuel_ = 0;
            $approFuel_ = 0;

            //Données des courbe de consommation et approvisionnement jour après jour
            if (array_key_exists('FL', $value)) {
                $temp = $value['FL']; //Tableau tampon
                if (count($temp) > 0) {
                    for ($i = 0; $i < count($temp) - 1; $i++) {
                        $diff = abs($temp[$i + 1] - $temp[$i]);
                        if ($temp[$i + 1] >= $temp[$i]) {
                            $approFuel_ += $diff;
                        } else {
                            $consoFuel_ += $diff;
                        }
                    }
                }
            }

            $currentConsoFuel += $consoFuel_;
            $currentApproFuel += $approFuel_;

            $consoFuelDayByDay[] = number_format((float) $consoFuel_, 2, '.', '');
            $approFuelDayByDay[] = number_format((float) $approFuel_, 2, '.', '');
        }

        // ######## Récupération des données de courbe pour le mois (n - 1) ########
        $lastData = $manager->createQuery("SELECT d.dateTime as dat, d.fuelLevel as FL
                                        FROM App\Entity\DatetimeData d 
                                        JOIN d.smartMod sm
                                        WHERE d.dateTime BETWEEN :lastStartDate AND :lastEndDate
                                        AND sm.id = :smartModId
                                        ORDER BY dat ASC
                                        ")
            ->setParameters(array(
                //'selDate'      => $dateparam,
                'lastStartDate' => $lastStartDate->format('Y-m') . '%',
                'lastEndDate'   => $lastEndDate->format('Y-m-d H:i:s'),
                'smartModId'    => $smartMod->getId()
            ))
            ->getResult();
        // dump($lastData);
        $FL   = [];
        foreach ($lastData as $d) {
            // $date[]    = $d['dat']->format('Y-m-d H:i:s');
            $FL[]      = $d['FL'];
        }

        $lastConsoFuel = 0;
        $lastApproFuel = 0;

        if (count($FL) > 0) {
            for ($i = 0; $i < count($FL) - 1; $i++) {
                $diff = abs($FL[$i + 1] - $FL[$i]);
                if ($FL[$i + 1] >= $FL[$i]) {
                    $lastApproFuel += $diff;
                } else {
                    $lastConsoFuel += $diff;
                }
            }
        }

        $currentConsoFuelProgress = ($lastConsoFuel !== 0) ? ($currentConsoFuel - $lastConsoFuel) / $lastConsoFuel : 'INF';
        $currentApproFuelProgress = ($lastApproFuel !== 0) ? ($currentApproFuel - $lastApproFuel) / $lastApproFuel : 'INF';

        return array(
            'currentConsoFuel'         => $currentConsoFuel,
            'currentConsoFuelProgress' => floatval(number_format((float) $currentConsoFuelProgress, 2, '.', '')),
            'currentApproFuel'         => $currentApproFuel,
            'currentApproFuelProgress' => floatval(number_format((float) $currentApproFuelProgress, 2, '.', '')),
            'dayBydayConsoData' => [
                'dateConso'   => $day,
                "consoFuel"   => $consoFuelDayByDay,
                "approFuel"   => $approFuelDayByDay
            ]
        );
    }
}
