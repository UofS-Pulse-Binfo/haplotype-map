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
  SELECT p.stock_id, s.name 
  FROM chado.stock s
  WHERE
    s.stock_id IN (SELECT stock_id from chado.phenotype WHERE project_id = :project_id";

// Select an averaged value for each germplasm with the specified experiment.
// NOTE: we are averaging across site years here only. Grouping by trait-method-unit combo for a specific experiment and germplasm.
$pheno_query = "
  SELECT 
    attr.name as trait, 
    assay.name as method, 
    unit.name as unit, 
    avg(cast(p.value as decimal)) as value 
  FROM chado.phenotype p 
  LEFT JOIN chado.cvterm attr ON attr.cvterm_id=p.attr_id 
  LEFT JOIN chado.cvterm assay ON assay.cvterm_id=p.assay_id 
  LEFT JOIN chado.cvterm unit ON unit.cvterm_id=p.unit_id 
  WHERE 
    p.value ~ '^-?\d*\.?\d+$' 
    AND p.stock_id= :stock_id
    AND p.project_id = :project_id
  GROUP BY attr.name, assay.name, unit.name";

// Getting the Data
//--------------------------

$germplasm = chado_query($germ_query, [':project_id' => $experiment_id])->fetchKeyed(0,1);
print_r($germplasm);
