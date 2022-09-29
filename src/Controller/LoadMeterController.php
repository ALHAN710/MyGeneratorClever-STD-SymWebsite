<?php

namespace App\Controller;

use App\Entity\AlarmReporting;
use App\Message\UserNotificationMessage;
use Faker;
use DateTime;
use App\Entity\Zone;
use App\Entity\SmartMod;
use App\Entity\LoadDataEnergy;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ApplicationController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;

//use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LoadMeterController extends ApplicationController
{
    /**
     * @Route("/load/meter/{smartMod<\d+>}/{zone<\d+>}", name="load_meter")
     * 
     * @IsGranted("ROLE_USER")
     * 
     */
    public function index(SmartMod $smartMod, Zone $zone, EntityManagerInterface $manager): Response
    {
        // //dump($id);

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
     * @return JsonResponse
     */
    public function loadDataEnergy_add($modId, EntityManagerInterface $manager, Request $request)
    {
        //Récupération et vérification des paramètres au format JSON contenu dans la requête
        $paramJSON = $this->getJSONRequest($request->getContent());
        // //dump($paramJSON);
        // //dump($content);
        //die();

        $datetimeData = new LoadDataEnergy();

        //Recherche du module dans la BDD
        $smartMod = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $modId]);


        if ($smartMod != null) { // Test si le module existe dans notre BDD
            //data:{"date": "2020-03-20 12:15:00", "sa": 1.2, "sb": 0.7, "sc": 0.85, "va": 225, "vb": 230, "vc": 231, "s3ph": 2.75, "kWh": 1.02, "kvar": 0.4}
            // //dump($smartMod);//Affiche le module
            //die();

            //$date = new DateTime($paramJSON['date']);

            // //dump($date);
            //die();
            $alert = '';
            if ($smartMod->getModType() == 'Load Meter' && $smartMod->getSubType() == 'Central Meter') {
                $nbLoad = 2;
                $config = json_decode($smartMod->getConfiguration(), true);
                if($config) $nbLoad = array_key_exists("nbLoad", $config) ? $config['nbLoad'] : 2;//Temps en minutes converti en heure
                //if ($this->getParameter('app.env') === "dev") dd($nbLoad);

                //Recherche des modules dans la BDD
                $mod = [];
                $data = [];
                for ($i = 0; $i < $nbLoad; $i++) {
                    $mod[] = $manager->getRepository('App:SmartMod')->findOneBy(['moduleId' => $smartMod->getModuleId() . "_{$i}"]);
                    $data[] = new LoadDataEnergy();
                }

                //Paramétrage des champs de la nouvelle LoadDataEnergy aux valeurs contenues dans la requête du module
                if (array_key_exists("date", $paramJSON)) {

                    //Récupération de la date dans la requête et transformation en object de type Date au format date SQL
//                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                    $date = new DateTime('now');
//                    if($paramJSON['date'] !== '2000-01-01 00:00:00') $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
//                    else $date = new DateTime('now', new DateTimeZone('Africa/Douala'));

                    for ($i = 0; $i < $nbLoad; $i++) {
                        $data[$i]->setDateTime($date);
                    }

                    if ($smartMod->getNbPhases() === 1) {
                        if (array_key_exists("Cosfi", $paramJSON)) {
                            if (count($paramJSON['Cosfi']) >= 3) {
                                $firstData->setCosfi($paramJSON['Cosfi'][0]);
                                $secondData->setCosfi($paramJSON['Cosfi'][1]);
                            }
                        }
                        if (array_key_exists("Cosfimin", $paramJSON)) {
                            if (count($paramJSON['Cosfimin']) >= 3) {
                                $firstData->setCosfimin($paramJSON['Cosfimin'][0]); // En kW
                                $secondData->setCosfimin($paramJSON['Cosfimin'][1]); // En kW
                                //$GensetData->setCosfimin($paramJSON['Cosfimin'][1]); // En kW
                                $loadSiteData->setCosfimin($paramJSON['Cosfimin'][2]); // En kW
                            }
                        }
                        if (array_key_exists("Va", $paramJSON)) {
                            if (count($paramJSON['Va']) >= 3) {
                                $firstData->setVamoy($paramJSON['Va'][0]);
                                $secondData->setVamoy($paramJSON['Va'][1]);
                                //// $GensetData->setVamoy($paramJSON['Va'][1]);
                                $loadSiteData->setVamoy($paramJSON['Va'][2]);
                            }
                        }

                        if (array_key_exists("P", $paramJSON)) {
                            if (count($paramJSON['P']) >= 3) {
                                $firstData->setPmoy($paramJSON['P'][0]); // En kWatts
                                $secondData->setPmoy($paramJSON['P'][1]); // En kWatts
                                //$GensetData->setP($paramJSON['P'][1]); // En kWatts
                                $loadSiteData->setPmoy($paramJSON['P'][2]); // En kWatts
//                                dd($smartMod->getSite()->getPowerSubscribed());
                                if($oldPmoy !== null && $smartMod->getSite()->getPowerSubscribed()){
                                    $Psous = $smartMod->getSite()->getPowerSubscribed();
                                    if($paramJSON['P'][2] > $Psous && $oldPmoy < $Psous){
//                                        dump($paramJSON['P'][2]);
//                                        dd($oldPmoy);
                                    }
                                }
                            }
                        }
                        if (array_key_exists("Pmax", $paramJSON)) {
                            if (count($paramJSON['Pmax']) >= 3) {
                                $firstData->setPmax($paramJSON['Pmax'][0]); // En kW
                                $secondData->setPmax($paramJSON['Pmax'][1]); // En kW
                                //$GensetData->setPmax($paramJSON['Pmax'][1]); // En kW
                                $loadSiteData->setPmax($paramJSON['Pmax'][2]); // En kW
                            }
                        }
                        if (array_key_exists("Q", $paramJSON)) {
                            if (count($paramJSON['Q']) >= 3) {
                                $firstData->setQmoy($paramJSON['Q'][0]); // En kVAR
                                $secondData->setQmoy($paramJSON['Q'][1]); // En kVAR
                                //// $GensetData->setQmoy($paramJSON['Q'][1]); // En kVAR
                                $loadSiteData->setQmoy($paramJSON['Q'][2]); // En kVAR

                            }
                        }
                        if (array_key_exists("S", $paramJSON)) {
                            if (count($paramJSON['S']) >= 3) {
                                $firstData->setSmoy($paramJSON['S'][0]); // En kVA
                                $secondData->setSmoy($paramJSON['S'][1]); // En kVA
                                //$GensetData->setSmoy($paramJSON['S'][1]); // En kVA
                                $loadSiteData->setSmoy($paramJSON['S'][2]); // En kVA
                            }
                        }
                        if (array_key_exists("Ea", $paramJSON)) {
                            if (count($paramJSON['Ea']) >= 3) {
                                $firstData->setEa($paramJSON['Ea'][0]); // En kWh
                                $secondData->setEa($paramJSON['Ea'][1]); // En kWh
                                //$GensetData->setTotalEnergy($paramJSON['Ea'][1]); // En kWh
                                $loadSiteData->setEa($paramJSON['Ea'][2]); // En kWh

                            }
                        }
                        if (array_key_exists("Er", $paramJSON)) {
                            if (count($paramJSON['Er']) >= 3) {
                                $firstData->setEr($paramJSON['Er'][0]); // En kVARh
                                $secondData->setEr($paramJSON['Er'][1]); // En kVARh
                                // $GensetData->setEr($paramJSON['Er'][1]); // En kVARh
                                $loadSiteData->setEr($paramJSON['Er'][2]); // En kVARh

                            }
                        }
                    }
                    else if ($smartMod->getNbPhases() === 3) {
                        if (array_key_exists("Va", $paramJSON)) {
                            if (count($paramJSON['Va']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setVamoy($paramJSON['Va'][$i]);
                                }

                                /*$data0->setVamoy($paramJSON['Va'][0]);
                                $data1->setVamoy($paramJSON['Va'][1]);
                                $data2->setVamoy($paramJSON['Va'][2]);
                                $data3->setVamoy($paramJSON['Va'][3]);
                                $data4->setVamoy($paramJSON['Va'][4]);
                                $data5->setVamoy($paramJSON['Va'][5]);*/

                            }
                        }
                        if (array_key_exists("Vb", $paramJSON)) {
                            if (count($paramJSON['Vb']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setVbmoy($paramJSON['Vb'][$i]);
                                }

                                /*$data0->setVbmoy($paramJSON['Vb'][0]);
                                $data1->setVbmoy($paramJSON['Vb'][1]);
                                $data2->setVbmoy($paramJSON['Vb'][2]);
                                $data3->setVbmoy($paramJSON['Vb'][3]);
                                $data4->setVbmoy($paramJSON['Vb'][4]);
                                $data5->setVbmoy($paramJSON['Vb'][5]);*/

                            }
                        }
                        if (array_key_exists("Vc", $paramJSON)) {
                            if (count($paramJSON['Vc']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setVcmoy($paramJSON['Vc'][$i]);
                                }

                                /*$data0->setVcmoy($paramJSON['Vc'][0]);
                                $data1->setVcmoy($paramJSON['Vc'][1]);
                                $data2->setVcmoy($paramJSON['Vc'][2]);
                                $data3->setVcmoy($paramJSON['Vc'][3]);
                                $data4->setVcmoy($paramJSON['Vc'][4]);
                                $data5->setVcmoy($paramJSON['Vc'][5]);*/

                            }
                        }
                        if (array_key_exists("Pa", $paramJSON)) {
                            if (count($paramJSON['Pa']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPamoy($paramJSON['Pa'][$i]); // En kW
                                }

                                /*$data0->setPamoy($paramJSON['Pa'][0]); // En kW
                                $data1->setPamoy($paramJSON['Pa'][1]); // En kW
                                $data2->setPamoy($paramJSON['Pa'][2]); // En kW
                                $data3->setPamoy($paramJSON['Pa'][3]); // En kW
                                $data4->setPamoy($paramJSON['Pa'][4]); // En kW
                                $data5->setPamoy($paramJSON['Pa'][5]); // En kW*/

                            }
                        }
                        if (array_key_exists("Pb", $paramJSON)) {
                            if (count($paramJSON['Pb']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPbmoy($paramJSON['Pb'][$i]); // En kW
                                }

                                /*$data0->setPbmoy($paramJSON['Pb'][0]); // En kW
                                $data1->setPbmoy($paramJSON['Pb'][1]); // En kW
                                $data2->setPbmoy($paramJSON['Pb'][2]); // En kW
                                $data3->setPbmoy($paramJSON['Pb'][3]); // En kW
                                $data4->setPbmoy($paramJSON['Pb'][4]); // En kW
                                $data5->setPbmoy($paramJSON['Pb'][5]); // En kW*/

                            }
                        }
                        if (array_key_exists("Pc", $paramJSON)) {
                            if (count($paramJSON['Pc']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPcmoy($paramJSON['Pc'][$i]); // En kW
                                }

                            }
                        }
                        if (array_key_exists("P", $paramJSON)) {
                            if (count($paramJSON['P']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPmoy($paramJSON['P'][$i]); // En kW
                                }

                            }
                        }
                        /*if (array_key_exists("Pamax", $paramJSON)) {
                            if (count($paramJSON['Pamax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPamax($paramJSON['Pamax'][$i]); // En kW
                                }

                            }
                        }
                        if (array_key_exists("Pbmax", $paramJSON)) {
                            if (count($paramJSON['Pbmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPbmax($paramJSON['Pbmax'][$i]); // En kW
                                }

                            }
                        }
                        if (array_key_exists("Pcmax", $paramJSON)) {
                            if (count($paramJSON['Pcmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPcmax($paramJSON['Pcmax'][$i]); // En kW
                                }
                            }
                        }
                        if (array_key_exists("Pmax", $paramJSON)) {
                            if (count($paramJSON['Pmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setPmax($paramJSON['Pmax'][$i]); // En kW
                                }

                            }
                        }*/
                        if (array_key_exists("Sa", $paramJSON)) {
                            if (count($paramJSON['Sa']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setSamoy($paramJSON['Sa'][$i]); // En kVA
                                }

                            }
                        }
                        if (array_key_exists("Sb", $paramJSON)) {
                            if (count($paramJSON['Sb']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setSbmoy($paramJSON['Sb'][$i]); // En kVA
                                }
                            }
                        }
                        if (array_key_exists("Sc", $paramJSON)) {
                            if (count($paramJSON['Sc']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setScmoy($paramJSON['Sc'][$i]); // En kVA
                                }
                            }
                        }
                        if (array_key_exists("S", $paramJSON)) {
                            if (count($paramJSON['S']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setSmoy($paramJSON['S'][$i]); // En kVA
                                }
                            }
                        }
                        /*if (array_key_exists("Samax", $paramJSON)) {
                            if (count($paramJSON['Samax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setSamax($paramJSON['Samax'][$i]); // En kVA
                                }
                            }
                        }
                        if (array_key_exists("Sbmax", $paramJSON)) {
                            if (count($paramJSON['Sbmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setSbmax($paramJSON['Sbmax'][$i]); // En kVA
                                }

                            }
                        }
                        if (array_key_exists("Scmax", $paramJSON)) {
                            if (count($paramJSON['Scmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setScmax($paramJSON['Scmax'][$i]); // En kVA
                                }
                            }
                        }
                        if (array_key_exists("Smax", $paramJSON)) {
                            if (count($paramJSON['Smax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setSmax($paramJSON['Smax'][$i]); // En kVA
                                }

                            }
                        }
                        if (array_key_exists("Qa", $paramJSON)) {
                            if (count($paramJSON['Qa']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQamoy($paramJSON['Qa'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Qb", $paramJSON)) {
                            if (count($paramJSON['Qb']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQbmoy($paramJSON['Qb'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Qc", $paramJSON)) {
                            if (count($paramJSON['Qc']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQcmoy($paramJSON['Qc'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Q", $paramJSON)) {
                            if (count($paramJSON['Q']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQmoy($paramJSON['Q'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Qamax", $paramJSON)) {
                            if (count($paramJSON['Qamax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQamax($paramJSON['Qamax'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Qbmax", $paramJSON)) {
                            if (count($paramJSON['Qbmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQbmax($paramJSON['Qbmax'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Qcmax", $paramJSON)) {
                            if (count($paramJSON['Qcmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQcmax($paramJSON['Qcmax'][$i]); // En kVAR
                                }
                            }
                        }
                        if (array_key_exists("Qmax", $paramJSON)) {
                            if (count($paramJSON['Qmax']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setQmax($paramJSON['Qmax'][$i]); // En kVAR
                                }
                            }
                        }*/
                        if (array_key_exists("Cosfia", $paramJSON)) {
                            if (count($paramJSON['Cosfia']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfia($paramJSON['Cosfia'][$i]);
                                }
                            }
                        }
                        if (array_key_exists("Cosfib", $paramJSON)) {
                            if (count($paramJSON['Cosfib']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfib($paramJSON['Cosfib'][$i]);
                                }
                            }
                        }
                        if (array_key_exists("Cosfic", $paramJSON)) {
                            if (count($paramJSON['Cosfic']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfic($paramJSON['Cosfic'][$i]);
                                }
                            }
                        }
                        if (array_key_exists("Cosfi", $paramJSON)) {
                            if (count($paramJSON['Cosfi']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfi($paramJSON['Cosfi'][$i]);
                                }
                            }
                        }
                        /*if (array_key_exists("Cosfiamin", $paramJSON)) {
                            if (count($paramJSON['Cosfiamin']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfiamin($paramJSON['Cosfiamin'][$i]);
                                }
                            }
                        }
                        if (array_key_exists("Cosfibmin", $paramJSON)) {
                            if (count($paramJSON['Cosfibmin']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfibmin($paramJSON['Cosfibmin'][$i]);
                                }
                            }
                        }
                        if (array_key_exists("Cosficmin", $paramJSON)) {
                            if (count($paramJSON['Cosficmin']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosficmin($paramJSON['Cosficmin'][$i]);
                                }
                            }
                        }
                        if (array_key_exists("Cosfimin", $paramJSON)) {
                            if (count($paramJSON['Cosfimin']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setCosfimin($paramJSON['Cosfimin'][$i]);
                                }
                            }
                        }*/
                        if (array_key_exists("Eaa", $paramJSON)) {
                            if (count($paramJSON['Eaa']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setEaa($paramJSON['Eaa'][$i]); // En kWh
                                }
                            }
                        }
                        if (array_key_exists("Eab", $paramJSON)) {
                            if (count($paramJSON['Eab']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setEab($paramJSON['Eab'][$i]); // En kWh
                                }
                            }
                        }
                        if (array_key_exists("Eac", $paramJSON)) {
                            if (count($paramJSON['Eac']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setEac($paramJSON['Eac'][$i]); // En kWh
                                }
                            }
                        }
                        if (array_key_exists("Ea", $paramJSON)) {
                            if (count($paramJSON['Ea']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setEa($paramJSON['Ea'][$i]); // En kWh
                                }
                            }
                        }
                        if (array_key_exists("Era", $paramJSON)) {
                            if (count($paramJSON['Era']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setEra($paramJSON['Era'][$i]); // En kVARh
                                }
                            }
                        }
                        if (array_key_exists("Erb", $paramJSON)) {
                            if (count($paramJSON['Erb']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setErb($paramJSON['Erb'][$i]); // En kVARh
                                }
                            }
                        }
                        if (array_key_exists("Erc", $paramJSON)) {
                            if (count($paramJSON['Erc']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setErc($paramJSON['Erc'][$i]); // En kVARh
                                }
                            }
                        }
                        if (array_key_exists("Er", $paramJSON)) {
                            if (count($paramJSON['Er']) >= 6) {
                                for ($i = 0; $i < $nbLoad; $i++) {
                                    $data[$i]->setEr($paramJSON['Er'][$i]); // En kVARh
                                }
                            }
                        }
                    }

                    for ($i = 0; $i < $nbLoad; $i++) {
                        if ($mod[$i]) {
                            $data[$i]->setSmartMod($mod[$i]);
                            $manager->persist($data[$i]);
                        }
                    }

                    if ($this->getParameter('app.env') === "dev") dd($paramJSON);
                    $manager->flush();
                }

                return $this->json([
                    'code' => 200,
                    'server Time' => $date->format('Y-m-d H:i:s'),
                    'received' => $paramJSON

                ], 200);
            }
            else if ($smartMod->getModType() == 'Load Meter' || $smartMod->getModType() == 'AVR') {
                //Paramétrage des champs de la nouvelle LoadDataEnergy aux valeurs contenues dans la requête du module
                if (array_key_exists("date", $paramJSON)) {
                    //Récupération de la date dans la requête et transformation en object de type Date au format date SQL
//                    $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                    $date = new DateTime('now');
                    //if($paramJSON['date'] !== '2000-01-01 00:00:00') $date = DateTime::createFromFormat('Y-m-d H:i:s', $paramJSON['date']);
                    //else $date = new DateTime('now');

                    //Test si un enregistrement correspond à cette date pour ce module
                    $data = $manager->getRepository('App:LoadDataEnergy')->findOneBy(['dateTime' => $date, 'smartMod' => $smartMod->getId()]);
                    if ($data) {
                        return $this->json([
                            'code'    => 200,
                            'message' => 'data already saved'

                        ], 200);
                    }
                    $datetimeData->setDateTime($date)
                        ->setSmartMod($smartMod);
                    if ($smartMod->getNbPhases() === 1) {
                        if (array_key_exists("Cosfi", $paramJSON)) {
                            if ($paramJSON['Cosfi'] == 0 && $paramJSON['Va'] > 380) {
                                return $this->json([
                                    'code' => 200,
                                    'received' => $paramJSON,
                                    'message'  => 'Bad'

                                ], 200);
                            }
                            $datetimeData->setCosfi($paramJSON['Cosfi']);
                        }
                        if (array_key_exists("Va", $paramJSON)) {
                            $datetimeData->setVamoy($paramJSON['Va']);
                        }

                        if (array_key_exists("P", $paramJSON)) {
                            $datetimeData->setPmoy($paramJSON['P'] / 1000.0);
                        }
                        if (array_key_exists("S", $paramJSON)) {
                            $datetimeData->setSmoy($paramJSON['S'] / 1000.0);
                        }
                        if (array_key_exists("Ea", $paramJSON)) {
                            $datetimeData->setEa($paramJSON['Ea'] / 1000.0);
                        }
                        if (array_key_exists("Er", $paramJSON)) {
                            $datetimeData->setEr($paramJSON['Er'] / 1000.0);
                        }
                    }
                    else if ($smartMod->getNbPhases() === 3) {
                        if (array_key_exists("Va", $paramJSON)) {
                            $datetimeData->setVamoy($paramJSON['Va']);
                        }
                        if (array_key_exists("Vb", $paramJSON)) {
                            $datetimeData->setVbmoy($paramJSON['Vb']);
                        }
                        if (array_key_exists("Vc", $paramJSON)) {
                            $datetimeData->setVcmoy($paramJSON['Vc']);
                        }
                        /*if (array_key_exists("Vab", $paramJSON)) {
                            $datetimeData->setVabmoy($paramJSON['Vab']);
                        }
                        if (array_key_exists("Vac", $paramJSON)) {
                            $datetimeData->setVacmoy($paramJSON['Vac']);
                        }
                        if (array_key_exists("Vbc", $paramJSON)) {
                            $datetimeData->setVbcmoy($paramJSON['Vbc']);
                        }*/
                        if (array_key_exists("Pa", $paramJSON)) {
                            $datetimeData->setPamoy($paramJSON['Pa']);
                        }
                        if (array_key_exists("Pb", $paramJSON)) {
                            $datetimeData->setPbmoy($paramJSON['Pb']);
                        }
                        if (array_key_exists("Pc", $paramJSON)) {
                            $datetimeData->setPcmoy($paramJSON['Pc']);
                        }
                        if (array_key_exists("P", $paramJSON)) {
                            $datetimeData->setPmoy($paramJSON['P']);
                        }
                        if (array_key_exists("Sa", $paramJSON)) {
                            $datetimeData->setSamoy($paramJSON['Sa']);
                        }
                        if (array_key_exists("Sb", $paramJSON)) {
                            $datetimeData->setSbmoy($paramJSON['Sb']);
                        }
                        if (array_key_exists("Sc", $paramJSON)) {
                            $datetimeData->setScmoy($paramJSON['Sc']);
                        }
                        if (array_key_exists("S", $paramJSON)) {
                            $datetimeData->setSmoy($paramJSON['S']);
                        }
                        /*if (array_key_exists("Qa", $paramJSON)) {
                            $datetimeData->setQamoy($paramJSON['Qa']);
                        }
                        if (array_key_exists("Qb", $paramJSON)) {
                            $datetimeData->setQbmoy($paramJSON['Qb']);
                        }
                        if (array_key_exists("Qc", $paramJSON)) {
                            $datetimeData->setQcmoy($paramJSON['Qc']);
                        }
                        if (array_key_exists("Q", $paramJSON)) {
                            $datetimeData->setSmoy($paramJSON['Q']);
                        }*/
                        if (array_key_exists("Cosfia", $paramJSON)) {
                            $datetimeData->setCosfia($paramJSON['Cosfia']);
                        }
                        if (array_key_exists("Cosfib", $paramJSON)) {
                            $datetimeData->setCosfib($paramJSON['Cosfib']);
                        }
                        if (array_key_exists("Cosfic", $paramJSON)) {
                            $datetimeData->setCosfic($paramJSON['Cosfic']);
                        }
                        if (array_key_exists("Cosfi", $paramJSON)) {
                            $datetimeData->setCosfi($paramJSON['Cosfi']);
                        }
                        if (array_key_exists("Eaa", $paramJSON)) {
                            $datetimeData->setEaa($paramJSON['Eaa']);
                        }
                        if (array_key_exists("Eab", $paramJSON)) {
                            $datetimeData->setEab($paramJSON['Eab']);
                        }
                        if (array_key_exists("Eac", $paramJSON)) {
                            $datetimeData->setEac($paramJSON['Eac']);
                        }
                        if (array_key_exists("Ea", $paramJSON)) {
                            $datetimeData->setEa($paramJSON['Ea']);
                        }
                        if (array_key_exists("Era", $paramJSON)) {
                            $datetimeData->setEra($paramJSON['Era']);
                        }
                        if (array_key_exists("Erb", $paramJSON)) {
                            $datetimeData->setErb($paramJSON['Erb']);
                        }
                        if (array_key_exists("Erc", $paramJSON)) {
                            $datetimeData->setErc($paramJSON['Erc']);
                        }
                        if (array_key_exists("Er", $paramJSON)) {
                            $datetimeData->setEr($paramJSON['Er']);
                        }

                        //Gestion des alertes
                        if ($smartMod->getModType() == 'AVR'){
                            $oldData = [];
                            $oldData = $manager->createQuery("SELECT d.dateTime AS dt, d.vamoy AS VA, d.vbmoy AS VB, d.vcmoy AS VC
                                                FROM App\Entity\SmartMod sm
                                                JOIN sm.loadDataEnergies d 
                                                WHERE sm.id = :smartModId
                                                AND d.dateTime = (SELECT max(d1.dateTime) FROM App\Entity\LoadDataEnergy d1 WHERE d1.dateTime LIKE :date AND d1.smartMod = :smartModId)                                                                                                                
                                                ")
                                ->setParameters(array(
                                    //'selDate'      => $dat,
    //                                'date'  => $date->format('Y-m-d H:i:s'),
                                    'date'  => $date->format('Y') . "%",
                                    'smartModId' => $smartMod->getId()
                                ))
                                ->getResult();
                            //dd($oldData);
                            $alert = count($oldData);
                            if (count($oldData) > 0) {
                                /*return $this->json([
                                    'code' => 200,
                                    'VA' => $oldData[0]['VA'],
                                    'VB' => $oldData[0]['VB'],
                                    'VC' => $oldData[0]['VC'],
                                    'app.env' => $this->getParameter('app.env'),

                                ], 200);*/
                                //dd($this->getParameter('app.env'));
                                $ABSL1 = "ABSL1"; // 0
                                $ABSL2 = "ABSL2"; // 1
                                $ABSL3 = "ABSL3"; // 2
                                $CRTL1 = "CRTL1"; // 3
                                $CRTL2 = "CRTL2"; // 4
                                $CRTL3 = "CRTL3"; // 5
                                $SRTL1 = "SRTL1"; // 6
                                $SRTL2 = "SRTL2"; // 7
                                $SRTL3 = "SRTL3"; // 8
                                $CUT = "CUT"; // 9

                                if (array_key_exists("Va", $paramJSON) && array_key_exists("Vb", $paramJSON) && array_key_exists("Vc", $paramJSON)){
                                    if ( ($oldData[0]['VA'] > 0.0 && $paramJSON['Va'] === 0.0) && ($oldData[0]['VB'] > 0.0 && $paramJSON['Vb'] === 0.0) && ($oldData[0]['VC'] > 0.0 && $paramJSON['Vc'] === 0.0) ) {
                                        $mess = "{\"code\":\"{$CUT}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                        //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                        $response = $this->forward(
                                            'App\Controller\GensetController::sendToAlarmController',
                                            [
                                                'mess'  => $mess,
                                                'modId' => $smartMod->getModuleId(),
                                            ]
                                        );

                                        if ($this->getParameter('app.env') === "dev") dd($this->getParameter('app.env'));
                                    }else{
                                        if (array_key_exists("Va", $paramJSON)) {
                                            if ($oldData[0]['VA'] > 0.0 && $paramJSON['Va'] === 0.0) {
                                                $mess = "{\"code\":\"{$ABSL1}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                                //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                                $response = $this->forward(
                                                    'App\Controller\GensetController::sendToAlarmController',
                                                    [
                                                        'mess' => $mess,
                                                        'modId' => $smartMod->getModuleId(),
                                                    ]
                                                );
                                            }
                                            else if($oldData[0]['VA'] > 209 && $paramJSON['Va'] <= 209) {
                                                $mess = "{\"code\":\"{$CRTL1}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                                //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                                $response = $this->forward(
                                                    'App\Controller\GensetController::sendToAlarmController',
                                                    [
                                                        'mess' => $mess,
                                                        'modId' => $smartMod->getModuleId(),
                                                    ]
                                                );
                                            }
                                            else if($oldData[0]['VA'] < 241 && $paramJSON['Va'] >= 241) {
                                                $mess = "{\"code\":\"{$SRTL1}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
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
                                        if (array_key_exists("Vb", $paramJSON)) {
                                            if ($oldData[0]['VB'] > 0.0 && $paramJSON['Vb'] === 0.0) {
                                                $mess = "{\"code\":\"{$ABSL2}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                                //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                                $response = $this->forward(
                                                    'App\Controller\GensetController::sendToAlarmController',
                                                    [
                                                        'mess' => $mess,
                                                        'modId' => $smartMod->getModuleId(),
                                                    ]
                                                );
                                            }
                                            else if($oldData[0]['VB'] > 209 && $paramJSON['Vb'] <= 209) {
                                                $mess = "{\"code\":\"{$CRTL2}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                                //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                                $response = $this->forward(
                                                    'App\Controller\GensetController::sendToAlarmController',
                                                    [
                                                        'mess' => $mess,
                                                        'modId' => $smartMod->getModuleId(),
                                                    ]
                                                );
                                            }
                                            else if($oldData[0]['VB'] < 241 && $paramJSON['Vb'] >= 241) {
                                                $mess = "{\"code\":\"{$SRTL2}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
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
                                        if (array_key_exists("Vc", $paramJSON)) {
                                            if ($oldData[0]['VC'] > 0.0 && $paramJSON['Vc'] === 0.0) {
                                                $mess = "{\"code\":\"{$ABSL3}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                                //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                                $response = $this->forward(
                                                    'App\Controller\GensetController::sendToAlarmController',
                                                    [
                                                        'mess' => $mess,
                                                        'modId' => $smartMod->getModuleId(),
                                                    ]
                                                );
                                            }
                                            else if($oldData[0]['VC'] > 209 && $paramJSON['Vc'] <= 209) {
                                                $mess = "{\"code\":\"{$CRTL3}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
                                                //$mess = "{\"code\":\"{$MAINPR}\",\"date\":\"{$paramJSON['date1']}\"}";

                                                $response = $this->forward(
                                                    'App\Controller\GensetController::sendToAlarmController',
                                                    [
                                                        'mess' => $mess,
                                                        'modId' => $smartMod->getModuleId(),
                                                    ]
                                                );
                                            }
                                            else if($oldData[0]['VC'] < 241 && $paramJSON['Vc'] >= 241) {
                                                $mess = "{\"code\":\"{$SRTL3}\",\"date\":\"{$date->format('Y-m-d H:i:s')}\"}";
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
                                        if ($this->getParameter('app.env') === "dev") dd('app.env : ' . $this->getParameter('app.env'));
                                    }
                                }


                                $alert = 'Alerte Ok';
                                /*dd('alerte ok');
                                return $this->json([
                                    'code' => 200,
                                    'alerte' => 'Ok'

                                ], 200);*/

                            }

                        }
                    }

                    $manager->persist($datetimeData);
                    $manager->flush();
                }

                return $this->json([
                    'code' => 200,
                    'received' => $paramJSON,
                    'alert'    => $alert

                ], 200);
            }

            // //dump($datetimeData);
            //die();
            //Insertion de la nouvelle datetimeData dans la BDD

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
     * @IsGranted("ROLE_USER")
     * 
     * @param [SmartMod] $smartMod
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
            // dump($Energy);

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
                // dump($data);
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
                // dump($data);
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
