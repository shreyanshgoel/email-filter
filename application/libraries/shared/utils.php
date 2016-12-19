<?php
namespace Shared;

use WebBot\Core\Bot as Bot;
use Framework\{Registry, ArrayMethods, RequestMethods};

class Utils {
	/**
	 * Converts the object to string by using 'sprintf'
	 * @param  mixed $field Can be any thing which is needed in string format
	 * @return string
	 */
	public static function getMongoID($field) {
		if (is_object($field)) {
			$id = sprintf('%s', $field);
		} else {
			$id = $field;
		}
		return $id;
	}

	public static function flashMsg($msg) {
		$session = Registry::get("session");
		$session->set('$flashMessage', $msg);
	}

	/**
	 * Converts the string to a valid BSON ObjectID of 24 characters or if $id -> string
	 * else if $id -> array recursively converts each id to bson objectId
	 * 
	 * @param  string|object|array $id ID to converted to bson type
	 * @return string|object|array (objects)     Returns an BSON ObjectID if $id is valid else empty string
	 */
	public static function mongoObjectId($id) {
		$result = "";
		if (is_array($id)) {
			$result = [];
			foreach ($id as $i) {
				$result[] = self::mongoObjectId($i);
			}
		} else if (!Services\Db::isType($id, 'id')) {
			if (strlen($id) === 24) {
				$result = new \MongoDB\BSON\ObjectID($id);	
			} else {
				$result = "";
			}
        } else {
        	$result = $id;
        }
        return $result;
	}

	/**
	 * Downloads the Image From the URL by checking its Content Type and matching against
	 * the valid image content types defined by the standard. Image is stored into
	 * the uploads directory
	 * @param  string $url URL of the image
	 * @return string|boolean      FALSE on failure else uploaded image name
	 */
	public static function downloadImage($url = null, $opts = []) {
		if (!$url) { return false; }
		try {
			$bot = new Bot(['image' => $url], ['logging' => false]);
			$bot->execute();
			$documents = $bot->getDocuments();
			$doc = array_shift($documents);

			$contentType = $doc->type;
			preg_match('/image\/(.*)/i', $contentType, $matches);
			if (!isset($matches[1])) {
				return false;
			} else {
				$extension = $matches[1];
			}

		} catch (\Exception $e) {
			return false;
		}
		
		if (!preg_match('/^jpe?g|gif|bmp|png|tif$/', $extension)) {
			return false;
		}

		$path = APP_PATH . '/public/assets/uploads/images/';
		$img = uniqid() . ".{$extension}";

		$str = file_get_contents($url);
		if ($str === false) {
			return false;
		}
		$status = file_put_contents($path . $img, $str);
		if ($status === false) {
			return false;
		}
		return $img;
	}

	public static function particularFields($field) {
	    switch ($field) {
	        case 'name':
	            $type = 'text';
	            break;
	        
	        case 'password':
	            $type = 'password';
	            break;

	        case 'email':
	            $type = 'email';
	            break;

	        case 'phone':
	            $type = "text";
	            break;

	        default:
	            $type = 'text';
	            break;
	    }
	    return array("type" => $type);
	}

	public static function parseValidations($validations) {
	    $html = ''; $pattern = '';
	    foreach ($validations as $key => $value) {
	        preg_match("/(\w+)(\((\d+)\))?/", $value, $matches);
	        $type = isset($matches[1]) ? $matches[1] : 'none';
	        switch ($type) {
	            case 'required':
	                $html .= ' required="" ';
	                break;
	            
	            case 'max':
	                $html .= ' maxlength="' .$matches[3] . '" ';
	                break;

	            case 'min':
	                $pattern .= ' pattern="(.){' . $matches[3] . ',}" ';
	                break;
	        }
	    }
	    return array("html" => $html, "pattern" => $pattern);
	}

