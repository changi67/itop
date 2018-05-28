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
use AttributeExternalField;
use AttributeFriendlyName;
use AttributeSubItem;
use CMDBObjectSet;
use Combodo\iTop\Application\Search\CriterionConversion\CriterionToSearchForm;
use CoreException;
use DBObjectSearch;
use DBObjectSet;
use Dict;
use Exception;
use Expression;
use FieldExpression;
use IssueLog;
use MetaModel;
use MissingQueryArgument;
use TrueExpression;
use utils;
use WebPage;

class SearchForm
{

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
		$oAppContext = new ApplicationContext();
		$sClassName = $oSet->GetFilter()->GetClass();
		$aListParams = array();

		foreach($aExtraParams as $key => $value)
		{
			$aListParams[$key] = $value;
		}

		// Simple search form
		if (isset($aExtraParams['currentId']))
		{
			$sSearchFormId = $aExtraParams['currentId'];
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
		//should the search be opend on load?
		if (isset($aExtraParams['open']))
		{
			$bOpen = $aExtraParams['open'];
		}
		else
		{
			$bOpen = true;
		}

		$sJson = utils::ReadParam('json', '', false, 'raw_data');
		if (!empty($sJson))
		{
			$aListParams['json'] = json_decode($sJson, true);
		}

		if (!isset($aExtraParams['result_list_outer_selector']))
		{
			if (isset($aExtraParams['table_id']))
			{
				$aExtraParams['result_list_outer_selector'] = $aExtraParams['table_id'];
			}
			else
			{
				$aExtraParams['result_list_outer_selector'] = "search_form_result_{$sSearchFormId}";
			}
		}

		if (isset($aExtraParams['search_header_force_dropdown']))
		{
			$sClassesCombo = $aExtraParams['search_header_force_dropdown'];
		}
		else
		{
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
				$sClassesCombo = "<select name=\"class\" onChange=\"ReloadSearchForm('$sSearchFormId', this.value, '$sRootClass', '$sContext', '{$aExtraParams['result_list_outer_selector']}')\">\n".implode('',
						$aOptions)."</select>\n";
			}
			else
			{
				$sClassesCombo = MetaModel::GetName($sClassName);
			}
		}

		$bAutoSubmit = true;
		$mSubmitParam = utils::GetConfig()->Get('search_manual_submit');
		if (is_array($mSubmitParam))
		{
			// List of classes
			if (isset($mSubmitParam[$sClassName]))
			{
				$bAutoSubmit = !$mSubmitParam[$sClassName];
			}
			else
			{
				// Search for child classes
				foreach($mSubmitParam as $sConfigClass => $bFlag)
				{
					$aChildClasses = MetaModel::EnumChildClasses($sConfigClass);
					if (in_array($sClassName, $aChildClasses))
					{
						$bAutoSubmit = !$bFlag;
						break;
					}
				}

			}
		}
		else if ($mSubmitParam !== false)
		{
			$bAutoSubmit = false;
		}

		$sAction = (isset($aExtraParams['action'])) ? $aExtraParams['action'] : utils::GetAbsoluteUrlAppRoot().'pages/UI.php';
		$sStyle = ($bOpen == 'true') ? '' : 'closed';
		$sStyle .= ($bAutoSubmit === true) ? '' : ' no_auto_submit';
		$sHtml .= "<form id=\"fs_{$sSearchFormId}\" action=\"{$sAction}\" class=\"{$sStyle}\">\n"; // Don't use $_SERVER['SCRIPT_NAME'] since the form may be called asynchronously (from ajax.php)
		$sHtml .= "<h2 class=\"sf_title\"><span class=\"sft_long\">" . Dict::Format('UI:SearchFor_Class_Objects', $sClassesCombo) . "</span><span class=\"sft_short\">" . Dict::S('UI:SearchToggle') . "</span>";
		$sHtml .= "<a class=\"sft_toggler fa fa-caret-down pull-right\" href=\"#\" title=\"" . Dict::S('UI:Search:Toggle') . "\"></a>";
		$sHtml .= "<span class=\"sft_hint pull-right\">" . Dict::S('UI:Search:AutoSubmit:DisabledHint') . "</span>";
		$sHtml .= "</h2>\n";
		$sHtml .= "<div id=\"fs_{$sSearchFormId}_message\" class=\"sf_message header_message\"></div>\n";
		$sHtml .= "<div id=\"fs_{$sSearchFormId}_criterion_outer\">\n</div>\n";
		$sHtml .= "</form>\n";

