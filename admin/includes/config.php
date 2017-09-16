<?php
function modify_config_setting($config_path, $key, $value)
{
	$php = file_get_contents($config_path);
	
	if (is_string($value) && ! preg_match('~^\$~', $value) && ! preg_match('~^[A-Z_]+\s*\.\s*~', $value))
	{
		$value = "'$value'";
	}
	
	if (in_array($key, array_keys($GLOBALS['config'])))
	{
		$php = preg_replace('~(\$config\[\''.$key.'\'\]\s*=\s*).+?(;)~', '${1}'.$value.'$2', $php);
	}
	else
	{
		$php .= '\n$config[\''.$key.'\'] = '.$value.';';
	}
	
	file_put_contents($config_path, $php);
}
