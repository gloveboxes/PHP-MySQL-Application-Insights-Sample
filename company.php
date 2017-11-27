

<html>
<head>
<title>Glovebox PHP/MySQL Sample</title>
</head>
<body>
<?php

function currentTime() {
  $time = microtime();
  $time = explode(' ', $time);
  $time = $time[1] + $time[0];
  return $time;
}

$startTime = currentTime();

require_once 'vendor/autoload.php';

$telemetryClient = new \ApplicationInsights\Telemetry_Client();
$telemetryClient->getContext()->setInstrumentationKey('561e13ff-c516-46d7-b415-92457a30bfdb');

// $telemetryClient->trackPageView('company', 'http://dgphpsample.azurewebsites.net/company.php');

ini_set('display_errors', 1);
error_reporting(~0);

$ini = parse_ini_file('config.ini');

$conn = mysqli_connect($ini['db_host'], $ini['db_user'], $ini['db_password'], $ini['db_name']);

$sql = "SELECT * FROM departments ORDER BY departments.dept_name";

$sql = "SELECT *, (SELECT COUNT(*) FROM dept_emp WHERE departments.dept_no = dept_emp.dept_no) AS number_employees
FROM departments ORDER BY departments.dept_name";

$query = mysqli_query($conn, $sql);

?>
<h1>Welcome</h1>
<h2>Departments</h2>

<table width="600" border="1">
  <tr>
    <th width="91"> <div align="left">Department Name </div></th>
    <th width="160"> <div align="left">Number of Employees </div></th>
    <th width="160"> <div align="center">Department Number </div></th>

  </tr>
<?php
while ($result=mysqli_fetch_array($query, MYSQLI_ASSOC)) {
?>
  <tr>
  <td align="left"><a href="department.php?dept_no=<?php echo $result["dept_no"];?>"><?php echo $result["dept_name"];?></a></td>
  <td><div align="center"><?php echo $result["number_employees"];?></div></td>
  <td><div align="center"><?php echo $result["dept_no"];?></div></td>
    
  </tr>
<?php
}

$telemetryClient->trackDependency('Departments', \ApplicationInsights\Channel\Contracts\Dependency_Type::OTHER, $sql, time(), currentTime() - $startTime, true);

?>
</table>
<?php

$finishTime = currentTime();
$total_time = round(($finishTime - $startTime), 4) * 1000;
echo '<br/><br/>Page generated in '.$total_time.' milliseconds.';

$telemetryClient->trackPageView('company', 'http://dgphpsample.azurewebsites.net/company.php', $total_time);
$telemetryClient->trackRequest("company", "http://dgphpsample.azurewebsites.net/company.php", time(), $total_time);
$telemetryClient->flush();

// unset($stmt);
mysqli_close($conn);
// unset($conn);
?>
</body>
</html>