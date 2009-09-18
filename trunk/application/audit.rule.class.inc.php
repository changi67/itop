<?php
require_once('../application/audit.category.class.inc.php');

/**
 * This class manages the audit "rule" linked to a given audit category.
 * Each rule is based ona SibusQL expression that returns either the "good" objects
 * or the "bad" ones. The core audit engines computes the complement to the definition
 * set when needed to obtain either the valid objects, or the ones with an error
 */
class AuditRule extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "application",
			"name" => "AuditRule",
			"description" => "A rule to check for a given Audit category",
			"key_type" => "autoincrement",
			"key_label" => "",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array('name'),
			"db_table" => "priv_auditrule",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "../business/templates/default.html",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_AddAttribute(new AttributeString("name", array("label"=>"Rule Name", "description"=>"Short name for this rule", "allowed_values"=>null, "sql"=>"name", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("description", array("label"=>"Audit Rule Description", "description"=>"Long description for this audit rule", "allowed_values"=>null, "sql"=>"description", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeText("query", array("label"=>"Query to Run", "description"=>"The SibusQL expression to run", "allowed_values"=>null, "sql"=>"query", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeEnum("valid_flag", array("label"=>"Valid objects?", "description"=>"True if the rule returns the valid objects, false otherwise", "allowed_values"=>new ValueSetEnum('true,false'), "sql"=>"valid_flag", "default_value"=>"true", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalKey("category_id", array("label"=>"Category", "description"=>"The category for this rule", "allowed_values"=>null, "sql"=>"category_id", "targetclass"=>"AuditCategory", "is_null_allowed"=>false, "on_target_delete"=>DEL_MANUAL, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeExternalField("category_name", array("label"=>"Category", "description"=>"Name of the category for this rule", "allowed_values"=>null, "extkey_attcode"=> 'category_id', "target_attcode"=>"name")));

		MetaModel::Init_AddFilterFromAttribute("name");
		MetaModel::Init_AddFilterFromAttribute("description");
		MetaModel::Init_AddFilterFromAttribute("query");
		MetaModel::Init_AddFilterFromAttribute("valid_flag");
		MetaModel::Init_AddFilterFromAttribute("category_id");
		MetaModel::Init_AddFilterFromAttribute("category_name");

		// Display lists
		MetaModel::Init_SetZListItems('details', array('category_id', 'name', 'description', 'query', 'valid_flag')); // Attributes to be displayed for the complete details
		MetaModel::Init_SetZListItems('list', array('category_id', 'name', 'description', 'valid_flag')); // Attributes to be displayed for a list
		// Search criteria
		MetaModel::Init_SetZListItems('standard_search', array('category_id', 'name', 'description', 'valid_flag')); // Criteria of the std search form
		MetaModel::Init_SetZListItems('advanced_search', array('category_id', 'name', 'description', 'valid_flag', 'query')); // Criteria of the advanced search form
	}
}
?>
