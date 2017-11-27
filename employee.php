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

// $telemetryClient->trackPageView('employee', 'http://dgphpsample.azurewebsites.net/employee.php');


ini_set('display_errors', 1);
error_reporting(~0);

$dept_no = null;
    
if (isset($_GET["emp_no"])) {
    $strEmpNo = $_GET["emp_no"];
}

if (isset($_GET["dept_no"])) {
    $dept_no = $_GET["dept_no"];
}

if (isset($_GET["page"])) {
    $pageNumber = $_GET["page"];
}

$ini = parse_ini_file('config.ini');

$conn = mysqli_connect($ini['db_host'], $ini['db_user'], $ini['db_password'], $ini['db_name']);

$sql = "SELECT *, (SELECT ROUND(AVG(salaries.salary)) FROM salaries where employees.emp_no = salaries.emp_no) AS avg_salary ".
", (SELECT ROUND(MAX(salaries.salary)) FROM salaries where employees.emp_no = salaries.emp_no) AS max_salary ".
", (SELECT ROUND(MIN(salaries.salary)) FROM salaries where employees.emp_no = salaries.emp_no) AS min_salary ".
"FROM `employees` WHERE employees.emp_no = ".$strEmpNo;

$query = mysqli_query($conn, $sql);

?>

<?php

if (isset($dept_no) and isset($pageNumber)){
  echo "<a href='department.php?dept_no=$dept_no&page=$pageNumber'>Back</a>&nbsp;&nbsp;&nbsp;";
}
?>

<a href="company.php">Home</a> 


<h2>Employee Details</h2>

<table  border="1">
  <tr>
    <th width="91"> <div align="center">First Name</div></th>
    <th width="98"> <div align="center">Last Name</div></th>
    <th width="98"> <div align="center">Hire Date</div></th>
    <th width="98"> <div align="center">Min Salary</div></th>
    <th width="98"> <div align="center">Max Salary</div></th>
    <th width="98"> <div align="center">Average Salary</div></th>
    <th width="98"> <div align="center">Employee Number</div></th>

  </tr>
<?php
while ($result=mysqli_fetch_array($query, MYSQLI_ASSOC)) {
?>
  <tr>
    <td><div align="center"><?php echo $result["first_name"];?></div></td>
    <td><div align="center"><?php echo $result["last_name"];?></div></td>
    <td><div align="center"><?php echo $result["hire_date"];?></div></td>
    <td><div align="center"><?php echo $result["min_salary"];?></div></td>
    <td><div align="center"><?php echo $result["max_salary"];?></div></td>
    <td><div align="center"><?php echo $result["avg_salary"];?></div></td>
    <td><div align="center"><?php echo $result["emp_no"];?></div></td>
  </tr>
<?php
}
?>
</table>

<br/>

<h2>Salary</h2>

<?php

$sql = "SELECT * FROM `salaries` WHERE salaries.emp_no = ".$strEmpNo." ORDER BY from_date";
$query = mysqli_query($conn, $sql);

?>
<table width="600" border="1">
  <tr>
    <th width="91"> <div align="center">Salary</div></th>
    <th width="98"> <div align="center">From</div></th>
    <th width="98"> <div align="center">To</div></th>

  </tr>
<?php
while ($result=mysqli_fetch_array($query, MYSQLI_ASSOC)) {
?>
  <tr>
    <td><div align="center"><?php echo $result["salary"];?></div></td>
    <td><div align="center"><?php echo $result["from_date"];?></div></td>
    <td><div align="center"><?php echo $result["to_date"];?></div></td>
  </tr>
<?php
}
?>
</table>


<h2>Job Titles</h2>

<?php

$sql = "SELECT * FROM `titles` where titles.emp_no = ".$strEmpNo." ORDER BY from_date";
$query = mysqli_query($conn, $sql);

?>
<table width="600" border="1">
  <tr>
    <th width="91"> <div align="center">Title</div></th>
    <th width="98"> <div align="center">From</div></th>
    <th width="98"> <div align="center">To</div></th>

  </tr>
<?php
while ($result=mysqli_fetch_array($query, MYSQLI_ASSOC)) {
?>
  <tr>
    <td><div align="center"><?php echo $result["title"];?></div></td>
    <td><div align="center"><?php echo $result["from_date"];?></div></td>
    <td><div align="center"><?php echo $result["to_date"];?></div></td>
  </tr>
<?php
}
?>
</table>





<?php
$telemetryClient->trackDependency('EmployeeDetail', \ApplicationInsights\Channel\Contracts\Dependency_Type::OTHER, $sql, time(), currentTime() - $startTime, true);

$finishTime = currentTime();
$total_time = round(($finishTime - $startTime), 4) * 1000;
echo '<br/><br/>Page generated in '.$total_time.' milliseconds.';

$telemetryClient->trackPageView('employee', 'http://dgphpsample.azurewebsites.net/employee.php', $total_time);
$telemetryClient->trackRequest("employee", "http://dgphpsample.azurewebsites.net/employee.php", time(), $total_time);
$telemetryClient->flush();

// $telemetryClient->trackPageView('employee', '/employee.php', 52, ['InlineProperty' => 'glovebox'], ['duration' => 54]);
$telemetryClient->flush();

mysqli_close($conn);


?>
</body>
</html>