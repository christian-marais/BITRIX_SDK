<?php
namespace NS2B;

require_once __DIR__ . '/../base/CrmActivity.php';

class NSContactMailActivity extends CrmActivity {
    public function getActivities() {
        try {
            if(!$this->hasScope('crm')){
                throw new Exception('Le module ou scope CRM n\'est pas activé');
            }

            $start = ($this->currentPage - 1) * $this->itemsPerPage;
            
            $params = [
                'filter' => [
                    'PROVIDER_ID' => 'CRM_EMAIL',
                    'PROVIDER_TYPE_ID' => 'EMAIL',
                    'OWNER_ID' => 3,
                    'OWNER_TYPE_ID' => 1,
                ],
                'select' => [
                    'ID', 'SUBJECT', 'CREATED', 'LAST_UPDATED', 
                    'START_TIME', 'END_TIME', 'COMPLETED', 
                    'RESPONSIBLE_ID', 'DESCRIPTION'
                ],
                'order' => ['CREATED' => 'DESC'],
                'start' => $start,
                'limit' => $this->itemsPerPage
            ];

            $totalCountParams = $params;
            unset($totalCountParams['start'], $totalCountParams['limit']);
            $totalCount = $this->B24->core->call('crm.activity.list', $totalCountParams)->getResponseData()->getPagination()->getTotal();
            $result = $this->B24->core->call('crm.activity.list', $params)->getResponseData()->getResult();

            $this->activityCollection->activities = $result;
            $this->activityCollection->pagination = [
                'total' => $totalCount,
                'itemsPerPage' => $this->itemsPerPage,
                'currentPage' => $this->currentPage,
                'totalPages' => ceil($totalCount / $this->itemsPerPage)
            ];
            
        } catch (Exception $e) {
            $this->log($e, 'Erreur lors de la récupération des activités mail');
            $this->activityCollection->activities = [];
            $this->activityCollection->pagination = [
                'total' => 0,
                'itemsPerPage' => $this->itemsPerPage,
                'currentPage' => $this->currentPage,
                'totalPages' => 0
            ];
        }
 
        return $this;
    }

    public function renderActivitiesList() {
        $activities = $this->activityCollection->activities;
        $responsibles = [];
        foreach($activities as $key => $activity){
            $this->getResponsible($activity['RESPONSIBLE_ID']);
            $activities[$key]['responsible'] = $this->activityCollection->responsible[0] ?? null;
            $responsibles[] = $this->activityCollection->responsible[0] ?? null;
        }
        $errorMessages = $this->errorMessages;
        $NSContactMailActivityCollection = $this->activityCollection;
        include dirname(__FILE__) . '/template.php';
    }
}
?>
