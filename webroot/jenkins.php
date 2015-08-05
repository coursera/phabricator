<?php
  $query = $_SERVER['QUERY_STRING'];
  #print "HEY $query<br>";
  $script = $_SERVER['SCRIPT_NAME'];
  $script = preg_replace('#/?jenkins.php/?#', '', $script);

  # http://192.168.56.101:81/jenkins.php/jenkins.dkandu.me/job/c3-diph/buildWithParameters?DIFF_ID=926
  $username = "phabricator";
  $password = "";

  $url2 = "$script?$query";
  print "Kicking off a build: $url2\n";
  system("curl -X POST 'https://$username:$password@$url2'");
?>