		if (isset($aExtraParams['query_params']))
		{
			$aArgs = $aExtraParams['query_params'];
		}
		else
		{
			$aArgs = array();
		}

		$bIsRemovable = true;
		if (isset($aExtraParams['selection_type']) && ($aExtraParams['selection_type'] == 'single'))
		{
			// Mark all criterion as read-only and non-removable for external keys only
			$bIsRemovable = false;
		}

		$aFields = $this->GetFields($oSet);
		$oSearch = $oSet->GetFilter();
		$aCriterion = $this->GetCriterion($oSearch, $aFields, $aArgs, $bIsRemovable);
		$aClasses = $oSearch->GetSelectedClasses();
		$sClassAlias = '';
		foreach($aClasses as $sAlias => $sClass)
		{
			$sClassAlias = $sAlias;
		}

		$oBaseSearch = $oSearch->DeepClone();
		if (method_exists($oSearch, 'GetCriteria'))
		{
			$oBaseSearch->ResetCondition();
		}
		$sBaseOQL = str_replace(' WHERE 1', '', $oBaseSearch->ToOQL());

		if (isset($aExtraParams['table_inner_id']))
		{
			$sDataConfigListSelector = $aExtraParams['table_inner_id'];
		}
		else
		{
			$sDataConfigListSelector = $aExtraParams['result_list_outer_selector'];
		}
		if (!isset($aExtraParams['table_inner_id']))
		{
			$aListParams['table_inner_id'] = "table_inner_id_{$sSearchFormId}";
		}

		$sDebug = utils::ReadParam('debug', 'false', false, 'parameter');
		if ($sDebug == 'true')
		{
			$aListParams['debug'] = 'true';
		}

		$aDaysMin = array(Dict::S('DayOfWeek-Sunday-Min'), Dict::S('DayOfWeek-Monday-Min'), Dict::S('DayOfWeek-Tuesday-Min'), Dict::S('DayOfWeek-Wednesday-Min'),
			Dict::S('DayOfWeek-Thursday-Min'), Dict::S('DayOfWeek-Friday-Min'), Dict::S('DayOfWeek-Saturday-Min'));
		$aMonthsShort = array(Dict::S('Month-01-Short'), Dict::S('Month-02-Short'), Dict::S('Month-03-Short'), Dict::S('Month-04-Short'), Dict::S('Month-05-Short'), Dict::S('Month-06-Short'),
			Dict::S('Month-07-Short'), Dict::S('Month-08-Short'), Dict::S('Month-09-Short'), Dict::S('Month-10-Short'), Dict::S('Month-11-Short'), Dict::S('Month-12-Short'));

		$sDateTimeFormat = \AttributeDateTime::GetFormat()->ToDatePicker();
		$iDateTimeSeparatorPos = strpos($sDateTimeFormat, ' ');
		$sDateFormat = substr($sDateTimeFormat, 0, $iDateTimeSeparatorPos);
		$sTimeFormat = substr($sDateTimeFormat, $iDateTimeSeparatorPos + 1);

		$aSearchParams = array(
			'criterion_outer_selector' => "#fs_{$sSearchFormId}_criterion_outer",
			'result_list_outer_selector' => "#{$aExtraParams['result_list_outer_selector']}",
			'data_config_list_selector' => "#{$sDataConfigListSelector}",
			'endpoint' => utils::GetAbsoluteUrlAppRoot().'pages/ajax.searchform.php',
			'init_opened' => $bOpen,
			'auto_submit' => $bAutoSubmit,
			'list_params' => $aListParams,
			'search' => array(
				'has_hidden_criteria' => (array_key_exists('hidden_criteria', $aListParams) && !empty($aListParams['hidden_criteria'])),
				'fields' => $aFields,
				'criterion' => $aCriterion,
				'class_name' => $sClassName,
				'class_alias' => $sClassAlias,
				'base_oql' => $sBaseOQL,
			),
			'conf_parameters' => array(
				'min_autocomplete_chars' => MetaModel::GetConfig()->Get('min_autocomplete_chars'),
				'datepicker' => array(
					'dayNamesMin' => $aDaysMin,
					'monthNamesShort' => $aMonthsShort,
					'firstDay' => (int) Dict::S('Calendar-FirstDayOfWeek'),
					'dateFormat' => $sDateFormat,
					'timeFormat' => $sTimeFormat,
				),
			),
		);

