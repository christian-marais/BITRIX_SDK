<?php
namespace NS2B\SDK\MODULES\CRM\COMPANY\INSEE;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NextcloudService
{
    private HttpClientInterface $client;
    public function __construct(
        private string $adminUser='christian.marais@ns2b.fr',
        private string $adminPassword='christian.marais@ns2b.fr',
        private string $baseUrl='http://192.168.20.3:8081',
        private array $headers = [
            'OCS-APIRequest' => 'true',
            'accept' => 'application/json',
        ],
        private array $credentials=[]
    ) {
        $this->client = HttpClient::create();
        $this->credentials=[
            'username' => $this->adminUser,
            'password' => $this->adminPassword
        ];
    }

    public function getBaseUrl(){
        return $this->baseUrl;
    }

   
    public function createNextCloudUserShareSpace(string $user, string $password, string $company){

        try{
            if(
                empty($user) || 
                empty($password) || 
                empty($company)
            ){
                throw new \Exception("Missing parameters user, password, company");
            }
            $rootPath='/public/';
            $groupPath=$rootPath.($company??'unknown');
            $folderPath=$groupPath;//"/".$user;
            if(
                ($result[0]=$this->getUser($user))["status"]==="fail" &&
                ($result[1]=$this->createUser($user,$password))["status"]==="fail"
                ){
                    throw new \Exception("Failed to create user:".$user);
            }
            
            $result[2]=$this->createFolder($this->adminUser,$rootPath);
            $result[3]='No subfolder needed';//$this->createFolder($this->adminUser,$groupPath);
            $result[4]=$this->createFolder($this->adminUser,$folderPath);
            if(
                $result[2]["status"]==="fail" &&
                // $result[3]["status"]==="fail" &&
                $result[4]["status"]==="fail"
            ){
                throw new \Exception("Failed to create folder:".$folderPath);
            }
            if(($result[5]=$this->shareFolder(folderPath:$folderPath,targetUser:$user))["status"]==="fail")
                throw new \Exception("Failed to share folder:".$folderPath);

            $result[7]=$this->createGroup($company);
            $result[8]=$this->addUserToGroup($user,$company);   
            
            return [
                'status' => 'success',
                'data' => $result,
                'message' => 'User :'.$user.' shared with success folder :'.$folderPath,
                'method' => 'createNextCloudUserShareSpace',
                'folderPath' => $this->baseUrl.'/apps/files/files?dir='.$folderPath
            ];
        }catch(\Exception $e){
             return [
                'status' => 'fail',
                'data' => $result,
                'message' => 'User :'.$user.' failed to share folder :'.$folderPath. ' - '.$e->getMessage(),
                'method' => 'createNextCloudUserShareSpace'
            ];
        }
    }



    /*
        curl -X POST http://192.168.20.3:8081/ocs/v1.php/cloud/groups \
    -u "admin:motdepasse" \
    -d "groupid=nouveaugroupe" \
    -H "OCS-APIRequest: true"
        * Function to create a user on Nextcloud
        curl -X POST http://192.168.20.3:8081/ocs/v1.php/cloud/users \
            -u christian.marais@ns2b.fr:christian.marais@ns2b.fr \
            -H "OCS-APIRequest: true" \
            -d userid=nouvelutilisateur \
    */
    

