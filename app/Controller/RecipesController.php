<?php
Class RecipesController extends AppController {
    
    public $helpers = array('Html', 'Form','Js');
    public $components = array('Session','RequestHandler');
    
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
            $targetDir = CONTENT_URL.$requestData['Recipe']['contentkey'].DIRECTORY_SEPARATOR;
            $count = 0;
            foreach ($requestData['Image'] as $img) {
                //Check if image is marked for deletion (odernum = -1)
                if ($img['ordernum'] == -1) {
                    if (is_file($targetDir.$img['name'])){
                        @unlink($targetDir.$img['name']);
                        $this->Recipe->Image->delete($img['id']);
                        unset($requestData['Image'][$count]);
                    }
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
        
        if ($searchToken && $searchToken != "") {
            
            $url = "http://api.chefkoch.de/api/1.0/api-recipe-search.php?Suchbegriff=";
            
            $json_url = $url.$searchToken."&start=".$start."&limit=".$limit;
            $ch = curl_init( $json_url );

            // Configuring curl options
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_PROXY => 'http://10.158.0.79:80'                    
            );

            // Setting curl options
            curl_setopt_array( $ch, $options );

            // Getting jSON result string
            $jsonResponse = curl_exec($ch); 

            // Close the curl handler
            curl_close($ch);

            $this->set('result', json_decode($jsonResponse, TRUE));
        }
        else {
            $this->set('result', array());
        }
    }
    /**
     * 
     * @param type $recipeID
     * @return type
     */
    public function getRecipeCKJson($recipeID) {
        if ($recipeID && $recipeID != "") {
            
            $url = "http://api.chefkoch.de/api/1.0/api-recipe.php?ID=";
            
            $json_url = $url.$recipeID;
            $ch = curl_init( $json_url );

            // Configuring curl options
            $options = array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_PROXY => 'http://10.158.0.79:80'                    
            );

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
    

    public function view($id = null) {
        $this->Recipe->id = $id;
        $recipe = $this->Recipe->read();
        $rating = $this->Recipe->query(
                    'SELECT ROUND(AVG(rating)) FROM ratings WHERE recipe_id = ?',
                    array($id)
        );
        $recipe['Recipe']['rating'] = $rating[0][0][key($rating[0][0])];
        //pr($this->Recipe->find('list',array('fields' => array('Category.name'))));
        $this->set('recipe', $recipe);
    }
    
    protected function saveRemoteImages($source = array()) {
        $remote_img = 'http://www.somwhere.com/images/image.jpg';
        $img = imagecreatefromjpeg($remote_img);
        $path = 'images/';
        imagejpeg($img, $path);
    }

        public function add($saveCK = false,$id = NULL) {
        if ($this->request->is('post')) {
            
            $contentKey = String::uuid();
            $targetDir = CONTENT_URL.$contentKey;
            
            $this->request->data['Recipe']['contentkey'] = $contentKey;
                    
            //pr($this->request->data);
            
            if ($this->Recipe->saveAll($this->request->data)) {
                #read new tags and update the database
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
                if (!is_dir($targetDir)) {
                    @mkdir($targetDir);
                }
                $this->moveImages2Recipe($this->request->data,$targetDir);
                
                $this->Session->setFlash('Your post has been saved.');
                //$this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Unable to add your recipe.');
            }
        }else {
            if ($saveCK && $id){
                $rmRecipe = $this->getRecipeCKJson($id);
                $remotePics = array();
                
                if (isset($rmRecipe['result'][0]['rezept_bilder'])) {                    
                    $remotePics[] = isset($rmRecipe['result'][0]['rezept_bilder'][0]['bigfix']['file'])?array($rmRecipe['result'][0]['rezept_bilder'][0]['bigfix']['file'],""):array();                    
                    foreach ($rmRecipe['result'][0]['rezept_bilder'] as $img) {
                        if (isset($img['big']['file'])) {
                            $remotePics[] = array($img['big']['file'],"");
                        }
                    }
                }
                pr($remotePics);
            }            
            $this->set('categories', $this->Recipe->Category->find('list'));
        }
    }
    /**
     * Function to rate recipes with ip lock.
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
    
    protected function moveImages2Recipe($requestData = array(),$targetDir) {
        if (isset($requestData['Image']) && count($requestData['Image']) !== 0) {
                    foreach ($requestData['Image'] as $img) {
                        $files2bMoved = glob(UPLOADSTMP.DIRECTORY_SEPARATOR."*".$img['name']);
                        foreach ($files2bMoved as $move) {
                            if (copy($move, str_replace(UPLOADSTMP,$targetDir,$move))){
                                @unlink($move);
                            }
                        }
                    }
                }
    }

        public function removeImage() {
        // some logic here
        Configure::write('debug', 0);
        // Data to be sent as JSON response
        $response = array(
                    'success' => true,
                     'message' => 'My error message',
                     'test' => '123',
                     'pic_id' => $this->request->data['pic_id']
                    );
        $this->set('resp',$response);
        $this->set('_serialize', 'resp');
    }

    public function addImages() {
		
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        //$maxFileAge = 30;
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

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists(UPLOADSTMP . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            
            while (file_exists(UPLOADSTMP . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = UPLOADSTMP . DIRECTORY_SEPARATOR . $fileName;

        // Create target dir
        if (!file_exists(UPLOADSTMP)) {
            @mkdir(UPLOADSTMP);
        }

        // Remove old temp files
        if ($cleanupTargetDir && is_dir(UPLOADSTMP) && ($dir = opendir(UPLOADSTMP))) {
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = UPLOADSTMP . DIRECTORY_SEPARATOR . $file;
                //$this->log(filemtime($tmpfilePath)." <= MAXTIME = ".(time() - $maxFileAge),'Debug');
//                if (preg_match('/\.part$/', $file)) {
//                    $this->log('MAXTIME IS REACHED FOR '.$tmpfilePath,'Debug');
//                }
                // Remove temp file if it is older than the max age and is not the current file
                //if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
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
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
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
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }
        
         $image = new Imagick(UPLOADSTMP.DIRECTORY_SEPARATOR.$fileName);
         $image->cropThumbnailImage(500, 300);
         //remove the canvas
         $image->setImagePage(0, 0, 0, 0);
         $image->writeimage(UPLOADSTMP.DIRECTORY_SEPARATOR."500x300_".$fileName);
         $image->destroy();
         
         $image2 = new Imagick(UPLOADSTMP.DIRECTORY_SEPARATOR.$fileName);
         $image2->cropThumbnailImage(100, 75);
         //remove the canvas
         $image2->setImagePage(0, 0, 0, 0);
         $image2->writeimage(UPLOADSTMP.DIRECTORY_SEPARATOR."100x75_".$fileName);
         $image2->destroy();

        
        // Return JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : {"fileName":"'.$fileName.'"}}');
    }
    
    public function edit($id = null) {
        $this->Recipe->id = $id;
        if ($this->request->is('get')) {
            $this->request->data = $this->Recipe->read();
            $categories = array();
            #read multible categories and push it into an array
            foreach ($this->request->data['Category'] as $category) {
                array_push($categories, $category['name']);
            }
            //pr($this->request->data);
            $categories_csv = implode(";",$categories);
            $this->set('categories', $categories_csv);
            $this->set('recipe', $this->request->data);
        } else {
            $this->request->data = $this->rmFlaggedImages($this->request->data);
            pr($this->request->data);
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
                $this->Session->setFlash('Your post has been updated.');
                //$this->redirect(array('action' => 'view', $id));
            } else {
                $this->Session->setFlash('Unable to update your post.');
            }
        }
    }
    
    public function delete($id, $title) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
            $this->redirect(array('action' => 'index'));
        }
        
        $this->Recipe->id = $id;
        $contentkey = $this->Recipe->field('contentkey');
        
        if ($this->Recipe->delete($id)) {
            if ($this->recursiveDelete("uploads".DIRECTORY_SEPARATOR.$contentkey) !== true) {
                $this->log("Could not delete uploads from recipe id = ".$id." content must be deleted manually contentkey = ".$contentkey);
            }
            $this->Session->setFlash('The recipe ' . $title . ' has been deleted.');
            $this->redirect(array('action' => 'index'));
        } else {
            $this->Session->setFlash('The recipe ' . $title . ' could not be deleted. Try again later.');
            $this->redirect(array('action' => 'index'));
        }
    }
    // The magic is here. When not called, no view is rendered. 
    // When called it renders message.ctp,
    // content set to $message, or json_encode($message), respectively
    function respond($message=null, $json=false) {
        if ($message!=null) {
            if ($json==true) {
                $this->RequestHandler->setContent('json', 'application/json');
                $message=json_encode($message);
            }
            $this->set('message', $message);
        }
        $this->render('message');
    }
}
?>
