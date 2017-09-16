<?php
function readable_time($sec)
{
	$min = 60;
	$hour = $min * 60;
	$day = $hour * 24;
	$wk = $day * 7;
	
	$diff = time() - $sec;
	$str = '';
	if ($diff >= $day && $diff < $wk)
	{
		$i = (int)($diff/$day);
		$str = $i > 1 ? "$i days ago" : "$i day ago";
	}
	elseif ($diff >= $hour && $diff < $day)
	{
		$i = (int)($diff/$hour);
		$str = $i > 1 ? "$i hours ago" : "$i hour ago";
	}
	elseif ($diff >= $min && $diff < $hour)
	{
		$i = (int)($diff/$min);
		$str = $i > 1 ? "$i mins ago" : "$i min ago";
	}
	elseif ($diff >= $sec && $diff < $min)
	{
		$i = (int)($diff/$sec);
		$str = $i > 1 ? "$i secs ago" : "$i sec ago";
	}
	else
	{
		$str = date("d M Y, H:ia", $sec);
	}
	
	return $str;
}
