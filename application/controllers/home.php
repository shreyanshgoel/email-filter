<?php

/**
 * The Default Example Controller Class
 *
 * @author Faizan Ayubi, Hemant Mann
 */
use Shared\Controller as Controller;
use Framework\RequestMethods as RequestMethods;
use Curl\Curl as Curl;

class Home extends Controller {

    public function index() {
    	$layoutView = $this->getLayoutView();
    	$layoutView->set("seo", Framework\Registry::get("seo"));


		if(RequestMethods::post('go')){

		    $file = $_FILES["csv"]["tmp_name"];

		    $days = RequestMethods::post('days');

		    $date = date('Y-m-d');

		    $date2 = date('Y-m-d', strtotime($date . '- ' . $days . ' days'));

		    $str = strtotime($date2);

		    $content = '';

		    if(($handle = fopen($file, "r")) !== FALSE){

		        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		            
		            $curl = new Curl();
		            
		            $curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
		            
		            $response = $curl->get('http://api.madapi.xyz/?q=' . $data[0]);
		            
		            $curl->close();

		            $details = json_decode($response);


		            if(strtotime($details->{"active"}) > $str){

		                $content.= $data[0] . '
';
		            }



		        }

		        fclose($handle);

		    }

		    if(!empty($content)){

		        $this->download($content);
		    }
		}
    }

    protected function download($c = ''){

    	$this->noview();

    	header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-disposition: attachment; filename=filter.txt');
        header('Content-Length: '.strlen($c));
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');
        header('Pragma: public');
        echo $c;
        exit;
    }

}
