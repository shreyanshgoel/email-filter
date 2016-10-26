<?php 

require 'autoloader.php';
use Curl\Curl as Curl;

if(isset($_POST['go'])){


	$file = $_FILES["csv"]["tmp_name"];

	if(($handle = fopen($file, "r")) !== FALSE){

		while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
	        
			$curl = new Curl();
			
			$curl->setHeader('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/53.0.2785.143 Safari/537.36');
			
			$response = $curl->get('http://api.madapi.xyz/?q=' . $data[0]);
			
			$curl->close();

			$details = json_decode($response);

			echo $details->{"active"};

   		}

    	fclose($handle);

	}
}

?>

<form method="post" enctype="multipart/form-data">

<input type="file" name="csv"><br><br>

<input type="text" name="days" placeholder="enter the number of days"><br><br>

<input type="submit" name="go" value="go">

</form>

