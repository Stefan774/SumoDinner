<?php
Class RecipesController extends AppController {
    
    public $helpers = array('Html', 'Form','Js');
    public $components = array('Session','RequestHandler');
    private $useProxy = true;
    
    #Custom functions ########################################
    ##########################################################
    
    /**
     * Custom in_array function case insensitive
     * 
     * @param string $needle
     * @param array $haystack
     * @return bool
     */
    public function in_arrayi($needle, $haystack) {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
    /**
     * Custom case insensitive array_search function
     * 
     * @param string $needle
     * @param array $haystack
     * @return mixed
     */
    public function array_searchi($needle, $haystack) {
        return array_search(strtolower($needle), array_map('strtolower', $haystack));
    }
    /**
     * Delete a single file or recursively delete a directory
     *
     * @param string $str path to file or directory
     * @return bool returns true if direcory or file was deleted successfully.
     */
    protected function recursiveDelete($str){
        if(is_file($str)){
            return @unlink($str);
        }
        elseif(is_dir($str)){
            //Search all files and dirctorys in the assigned directory and
            //recursivly call this function to delete all content
            $scan = glob(rtrim($str,'/').'/*');
            foreach($scan as $index=>$path){
                $this->recursiveDelete($path);
            }
            return @rmdir($str);
        }
    }
    /**
     * Remove flagged images from file system.
     * All images which are flagged with order number -1 will be deleted from 
     * the corresponding folder. The function expects cakephp's requestData array.
     * 
     * @param array $requestData
     * @return array returns the clean request data array. All image objects with -1 order num are removed. 
     */
    protected function rmFlaggedImages($requestData){
        if (isset($requestData['Image'])) {
            //Set the target directory
            $targetDir = CONTENT_URL.$requestData['Recipe']['contentkey']."/";
            $count = 0;
            foreach ($requestData['Image'] as $img) {
                //Check if image is marked for deletion (odernum = -1)
                if ($img['ordernum'] == -1) {
                    if (is_file($targetDir.$img['name'])){
                        @unlink($targetDir.$img['name']);
                        $this->Recipe->Image->delete($img['id']);                        
                    }
                    unset($requestData['Image'][$count]);
                }
                $count++;
            }
        }
            return $requestData;
    }
    
    #Application-Controller methods ##########################
    ##########################################################
 
    /**
     * App entering page
     * @todo return only the needed data for recipe preview
     */
    public function index() {
         $this->paginate = array (
            'fields' => array('Recipe.id', 'Recipe.title','Recipe.picture','Recipe.maincategory','Recipe.contentkey','Recipe.description'),
            'order' => array('Recipe.maincategory' => 'asc'),
            'limit' => 50,
            'recursive' => 0
         );
        $data = $this->paginate('Recipe');
        $this->set('recipes', $data);
    }
    /**
     * Query recipes by category name and returns a json encoded data representation.
     * 
     * @param string $category
     * @return json recipes found for given $category
     */
    public function getRecipesByCategory($category) {
        if (isset($category) && $category != "") {
            $conditions = array (
                'fields' => array('Recipe.id', 'Recipe.title','Recipe.picture','Recipe.maincategory','Recipe.contentkey','Recipe.description'),
                'conditions' => array('Recipe.maincategory' => $category ),
                'order' => array('Recipe.maincategory' => 'asc'),
                'recursive' => 0
             );
            $data = $this->Recipe->find('all',$conditions);
            die(json_encode($data));
        }
        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "no category was set"}}');
    }
    /**
     * Search recipes which contain the given searchToken. Results are passed to result array for the search view.
     * 
     * @param string $searchToken
     * @param int $start
     * @param int $limit
     * 
     * @todo Add lokal database search 
     */
    public function search($searchToken = "", $start = 0, $limit = 20) {
        
        $searchToken = $searchToken == ""?isset($_GET['searchToken'])?$_GET['searchToken']:$searchToken:$searchToken;
        $decodedSearcbToken = urlencode($searchToken);
        $this->log("searchToken = $searchToken ;; decodedSearchToken = $decodedSearcbToken");
        
//        $this->Recipe->query('DROP TABLE searches_recipes');
//        $this->Recipe->query('CREATE VIRTUAL TABLE searches_recipes USING fts4(content="recipes",title,description,ingredients,maincategory,picture,contentkey,rating,severity,tokenize=porter)');
//        $this->Recipe->query('INSERT INTO searches_recipes (docid,title,description,ingredients,maincategory,picture,contentkey,rating,severity) SELECT id,title,description,ingredients,maincategory,picture,contentkey,rating,severity FROM recipes');        
        
//  $dbftsTrigger = "CREATE TRIGGER recipes_bu BEFORE UPDATE ON recipes BEGIN
//                    DELETE FROM searches_recipes WHERE docid=old.rowid;
//                  END;
//                  CREATE TRIGGER recipes_bd BEFORE DELETE ON recipes BEGIN
//                    DELETE FROM searches_recipes WHERE docid=old.rowid;
//                  END;
//                  CREATE TRIGGER recipes_au AFTER UPDATE ON recipes BEGIN
//                    INSERT INTO searches_recipes(docid,title,description,ingredients,maincategory,picture,contentkey,rating,severity) VALUES(new.rowid,new.title,new.description,new.ingredients,new.maincategory,new.picture,new.contentkey,new.rating,new.severity);
//                  END;
//                  CREATE TRIGGER recipes_ai AFTER INSERT ON recipes BEGIN
//                    INSERT INTO searches_recipes(docid,title,description,ingredients,maincategory,picture,contentkey,rating,severity) VALUES(new.id,new.title,new.description,new.ingredients,new.maincategory,new.picture,new.contentkey,new.rating,new.severity);
//                  END;";
//        $this->Recipe->query($dbftsTrigger);
        
        if ($searchToken && $searchToken != "") {
            
            $url = "http://api.chefkoch.de/api/1.0/api-recipe-search.php?Suchbegriff=";
            
            $json_url = $url.$decodedSearcbToken."&start=".$start."&limit=".$limit;
            $ch = curl_init( $json_url );

            // Configuring curl options
            $options = array(CURLOPT_RETURNTRANSFER => true);

            if ($this->useProxy)
                $options[CURLOPT_PROXY] = 'http://10.158.0.79:80';
            
            // Setting curl options
            curl_setopt_array( $ch, $options );

            // Getting jSON result string
            $jsonResponse = curl_exec($ch); 

            // Close the curl handler
            curl_close($ch);
            
            $data = $this->Recipe->query("SELECT docid, title, contentkey, picture FROM searches_recipes WHERE searches_recipes MATCH '$searchToken' ");
            $this->set('loresult', $data);
            $this->set('result', json_decode($jsonResponse, TRUE));
        }
        else {
            $this->set('result', array());
        }
    }
    /**
     * Query chefkoch.de and get recipe data into array.
     * 
     * @param int $recipeID id from recipe to load from chefkoch
     * @return array returns an array with recipe data from chefkoch.de
     */
    public function getRecipeCKJson($recipeID) {
        if ($recipeID && $recipeID != "") {
            
            $url = "http://api.chefkoch.de/api/1.0/api-recipe.php?ID=";
            
            $json_url = $url.$recipeID;
            $ch = curl_init( $json_url );

            // Configuring curl options
            $options = array(CURLOPT_RETURNTRANSFER => true);
            
            if ($this->useProxy)
                $options[CURLOPT_PROXY] = 'http://10.158.0.79:80';
  
            // Setting curl options
            curl_setopt_array( $ch, $options );

            // Getting jSON result string
            $jsonResponse = curl_exec($ch); 

            // Close the curl handler
            curl_close($ch);

            $this->set('result', json_decode($jsonResponse, TRUE));
            return json_decode($jsonResponse, TRUE);
        }
        else {
            $this->set('result', array());
            return array();
        }
    }
    
    /**
     * Shows the recipe with the given id
     * 
     * @param int $id recipe id to view
     */
    public function view($id = null) {
        if ($id != null && is_numeric($id)) {
            $this->Recipe->id = $id;
            $recipe = $this->Recipe->read();
            $rating = $this->Recipe->query(
                        'SELECT ROUND(AVG(rating)) FROM ratings WHERE recipe_id = ?',
                        array($id)
            );
            $recipe['Recipe']['rating'] = $rating[0][0][key($rating[0][0])];
            $this->set('recipe', $recipe);
        } else {
            $this->Session->setFlash("Tschuldigung das Rezept das du suchst ist leider nicht mehr (oder noch nicht) vorhanden. 
                                      <br><b>Eine hervorragende Gelegenheit ein neues Rezept zu erstellen.</b>", 'default', array("class"=>"alert alert-info"));
            $this->redirect(array("action"=>"index"));
        }
    }
    /**
     * @todo Not jet implementet method. $source urls array containing remote image sources must be given as parameter
     * @param array $remoteImages
     * @return array $savedImages returns an array of saved images filenames.
     */
    protected function saveRemoteImages($remoteImages = array(),$path = NULL) {
        $rustart = getrusage();
        $savedImages = array();
        $path = isset($path)?$path:UPLOADSTMP;
        
        //place this before any script you want to calculate time
$time_start = microtime(true); 
        
        $curlHandles = array();
        $handelCounter = 0;
        foreach ($remoteImages as $remoteImage ) {
            $imgFileName = substr($remoteImage, (strrpos($remoteImage,"/") + 1));
            $this->log("Remote image url = $remoteImage :: FileName = $imgFileName",'Debug');
            //try to get the remote image with the simple file_puts function if it is not working try to get it with curl
            //if (file_put_contents(UPLOADSTMP."/".$imgFileName, $imgFileName) === FALSE) {
                $this->log("Cannot get remote image url = $remoteImage :: FileName = $imgFileName with file_put_contents try to curl it.",'Debug');
                $curlHandles[$handelCounter] = curl_init($remoteImage);
                $fp = fopen($path."/".$imgFileName, 'wb');
                curl_setopt($curlHandles[$handelCounter], CURLOPT_FILE, $fp);
                curl_setopt($curlHandles[$handelCounter], CURLOPT_HEADER, 0);
                if ($this->useProxy)
                    curl_setopt($curlHandles[$handelCounter], CURLOPT_PROXY,'http://10.158.0.79:80');
                $handelCounter++;
                $savedImages[] = $imgFileName;
//             
//                if (curl_exec($ch)) {
//                    $savedImages[] = $imgFileName;
//                    $this->thumbnailImage($path."/".$imgFileName , 500, 300);
//                    $this->thumbnailImage($path."/".$imgFileName , 100, 75);
        }
        
       // create the multiple cURL handle
        $mh = curl_multi_init();
        
        foreach ($curlHandles as $cH ) {
         curl_multi_add_handle($mh,$cH);
        }
        $active = null;
        //execute the handles
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($mh) != -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
       }
        foreach ($curlHandles as $cH ) {
         curl_multi_remove_handle($mh,$cH);
        }
        foreach ($savedImages as $img ) {
            $this->thumbnailImage($path."/".$img , 500, 300);
            $this->thumbnailImage($path."/".$img , 100, 75);
        }
                    
                curl_multi_close($mh);
                fclose($fp);
              
            //}
        
//        foreach ($remoteImages as $remoteImage ) {
//            $imgFileName = substr($remoteImage, (strrpos($remoteImage,"/") + 1));
//            $this->log("Remote image url = $remoteImage :: FileName = $imgFileName",'Debug');
//            //try to get the remote image with the simple file_puts function if it is not working try to get it with curl
//            //if (file_put_contents(UPLOADSTMP."/".$imgFileName, $imgFileName) === FALSE) {
//                $this->log("Cannot get remote image url = $remoteImage :: FileName = $imgFileName with file_put_contents try to curl it.",'Debug');
//                $ch = curl_init($remoteImage);
//                $fp = fopen($path."/".$imgFileName, 'wb');
//                curl_setopt($ch, CURLOPT_FILE, $fp);
//                curl_setopt($ch, CURLOPT_HEADER, 0);
//                if ($this->useProxy)
//                    curl_setopt($ch, CURLOPT_PROXY,'http://10.158.0.79:80');
//                
//                if (curl_exec($ch)) {
//                    $savedImages[] = $imgFileName;
//                    $this->thumbnailImage($path."/".$imgFileName , 500, 300);
//                    $this->thumbnailImage($path."/".$imgFileName , 100, 75);
//                }
//                curl_close($ch);
//                fclose($fp);
//            //}
//        }
                              $time_end = microtime(true);

//dividing with 60 will give the execution time in minutes other wise seconds
$execution_time = ($time_end - $time_start)/60;

//execution time of the script
echo '<b>Total Execution Time:</b> '.$execution_time.' Mins'; 
                // Script end
  return $savedImages;
    }
    /**
     * Add and store recipes from "local" or from chefkoch.de
     * 
     * @param bool $saveCK (default = FALSE) set to true to store recipe from chefkoch.de
     * @param int $id (default = NULL) chefkoch.de recipe id (only needed if $saveCK = true)
     * @todo Error handling for form validation
     * @todo store remote images from ck
     */
    public function add($saveCK = false, $id = NULL) {
        
        if ($this->request->is('post')) {
            
            $contentKey = String::uuid();
            $targetDir = CONTENT_URL.$contentKey;
            
            $this->request->data['Recipe']['contentkey'] = $contentKey;

            if ($this->Recipe->saveAll($this->request->data)) {
                #read new tags and update the database
                $categories = $this->Recipe->Category->find('list');
                $categories_input = explode(";" , $this->request->data['Category']['name']);
                $categories_input = str_replace(" ","", $categories_input);
                
                foreach ($categories_input as $category_name) {
                    #Check if category already exists. If it is existing use category id instead of creating a new one.
                    if ($this->in_arrayi($category_name, $categories)) {
                        $category = array(
                            'Recipe'=> array('id' => $this->Recipe->id),
                            'Category' => array('id' => $this->array_searchi($category_name, $categories))
                        );
                    } else {
                        $category = array(
                            'Recipe'=> array('id' => $this->Recipe->id),
                            'Category' => array('name' => $category_name)
                        );
                        $this->Recipe->Category->Create();
                    }                 
                    $this->Recipe->Category->save($category);
                }
                #Creat recipe media content target directory
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir);
                }
                #Move recipe images from temp to target dir
                $this->moveImages2Recipe($targetDir,$this->request->data);
                
                $this->Session->setFlash('Dein Rezept wurde im Rezepteordner abgeheftet und kann jetzt jeder Zeit wieder gefunden werden.
                                          <br><b>Klasse weiter so mehr bitte !!</b>','default',array("class" => "alert alert-success"));
                $this->redirect(array('action' => 'view', $this->Recipe->id));
            } else {
                $this->Session->setFlash('Dein Rezept konnte leider nicht im Rezepteordner abgelegt werden. Anscheinend ist der Ordner schon wieder voll ;( <br>
                                          <b>Ich werde mich schnellst möglich um den Ordner kümmern. Versuch es doch bitte später nochmal.</b>'
                                          ,'default',array('class'=>'alert alert-error'));
            }
        } else {
            if ($saveCK && $id) {
                #get the recipe data from chefkoch.de
                $rmRecipe = $this->getRecipeCKJson($id);
                $remotePics = array();
                #if there are any recipe images attached get the title pic first and then all other pics
                if (isset($rmRecipe['result'][0]['rezept_bilder'])) {                    
                    $remotePics[] = isset($rmRecipe['result'][0]['rezept_bilder'][0]['bigfix']['file'])?$rmRecipe['result'][0]['rezept_bilder'][0]['bigfix']['file']:"";                    
                    foreach ($rmRecipe['result'][0]['rezept_bilder'] as $img) {
                        if (isset($img['big']['file'])) {
                            $remotePics[] = $img['big']['file'];
                        }
                    }
                }
                if (count($remotePics) != 0) {
                    $this->saveRemoteImages($remotePics);
                    $this->set('remoteImages',$this->saveRemoteImages($remotePics)); 
                }
            }            
            $this->set('categories', $this->Recipe->Category->find('list'));
        }
    }
    /**
     * Function to rate recipes with integrated ip lock.
     * 
     * Return-codes are:
     * 200 = Success
     * 102 = Failed
     * 105 = Existing rating
     *  
     * @return json
     * @param type $id
     * @param type $rating
     */
    public function rateRecipe($id, $rating) {
        
        $clientIp = $_SERVER['REMOTE_ADDR'];
        $this->Recipe->id = $id;
        
        $logged = $this->Recipe->Rating->find('count', array(
            'fields' => 'DISTINCT Rating.ip',
            'conditions' => array('Rating.ip' => $clientIp, 'Rating.recipe_id' => $id)
        ));
        
        if ($logged != 1 && $this->Recipe->Rating->save(array('Rating'=>array('rating'=>$rating,'ip'=>$clientIp,'recipe_id'=>$id)))) {
             die('{"jsonrpc" : "2.0", "success" : {"code": 200 }}');
        }
        else {
            if ($logged == 1) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "Recipe was rated already."}, "id" : '.$id.'}');
            }
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to save rating."}, "id" : '.$id.'}');
        }
        
    }
    /**
     * Moves images from temp directory (request data array) to a specified target directory $targetDir
     * 
     * @param string $targetDir path to images target directory
     * @param type $requestData request data array ($this->request->data array expected)
     */
    protected function moveImages2Recipe($targetDir, $requestData = array()) {
        if (isset($requestData['Image']) && count($requestData['Image']) !== 0) {
            foreach ($requestData['Image'] as $img) {
                $files2bMoved = glob(UPLOADSTMP."/"."*".$img['name']);
                foreach ($files2bMoved as $move) {
                    if (copy($move, str_replace(UPLOADSTMP,$targetDir,$move))){
                        @unlink($move);
                    }
                }
            }
        }
    }
    /**
     * @todo Eval if this method is needed
     * @todo If needed implement it
     */
