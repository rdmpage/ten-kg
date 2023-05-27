<?php

// Add data from one or more sources, keeping any existing data

require_once (dirname(__FILE__) . '/core.php');

if (0)
{
	$triplestore = get_triplestore('oxigraph.yaml');
}
else
{
	$triplestore = get_triplestore('blazegraph.yaml');
}

if (!$triplestore)
{
	exit(1);
}

$sources = array(
	//'indexfungorum.yaml',
	//'ipni.yaml',
	//'orcid.yaml',
	'bionames.yaml',
);


foreach ($sources as $source_filename)
{
	$source = get_source($source_filename);

	if (!$source)
	{
		exit(1);
	}
	
	echo "Adding data for " . $source->name . "\n";
	
	$break_on_fail = false;
		
	if (add_source($triplestore, $source))
	{
		// all good
	}
	else
	{
		// not good, errors will have been output already
		exit(1);
	}
	
}

?>