		$oPage->add_ready_script('$("#fs_'.$sSearchFormId.'").search_form_handler('.json_encode($aSearchParams).');');

		return $sHtml;
	}

	/**
	 * @param DBObjectSet $oSet
	 *
	 * @return array
	 */
	public function GetFields($oSet)
	{
		$oSearch = $oSet->GetFilter();
		$aAllClasses = $oSearch->GetSelectedClasses();
		$aAuthorizedClasses = array();
		foreach($aAllClasses as $sAlias => $sClassName)
		{
			if (\UserRights::IsActionAllowed($sClassName, UR_ACTION_READ, $oSet) != UR_ALLOWED_NO)
			{
				$aAuthorizedClasses[$sAlias] = $sClassName;
			}
		}
		$aAllFields = array('zlist' => array(), 'others' => array());
		try
		{
			foreach($aAuthorizedClasses as $sAlias => $sClass)
			{
				$aZList = array();
				$aOthers = array();

				$this->PopulateFieldList($sClass, $sAlias, $aZList, $aOthers);

				$aAllFields[$sAlias.'_zlist'] = $aZList;
				$aAllFields[$sAlias.'_others'] = $aOthers;
			}
		}
		catch (CoreException $e)
		{
			IssueLog::Error($e->getMessage());
		}
		$aSelectedClasses = $oSearch->GetSelectedClasses();
		foreach($aSelectedClasses as $sAlias => $sClassName)
		{
			$aAllFields['zlist'] = array_merge($aAllFields['zlist'], $aAllFields[$sAlias.'_zlist']);
			unset($aAllFields[$sAlias.'_zlist']);
			$aAllFields['others'] = array_merge($aAllFields['others'], $aAllFields[$sAlias.'_others']);
			unset($aAllFields[$sAlias.'_others']);

		}

		return $aAllFields;
	}

	/**
	 * @param $sClass
	 * @param $sAlias
	 * @param $aZList
	 * @param $aOthers
	 *
	 * @throws \CoreException
	 */
	protected function PopulateFieldList($sClass, $sAlias, &$aZList, &$aOthers)
	{
		$aDBIndexes = MetaModel::DBGetIndexes($sClass);
		$aIndexes = array();
		foreach($aDBIndexes as $aIndexGroup)
		{
			foreach($aIndexGroup as $sIndex)
			{
				$aIndexes[$sIndex] = true;
			}
		}
		$aAttributeDefs = MetaModel::ListAttributeDefs($sClass);
		$aList = MetaModel::GetZListItems($sClass, 'standard_search');
		$bHasFriendlyname = false;
		foreach($aList as $sAttCode)
		{
			if (array_key_exists($sAttCode, $aAttributeDefs))
			{
				$bHasIndex = false;
				if (isset($aIndexes[$sAttCode]))
				{
					$bHasIndex = true;
				}
				$oAttDef = $aAttributeDefs[$sAttCode];
				$aZList = $this->AppendField($sClass, $sAlias, $sAttCode, $oAttDef, $aZList, $bHasIndex);
				unset($aAttributeDefs[$sAttCode]);
			}
			if ($sAttCode == 'friendlyname')
			{
				$bHasFriendlyname = true;
			}
		}
		if (!$bHasFriendlyname)
		{
			// Add friendlyname to the most popular
			$sAttCode = 'friendlyname';
			$oAttDef = $aAttributeDefs[$sAttCode];
			$aZList = $this->AppendField($sClass, $sAlias, $sAttCode, $oAttDef, $aZList);
			unset($aAttributeDefs[$sAttCode]);
		}
		$aZList = $this->AppendId($sClass, $sAlias, $aZList);
		uasort($aZList, function ($aItem1, $aItem2) {
			return strcmp($aItem1['label'], $aItem2['label']);
		});

		foreach($aAttributeDefs as $sAttCode => $oAttDef)
		{
			if ($this->IsSubAttribute($oAttDef)) continue;

			$aOthers = $this->AppendField($sClass, $sAlias, $sAttCode, $oAttDef, $aOthers);
		}
		uasort($aOthers, function ($aItem1, $aItem2) {
			return strcmp($aItem1['label'], $aItem2['label']);
		});
	}

	protected function IsSubAttribute($oAttDef)
	{
		return (($oAttDef instanceof AttributeFriendlyName) || ($oAttDef instanceof AttributeExternalField) || ($oAttDef instanceof AttributeSubItem));
	}

	/**
	 * @param \AttributeDefinition $oAttrDef
	 *
	 * @return array
	 */
	public static function GetFieldAllowedValues($oAttrDef)
	{
		if ($oAttrDef->IsExternalKey(EXTKEY_ABSOLUTE))
		{
			if ($oAttrDef instanceof AttributeExternalField)
			{
				$sTargetClass = $oAttrDef->GetFinalAttDef()->GetTargetClass();
			}
			else
			{
				/** @var \AttributeExternalKey $oAttrDef */
				$sTargetClass = $oAttrDef->GetTargetClass();
			}
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
			$iCount = $oSet->Count();
			if ($iCount > MetaModel::GetConfig()->Get('max_combo_length'))
			{
				return array('autocomplete' => true, 'count' => $iCount);
			}
			if ($oAttrDef instanceof AttributeExternalField)
			{
				$aAllowedValues = array();
				while ($oObject = $oSet->Fetch())
				{
					$aAllowedValues[$oObject->GetKey()] = $oObject->GetName();
				}
				return array('values' => $aAllowedValues, 'count' => $iCount);
			}
		}
		else
		{
			if (method_exists($oAttrDef, 'GetAllowedValuesAsObjectSet'))
			{
				/** @var DBObjectSet $oSet */
				$oSet = $oAttrDef->GetAllowedValuesAsObjectSet();
				$iCount = $oSet->Count();
				if ($iCount > MetaModel::GetConfig()->Get('max_combo_length'))
				{
					return array('autocomplete' => true, 'count' => $iCount);
				}
			}
		}

		$aAllowedValues = $oAttrDef->GetAllowedValues();

		$iCount = is_array($aAllowedValues) ? count($aAllowedValues) : 0;
		return array('values' => $aAllowedValues, 'count' => $iCount);
	}

	/**
	 * @param \DBObjectSearch $oSearch
	 * @param array $aFields
	 *
	 * @param array $aArgs
	 *
	 * @param bool $bIsRemovable
	 *
	 * @return array
	 * @throws \MissingQueryArgument
	 */
	public function GetCriterion($oSearch, $aFields, $aArgs = array(), $bIsRemovable = true)
	{
		$aOrCriterion = array();
		$bIsEmptyExpression = true;

		if (method_exists($oSearch, 'GetCriteria'))
		{
			$oExpression = $oSearch->GetCriteria();

			$aArgs = MetaModel::PrepareQueryArguments($aArgs, $oSearch->GetInternalParams());

			if (!empty($aArgs))
			{
				try
				{
					$sOQL = $oExpression->Render($aArgs);
					$oExpression = Expression::FromOQL($sOQL);
				}
				catch (MissingQueryArgument $e)
				{
					IssueLog::Error("Search form disabled: \"".$oSearch->ToOQL()."\" Error: ".$e->getMessage());
					throw $e;
				}
			}

			$aORExpressions = Expression::Split($oExpression, 'OR');
			foreach($aORExpressions as $oORSubExpr)
			{
				$aAndCriterion = array();
				$aAndExpressions = Expression::Split($oORSubExpr, 'AND');
				foreach($aAndExpressions as $oAndSubExpr)
				{
					/** @var Expression $oAndSubExpr */
					if (($oAndSubExpr instanceof TrueExpression) || ($oAndSubExpr->Render() == 1))
					{
						continue;
					}
					$aAndCriterion[] = $oAndSubExpr->GetCriterion($oSearch);
					$bIsEmptyExpression = false;
				}
				$aAndCriterion = CriterionToSearchForm::Convert($aAndCriterion, $aFields, $oSearch->GetJoinedClasses(), $bIsRemovable);
				$aOrCriterion[] = array('and' => $aAndCriterion);
			}
		}

		if ($bIsEmptyExpression)
		{
			// Add default criterion
			$aOrCriterion = $this->GetDefaultCriterion($oSearch);
		}

		return array('or' => $aOrCriterion);
	}

	/**
	 * @param $sClass
	 * @param $sClassAlias
	 * @param $aFields
	 *
	 * @return mixed
	 */
	private function AppendId($sClass, $sClassAlias, $aFields)
	{
		$aField = array();
		$aField['code'] = 'id';
		$aField['class'] = $sClass;
		$aField['class_alias'] = $sClassAlias;
		$aField['label'] = 'Id';
		$aField['widget'] = AttributeDefinition::SEARCH_WIDGET_TYPE_NUMERIC;
		$aField['is_null_allowed'] = false;
		$aNewFields = array($sClassAlias.'.id' => $aField);
		$aFields = array_merge($aNewFields, $aFields);
		return $aFields;
	}

	/**
	 * @param $sClass
	 * @param $sClassAlias
	 * @param $sAttCode
	 * @param AttributeDefinition $oAttDef
	 * @param $aFields
	 * @param bool $bHasIndex
	 *
	 * @return mixed
	 */
	private function AppendField($sClass, $sClassAlias, $sAttCode, $oAttDef, $aFields, $bHasIndex = false)
	{
		if (!is_null($oAttDef) && ($oAttDef->GetSearchType() != AttributeDefinition::SEARCH_WIDGET_TYPE_RAW))
		{
			if (method_exists($oAttDef, 'GetLabelForSearchField'))
			{
				$sLabel = $oAttDef->GetLabelForSearchField();
			}
			else
			{
				if ($sAttCode == 'friendlyname')
				{
					try
					{
						$sLabel = MetaModel::GetName($sClass);
					}
					catch (Exception $e)
					{
						$sLabel = $oAttDef->GetLabel();
					}
				}
				else
				{
					$sLabel = $oAttDef->GetLabel();
				}
			}

			if ($oAttDef instanceof AttributeExternalField)
			{
				$oTargetAttDef = $oAttDef->GetFinalAttDef();
			}
			else
			{
				$oTargetAttDef = $oAttDef;
			}

			if (method_exists($oTargetAttDef, 'GetTargetClass'))
			{
				$sTargetClass = $oTargetAttDef->GetTargetClass();
			}
			else
			{
				$sTargetClass = $oTargetAttDef->GetHostClass();
			}

			$aField = array();
			$aField['code'] = $sAttCode;
			$aField['class'] = $sClass;
			$aField['class_alias'] = $sClassAlias;
			$aField['target_class'] = $sTargetClass;
			$aField['label'] = $sLabel;
			$aField['widget'] = $oAttDef->GetSearchType();
			$aField['allowed_values'] = self::GetFieldAllowedValues($oAttDef);
			$aField['is_null_allowed'] = $oAttDef->IsNullAllowed();
			$aField['has_index'] = $bHasIndex;
			$aFields[$sClassAlias.'.'.$sAttCode] = $aField;

			// Sub items
			//
			//			if ($oAttDef->IsSearchable())
			//			{
			//				$sShortLabel = $oAttDef->GetLabel();
			//				$sLabel = $sShortAlias.$oAttDef->GetLabel();
			//				$aSubAttr = $this->GetSubAttributes($sClass, $sFilterCode, $oAttDef);
			//				$aValidSubAttr = array();
			//				foreach($aSubAttr as $aSubAttDef)
			//				{
			//					$aValidSubAttr[] = array('attcodeex' => $aSubAttDef['code'], 'code' => $sShortAlias.$aSubAttDef['code'], 'label' => $aSubAttDef['label'], 'unique_label' => $sShortAlias.$aSubAttDef['unique_label']);
			//				}
			//				$aAllFields[] = array('attcodeex' => $sFilterCode, 'code' => $sShortAlias.$sFilterCode, 'label' => $sShortLabel, 'unique_label' => $sLabel, 'subattr' => $aValidSubAttr);
			//			}

		}

		return $aFields;
	}

	/**
	 * @param DBObjectSearch $oSearch
	 * @return array
	 */
	protected function GetDefaultCriterion($oSearch)
	{
		$aAndCriterion = array();
		$sClass = $oSearch->GetClass();
		$aList = MetaModel::GetZListItems($sClass, 'default_search');
		while (empty($aList))
		{
			// search in parent class if default criteria are defined
			$sClass = MetaModel::GetParentClass($sClass);
			if (is_null($sClass))
			{
				$aOrCriterion = array(array('and' => $aAndCriterion));
				return $aOrCriterion;
			}
			$aList = MetaModel::GetZListItems($sClass, 'default_search');
		}
		$sAlias = $oSearch->GetClassAlias();
		foreach($aList as $sAttCode)
		{
			$oFieldExpression = new FieldExpression($sAttCode, $sAlias);
			$aCriterion = $oFieldExpression->GetCriterion($oSearch);
			if (isset($aCriterion['widget']) && ($aCriterion['widget'] != AttributeDefinition::SEARCH_WIDGET_TYPE_RAW))
			{
				$aAndCriterion[] = $aCriterion;
			}
		}
		// Overwrite with default criterion
		$aOrCriterion = array(array('and' => $aAndCriterion));
		return $aOrCriterion;
	}

}
