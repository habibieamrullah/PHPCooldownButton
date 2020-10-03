<?php 

$baseurl = "http://localhost/ThirteeNov/PHPCooldownButton/";
//Let's say, the countdown button can be enabled again after 10 seconds
$newbuttonafter = 10; //after 10 seconds

//A function to read file content
function phpReadFile($filename){
	//Create file if not exist
	if(!is_file($filename)){
		file_put_contents($filename, "");
	}
	$myfile = fopen($filename, "r") or die("Unable to open file!");
	$filecontent = "No content.";
	if(filesize($filename) > 0)
		$filecontent = fread($myfile, filesize($filename));
	fclose($myfile);
	return $filecontent;
}

//A function to write a text file. $wtw is "What to Write" :D.
function phpWriteFile($filename, $wtw){
	$myfile = fopen($filename, "w") or die("Unable to open file!"); 
	fwrite($myfile, $wtw);
	fclose($myfile);
	echo "<br>" . $wtw . " has been written.";
}

//A function to get the client IP address
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
?>

<!DOCTYPE html>
<html>
	<head>
		<title>How to make cooldown button in PHP</title>
	</head>
	<body>
		<a href="<?php echo $baseurl ?>">Home</a>
		<?php
		//First, we need to detect user's IP address
		$useripaddress = get_client_ip();
		?>
		
		<p>Your IP Address: <?php echo $useripaddress; ?></p>
	
		<?php
		$websitedata = phpReadFile("useripinfo.txt");
		$newwebsitedata = ""; //this one will be a new website data variable.
		
		?>
		<p>Raw Website Data = <?php echo nl2br($websitedata) ?></p>
		<?php
		
		//This variable store the current time in millisecond to a file.
		$currenttime = round(microtime(true) * 1000);
		
		//We need to process the raw data, and make a list of ip addresses that visited this website
		$iplist = explode(",", $websitedata); //splitting data by comma
		$isnew = true;
		$lastvisit = 0;
		//Doing a loop. In this loop we check the current ip address, is it already visited this website or not
		echo "<p>List of IP Addresses:</p>";
		echo "<ul>";
		for($i = 0; $i < count($iplist); $i++){
			if($iplist[$i] != ""){
				//iplist itself need to be split by dash (-)
				$listip = explode("-", $iplist[$i])[0];
				$listtime = explode("-", $iplist[$i])[1];
				echo "<li>IP Address: " . $listip . " Time:" . $listtime . "</li>"; 
				
				//Check is this ip address is current website vistor ip address or not
				//We need to make sure there should not be duplicated ip address on website data
				if($listip == $useripaddress){
					$isnew = false;
					echo "<li>IP Address above is your current IP Address. It means you already visited this website before.</li>";
					//So, if it is already visted this website, let's not write to file again
					//Now we focus on here... If visitor is already visited before, we need to check when did he visit.
					$lastvisit = $listtime; //lastvisit variable hold the time value the last time he/she visited this website
					if(isset($_GET["buttonclicked"])){
						if($listip == $useripaddress){
							
							$timeelapsed = (($currenttime-$lastvisit) / 1000);
							if($timeelapsed > $newbuttonafter){
								$newwebsitedata .= $listip . "-" . $currenttime . ","; //appending new websitedata, but this time it stores new time value
							}else{
								$newwebsitedata .= $listip . "-" . $listtime . ","; //appending new websitedata
							}
						}else{
							$newwebsitedata .= $listip . "-" . $listtime . ","; //appending new websitedata
						}
					}else{
						$newwebsitedata .= $listip . "-" . $listtime . ","; //appending new websitedata
					}
				}else{
					$newwebsitedata .= $listip . "-" . $listtime . ","; //appending new websitedata
				}
				
			}
		}
		echo "</ul>";
		
		//WARNING: there is repetitive $newwebsitedata .= $listip . "-" . $listtime . ",";  as seen above and need to be fixed... I'm not going to do it now :D//
		
		if(isset($_GET["buttonclicked"])){
			if($isnew){
				echo "<h1>Hurray! You are new visitor!</h1>";
				
				?>
				
				<p>Current time in millisecond is: <?php echo $currenttime; ?></p>
				
				<?php
				//Now we have both $useripaddress and $currenttime, next is to save both info to a file.
				//We append newwebsitedata with new visitor info
				$newwebsitedata .= $useripaddress . "-" . $currenttime . ",";
				
				
			}
			
			?>
			<script>
			location.href = "<?php echo $baseurl ?>";
			</script>
			<?php
		}
		
		//Saving new website data to the file
		phpWriteFile("useripinfo.txt", $newwebsitedata);
		
		//Now we talk about the countdown button
		if($lastvisit != 0 && !$isnew){
			echo "<h1>You are old vistor, you visted us on " . $lastvisit . "</h1>";
			//We need to convert the millisecond to second...
			$timeelapsed = (($currenttime-$lastvisit) / 1000);//This will hold the value of how many seconds elapsed after previous visit
			echo "<p>" .$lastvisit. " means " . $timeelapsed . " second ago.</p>";
			
			//in this case, we check how long the time elapsed...
			if($timeelapsed > $newbuttonafter){
				
				//re-enable the button
				?>
				<p>Yes, you can clik the button again</p>
				
				<button onclick="buttonclicked()" style="background-color: blue; color: white;">Click me!</button>
				<?php
				
			}else{
				//disable the button
				?>
				<p>Hey, get back again later okay?!</p>
				<button onclick="buttonclicked()" style="background-color: red; color: gray;">Don't click me!</button>
				<?php
			}
			
		}else{
			//Here, because this vistor is new visitor, we make the countdown button available:
			?>
			<button onclick="buttonclicked()" style="background-color: blue; color: white;">Click me!</button>
			<?php
		}
		
		
		?>
		<a href="<?php echo $baseurl ?>">Home</a>
		<script>
			function buttonclicked(){
				location.href = "<?php echo $baseurl ?>?buttonclicked";
			}
		</script>
		
	</body>
</html>
