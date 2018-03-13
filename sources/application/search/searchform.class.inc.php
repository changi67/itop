<?php
/**
 * Copyright (C) 2010-2018 Combodo SARL
 *
 * This file is part of iTop.
 *
 *  iTop is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with iTop. If not, see <http://www.gnu.org/licenses/>
 *
 */


namespace Combodo\iTop\Application\Search;


use ApplicationContext;
use AttributeDefinition;
use CMDBObjectSet;
use Combodo\iTop\Application\Search\CriterionConversion\CriterionToSearchForm;
use CoreException;
use DBObjectSearch;
use DBObjectSet;
use Dict;
use Exception;
use Expression;
use IssueLog;
use MetaModel;
use TrueExpression;
use utils;
use WebPage;

class SearchForm
{
	private $aLabels = array();

	/**
	 * @param \WebPage $oPage
	 * @param \CMDBObjectSet $oSet
	 * @param array $aExtraParams
	 *
	 * @return string
	 * @throws \CoreException
	 * @throws \DictExceptionMissingString
	 */
	public function GetSearchForm(WebPage $oPage, CMDBObjectSet $oSet, $aExtraParams = array())
	{
		$sHtml = '';
		$this->aLabels = array();
		$oAppContext = new ApplicationContext();
		$sClassName = $oSet->GetFilter()->GetClass();
		$aListParams = array();

		// Simple search form
		if (isset($aExtraParams['currentId']))
		{
			$sSearchFormId = $aExtraParams['currentId'];
			$aListParams['currentId'] = $aExtraParams['currentId'];
		}
		else
		{
			$iSearchFormId = $oPage->GetUniqueId();
			$sSearchFormId = 'SimpleSearchForm'.$iSearchFormId;
			$sHtml .= "<div id=\"ds_$sSearchFormId\" class=\"mini_tab{$iSearchFormId}\">\n";
			$aListParams['currentId'] = "$iSearchFormId";
		}
		// Check if the current class has some sub-classes
		if (isset($aExtraParams['baseClass']))
		{
			$sRootClass = $aExtraParams['baseClass'];
		}
		else
		{
			$sRootClass = $sClassName;
		}
		if (array_key_exists('selection_mode', $aExtraParams))
		{
			$aListParams['selection_mode'] = $aExtraParams['selection_mode'];
		}
		if (array_key_exists('selection_type', $aExtraParams))
		{
			$aListParams['selection_type'] = $aExtraParams['selection_type'];
		}
		$sJson = stripslashes(utils::ReadParam('json', '', false, 'raw_data'));
		if (!empty($sJson))
		{
			$aListParams['json'] = json_decode($sJson, true);
		}
		if (array_key_exists('cssCount', $aExtraParams))
		{
			$aListParams['cssCount'] = $aExtraParams['cssCount'];
		}

		$aSubClasses = MetaModel::GetSubclasses($sRootClass);
		if (count($aSubClasses) > 0)
		{
			$aOptions = array();
			$aOptions[MetaModel::GetName($sRootClass)] = "<option value=\"$sRootClass\">".MetaModel::GetName($sRootClass)."</options>\n";
			foreach($aSubClasses as $sSubclassName)
			{
				$aOptions[MetaModel::GetName($sSubclassName)] = "<option value=\"$sSubclassName\">".MetaModel::GetName($sSubclassName)."</options>\n";
			}
			$aOptions[MetaModel::GetName($sClassName)] = "<option selected value=\"$sClassName\">".MetaModel::GetName($sClassName)."</options>\n";
			ksort($aOptions);
			$sContext = $oAppContext->GetForLink();
			$sClassesCombo = "<select name=\"class\" onChange=\"ReloadSearchForm('$sSearchFormId', this.value, '$sRootClass', '$sContext')\">\n".implode('',
					$aOptions)."</select>\n";
		}
		else
		{
			$sClassesCombo = MetaModel::GetName($sClassName);
		}
		$sAction = (isset($aExtraParams['action'])) ? $aExtraParams['action'] : utils::GetAbsoluteUrlAppRoot().'pages/UI.php';
		$sHtml .= "<form id=\"fs_{$sSearchFormId}\" action=\"{$sAction}\">\n"; // Don't use $_SERVER['SCRIPT_NAME'] since the form may be called asynchronously (from ajax.php)
		$sHtml .= "<h2>".Dict::Format('UI:SearchFor_Class_Objects', $sClassesCombo)."</h2>\n";
		$sHtml .= "<div id=\"fs_{$sSearchFormId}_criterion_outer\">\n";
		$sHtml .= "</div>\n";

		$sPrimaryClassName = $oSet->GetClass();
		$sPrimaryClassAlias = $oSet->GetClassAlias();

		$aFields = $this->GetFields($sPrimaryClassName, $sPrimaryClassAlias);
		$oSearch = $oSet->GetFilter();
		$aCriterion = $this->GetCriterion($oSearch);

		$oBaseSearch = $oSearch->DeepClone();
		$oBaseSearch->ResetCondition();
		$sBaseOQL = str_replace(' WHERE 1', '', $oBaseSearch->ToOQL());


		if (!isset($aExtraParams['table_id']))
		{
			$aExtraParams['table_id'] = "search_form_result_{$sSearchFormId}";
			$sHtml .= "<div class=\"display_block\" id=\"search_form_result_{$sSearchFormId}\"></div>\n";
		}
		$aListParams['table_id'] = $aExtraParams['table_id'];
		if (array_key_exists('table_inner_id', $aExtraParams))
		{
			$aListParams['table_inner_id'] = $aExtraParams['table_inner_id'];
		}
		$aSearchParams = array(
			'criterion_outer_selector' => "#fs_{$sSearchFormId}_criterion_outer",
			'result_list_outer_selector' => "#{$aExtraParams['table_id']}",
			'endpoint' => utils::GetAbsoluteUrlAppRoot().'pages/ajax.searchform.php',
			'list_params' => $aListParams,
			'search' => array(
				'fields' => $aFields,
				'criterion' => $aCriterion,
				'base_oql' => $sBaseOQL,
			),
		);

		$oPage->add_ready_script('$("#fs_'.$sSearchFormId.'").search_form_handler('.json_encode($aSearchParams).');');

		return $sHtml;
	}

