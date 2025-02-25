#!/usr/bin/env drush

/**
 * Exports data from analyzed phenotypes for use in the haplotype map app.
 *
 * Parameters:
 *   --experiment-name: the full or unique portion of an experiment name.
 *
 * Author: Lacey-Anne Sanderson
 * Date: 2022June21
 */

// Parameters
//--------------------------
// --experiment
$experiment_name = drush_get_option('experiment-name');
if (empty($experiment_name)) {
	return drush_set_error('MISSING_PARAM', 'The --experiment-name paramter is required.');
}
$project_query = "SELECT project_id FROM chado.project WHERE name~ :project";
$experiment_id = chado_query($project_query, [':project' => $experiment_name])->fetchCol();
if (empty($experiment_id)) {
	return drush_set_error('INVALID_EXP', "The experiment name you provided is not in the database.");
}
elseif (count($experiment_id) > 1) {
	return drush_set_error('TOO_MANY_EXP', "The experiment name you provided is not unique in the database. Too many results returned.");
}
else {
	$experiment_id = (int) $experiment_id[0];
}

// QUERIES
//--------------------------

// Select the germplasm that have phenotypes for that project.
$germ_query = "
  SELECT stock_id, name 
  FROM chado.stock
  WHERE
    stock_id IN (SELECT stock_id from chado.phenotype WHERE project_id = :project_id)";

$trait_query = "
	SELECT
		p.attr_id || '-' || p.assay_id || '-' || p.unit_id as code,
    attr.name as trait, 
    '' as trait_abbrev,
    assay.name as method, 
    unit.name as unit
	FROM chado.phenotype p
  LEFT JOIN chado.cvterm attr ON attr.cvterm_id=p.attr_id 
  LEFT JOIN chado.cvterm assay ON assay.cvterm_id=p.assay_id 
  LEFT JOIN chado.cvterm unit ON unit.cvterm_id=p.unit_id 
  WHERE 
    p.value ~ '^-?\d*\.?\d+$' 
    AND p.project_id = :project_id
  GROUP BY p.attr_id, p.assay_id, p.unit_id, attr.name, assay.name, unit.name
  ORDER BY p.attr_id ASC, p.assay_id ASC, p.unit_id ASC";

// Select an averaged value for each germplasm with the specified experiment.
// NOTE: we are averaging across site years here only. Grouping by trait-method-unit combo for a specific experiment and germplasm.
$pheno_query = "
  SELECT 
    p.attr_id || '-' || p.assay_id || '-' || p.unit_id as code,
    round(avg(cast(p.value as numeric)), 3) as value 
  FROM chado.phenotype p  
  WHERE 
    p.value ~ '^-?\d*\.?\d+$' 
    AND p.stock_id= :stock_id
    AND p.project_id = :project_id
  GROUP BY p.attr_id, p.assay_id, p.unit_id
  ORDER BY p.attr_id ASC, p.assay_id ASC, p.unit_id ASC";

// Getting the Data
//--------------------------

// Get the list of germplasm for this experiment.
$germplasm = chado_query($germ_query, [':project_id' => $experiment_id])->fetchAllKeyed(0,1);

// Add the trait header.
$traits = chado_query($trait_query, [':project_id' => $experiment_id])->fetchAllAssoc('code', PDO::FETCH_ASSOC);
$traits_header = [];
$i = 0;
foreach ($traits as $code => $v) {
  $trait_tooltip = '(Trait: ' . $v['trait'] . '<br />Method:' . $v['method'] . '<br />Unit:' . $v['unit'] . ')';
	if (empty($v['trait_abbrev'])) {
		$i++;
		$traits[$code]['trait_abbrev'] = 'T' . $i;
		$traits_header[$code] = '"T' . $i . '-' . $trait_tooltip . '"';
	}
	else {
		$traits_header[$code] = '"' . $v['trait_abbrev'] . '-' . $trait_tooltip . '"';
	}
}
print "\t" . implode("\t",$traits_header) . "\n";

// For each germplasm, get the data for all traits and print it to the screen.
foreach ($germplasm as $stock_id => $germplasm_name) {
	$data = chado_query($pheno_query, [':project_id' => $experiment_id, ':stock_id' => $stock_id])->fetchAllKeyed(0,1);
	print $germplasm_name . "\t" . implode("\t",$data) . "\n";
}
