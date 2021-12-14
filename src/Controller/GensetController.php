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
        $firstDatetimeDataDayRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
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
            ->getResult();
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
        $lastDatetimeDataDayRecord = $manager->createQuery("SELECT MAX(d.totalRunningHours) AS TRH, MAX(d.totalEnergy) AS TEP,
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
            ->getResult();
        // // dump($lastDatetimeDataDayRecord);
        $npsd = 0;
        $trhd = 0;
        $tepd = 0;
        if (count($firstDatetimeDataDayRecord) && count($lastDatetimeDataDayRecord)) {
            $npsd = intval($lastDatetimeDataDayRecord[0]['NPS']) - intval($firstDatetimeDataDayRecord[0]['NPS']);
            $trhd = intval($lastDatetimeDataDayRecord[0]['TRH']) - intval($firstDatetimeDataDayRecord[0]['TRH']);
            $tepd = intval($lastDatetimeDataDayRecord[0]['TEP']) - intval($firstDatetimeDataDayRecord[0]['TEP']);
            // // dump($npsd);
            // // dump($trhd);
            // // dump($tepd);
        }

        $firstDatetimeDataMonthRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
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
            ->getResult();
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
        $lastDatetimeDataMonthRecord = $manager->createQuery("SELECT MAX(d.totalRunningHours) AS TRH, MAX(d.totalEnergy) AS TEP,
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
            ->getResult();

        // // dump($lastDatetimeDataMonthRecord);
        $npsm = 0;
        $trhm = 0;
        $tepm = 0;
        if (count($firstDatetimeDataMonthRecord) && count($lastDatetimeDataMonthRecord)) {
            $npsm = intval($lastDatetimeDataMonthRecord[0]['NPS']) - intval($firstDatetimeDataMonthRecord[0]['NPS']);
            $trhm = intval($lastDatetimeDataMonthRecord[0]['TRH']) - intval($firstDatetimeDataMonthRecord[0]['TRH']);
            $tepm = intval($lastDatetimeDataMonthRecord[0]['TEP']) - intval($firstDatetimeDataMonthRecord[0]['TEP']);
            // // dump($npsm);
            // // dump($trhm);
            // // dump($tepm);
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
        $firstDatetimeDataYearRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalRunningHours,0)) AS TRH, MIN(NULLIF(d.totalEnergy,0)) AS TEP,
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
            ->getResult();

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
        $lastDatetimeDataYearRecord = $manager->createQuery("SELECT MAX(d.totalRunningHours) AS TRH, MAX(d.totalEnergy) AS TEP,
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
            ->getResult();
        // //dump($lastDatetimeDataYearRecord);

        $npsy = 0;
        $trhy = 0;
        $tepy = 0;
        if (count($firstDatetimeDataYearRecord) && count($lastDatetimeDataYearRecord)) {
            $npsy = intval($lastDatetimeDataYearRecord[0]['NPS']) - intval($firstDatetimeDataYearRecord[0]['NPS']);
            $trhy = intval($lastDatetimeDataYearRecord[0]['TRH']) - intval($firstDatetimeDataYearRecord[0]['TRH']);
            $tepy = intval($lastDatetimeDataYearRecord[0]['TEP']) - intval($firstDatetimeDataYearRecord[0]['TEP']);
            // // dump($npsy);
            // // dump($trhy);
            // // dump($tepy);
        }
        // //dump($lastRecord);

        $poe = [];
        $FCD = $manager->createQuery("SELECT AVG(NULLIF(COALESCE(d.fuelInstConsumption,0), 0)) AS FC
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

        $FCM = $manager->createQuery("SELECT AVG(NULLIF(COALESCE(d.fuelInstConsumption,0), 0)) AS FC
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


        $precDayLastTEPRecord = $manager->createQuery("SELECT MAX(d.totalEnergy) AS TEP
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
            ->getResult();
        $prevMonthFirstTEPRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalEnergy,0)) AS TEP
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
            ->getResult();
        $prevYearFirstTEPRecord = $manager->createQuery("SELECT MIN(NULLIF(d.totalEnergy,0)) AS TEP
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
            ->getResult();

        $prev_tepd = 0;
        if (count($precDayLastTEPRecord) && count($precDayFirstTEPRecord)) {
            $prev_tepd = intval($precDayFirstTEPRecord[0]['TEP']) - intval($precDayLastTEPRecord[0]['TEP']);
            // dump($prev_tepd);
        }
        $prev_tepm = 0;
        if (count($prevMonthFirstTEPRecord) && count($prevMonthLastTEPRecord)) {
            $prev_tepm = intval($prevMonthLastTEPRecord[0]['TEP']) - intval($prevMonthFirstTEPRecord[0]['TEP']);
            // dump($prev_tepm);
        }
        $prev_tepy = 0;
        if (count($prevYearFirstTEPRecord) && count($prevYearLastTEPRecord)) {
            $prev_tepy = intval($prevYearLastTEPRecord[0]['TEP']) - intval($prevYearFirstTEPRecord[0]['TEP']);
            // dump($prev_tepy);
        }

        $prev_poe = [];
        if ($prev_tepd > 0) $prev_poe[] = ($FCD[0]['FC'] * 1.0) / $prev_tepd;
        else $prev_poe[] = 0;
        if ($prev_tepm > 0) $prev_poe[] = ($FCM[0]['FC'] * 1.0) / $prev_tepm;
        else $prev_poe[] = 0;
        if ($prev_tepy > 0) $prev_poe[] = ($FCY[0]['FC'] * 1.0) / $prev_tepy;
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
            'TEP'    => [$lastRecord[0]['TEP'] ?? 0, $tepd, $tepm, $tepy],
            'TRH'    => [$lastRecord[0]['TRH'] ?? 0, $trhd, $trhm, $trhy],
            'FC'    => [$FCD[0]['FC'] ?? 0, $FCM[0]['FC'] ?? 0, $FCY[0]['FC'] ?? 0],
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

        $Energy = $manager->createQuery("SELECT SUBSTRING(d.dateTime, 1, 10) AS jour, MAX(d.totalRunningHours) - MIN(NULLIF(d.totalRunningHours, 0)) AS TRH, 
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
            ->getResult();
        // dump($Energy);
        //die();
        foreach ($Energy as $d) {
            $dateE[] = $d['jour'];
            $TRH[] = number_format((float) $d['TRH'], 2, '.', '');
            $TEP[] = number_format((float) $d['TEP'], 2, '.', '');
            $FC[] = number_format((float) $d['FC'], 2, '.', '') ?? 0;
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
                'genpower'  => $smartMod->getPower(),
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

        return $this->json([
            'code'         => 200,
            //'startDate'    => $startDate,
            //'endDate'      => $endDate,
            'date'         => $date,
            'Mix1'            => [$TRH, $TEP, $FC],
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
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date1']);
                //$date = new DateTime('now');

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
                $dataMod->setL12G($paramJSON['L12'])
                    ->setDateTime($date)
                    ->setL13G($paramJSON['L13'])
                    ->setL23G($paramJSON['L23'])
                    ->setL1N($paramJSON['L1'])
                    ->setL2N($paramJSON['L2'])
                    ->setL3N($paramJSON['L3'])
                    ->setL12M($paramJSON['L12M'])
                    ->setL13M($paramJSON['L13M'])
                    ->setL23M($paramJSON['L23M'])
                    ->setI1($paramJSON['I1'])
                    ->setI2($paramJSON['I2'])
                    ->setI3($paramJSON['I3'])
                    ->setFreq($paramJSON['Fr'])
                    ->setIDiff($paramJSON['Id'])
                    ->setFuelLevel($paramJSON['FL'])
                    ->setWaterLevel($paramJSON['WL'])
                    ->setOilLevel($paramJSON['OL'])
                    ->setAirPressure($paramJSON['AP'])
                    ->setOilPressure($paramJSON['OP'])
                    ->setWaterTemperature($paramJSON['WT'])
                    ->setCoolerTemperature($paramJSON['CT'])
                    ->setEngineSpeed($paramJSON['ESD'])
                    ->setBattVoltage($paramJSON['BV'])
                    ->setHoursToMaintenance($paramJSON['HTM'])
                    ->setCg($paramJSON['CG'])
                    ->setCr($paramJSON['CR'])
                    ->setGensetRunning($paramJSON['GenRun'])
                    ->setMainsPresence($paramJSON['MainsPresence'])
                    ->setPresenceWaterInFuel($paramJSON['PWF'])
                    ->setMaintenanceRequest($paramJSON['MRqst'])
                    ->setLowFuel($paramJSON['LowFuel'])
                    ->setOverspeed($paramJSON['Overspeed'])
                    ->setMaxFreq($paramJSON['MaxFr'])
                    ->setMinFreq($paramJSON['MinFr'])
                    ->setMaxVolt($paramJSON['MaxVolt'])
                    ->setMinVolt($paramJSON['MinVolt'])
                    ->setMaxBattVolt($paramJSON['MaxBV'])
                    ->setMinBattVolt($paramJSON['MinBV'])
                    ->setOverload($paramJSON['Overload'])
                    ->setShortCircuit($paramJSON['SC'])
                    ->setMainsIncSeq($paramJSON['MIS'])
                    ->setGensetIncSeq($paramJSON['GIS'])
                    ->setDifferentialIntervention($paramJSON['DIT'])
                    ->setSmartMod($smartMod);


                if (!$isNew) {
                    $BATT = "MINB"; // 0
                    $MAINAB = "MAIAB"; // 1
                    $MAINPR = "MAIPR"; // 1
                    $SPEED = "OVSPD"; // 2
                    $LOAD = "OVLOD"; // 3
                    $VOLT = "MINV"; // 4
                    $FREQ = "MINF"; // 5
                    $GENRUN = "GENR"; // 6
                    $FUEL = "LOFL"; // 7
                    $DIFFC = "DIFFC"; // 8
                    $WATFL = "WATFL"; // 9

                    if ($oldData->getMinBattVolt()  === 0 && $paramJSON['MinBV']  === 1) {
                        $mess = "{\"code\":\"{$BATT}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$BATT}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess' => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getCr()  === 1 && $paramJSON['CR']  === 0) {
                        $mess = "{\"code\":\"{$MAINAB}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$MAINAB}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess'   => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getCr()  === 0 && $paramJSON['CR']  === 1) {
                        $mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess'   => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getOverspeed() === 0 && $paramJSON['Overspeed']  === 1) {
                        $mess = "{\"code\":\"{$SPEED}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$SPEED}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess' => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getOverload() === 0 && $paramJSON['Overload'] === 1) {
                        $mess = "{\"code\":\"{$LOAD}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$LOAD}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess' => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getMinVolt() === 0 && $paramJSON['MinVolt'] === 1) {
                        $mess = "{\"code\":\"{$VOLT}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$VOLT}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess' => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getMinFreq() === 0 && $paramJSON['MinFr'] === 1) {
                        $mess = "{\"code\":\"{$FREQ}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$FREQ}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward(
                            'App\Controller\GensetController::sendToAlarmController',
                            [
                                'mess' => $mess,
                                'modId'  => $smartMod->getModuleId(),
                            ]
                        );
                    }
                    if ($oldData->getGensetRunning() === 0 && $paramJSON['GenRun'] === 1) {
                        $mess = "{\"code\":\"{$GENRUN}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$GENRUN}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                            'mess'   => $mess,
                            'modId'  => $smartMod->getModuleId(),
                        ]);
                    }
                    if ($oldData->getLowFuel() === 0 && $paramJSON['LowFuel'] === 1) {
                        $mess = "{\"code\":\"{$FUEL}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$FUEL}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                            'mess' => $mess,
                            'modId'  => $smartMod->getModuleId(),
                        ]);
                    }
                    if ($oldData->getDifferentialIntervention() === 0 && $paramJSON['DIT'] === 1) {
                        $mess = "{\"code\":\"{$DIFFC}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$DIFFC}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                            'mess' => $mess,
                            'modId'  => $smartMod->getModuleId(),
                        ]);
                    }
                    if ($oldData->getPresenceWaterInFuel() === 0 && $paramJSON['PWF'] === 1) {
                        $mess = "{\"code\":\"{$WATFL}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                        //$mess = "{\"code\":\"{$WATFL}\",\"date\":\"{$paramJSON['date1']}\"}";

                        $response = $this->forward('App\Controller\GensetController::sendToAlarmController', [
                            'mess' => $mess,
                            'modId'  => $smartMod->getModuleId(),
                        ]);
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
                $date = new DateTime('now');
                // //dump($date);
                //die();

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
                }

                // //dump($datetimeData);
                //die();
                //Insertion de la nouvelle datetimeData dans la BDD
                $manager->persist($datetimeData);
                $manager->flush();
            }

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


                if ($smartMod->getSite()) $site = $smartMod->getSite();
                else {
                    foreach ($smartMod->getZones() as $zone) {
                        $site = $zone->getSite();
                        if ($site) break;
                    }
                }

                if ($alarmCode->getType() !== 'FUEL') $message = $alarmCode->getLabel() . ' sur <<' . $smartMod->getName() . '>> du site ' . $site->getName() . ' survenu(e) le ' . $date->format('d/m/Y à H:i:s');
                else if ($alarmCode->getType() === 'FUEL') {
                    $data = clone $smartMod->getNoDatetimeData();
                    if ($alarmCode->getCode() === 'GENR') $message = $alarmCode->getLabel() . ' du site ' . $site->getName() . ' survenu(e) le ' . $date->format('d/m/Y à H:i:s') . ' avec un niveau de Fuel de ' . $data->getFuelLevel() . '%';
                    else $message = $alarmCode->getLabel() . ' du site ' . $site->getName() . ' survenu(e) le ' . $date->format('d/m/Y à H:i:s');
                }

                foreach ($site->getContacts() as $contact) {
                    $messageBus->dispatch(new UserNotificationMessage($contact->getId(), $message, $alarmCode->getMedia(), $alarmCode->getAlerte()));
                    //$messageBus->dispatch(new UserNotificationMessage($contact->getId(), $message, 'SMS', ''));
                }

                $adminUsers = [];
                $Users = $manager->getRepository('App:User')->findAll();
                foreach ($Users as $user) {
                    if ($user->getRoles()[0] === 'ROLE_SUPER_ADMIN') $adminUsers[] = $user;
                }
                foreach ($adminUsers as $user) {
                    $messageBus->dispatch(new UserNotificationMessage($user->getId(), $message, 'Email', $alarmCode->getAlerte()));
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
}