	public static function fetchCampaign($url) {
		$data = [];
    	
        try {
    		$bot = new Bot(['cloud' => $url], ['logging' => false]);
    	    $bot->execute();
    	    $documents = $bot->getDocuments();	// because only variables can be passed as reference
    	    $doc = array_shift($documents);

    	    $type = $doc->type;
    	    if (preg_match("/image/i", $type)) {
    	        $data["image"] = $data["url"] = $url;
    	        $data["description"] = $data["title"] = "";
    	        return $data;
    	    }
            $data["title"] = $doc->query("/html/head/title")->item(0)->nodeValue;
            $data["url"] = $url;
            $data["description"] = "";
            $data["image"] = "";

            $metas = $doc->query("/html/head/meta");
            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);
                
                if ($meta->getAttribute('name') == 'description') {
                    $data["description"] = $meta->getAttribute('content');
                }

                if ($meta->getAttribute('property') == 'og:image') {
                    $data["image"] = $meta->getAttribute('content');
                }
            }
        } catch (\Exception $e) {
            $data["url"] = $url;
            $data["image"] = $data["description"] = $data["title"] = "";
        }
        return $data;
	}

	public static function randomPass($numbers = true) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		if (!$numbers) {
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
	}

	public static function urlRegex($url) {
		$regex = "((https?|ftp)\:\/\/)"; // SCHEME
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

        $result = preg_match('/^'.$regex.'$/', $url);
        return (boolean) $result;
	}

	/**
	 * Converts dates to be passed for mongodb query
	 * @return array       mongodb start and end date
	 */
	public static function dateQuery($dateQ, $endDate = null) {
		if (!is_array($dateQ)) {
			$opts = ['start' => $dateQ, 'end' => $endDate];
		} else {
			$opts = $dateQ;
		}
		$start = strtotime("-1 day");
		$end = strtotime("+1 day");

		if (isset($opts['start'])) {
			$start = (int) strtotime($opts['start'] . ' 00:00:00');	// this returns in seconds
		}
		$start = $start * 1000;	// we need time in milliseconds

		if (isset($opts['end'])) {
			$end = (int) strtotime($opts['end'] . ' 23:59:59');
		}
		$end = ($end * 1000) + 999;

		return [
			'start' => new \MongoDB\BSON\UTCDateTime($start),
			'end' => new \MongoDB\BSON\UTCDateTime($end)
		];
	}

	public static function toArray($object) {
		$arr = [];
		$obj = (array) $object;
		foreach ($obj as $key => $value) {
			if (Services\Db::isType($value, 'document')) {
				$arr[$key] = self::toArray($value);
			} else {
				$arr[$key] = $value;
			}
		}
		return $arr;
	}

	public static function mongoRegex($val) {
		return new \MongoDB\BSON\Regex($val, 'i');
	}

	public static function dateArray($arr) {
		$result = [];
		foreach ($arr as $key => $value) {
			$date = \Framework\StringMethods::only_date($key);
			$result[$date] = $value;
		}
		return $result;
	}

	public static function getClientIp() {
		$headers = getallheaders();
		$ip = '';
		if (isset($headers['Cf-Connecting-Ip'])) {
			$ipaddr = $headers['Cf-Connecting-Ip'];
			$ip = explode(",", $ipaddr);

			$ip = $ip[0];
		} else {
			$ip = RequestMethods::server('REMOTE_ADDR') ?? RequestMethods::server('HTTP_CLIENT_IP');
		}
		return $ip;
	}

	public static function encrypt($data, $key) {
		$e = new Services\Encrypt(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		$hashed = $e->encrypt($data, $key);

		return utf8_encode($hashed);
	}

	public static function decrypt($data, $key) {
		$data = utf8_decode($data);
		$e = new Services\Encrypt(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		$normal = $e->decrypt($data, $key);

		return $normal;
	}

	public static function getConfig($name, $property = null) {
		$config = Registry::get("configuration")->parse("configuration/{$name}");

		if ($property && property_exists($config, $property)) {
			return $config->$property;
		}
		return $config;
	}

	/**
	 * Uploads the image sent by the user in $_FILES array when submitting
	 * the form using file-upload. Assigns a name to the file and also checks
	 * for a valid file extension based on the type (if provided)
	 * 
	 * @return string|boolean      FALSE on failure else uploaded image name
	 */
	public static function uploadImage($name, $type = "images", $opts = []) {
	    if (isset($_FILES[$name])) {
	        $file = $_FILES[$name];
	        $path = APP_PATH . "/public/assets/uploads/{$type}/";
	        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

	        $extensionRegex = $opts['extension'] ?? 'jpe?g|gif|bmp|png|tif';
	        if (!preg_match("/^".$extensionRegex."$/", $extension)) {
	            return false;
	        }

	        if (isset($opts['name'])) {
	            $filename = $opts['name'];
	        } else {
	            $filename = uniqid() . ".{$extension}";
	        }

	        if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
	            return $filename;
	        }
	    }
	    return false;
	}

	public static function downloadVideo($url, $opts = []) {
		$folder = self::media('', 'show', ['type' => 'video']);
		$extension = $opts['extension'] ?? 'mp4';
		try {
			$ytdl = new \YTDownloader\Service\Download($url, [
				'path' => $folder
			]);
			$file = $ytdl->convert($extension, [
				'type' => 'video',
				'quality' => $opts['quality'] ?? '240p'
			]);

			$name = uniqid() . ".{$extension}";
			copy($folder . $file, $folder . $name);
			unlink($folder . $file);
		} catch (\Exception $e) {
			$name = false;
		}
		return $name;
	}

	public static function media($name, $task = 'show', $opts = []) {
		$type = ($opts['type']) ?? 'image';
		$folder = APP_PATH . "/public/assets/uploads/{$type}s/";
		switch ($task) {
			case 'remove':
				@unlink($folder . $name);
				break;
			
			case 'show':
				return $folder . $name;

			case 'upload':
				$func = "upload" . ucfirst($type);
				$media = self::$func($name, 'images', $opts);
				if ($media === false) {
					$media = ' ';
				}
				return $media;

			case 'download':
				$func = "download" . ucfirst($type);
				$media = self::$func($name, $opts);
				if ($media === false) {
					$media = ' ';
				}
				return $media;

		}
	}
}