//    public function removeImage() {
//        // some logic here
//        Configure::write('debug', 0);
//        // Data to be sent as JSON response
//        $response = array(
//                    'success' => true,
//                     'message' => 'My error message',
//                     'test' => '123',
//                     'pic_id' => $this->request->data['pic_id']
//                    );
//        $this->set('resp',$response);
//        $this->set('_serialize', 'resp');
//    }

    /**
     * Upload helper method for plupload component. Handels the upload of recipe images to temp directory.
     * Additionally picture resize operations are done.
     * 
     * @todo implement support for addImages on edit recipe action
     */
    public function addImages($contentKey = NULL) {	
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds

        // 5 minutes execution time
        @set_time_limit(5 * 60);

        // Uncomment this one to fake upload time
        usleep(5000);
        
        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

        $this->log("Number of chunks = $chunks for file $fileName :: contentkey = $contentKey",'Debug');
        
        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists(UPLOADSTMP . "/" . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            
            while (file_exists(UPLOADSTMP . "/" . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $contentKey!=NULL?CONTENT_URL.$contentKey."/".$fileName:UPLOADSTMP . "/" . $fileName;

        // Create target dir
        if (!file_exists(UPLOADSTMP)) {
            @mkdir(UPLOADSTMP);
        }

        // Remove old temp files
        if ($cleanupTargetDir && is_dir(UPLOADSTMP) && ($dir = opendir(UPLOADSTMP))) {
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = UPLOADSTMP . "/" . $file;
                /** Remove all files when max age is reached not only tmp files **/
                if ((filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                    $this->log($tmpfilePath,'Debug');
                    $this->log(@unlink($tmpfilePath),'Debug');
                }
            }
            closedir($dir);
        } else {
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
        }

        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])) {
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
        }

        if (isset($_SERVER["CONTENT_TYPE"])) {
            $contentType = $_SERVER["CONTENT_TYPE"];
        }

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            $this->log("This uplaod is not a multipart upload",'Debug');
            
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                $this->log("The temp file name is : ".$_FILES['file']['tmp_name'],'Debug');
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                
                if ($out) {
                    $this->log("Tempfile successfully opend",'Debug');
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        $this->log("Binary input stream successfully opend",'Debug');
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    }
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                }
            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
        } else {
            $this->log("This uplaod is multipart",'Debug');
            // Open temp file
            $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096))
                    fwrite($out, $buff);
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                }
                fclose($in);
                fclose($out);
            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }
        }
        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            $this->log("The file $filePath has been successfully uploaded.",'Debug');
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
            //$writePath = isset($contentKey)?CONTENT_URL.$contentKey:NULL;
            $writePath = NULL;
            $this->thumbnailImage($filePath,500,300,$writePath);
            $this->thumbnailImage($filePath,100,75,$writePath);
        }
        // Return JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : {"fileName":"'.$fileName.'"}}');
    }
    
    /**
     * Edit method for recipes
     * 
     * @param int $id recipe id
     * @todo create method to set categories to avoied dublicate code in add and edit action
     */
    public function edit($id = null) {
        if (!isset($id)) {
            $this->redirect($this->referer());
            return false;
        }
        //Set request id
        $this->Recipe->id = $id;
        //Display current recipe data if requested and recipe with id exists
        if ($this->request->is('get')) {
            $this->request->data = $this->Recipe->read();
            if (empty($this->request->data)) {
                $this->redirect($this->referer());
                return false;
            }
            $categories = array();
            
            #read multible categories and push it into an array
            foreach ($this->request->data['Category'] as $category) {
                array_push($categories, $category['name']);
            }
            
            $categories_csv = implode(";",$categories);
            $this->set('categories', $categories_csv);
            $this->set('categories_drdown', $this->Recipe->Category->find('list'));
            $this->set('recipe', $this->request->data);
            
        } else {
            $this->request->data = $this->rmFlaggedImages($this->request->data);
            //pr($this->request->data);
            if ($this->Recipe->saveAll($this->request->data)) {
                #first delete all associated categories for the recipe
                $this->Recipe->CategoryRecipe->deleteAll(array('recipe_id' => $this->Recipe->id),false);
                #read new categories and update the database
                $categories = $this->Recipe->Category->find('list');
                $categories_input = explode(";" , $this->request->data['Category']['name']);
                $categories_input = str_replace(" ","", $categories_input);
                
                foreach ($categories_input as $category_name) {
                    if ($this->in_arrayi($category_name, $categories)){
                        $category = array(
                            'Recipe'=> array('id' => $this->Recipe->id),
                            'Category' => array('id' => $this->array_searchi($category_name, $categories))
                        );
                    } else {
                        $category = array(
                            'Recipe'=> array('id' => $this->Recipe->id),
                            'Category' => array('name' => $category_name)
                        );
                        $this->Recipe->Category->Create();
                    }                 
                    $this->Recipe->Category->save($category);
                }
                
                $this->moveImages2Recipe(CONTENT_URL.$this->request->data['Recipe']['contentkey'],$this->request->data);
                $this->Session->setFlash('Your post has been updated.');
                $this->redirect(array('action' => 'view', $id));
            } else {
                $this->Session->setFlash('Unable to update your post.');
            }
        }
    }
    /**
     * Creat a croped thumbnail of an image with the given $x=width $y=height.
     * If no writePath is given it will store the images in standard temp directory UPLOADSTMP
     * 
     * @param string $imgPath
     * @param int $x
     * @param int $y
     * @return boolean true if on success fals on failure
     */
    private function thumbnailImage($imgPath , $x, $y,$writePath = NULL) {
        if (isset($imgPath) && $imgPath != "" && isset($x) && isset($y)) {
            $lastSlash = strrpos($imgPath,"/");
            $fileName = substr($imgPath, $lastSlash + 1);          
            $this->log("All set for thumbnailing the images x=$x y=$y imagePath=$imgPath fileName=".isset($writePath)?$writePath."/".$x.'x'.$y.'_'.$fileName:UPLOADSTMP."/".$x.'x'.$y.'_'.$fileName,'Debug');
            try { 
                $image = new Imagick($imgPath);
                $image->cropThumbnailImage($x, $y);
                //remove the canvas
                $image->setImagePage(0, 0, 0, 0);
                $image->writeimage(isset($writePath)?$writePath."/".$x.'x'.$y.'_'.$fileName:UPLOADSTMP."/".$x.'x'.$y.'_'.$fileName);
                $image->destroy();
                return TRUE;
            } catch (Exception $e) {
                $this->log("Caught exception for thumbnailImage: ".$e->getMessage());
                return FALSE;
            }
        } else {
            $this->log("Thumbnailing could not be done to less arguments provided for method thumbnailImage(string imgPath , int x, int y)", 'Debug');
            return FALSE;
        }
    }
    /**
     * Delete recipe and associated data (pictures etc.)
     * 
     * @param int $id
     * @throws MethodNotAllowedException
     */
    public function delete($id) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
            $this->redirect($this->referer());
        }
        
        $this->Recipe->id = $id;
        $contentkey = $this->Recipe->field('contentkey');
        
        if ($this->Recipe->delete($id)) {
            if ($this->recursiveDelete("uploads"."/".$contentkey) !== true) {
                $this->log("Could not delete uploads from recipe id = ".$id." content must be deleted manually contentkey = ".$contentkey);
            }
            $this->Session->setFlash('Das Rezept wurde erfolgreich durch den Reisswolf gedreht','default',array("class" => "alert alert-success"));
            $this->redirect(array('action' => 'index'));
        } else {
            $this->Session->setFlash('Hmm der Reisswolf scheint nicht zu funktionieren, 
                                      ich arbeite dran!<br><b>Bitte versuch es doch etwas später nochmal</b>',
                                      'default',array("class" => "alert alert-error"));
            $this->log("Could not delete recipe $id");
            $this->redirect(array('action' => 'index'));
        }
    }
}
?>
