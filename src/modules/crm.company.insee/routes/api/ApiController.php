<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE\ROUTES\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use NS2B\SDK\MODULES\BASE\WebhookManager;
use NS2B\SDK\DATABASE\DatabaseSQLite;
use NS2B\SDK\MODULES\CRM\COMPANY\INSEE\CompanyComponent;

class ApiController
{   
    private WebhookManager $webhookManager;
    public function __construct(
        private CompanyComponent $companyComponent=new CompanyComponent(),
        private DatabaseSQLite $db=new DatabaseSQLite(),
        private \stdClass $company=new \stdClass()
    ) {
        $this->webhookManager = new WebhookManager($this->db);
    }

    public function saveContact(Request $request,...$params): Response
    {
        error_log('Starting saveContact...');
        try {
            extract($params);
            $requestBody=json_decode($request->getContent(), true);
            error_log('Processing saveContact...');
            $result=$B24->core->call('crm.contact.add',[
                "fields"=>$requestBody
            ])->getResponseData()->getResult()["result"];
            return new JsonResponse($result,200);
        } catch (\Exception $e) {
            error_log('Error response saveContact...');
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'result'=>$result
            ], 400);
        }
    }


    public function saveCompany(Request $request,...$params): Response
    {
        error_log('Starting saveCompany');
        try {
            extract($params);
            if(!isset($siret) && !is_numeric($siret)){
                throw new \Exception('A nuemric Siret is required');
            }
            error_log('Processing saveCompany...');
            $company=$this->companyComponent->setCustomSiret($siret)->getCompanyFromInsee()->getCompanyFromAnnuaire()->getCollection()->currentCompany;
            $fields=$this->companyComponent->getCollection()->currentCompany["fields"]["bitrix"];
            
            if($company["annuaire"]?->siege->siret==$siret){
                $company["saveToB24"]=[
                    $fields['siret']=>$company["annuaire"]?->siege->siret??'',
                    $fields['siren']=>substr($company["annuaire"]?->siege->siret,0,9)??'',
                    $fields['codePostale_mention']=>$company["annuaire"]?->siege->code_postal??'',
                    $fields['commune_mention']=>$company["annuaire"]?->siege->libelle_commune??'',
                    $fields['rue_mention']=>($company["annuaire"]?->siege->numero_voie.' '.$company["annuaire"]?->siege->type_voie.' '.$company["annuaire"]?->siege->libelle_voie)??'',
                    $fields['ville_mention']=>$company["annuaire"]?->siege?->libelle_commune??'',
                    $fields['nom']=>$company["annuaire"]?->nom_complet??'',
                    $fields['activite']=>$company["annuaire"]?->activite_principale??'',
                    $fields['naf']=>($company["annuaire"]?->activite_principale.' '.$company["annuaire"]?->libelle_activite_principale)??'',
                    $fields['ca']=>'',
                    $fields["adresse"]=>$company["annuaire"]?->adresse??'',
                    $fields['zoneNS2B_enum']=>'',
                    $fields['statut_enum']=>'',
                    $fields['activite_enum']=>'',
                    $fields['email']=>'',
                    $fields['tel']=>'',
                    $fields['famille_enum']=>'',
                    $fields['nom_mention']=>$company["annuaire"]?->nom_complet??'',
                    $fields['forme_juridique_mention']=>$company["annuaire"]?->siege->forme_juridique??'',
                    $fields['ca_mention']=>'',
                    $fields['siret_mention']=>$company["annuaire"]?->siret??'',
                    $fields['naf_mention']=>($company["annuaire"]?->activite_principale.' '.$company["annuaire"]?->libelle_activite_principale)??'',
                    $fields['rcs_mention']=>'',
                    $fields['tva_intracommunautaire_mention']=>'',
                    $fields['date_clôture_mention']=>$company["annuaire"]?->activite_principale??'',
                    $fields['identifiant_association_mention']=>'',
                    $fields['pappersUrl_mention']=>'',
                ];
            }elseif(!empty($etablissements=$company["annuaire"]?->matching_etablissements)){
                
                foreach($etablissements as $etablissement){
                    if($etablissement?->siret==$siret){
                        $company["saveToB24"]=[
                            $fields['siret']=>$etablissement?->siret??'',
                            $fields['codePostale_mention']=>$etablissement?->code_postale??'',
                            $fields['commune_mention']=>$etablissement?->libelle_commune??'',
                            $fields['rue_mention']=>$etablissement?->adresse??'',
                            $fields['ville_mention']=>$etablissement?->libelle_commune??'',
                            $fields['siren']=>substr($etablissement?->siret,0,9)??'',
                            $fields['nom']=>$etablissement?->nom_complet??'',
                            $fields['activite']=>$etablissement?->activite_principale??'',
                            $fields['naf']=>($etablissement?->activite_principale.' '.$etablissement?->libelle_activite_principale)??'',
                            $fields['ca']=>'',
                            $fields["adresse"]=>$etablissement?->adresse??'',
                            $fields['zoneNS2B_enum']=>'',
                            $fields['statut_enum']=>'',
                            $fields['activite_enum']=>'',
                            $fields['email']=>'',
                            $fields['tel']=>'',
                            $fields['famille_enum']=>'',
                            $fields['nom_mention']=>$etablissement?->nom_complet??'',
                            $fields['forme_juridique_mention']=>$etablissement?->forme_juridique??'',
                            $fields['ca_mention']=>'',
                            $fields['siret_mention']=>$etablissement?->siret??'',
                            $fields['naf_mention']=>($etablissement?->activite_principale.' '.$etablissement?->libelle_activite_principale)??'',
                            $fields['rcs_mention']=>'',
                            $fields['tva_intracommunautaire_mention']=>'',
                            $fields['date_clôture_mention']=>$etablissements?->activite_principale??'',
                            $fields['identifiant_association_mention']=>'',
                            $fields['pappersUrl_mention']=>'',
                        ];
                    }
                }
                
                unset($company["saveToB24"][""]);
            }else{
                throw new \Exception('Siret not found in annuaire');
            }
            error_log('Success response saveCompany...');
            $result=$this->companyComponent->addCompanyToBitrix($company["saveToB24"]);
            return new JsonResponse($result,200);
        } catch (\Exception $e) {
            error_log('Error response saveCompany...');
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage(),
                'result'=>$result
            ], 400);
        }
    }
    public function saveCompanyToDb(Request $request, ...$params): Response
    {   
        try {
            $database=new DatabaseSQLite('crmcompanyinsee.db');
            $data = json_decode($request->getContent(), true);
            
            // Logique pour sauvegarder une entreprise dans la base de données
            // TODO: Implémenter la logique de sauvegarde

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Entreprise sauvegardée avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getCompany(Request $request, ...$params): Response
    {
        try {
            extract($params);
            $this->company->currentCompany["bitrix"]=$this->companyComponent->getCompanyWithSiretFromBitrix($siret)->getCollection()->currentCompany;
            if(empty($this->company->currentCompany["bitrix"])||empty($this->company->currentCompany["bitrix"]["ID"])){
                throw new \Exception('Entreprise introuvable dans bitrix');
            }
                $this->company->currentCompany['exists'] = true;
                $this->company->currentCompany["siret"] = $siret;
                return new JsonResponse([
                    'status' => 'success',
                    'data' => $this->company->currentCompany["bitrix"]
                ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function getCompanies(Request $request, ...$params): Response
    {
        try {
            extract($params);
            $posts = json_decode($request->getContent(), true);
            // error_log(print_r($posts, true)); // Log the received data
            $batch=[];
            if(empty($posts[0]["method"])||!isset($posts[0]["params"])||empty($posts[0]["name"]))
            {
                throw new \Exception('Methode, params ou name manquants ');
            }
            foreach($posts as $post)
            {
                
                $batch[$post["name"]]=$post["method"]."?".http_build_query($post["params"]);
            }

            $result=$B24->core->call('batch',[
                "cmd"=>$batch
            ])->getResponseData()->getResult()["result"];
            $ids=[];
            foreach($result as $key => $value) {
                if(!empty($value[0]["ID"])){
                    $ids[$key]=$value[0]["ID"];
                }
                
            }
            return new JsonResponse(
                [
                    'status' => 'success',
                    'data'=>$ids
                ],200)
                ;
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
         
    }
    public function getAnnuaire(Request $request, ...$params): Response
    {
        try {
            extract($params);
            $this->company->currentCompany=$this->companyComponent->setCustomSiret($siret)->getCompanyFromAnnuaire()->getCollection()->currentCompany;
            if(empty($this->company->currentCompany["annuaire"])){
                throw new \Exception('Entreprise introuvable dans annuaire');
            }
                return new JsonResponse([
                    'status' => 'success',
                    'data' => $this->company->currentCompany["annuaire"]
                ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function updateCompany(Request $request, ...$params): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            // Logique pour mettre à jour une entreprise
            // TODO: Implémenter la logique de mise à jour

            return new JsonResponse([
                'status' => 'success',
                'message' => 'Entreprise mise à jour avec succès'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function saveWebhook(Request $request,...$params): Response
    {   
       
       try {
            $hasBeenSaved=$this->webhookManager->save($request);
            if($hasBeenSaved['status'] !=='success') {
                throw new \Exception($hasBeenSaved['message']);
            }else{
                return new JsonResponse([
                    'status' => 'success',
                    'message' => 'Webhook sauvegardé avec succès.'.$hasBeenSaved['message']
                ], 200);
            }

       } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function deleteWebhook(Request $request,...$params): Response
    {   
        try {
            if(!$this->webhookManager->deleteWebhook()) {
                throw new \Exception('Erreur lors de la suppression du webhook');
            }
            return new JsonResponse([
                'status' => 'success',
                'message' => 'Webhook supprimé avec succès'
            ], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getWebhook(Request $request,...$params): Response
    {   
        try {
            if(empty($webhook=$this->webhookManager->getWebhook())) {
                throw new \Exception('Webhook not found');
            }
            return new JsonResponse([
                'status' => 'success',
                'data' => [
                    'webhook' => $webhook??''
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $webhook.' '.$e->getMessage()
            ], 404);
        }
    }
}
