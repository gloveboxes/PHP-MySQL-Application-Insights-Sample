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

$start = currentTime();

require_once 'vendor/autoload.php';

$telemetryClient = new \ApplicationInsights\Telemetry_Client();
$telemetryClient->getContext()->setInstrumentationKey('561e13ff-c516-46d7-b415-92457a30bfdb');




ini_set('display_errors', 1);
error_reporting(~0);

$dept_no = null;
    
if (isset($_GET["dept_no"])) {
    $strDeptNo = $_GET["dept_no"];
}

if (isset($_GET["page"])) {
    $PageNumber = $_GET["page"];
} else {
    $PageNumber = 0;
}


$ini = parse_ini_file('config.ini');

$conn = mysqli_connect($ini['db_host'], $ini['db_user'], $ini['db_password'], $ini['db_name']);

//  $sql = "SELECT * FROM employees JOIN dept_emp ON dept_emp.emp_no = employees.emp_no WHERE dept_emp.dept_no = '".$strDeptNo."' ORDER BY employees.last_name, employees.first_name LIMIT 20 OFFSET ".$PageNumber * 20;

$sql = "SELECT *,(SELECT ROUND(AVG(salaries.salary)) FROM salaries where employees.emp_no = salaries.emp_no) AS avg_salary ".
"FROM employees JOIN dept_emp ON dept_emp.emp_no = employees.emp_no WHERE dept_emp.dept_no = '".$strDeptNo."' ORDER BY avg_salary DESC LIMIT 20 OFFSET ".$PageNumber * 20;

// $start = time();

$query = mysqli_query($conn, $sql);



// echo date();

?>
<a href="company.php">Home</a> 

<h2>Employees by Department</h2>

<table width="900" border="1">
  <tr>
  <th width="120"> <div align="left">Last Name</div></th>
  <th width="120"> <div align="left">First Name</div></th>
  <th width="120"> <div align="left">Hire Date</div></th>
  <th width="120"> <div align="left">Average Salary</div></th>
  <th width="140"> <div align="left">Employee Number</div></th>
  </tr>
<?php
while ($result=mysqli_fetch_array($query, MYSQLI_ASSOC)) {
?>
  <tr>
  <td><div align="left"><?php echo $result["last_name"];?></div></td>
    <td><div align="left"><?php echo $result["first_name"];?></div></td>

    <td><div align="left"><?php echo $result["hire_date"];?></div></td>
    <td><div align="left"><?php echo $result["avg_salary"];?></div></td>
    <td align="right"><a href="employee.php?emp_no=<?php echo $result["emp_no"];?>&dept_no=<?php echo $strDeptNo;?>&page=<?php echo $PageNumber;?>"><?php echo $result["emp_no"];?></a></td>
  </tr>
<?php
}
$telemetryClient->trackDependency('EmployeeByDept', \ApplicationInsights\Channel\Contracts\Dependency_Type::SQL, $sql, time(), currentTime() - $start, true);

?>
</table>
<?php

$nextPage = $PageNumber + 1;
$prePage = $PageNumber == 0 ? 0 : $PageNumber - 1;

echo "<br/>";
echo "<a href='department.php?dept_no=$strDeptNo&page=$prePage'>Back</a>&nbsp;&nbsp;&nbsp;";

for ($i = $nextPage; $i <= $nextPage + 25; $i++) {
  // echo "$i&nbsp;&nbsp;&nbsp;&nbsp;";

    echo "<a href='department.php?dept_no=$strDeptNo&page=$i'>$i</a>";
    echo "&nbsp;&nbsp;";
}

echo "<a href='department.php?dept_no=$strDeptNo&page=$nextPage'>Next</a>";


$finish = currentTime();
$total_time = round(($finish - $start), 4) * 1000;
echo '<br/><br/>Page generated in '.$total_time.' milliseconds.';

$telemetryClient->trackPageView('department', 'http://dgphpsample.azurewebsites.net/department.php', $total_time);
$telemetryClient->trackRequest("department.php", "department.php", time(), $total_time);

$startFlush = currentTime();

// $telemetryClient->flush();

$endFlush = currentTime();
$totalFlush = round(($endFlush - $startFlush), 4) * 1000;
echo '<br/><br/>flush time '.$totalFlush.' milliseconds.';

mysqli_close($conn);
?>
</body>
</html>

