<?php

/**
 * The Default Example Controller Class
 *
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Shared\Mail as Mail;

class Home extends Controller {

    public function index() {

    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));

		if(RequestMethods::post('go')){

		    $file = $_FILES["csv"]["tmp_name"];

		    $days = RequestMethods::post('days');

		    $email = RequestMethods::post('email');

		    $name = uniqid();

		    $task = new models\Task(array(
		    	'email' => $email,
		    	'days' => $days,
		    	'csv' => $name,
                'live' => 1
		    	));

		    if($file){
		    	$img = $this->_upload('csv', 'logo', ['extension' => 'csv', 'name' => $name, 'size' => '6000000']);

                if($img){

                	$task->save();
                    echo "<script>alert('task is scheduled')</script>";

                }
            }
		}
    }

    public function test(){

        $task = models\Task::all();

        foreach ($task as $key => $value) {
            echo $value->email;
        }
    }

    public function install(){
        $models = Shared\Markup::models();
        foreach($models as $key => $value){
            $this->sync($value);
        }
    }
    public function sync($model){
        try {
            $this->noview();
            $db = Framework\Registry::get("database");
            
            $model = "models\\" . $model; 
            $model = new $model;
            $db->sync($model);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        
    }

}
