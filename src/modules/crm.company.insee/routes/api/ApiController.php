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
    public function saveCompany(Request $request,...$params): Response
    {
        try {
            extract($params);
            $company=$this->companyComponent->setCustomSiret($siret)->getCompanyFromAnnuaire()->getCompanyFromInsee()->getCompanyFromBodacc()->setBodaccCustomRecord()->getCollection()->currentCompany;
            

            if(!empty($etablissements=$company["annuaire"]?->matching_etablissements)){
                $fields=$this->companyComponent->getCollection()->currentCompany["fields"]["bitrix"];
                $company["saveToB24"]=[
                    $fields['siret']=>$etablissements[0]?->siret,
                    $fields['codePostale_mention']=>$etablissements[0]?->code_postale,
                    $fields['commune_mention']=>$etablissements[0]?->libelle_commune,
                    $fields['rue_mention']=>$etablissements[0]?->adresse,
                    $fields['ville_mention']=>$etablissements[0]?->libelle_commune,
                    $fields['siren']=>substr($etablissements[0]?->siret,0,9),
                    $fields['nom']=>$etablissements[0]?->nom_complet,
                    $fields['activite']=>$etablissements[0]?->activite_principale,
                    $fields['naf']=>$etablissements[0]?->activite_principale.' '.$etablissements[0]?->libelle_activite_principale,
                    $fields['ca']=>'',
                    $fields["adresse"]=>$etablissements[0]?->adresse,
                    $fields['zoneNS2B_enum']=>'',
                    $fields['statut_enum']=>'',
                    $fields['activite_enum']=>'',
                    $fields['email']=>'',
                    $fields['tel']=>'',
                    $fields['famille_enum']=>'',
                    $fields['nom_mention']=>$etablissements[0]?->nom_complet,
                    $fields['forme_juridique_mention']=>$etablissements[0]?->forme_juridique,
                    $fields['ca_mention']=>'',
                    $fields['siret_mention']=>$etablissements[0]?->siret,
                    $fields['naf_mention']=>$etablissements[0]?->activite_principale.' '.$etablissements[0]?->libelle_activite_principale,
                    $fields['rcs_mention']=>'',
                    $fields['tva_intracommunautaire_mention']=>'',
                    $fields['date_clôture_mention']=>$etablissements[0]?->activite_principale,
                    $fields['identifiant_association_mention']=>'',
                    $fields['pappersUrl_mention']=>'',
                ];
                unset($company["saveToB24"][""]);
            }
            $result=$this->companyComponent->addCompanyToBitrix($company["saveToB24"]);
            return new JsonResponse($result,200);
        } catch (\Exception $e) {
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
