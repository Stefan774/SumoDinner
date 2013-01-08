<?php
Class RecipesController extends AppController {
    
    public $helpers = array('Html', 'Form','Js');
    public $components = array('Session','RequestHandler');
    
    #Custom functions ########################################
    public function in_arrayi($needle, $haystack) {
        return in_array(strtolower($needle), array_map('strtolower', $haystack));
    }
    public function array_searchi($needle, $haystack) {
        return array_search(strtolower($needle), array_map('strtolower', $haystack));
    }
    ##########################################################
    
    public function index() {
         $this->paginate = array (
            'order' => array(
            'Recipe.title' => 'asc'
            ),
            'limit' => 5
        );
        $data = $this->paginate('Recipe');
        $this->set('recipes', $data);
    }
    
    public function view($id = null) {
        $this->Recipe->id = $id;
        $this->set('recipe', $this->Recipe->read());
    }
    
    public function add() {
        if ($this->request->is('post')) {
            
            $contentKey = String::uuid();
            $targetDir = "uploads/".$contentKey;
            
            $this->request->data['Recipe']['contentkey'] = $contentKey;
                    
            pr($this->request->data);
            
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
                $this->Session->setFlash('Your post has been saved.');
                #$this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Unable to add your recipe.');
            }
        }else {
            $this->set('categories', $this->Recipe->Category->find('list'));
        }
    }
	
    public function addImages() {
		
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");

        $targetDir = "uploads/tmp";

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

        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b)) {
                $count++;
            }

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Create target dir
        if (!file_exists($targetDir)) {
            @mkdir($targetDir);
        }

        // Remove old temp files
        if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                    @unlink($tmpfilePath);
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
            $categories_csv = implode(";",$categories);
            $this->Set('categories', $categories_csv);
        } else {
            if ($this->Recipe->save($this->request->data)) {
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
                $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash('Unable to update your post.');
            }
        }
    }
    public function delete($id, $title) {
        if ($this->request->is('get')) {
            throw new MethodNotAllowedException();
        }
        if ($this->Recipe->delete($id)) {
            $this->Session->setFlash('The recipe ' . $title . ' has been deleted.');
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
