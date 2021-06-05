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
            'smartModsProduction' => $smartModsProduction
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
}
