#!/usr/bin/php
<?php
// place this script in the same directory as the http*.txt files

$speeds = ['lan', 'vdsl50', 'ki100', 'lte', '3g'];
$cases = ['all-domains', 'all-ips', 'one-domain', 'one-ip', 'attached-archive', 'linked-archive'];


$speed_print = [
	'lan' => 'lan',
	'vdsl50' => 'dsl',
	'ki100' => 'cable',
	'lte' => 'lte',
	'3g' => '3g'
];
$case_print = [
	'all-domains' => 'multiple domains',
	'all-ips' => 'multiple IPs',
	'attached-archive' => 'attached archive',
	'linked-archive' => 'linked archive',
	'one-domain' => 'one domain',
	'one-ip' => 'one IP'
];


// since the timings.user.js script can only catch subsequent requests,
// here are manually tested timings for attached-archive test case:
$results['lan']['attached-archive'][] = 945;
$results['lan']['attached-archive'][] = 886;
$results['lan']['attached-archive'][] = 902;
$results['lan']['attached-archive'][] = 903;
$results['lan']['attached-archive'][] = 899;

$results['vdsl50']['attached-archive'][] = 1651;
$results['vdsl50']['attached-archive'][] = 1689;
$results['vdsl50']['attached-archive'][] = 1663;
$results['vdsl50']['attached-archive'][] = 1711;
$results['vdsl50']['attached-archive'][] = 1694;

$results['ki100']['attached-archive'][] = 1141;
$results['ki100']['attached-archive'][] = 1100;
$results['ki100']['attached-archive'][] = 1100;
$results['ki100']['attached-archive'][] = 1078;
$results['ki100']['attached-archive'][] = 1139;

$results['lte']['attached-archive'][] = 1670;
$results['lte']['attached-archive'][] = 1710;
$results['lte']['attached-archive'][] = 1718;
$results['lte']['attached-archive'][] = 1710;
$results['lte']['attached-archive'][] = 1668;

$results['3g']['attached-archive'][] = 8316;
$results['3g']['attached-archive'][] = 8369;
$results['3g']['attached-archive'][] = 8360;
$results['3g']['attached-archive'][] = 8377;
$results['3g']['attached-archive'][] = 8354;




$ls = [];
exec("ls -1 http*.txt", $ls);

foreach ($ls as $file) {
		//echo "$file     ...";
		$obj = json_decode(file_get_contents($file), true);
		$data_count = count($obj["data"]);
		$first_dns = null;
		$last_dns = null;
		$data = [];
		$data["dns"] = 0;
		$data["connect"] = 0;
		$data["request wait"] = 0;
		$data["transfer"] = 0;
		$data["redirects"] = 0;
		$first_start = null;
		$last_end = null;
		$data["connection reuse"] = 0;
		$data["new connection"] = 0;
		for ($i = 0; $i < $data_count; $i++) {
			if ($first_start === null || $obj["data"][$i]["startTime"] < $first_start) $first_start = $obj["data"][$i]["startTime"];
			if ($last_end === null || $obj["data"][$i]["responseEnd"] > $last_end) $last_end = $obj["data"][$i]["responseEnd"];
			if ($first_dns === null || $obj["data"][$i]["domainLookupStart"] < $first_dns) $first_dns = $obj["data"][$i]["domainLookupStart"];
			if ($last_dns === null || $obj["data"][$i]["domainLookupEnd"] > $last_dns) $last_dns = $obj["data"][$i]["domainLookupEnd"];
			//$data["dns"] += $obj["data"][$i]["domainLookupEnd"] - $obj["data"][$i]["domainLookupStart"];
			$data["connect"] += $obj["data"][$i]["connectEnd"] - $obj["data"][$i]["connectStart"];
			if ($obj["data"][$i]["connectEnd"] === $obj["data"][$i]["domainLookupEnd"]) {
				$data["connection reuse"]++;
			} else {
				$data["new connection"]++;
			}
			$data["request wait"] += $obj["data"][$i]["responseStart"] - $obj["data"][$i]["requestStart"];
			$data["transfer"] += $obj["data"][$i]["responseEnd"] - $obj["data"][$i]["responseStart"];
			//$data["redirects"] += $obj["data"][$i]["redirectEnd"] - $obj["data"][$i]["redirectStart"];
		}
		$data["dns"] = $last_dns - $first_dns;
		$data["duration"] = $last_end - $first_start;
		
		
		$this_case_speed = explode('.', explode('_', $file)[4])[0];
		
		$this_case = explode('-', $this_case_speed)[0] . '-' . explode('-', $this_case_speed)[1];
		$this_speed = explode('-', $this_case_speed)[2];
		
		$results[$this_speed][$this_case][] = round($data["duration"]);

		//echo $this_speed . "    " . $this_case . "    " /* . $file */ . "    " . round($data["duration"]) . "\n";
}


echo "\n\nfull result table:\n\n";


foreach ($speeds as $speed) {
	echo "\\hline\n\\multirow{6}{*}{" . $speed_print[$speed] . "}";
	$plot_data = '';
	foreach ($cases as $case) {
			echo " & " . $case_print[$case];
		
			// LATEX TABLE
		
			$max = max($results[$speed][$case]);
			$min = min($results[$speed][$case]);
			//echo $speed . " & " . $case . " & " . $min . " & " . $max . " & " . ($max - $min);
			foreach ($results[$speed][$case] as $time) {
				echo " & " . $time;
			}
	
			$results_avg[$speed][$case] = round(
				(array_sum($results[$speed][$case]) - $max - $min) //ignore max and min test results
				/
				(count($results[$speed][$case]) - 2)
				);
			
			//echo " & " . $results_avg[$speed][$case];
			
			echo " \\\\\n";
			
			
			// PLOT
			$plot_data .= '"' . $case_print[$case] . '" ' . $results_avg[$speed][$case] . "\n";
			
	}
	
	file_put_contents('plot.dat', $plot_data);
	
	$plot_script =
"
set term png truecolor
set output \"plot_" . $speed_print[$speed] . ".png\"
set boxwidth 0.5
set style fill solid
set ylabel \"duration (ms)\"
set xtics rotate by 45 right
set yrange [0:]
plot \"plot.dat\" using 0:2:xtic(1) with boxes lc rgb\"#9C1C26\" notitle, \"plot.dat\" using 0:($2*1.05):2 with labels notitle
";

	file_put_contents("plot.script", $plot_script);
	system("gnuplot plot.script");

}

echo "\\hline\n\n";


echo "\n\nsmall result table:\n\n";

echo "\\hline\n";
foreach ($cases as $case) {
	echo " & " . $case_print[$case];
}
echo " \\\\\n\\hline\n";


foreach ($speeds as $speed) {
	echo $speed_print[$speed];
	foreach ($cases as $case) {
	
		echo " & " . $results_avg[$speed][$case];
		
		
	}
	echo " \\\\\n";
}
echo "\\hline\n";




