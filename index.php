<html>
	<head>
		<title>Mixcrib</title>
		<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>
		<script type="text/javascript" src="resources/css/bootstrap/js/bootstrap.js"></script>
		<script src="https://w.soundcloud.com/player/api.js" type="text/javascript"></script>

		<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
		<link href="resources/css/bootstrap/css/bootstrap.css" rel="stylesheet" type="text/css" />
		<link href="resources/css/styles.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">

			// youtube stuff start
			var tag = document.createElement('script');

      tag.src = "https://www.youtube.com/iframe_api";
      var firstScriptTag = document.getElementsByTagName('script')[0];
      firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
			var player;
			var playList = new Array();
			var position;
      
      function onPlayerReady() {
      	player.playVideo();
    	}

    	function onPlayerStateChange(e){
    		//ended
    		if(e.data == YT.PlayerState.ENDED){
    			$(".right").click();
    		}
    	}


    	//youtube stuff end

			$("document").ready(function(){
				$("#pl-import-form").submit(function(){
					var pl_url = $("#pl_url").val();
					
					if(pl_url == "")
						return false;

					$("#pl_url").val("");
					$("#pl-player").html("<img src='resources/img/loader.gif' id='ajax-loader-image' />");
					$.ajax({
					  type: "GET",
					  url: "generate.php",
					  data: { pl_url:  pl_url}
					}).done(function( msg ) {
								$("#pl-slider").html("");
								msg = $.parseJSON(msg);
								playList = playList.concat(msg);
								playList = shuffle(playList);
								$("#playlist-wrapper").html("");
								for(var i=0; i < playList.length; ++i){
									var divText = "", imgText = "";
									
									if(playList[i]['type'] == "youtube")
										imgText = "<img class='type-logo' src='resources/img/youtube-logo.png' />";
									else if(playList[i]['type'] == "soundcloud")
										imgText = "<img class='type-logo' src='resources/img/soundcloud-logo.png' />";
									
									divText += "<div data-position='"+i+"' class='playlist-track'>" + imgText + playList[i]['title'] + "</div>";
									$("#playlist-wrapper").append(divText);
								}
								position = 0;
								createPlayer(playList[0]['type'], playList[0]['tracks'], position);
						});
					return false;
				});

				$(".carousel-arrows").click(function(){
					if($(this).hasClass('right'))
						position = position <= playList.length - 2 ? position + 1 : 0;
					else if($(this).hasClass('left'))
						position = position >=1 ? position - 1 : playList.length - 1;

					createPlayer(playList[position]['type'], playList[position]['tracks'], position);
				});

				$(document).on("click", ".playlist-track", function() {
					var pos = $(this).attr("data-position");
					createPlayer(playList[pos]['type'], playList[pos]['tracks'], pos);
				});
			});

		function createPlayer(type,trackId, newPosition){
      if(type == "youtube"){
      	$("#pl-player").html("<div id='current-player-frame'></div>");
			  player = new YT.Player("current-player-frame", {
	        height: '315',
	        width: '560',
	        videoId: trackId,
	        events: {
	        		'onReady': onPlayerReady,
	            'onStateChange': onPlayerStateChange
	        }
	      });
      }
      
      else if(type == "soundcloud"){
      	var divText = "";
      	divText += '<iframe id="soundcloud_widget"';
      	divText += 'src="http://w.soundcloud.com/player/?url=https://api.soundcloud.com/tracks/'+trackId;
      	divText += '?enable_api=true&sharing=false&auto_play=true&show_comments=false';
      	divText += '&default_width=560&default_height=120&frameborder=no" width="560" height="120"></iframe>';
      	$("#pl-player").html(divText);

	    	var widgetIframe = document.getElementById('soundcloud_widget'),
      	widget       = SC.Widget(widgetIframe);

	      widget.bind(SC.Widget.Events.READY, function() {
		      widget.bind(SC.Widget.Events.FINISH, function() {
		      	$(".right").click();
		    	});
		    });
      }
		}

		function shuffle(o){ //v1.0
    	for(var j, x, i = o.length; i; j = parseInt(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    	return o;
		}
		</script>
	</head>
	<body>
		<div id="content-main-wrapper" class="row">
			<div class="span8">
				<div id="pl-import-form-wrapper" >
					<form action="generate.php" method="GET" id="pl-import-form">
						<input type="text" name="pl_url" id="pl_url" placeholder="Enter your playlist/track URL" />
						<input type="submit" value="Import" class="btn btn-primary btn-large btn-block" />
					</form>
				</div>
				<div id="pl-player-wrapper" >
					<div id="pl-player">
			  	</div>
				  <a class="left carousel-arrows" href="#pl-slider" data-slide="prev"><</a>
				  <a class="right carousel-arrows" href="#pl-slider" data-slide="next">></a>
			  </div>
		  </div>
		  <div id="playlist-wrapper" class="span5">
	  	</div>
		</div>
	</body>
</html>