    public function createGroup(string $group){
        try{
            $url = $this->baseUrl . '/ocs/v1.php/cloud/groups';
            $response = $this->client->request('POST', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
                'body' => [
                    'groupid' => $group,
                ],
            ]);
            $result = json_decode($response->getContent(), true);
            if ($response->getStatusCode() >= 300) {
                throw new \Exception('Failed to create user');
            }
            
            return [
                'status' => 'success',
                'data' => $result,
                'message' => 'Group :'.$group.' created with success',
                'method' => 'createGroup'
            ];
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'data' => $result,
                'message' => 'Group :'.$group.' failed to create - '.$e->getMessage(),
                'method' => 'createGroup'
            ];
        }
    }

    /*
        curl -X POST http://192.168.20.3:8081/ocs/v1.php/cloud/users/nouvelutilisateur/groups \
    -u "admin:motdepasse" \
    -d "groupid=nouveaugroupe" \
    -H "OCS-APIRequest: true"
     */

     public function addUserToGroup(string $username, string $group){
        try{
            $url = $this->baseUrl . '/ocs/v1.php/cloud/users/' . $username . '/groups';
            $response = $this->client->request('POST', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
                'body' => [
                    'groupid' => $group,
                ],
            ]);
            $result = json_decode($response->getContent(), true);
            if ($response->getStatusCode() >=300) {
                throw new \Exception('Failed to add user to group');
            }
          
            return [
                'status' => 'success',
                'data' => $result,
                'message' => 'User :'.$username.' added to group :'.$group.' with success',
                'method' => 'addUserToGroup'
            ];
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'data' => $result,
                'message' => 'User :'.$username.' failed to add to group :'.$group. ' - '.$e->getMessage(),
                'method' => 'addUserToGroup'
            ];
        }
    }

    /*
        curl -X GET http://192.168.20.3:8081/ocs/v1.php/cloud/users/nouvelutilisateur/groups \
    -u "admin:motdepasse" \
    -H "OCS-APIRequest: true"
    */

     public function getUserGroups(string $username){
        try{
            $url = $this->baseUrl . '/ocs/v1.php/cloud/users/' . $username . '/groups';
            $response = $this->client->request('GET', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
            ]);
            $result = json_decode($response->getContent(), true);
            if ($response->getStatusCode() >=300) {
                throw new \Exception('Failed to get user\'s groups');
            }
            return [
                'status' => 'success',
                'data' => $result["ocs"]["data"],
                'message' => 'User :'.$username.' get user\'s groups with success',
                'method' => 'getUserGroups'
            ];
        }catch(\Exception $e){
            return [
                'status' => 'fail',
                'data' => $result,
                'message' => 'User :'.$username.' failed to get user\'s groups - '.$e->getMessage(),
                'method' => 'getUserGroups'
            ];
        }
     }

     /*
        Function to create a user on Nextcloud
        curl -X POST https://192.168.20.3:8081/ocs/v1.php/cloud/users \
        -u christian.marais@ns2b.fr:christian.marais@ns2b.fr \
        -u christian.marais@ns2b.fr:christian.marais@ns2b.fr \
        -H "OCS-APIRequest: true" \
        -d userid=nouvelutilisateur \
        -d password=motdepassenouvelutilisateur 

            curl -X POST http://192.168.20.3:8081/ocs/v1.php/cloud/users \
        -u "christian.marais@ns2b.fr:christian.marais@ns2b.fr" \
        -d "userid=nouvelutilisateur" \
        -d "password=motdepassenouvelutilisateur" \
        -d "displayName=Nom Complet" \
        -d "email=christian.marais@ns2b.fr" \
        -d "groups[]=groupe1" \
        -d "groups[]=groupe2" \
        -H "OCS-APIRequest: true"
    */
    public function createUser(string $username, string $password): array
    {
        try {
            $url = $this->baseUrl . '/ocs/v1.php/cloud/users';
            $response = $this->client->request('POST', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
                'body' => [
                    'userid' => $username,
                    'password' => $password,
                    // 'email' => $username,
                ],
            ]);
            
            if(
                $response->getStatusCode() != 200 || 
                (($result=json_decode($response->getContent(), true))["ocs"]["meta"]["status"]??[]) == 'failure'
            ) {
                throw new \Exception('Failed to create user - '.$result['ocs']['meta']['message']??'');
            }
            
            return [
                'status' => 'success',
                'data' => $result['ocs']['data']??[],
                'message' => 'User :'.$username.' created with success',
                'method' => 'createUser'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'data' => $e->getMessage(),
                'message' => 'User :'.$username.' failed to create - '.$e->getMessage(),
                'method' => 'createUser'
            ];
        }
    }

    /* 
        Function to get a user on Nextcloud
        curl -X GET http://192.168.20.3:8081/ocs/v1.php/cloud/users \
    -u christian.marais@ns2b.fr:christian.marais@ns2b.fr \
    -H "OCS-APIRequest: true" \
    -d userid=nouvelutilisateur \
    -d password=motdepassenouvelutilisateur 
    */

    public function getUser(string $username): mixed
    {   
        try {
            $url = $this->baseUrl . '/ocs/v1.php/cloud/users';
            $response = $this->client->request('GET', $url.'/'.$username, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers
            ]);
            $response = json_decode($response->getContent(), true);
            if (empty($user=$response['ocs']['data']??[])) { 
                throw new \Exception('Failed to get user');
            }
            return [
                'status' => 'success',
                'data' => $user,
                'method' => 'getUser'
            ];
        }catch (\Exception $e) {
            return [
                'status' => 'fail',
                'data' => $e->getMessage(),
                'method' => 'getUser'
            ];
        }
    }

    /* 
        Function to create a folder on Nextcloud
        curl -X MKCOL https://192.168.20.3:8081/remote.php/dav/files/christian.marais@ns2b.fr/NouveauDossier \
    -u christian.marais@ns2b.fr:christian.marais@ns2b.fr \
    -H "OCS-APIRequest: true" \
    */
    public function getMainAccount(){
        return $this->adminUser;
    }
    public function createFolder(string $username, string $folderName): mixed
    {   
      
        
        try{
            $data=[
                'method' => 'createFolder',
                'request' => [$username, $folderName]
            ];
            if(($folder=$this->getFolder($username, $folderName))["status"]==="success")
                goto end;

            $url = $this->baseUrl.'/remote.php/dav/files/' . $username . $folderName;
            $response = $this->client->request(
                'MKCOL', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
            ]);
            $data['data']=$response->getContent();
            $data['folderRequest']=$folder;
            $data['hasFolder'] =$folder["status"]==="success" ;
            if ($response->getStatusCode() != 201) 
                throw new \Exception('Failed to create folder');
            
            end:

            return array_merge($data??[],[
                'status' => 'success'
            ]);
        }catch (\Exception $e) {
            return array_merge($data??[],[
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getFolder(string $username, string $folderName): mixed
    
    {   
        try{
            $url = $this->baseUrl . '/remote.php/dav/files/' . $username . $folderName;
            $response = $this->client->request(
                'PROPFIND', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
            ]);

            if ($response->getStatusCode() != 207) {
                throw new \Exception('Failed to get folder');
            }

            return [
                'status' => 'success',
                'message' => "Folder $folderName found",
                'method'=>'getFolder'
            ];
        }catch (\Exception $e) {
            return [
                'status' => 'fail',
                'message' => $e->getMessage(),
                'method'=>'getFolder'
            ];
        }
    }
    //     curl -X POST https://192.168.20.30:8081/ocs/v1.php/apps/files_sharing/api/v1/shares \
    //   -u nouvelutilisateur:motdepassenouvelutilisateur \
    //   -H "OCS-APIRequest: true" \
    //   -d path=/NouveauDossier \
    //   -d shareType=0 \
    //   -d shareWith=utilisateurcible \
    //   -d permissions=31


    public function shareFolder(string $folderPath, string $targetUser):mixed
    {   
        try{
            if(
                empty($folderPath) || 
                empty($targetUser)
            ){
                throw new \Exception('Missing parameters Folder or Target User');
            }
            $url = $this->baseUrl . '/ocs/v1.php/apps/files_sharing/api/v1/shares';
            $response = $this->client->request('POST', $url, [
                'auth_basic' => $this->credentials,
                'headers' => $this->headers,
                'body' => [
                    'path' => $folderPath,
                    'shareType' => 0, // 0 = utilisateur
                    'shareWith' => $targetUser,
                    'permissions' => 31, // Tous les droits
                ],
            ]);
            if ($response->getStatusCode() != 200) {
                throw new \Exception('Failed to share folder');
            }
            return [
                'status' => 'success',
                'data' => json_decode($response->getContent(), true)
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'fail',
                'data' => $e->getMessage(),
                'response' => json_decode($response->getContent(), true)
            ];
        }
    }

}   