<?php
	libxml_use_internal_errors(true);

	function getStuff($url){
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$server_output = curl_exec ($ch);
		curl_close ($ch);
		return $server_output;
	}

	function print_array($in_array){
		echo "<pre>";
		print_r($in_array);
		echo "</pre>";
	}

		$pl_url = $_GET['pl_url'];
		$domain = parse_url($pl_url, PHP_URL_HOST);
		
		if(strstr($domain, "youtube.com"))
			$site = "youtube";
		else if(strstr($domain, "soundcloud.com"))
			$site = "soundcloud";

		switch($site){
			case "youtube": {
				$query = parse_url($pl_url, PHP_URL_QUERY);
				parse_str($query, $params);
				
				if(isset($params['list'])){
					$pl_id = $params['list'];
					$url = "https://www.googleapis.com/youtube/v3/playlistItems?".
					"part=snippet&key=AIzaSyB7wNJo-OvzJptcNVfz9y0YSI7NLz8ER2Q&playlistId=".$pl_id."&maxResults=50";	
					$content = getStuff($url);
					$content = json_decode($content, true);
					
					for($i=0; $i<count($content['items']); ++$i){
						$result[$i]['tracks'] = $content['items'][$i]['snippet']['resourceId']['videoId'];
						$result[$i]['title'] = $content['items'][$i]['snippet']['title'];
						$result[$i]['type'] = "youtube";
					}
					print_r(json_encode($result));
				}

				else if(isset($params['v'])){
					$vid_id = $params['v'];
					$content = getStuff($pl_url);
					$DOM = new DOMDocument;
					$DOM->loadHTML($content);
					$vidTitle = $DOM->getElementById('eow-title');
					//echo $vidTitle->nodeValue;

					$result[0]['tracks'] = $vid_id;
					$result[0]['title'] = 'Shit';
					$result[0]['type'] = "youtube";
					print_r(json_encode($result));
				}	
				break;
			}

			case "soundcloud" : {
				$pl_url = $_GET['pl_url'];
				$pl_url = str_replace("https", "http", $pl_url);
				$content = getStuff($pl_url);

				$DOM = new DOMDocument;
				$DOM->loadHTML($content);
				$trackListDoms = $DOM->getElementsByTagName('ol');

				for ($i = 0; $i < $trackListDoms->length; $i++){
				  if($trackListDoms->item($i)->getAttribute('class') == 'tracks'){
				  		$trackNodeList = $trackListDoms->item($i);
				  		break;
				  }
				}
				$trackList = $trackNodeList->getElementsByTagName('li');
				$i = 0;
				foreach($trackList as $track){
					$titleList = $track->getElementsByTagName('a');
					foreach ($titleList as $title) {
						if($title->getAttribute('class') == "set-track-title"){
							$result[$i]['title'] = $title->nodeValue;
							break;
						}
					}
					if($track->hasAttribute('data-sc-track')){
						$result[$i]['tracks'] = $track->getAttribute('data-sc-track');
						$result[$i]['type'] = "soundcloud";
						++$i;
					}
				}
				print_r(json_encode($result));
				break;
			}
		}
?>
