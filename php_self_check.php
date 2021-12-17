<?php

const ECHO_OUTPUT=1;
$email_ricipiants_contacts=array(""); // ADMIN EMAILS


const PHP_STAN_LEVEL=9; // see https://phpstan.org/user-guide/rule-levels

//Get all folders
$all_folders = getSubDirectories("../");
#echo json_encode($all_folders);


$all_files = array("");;
foreach($all_folders as $folder){
    //Give out the actual folder
    if(ECHO_OUTPUT ==1)
    echo $folder . "<br>";


   //Get all files
    $fileList = glob($folder  .  '/*.php');
    $all_files = array_merge($all_files, $fileList);


}


$test_index=0;

foreach($all_files as $file){
$sha256 = hash_file("sha256", $file);

if(ECHO_OUTPUT ==1)
echo json_encode($file) .  " sha256: " . $sha256 . "<br>";



            if (file_exists($file)) {

                      $myfile = fopen($file, "r") or die("Unable to open file!:" . $file);
                      $code = fread($myfile,filesize($file));
                      #$code = htmlspecialchars($code);
                      $code = str_replace('"', '\"' ,$code);
                    
                    
                    
                    
                      //Send code to https://phpstan.org


                  
                    $url = "https://api.phpstan.org/analyse";
                    

                    
/*
                    $data = array(
                          'code' => 'hfjhf',
                          'level' => PHP_STAN_LEVEL,
                          'strictRules' => 'false',
                          'bleedingEdge' => 'false',
                          'treatPhpDocTypesAsCertain' => 'true',
                          'saveResult' => 'false'
                    );
                  
                    print_r($data);

                    $options = array(
                      'http' => array(
                        'method'  => 'POST',
                        'content' => json_encode( $data ),
                        'header'=>  "Content-Type: application/json\r\n" .
                                    "Accept: application/json\r\n"
                        )
                    );
                    
 

                    $context  = stream_context_create( $options );
                    $result = file_get_contents( $url, false, $context );
                    $response = json_decode( $result );


                    echo $response;
*/



                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                    $headers = array(
                    "Content-Type: application/json",
                    );
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                    $data = '{"code":"' . $code .'","level":"' . PHP_STAN_LEVEL . '","strictRules":false,"bleedingEdge":false,"treatPhpDocTypesAsCertain":true,"saveResult":false}';
                    #echo htmlspecialchars($data);

                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

                    //for debug only!
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                    $resp = curl_exec($curl);
                    curl_close($curl);


                    $result = json_decode($resp);






                    if($result == null)
                        die("It was not possible to send the file (" . $file . ") to the server to phpstan.org.");

                    if(ECHO_OUTPUT ==1)
                    echo "result_answer: " . json_encode($result) . "<br>";

                    #echo json_encode($result->tabs[0]->errors);
                    $number_of_errors = count($result->tabs[0]->errors);


                    if(ECHO_OUTPUT ==1){
                      echo "number_of_errors:". $number_of_errors . "<br>";
                    }


                    //Inform admin if there are some errors
                    if($number_of_errors > 0){
                            $subject="Found " . $number_of_errors . " errors in " . $file;
                            $message="Subject: " . $subject . " <br> Full result: " . $result;
                            sendmailtorecipients($email_ricipiants_contacts,$subject,$message);
                            if(ECHO_OUTPUT ==1)
                            echo "send_mail <br>";
                    }




                    //Giveout status
                    if(ECHO_OUTPUT ==1)
                    echo "Status: " . (($test_index+1)*100/count($all_files)) . "%<br>";


                    //Dont DDOS the server!
                    sleep(1);









            }
                    
            $test_index=$test_index+1;
}









  // Return an array with the list of sub directories of $dir
  function getSubDirectories($dir)
  {
      $subDir = array();
      // Get and add directories of $dir
      $directories = array_filter(glob($dir), 'is_dir');
      $subDir = array_merge($subDir, $directories);
      // Foreach directory, recursively get and add sub directories
      foreach ($directories as $directory) $subDir = array_merge($subDir, getSubDirectories($directory.'/*'));
      // Return list of sub directories
      return $subDir;
  }



  
function sendmailtorecipients($email_ricipiants_contacts,$subject,$message){
	// $contacts array
	//   $contacts = array("youremailaddress@yourdomain.com","youremailaddress@yourdomain.com");
	//....as many email address as you need
	
			foreach($email_ricipiants_contacts as $contact) {
			
			$to = $contact;
			mail($to, $subject, $message);
			
			}
	
	
	}




?>