	/**
	 * @param $sClassName
	 *
	 * @param $sClassAlias
	 *
	 * @return array
	 */
	public function GetFields($sClassName, $sClassAlias)
	{
		$aFields = array();
		try
		{
			$aList = MetaModel::GetZListItems($sClassName, 'standard_search');
			$aAttrDefs = MetaModel::ListAttributeDefs($sClassName);
			$aZList = array();
			foreach($aList as $sFilterCode)
			{
				if (array_key_exists($sFilterCode, $aAttrDefs))
				{
					$oAttrDef = $aAttrDefs[$sFilterCode];
				}
				else
				{
					$oAttrDef = null;
				}
				$aZList = $this->AppendField($sClassName, $sClassAlias, $sFilterCode, $oAttrDef, $aZList);
			}
			$aFields['zlist'] = $aZList;
			$aOthers = array();
			foreach($aAttrDefs as $sFilterCode => $oAttrDef)
			{
				if (!in_array($sFilterCode, $aList))
				{
					$aOthers = $this->AppendField($sClassName, $sClassAlias, $sFilterCode, $oAttrDef, $aOthers);
				}
			}
			$aFields['others'] = $aOthers;
		} catch (CoreException $e)
		{
			IssueLog::Error($e->getMessage());
		}

		return $aFields;
	}

	/**
	 * @param $oAttrDef
	 *
	 * @return array
	 */
	public function GetFieldAllowedValues($oAttrDef)
	{
		if ($oAttrDef->IsExternalKey(EXTKEY_ABSOLUTE))
		{
			$sTargetClass = $oAttrDef->GetTargetClass();
			try
			{
				$oSearch = new DBObjectSearch($sTargetClass);
			} catch (Exception $e)
			{
				IssueLog::Error($e->getMessage());

				return array('values' => array());
			}
			$oSearch->SetModifierProperty('UserRightsGetSelectFilter', 'bSearchMode', true);
			$oSet = new DBObjectSet($oSearch);
			if ($oSet->Count() > MetaModel::GetConfig()->Get('max_combo_length'))
			{
				return array('autocomplete' => true);
			}
		}
		else
		{
			if (method_exists($oAttrDef, 'GetAllowedValuesAsObjectSet'))
			{
				$oSet = $oAttrDef->GetAllowedValuesAsObjectSet();
				if ($oSet->Count() > MetaModel::GetConfig()->Get('max_combo_length'))
				{
					return array('autocomplete' => true);
				}
			}
		}

		return array('values' => $oAttrDef->GetAllowedValues());
	}

	/**
	 * @param DBObjectSearch $oSearch
	 */
	public function GetCriterion($oSearch)
	{
		$oExpression = $oSearch->GetCriteria();

		$aOrCriterion = array();
		$aORExpressions = Expression::Split($oExpression, 'OR');
		foreach($aORExpressions as $oORSubExpr)
		{
			$aAndCriterion = array();
			$aAndExpressions = Expression::Split($oORSubExpr, 'AND');
			foreach($aAndExpressions as $oAndSubExpr)
			{
				if ($oAndSubExpr instanceof TrueExpression)
				{
					continue;
				}
				$aAndCriterion[] = $oAndSubExpr->GetCriterion($oSearch);
			}
			$aAndCriterion = CriterionToSearchForm::Convert($aAndCriterion);
			$aOrCriterion[] = array('and' => $aAndCriterion);
		}

		return array('or' => $aOrCriterion);
	}

	/**
	 * @param $sClassName
	 * @param $sClassAlias
	 * @param $sFilterCode
	 * @param $oAttrDef
	 * @param $aFields
	 *
	 * @return mixed
	 */
	private function AppendField($sClassName, $sClassAlias, $sFilterCode, $oAttrDef, $aFields)
	{
		if (!is_null($oAttrDef) && ($oAttrDef->GetSearchType() != AttributeDefinition::SEARCH_WIDGET_TYPE_RAW))
		{
			$sLabel = $oAttrDef->GetLabel();
			if (!array_key_exists($sLabel, $this->aLabels))
			{
				$aField = array();
				$aField['code'] = $sFilterCode;
				$aField['class'] = $sClassName;
				$aField['class_alias'] = $sClassAlias;
				$aField['label'] = $sLabel;
				$aField['widget'] = $oAttrDef->GetSearchType();
				$aField['allowed_values'] = $this->GetFieldAllowedValues($oAttrDef);
				$aFields[$sClassAlias.'.'.$sFilterCode] = $aField;
				$this->aLabels[$sLabel] = true;
			}
		}

		return $aFields;
	}
}