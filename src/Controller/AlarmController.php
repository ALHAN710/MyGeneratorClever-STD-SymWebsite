<?php

namespace App\Controller;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AlarmController extends ApplicationController
{
    /**
     * @Route("/alarm", name="alarm")
     */
    public function index(): Response
    {
        return $this->render('alarm/index.html.twig', [
            'controller_name' => 'AlarmController',
        ]);
    }

    /**
     * Permet de mettre à jour le rapport d'alarme d'une zone
     *
     * @Route("/update/alarm/report", name="update_alarm_report")
     * 
     * @param [SmartMod] $smartMod
     * @param EntityManagerInterface $manager
     * @return Response
     */
    public function updateAlarmReport(EntityManagerInterface $manager, Request $request): Response
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());

        //$type = $paramJSON['type'];
        //$smartMod = $manager->getRepository('App:Zone')->findOneBy(['id' => $paramJSON['id']]);
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

        if ($zone) {

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


            $AlarmReport = $manager->createQuery("SELECT al.label AS Label, COUNT(ar.id) AS Occurence
                                                FROM App\Entity\AlarmReporting ar
                                                JOIN ar.alarm al 
                                                JOIN ar.smartMod sm 
                                                WHERE sm.id IN (SELECT stm.id FROM App\Entity\SmartMod stm JOIN stm.zones zn WHERE zn.id = :zoneId)
                                                AND ar.createdAt BETWEEN :startDate AND :endDate
                                                AND sm.levelZone = 2
                                                GROUP BY Label    
                                                ORDER BY Label DESC                                             
                                                ")
                ->setParameters(array(
                    'startDate'  => $startDate->format('Y-m-d H:i:s'),
                    'endDate'    => $endDate->format('Y-m-d H:i:s'),
                    'zoneId'     => $zone->getId(),
                    //'smartModId'   => $smartMod->getId()
                ))
                ->getResult();
            dump($AlarmReport);

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

            ], 200);
        }

        return $this->json([
            'code'         => 500,
        ], 500);
    }
}
