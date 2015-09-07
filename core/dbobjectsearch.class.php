<?php
// Copyright (C) 2010-2015 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify	
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>


/**
 * Define filters for a given class of objects (formerly named "filter") 
 *
 * @copyright   Copyright (C) 2010-2015 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */
 

class DBObjectSearch extends DBSearch
{
	private $m_aClasses; // queried classes (alias => class name), the first item is the class corresponding to this filter (the rest is coming from subfilters)
	private $m_aSelectedClasses; // selected for the output (alias => class name)
	private $m_oSearchCondition;
	private $m_aParams;
	private $m_aFullText;
	private $m_aPointingTo;
	private $m_aReferencedBy;

	public function __construct($sClass, $sClassAlias = null)
	{
		parent::__construct();

		if (is_null($sClassAlias)) $sClassAlias = $sClass;
		if(!is_string($sClass)) throw new Exception('DBObjectSearch::__construct called with a non-string parameter: $sClass = '.print_r($sClass, true));
		if(!MetaModel::IsValidClass($sClass)) throw new Exception('DBObjectSearch::__construct called for an invalid class: "'.$sClass.'"');

		$this->m_aSelectedClasses = array($sClassAlias => $sClass);
		$this->m_aClasses = array($sClassAlias => $sClass);
		$this->m_oSearchCondition = new TrueExpression;
		$this->m_aParams = array();
		$this->m_aFullText = array();
		$this->m_aPointingTo = array();
		$this->m_aReferencedBy = array();
	}

	// Create a search definition that leads to 0 result, still a valid search object
	static public function FromEmptySet($sClass)
	{
		$oResultFilter = new DBObjectSearch($sClass);
		$oResultFilter->m_oSearchCondition = new FalseExpression;
		return $oResultFilter;
	}


	public function GetJoinedClasses() {return $this->m_aClasses;}

	public function GetClassName($sAlias)
	{
		if (array_key_exists($sAlias, $this->m_aSelectedClasses))
		{
			return $this->m_aSelectedClasses[$sAlias];
		}
		else
		{
			throw new CoreException("Invalid class alias '$sAlias'");
		}
	}

	public function GetClass()
	{
		return reset($this->m_aSelectedClasses);
	}
	public function GetClassAlias()
	{
		reset($this->m_aSelectedClasses);
		return key($this->m_aSelectedClasses);
	}

	public function GetFirstJoinedClass()
	{
		return reset($this->m_aClasses);
	}
	public function GetFirstJoinedClassAlias()
	{
		reset($this->m_aClasses);
		return key($this->m_aClasses);
	}

	/**
	 * Change the class (only subclasses are supported as of now, because the conditions must fit the new class)
	 * Defaults to the first selected class (most of the time it is also the first joined class	 
	 */	 	
	public function ChangeClass($sNewClass, $sAlias = null)
	{
		if (is_null($sAlias))
		{
			$sAlias = $this->GetClassAlias();
		}
		else
		{
			if (!array_key_exists($sAlias, $this->m_aSelectedClasses))
			{
				// discard silently - necessary when recursing on the related nodes (see code below)
				return;
			}
		}
		$sCurrClass = $this->GetClassName($sAlias);
		if ($sNewClass == $sCurrClass)
		{
			// Skip silently
			return;
		}
		if (!MetaModel::IsParentClass($sCurrClass, $sNewClass))
		{
			throw new Exception("Could not change the search class from '$sCurrClass' to '$sNewClass'. Only child classes are permitted.");
		}

		// Change for this node
		//
		$this->m_aSelectedClasses[$sAlias] = $sNewClass;
		$this->m_aClasses[$sAlias] = $sNewClass;

		// Change for all the related node (yes, this was necessary with some queries - strange effects otherwise)
		//
		foreach($this->m_aPointingTo as $sExtKeyAttCode=>$aPointingTo)
		{
			foreach($aPointingTo as $iOperatorCode => $aFilter)
			{
				foreach($aFilter as $oExtFilter)
				{
					$oExtFilter->ChangeClass($sNewClass, $sAlias);
				}
			}
		}
		foreach($this->m_aReferencedBy as $sForeignClass => $aReferences)
		{
			foreach($aReferences as $sForeignExtKeyAttCode => $oForeignFilter)
			{
				$oForeignFilter->ChangeClass($sNewClass, $sAlias);
			}
		}
	}

	public function GetSelectedClasses()
	{
		return $this->m_aSelectedClasses;
	}


	public function SetModifierProperty($sPluginClass, $sProperty, $value)
	{
		$this->m_aModifierProperties[$sPluginClass][$sProperty] = $value;
	}

	public function GetModifierProperties($sPluginClass)
	{
		if (array_key_exists($sPluginClass, $this->m_aModifierProperties))
		{
			return $this->m_aModifierProperties[$sPluginClass];
		}
		else
		{
			return array();
		}
	}

	public function IsAny()
	{
		// #@# todo - if (!$this->m_oSearchCondition->IsTrue()) return false;
		if (count($this->m_aFullText) > 0) return false;
		if (count($this->m_aPointingTo) > 0) return false;
		if (count($this->m_aReferencedBy) > 0) return false;
		return true;
	}

	protected function TransferConditionExpression($oFilter, $aTranslation)
	{
		// Prevent collisions in the parameter names by renaming them if needed
		foreach($this->m_aParams as $sParam => $value)
		{
			if (array_key_exists($sParam, $oFilter->m_aParams) && ($value != $oFilter->m_aParams[$sParam]))
			{
				// Generate a new and unique name for the collinding parameter
				$index = 1;
				while(array_key_exists($sParam.$index, $oFilter->m_aParams))
				{
					$index++;
				}
				$secondValue = $oFilter->m_aParams[$sParam];
				$oFilter->RenameParam($sParam, $sParam.$index);
				unset($oFilter->m_aParams[$sParam]);
				$oFilter->m_aParams[$sParam.$index] = $secondValue;
			}
		}
		$oTranslated = $oFilter->GetCriteria()->Translate($aTranslation, false, false /* leave unresolved fields */);
		$this->AddConditionExpression($oTranslated);
		$this->m_aParams = array_merge($this->m_aParams, $oFilter->m_aParams);
	}

	protected function RenameParam($sOldName, $sNewName)
	{
		$this->m_oSearchCondition->RenameParam($sOldName, $sNewName);
		foreach($this->m_aPointingTo as $sExtKeyAttCode=>$aPointingTo)
		{
			foreach($aPointingTo as $iOperatorCode => $aFilter)
			{
				foreach($aFilter as $oExtFilter)
				{
					$oExtFilter->RenameParam($sOldName, $sNewName);
				}
			}
		}
		foreach($this->m_aReferencedBy as $sForeignClass => $aReferences)
		{
			foreach($aReferences as $sForeignExtKeyAttCode => $oForeignFilter)
			{
				$oForeignFilter->RenameParam($sOldName, $sNewName);
			}
		}
	}
	
	public function ResetCondition()
	{
		$this->m_oSearchCondition = new TrueExpression();
		// ? is that usefull/enough, do I need to rebuild the list after the subqueries ?
	}

	public function MergeConditionExpression($oExpression)
	{
		$this->m_oSearchCondition = $this->m_oSearchCondition->LogOr($oExpression); 
	}

	public function AddConditionExpression($oExpression)
	{
		$this->m_oSearchCondition = $this->m_oSearchCondition->LogAnd($oExpression); 
	}

  	public function AddNameCondition($sName)
	{
		$oValueExpr = new ScalarExpression($sName);
		$oNameExpr = new FieldExpression('friendlyname', $this->GetClassAlias());
		$oNewCondition = new BinaryExpression($oNameExpr, '=', $oValueExpr);
		$this->AddConditionExpression($oNewCondition);
	}

	public function AddCondition($sFilterCode, $value, $sOpCode = null)
	{
		MyHelpers::CheckKeyInArray('filter code in class: '.$this->GetClass(), $sFilterCode, MetaModel::GetClassFilterDefs($this->GetClass()));
		$oFilterDef = MetaModel::GetClassFilterDef($this->GetClass(), $sFilterCode);

		$oField = new FieldExpression($sFilterCode, $this->GetClassAlias());
		if (empty($sOpCode))
		{
			if ($sFilterCode == 'id')
			{
				$sOpCode = '=';
			}
			else
			{
				$oAttDef = MetaModel::GetAttributeDef($this->GetClass(), $sFilterCode);
				$oNewCondition = $oAttDef->GetSmartConditionExpression($value, $oField, $this->m_aParams);
				$this->AddConditionExpression($oNewCondition);
				return;
			}
		}
		MyHelpers::CheckKeyInArray('operator', $sOpCode, $oFilterDef->GetOperators());

		// Preserve backward compatibility - quick n'dirty way to change that API semantic
		//
		switch($sOpCode)
		{
		case 'SameDay':
		case 'SameMonth':
		case 'SameYear':
		case 'Today':
		case '>|':
		case '<|':
		case '=|':
			throw new CoreException('Deprecated operator, please consider using OQL (SQL) expressions like "(TO_DAYS(NOW()) - TO_DAYS(x)) AS AgeDays"', array('operator' => $sOpCode));
			break;

		case "IN":
			if (!is_array($value)) $value = array($value);
			$sListExpr = '('.implode(', ', CMDBSource::Quote($value)).')';
			$sOQLCondition = $oField->Render()." IN $sListExpr";
			break;

		case "NOTIN":
			if (!is_array($value)) $value = array($value);
			$sListExpr = '('.implode(', ', CMDBSource::Quote($value)).')';
			$sOQLCondition = $oField->Render()." NOT IN $sListExpr";
			break;

		case 'Contains':
			$this->m_aParams[$sFilterCode] = "%$value%";
			$sOperator = 'LIKE';
			break;

		case 'Begins with':
			$this->m_aParams[$sFilterCode] = "$value%";
			$sOperator = 'LIKE';
			break;

		case 'Finishes with':
			$this->m_aParams[$sFilterCode] = "%$value";
			$sOperator = 'LIKE';
			break;

		default:
			$this->m_aParams[$sFilterCode] = $value;
			$sOperator = $sOpCode;
		}

		switch($sOpCode)
		{
		case "IN":
		case "NOTIN":
			$oNewCondition = Expression::FromOQL($sOQLCondition);
			break;

		case 'Contains':
		case 'Begins with':
		case 'Finishes with':
		default:
			$oRightExpr = new VariableExpression($sFilterCode);
			$oNewCondition = new BinaryExpression($oField, $sOperator, $oRightExpr);
		}

		$this->AddConditionExpression($oNewCondition);
	}

	/**
	 * Specify a condition on external keys or link sets
	 * @param sAttSpec Can be either an attribute code or extkey->[sAttSpec] or linkset->[sAttSpec] and so on, recursively
	 *                 Example: infra_list->ci_id->location_id->country	 
	 * @param value The value to match (can be an array => IN(val1, val2...)
	 * @return void
	 */
	public function AddConditionAdvanced($sAttSpec, $value)
	{
		$sClass = $this->GetClass();

		$iPos = strpos($sAttSpec, '->');
		if ($iPos !== false)
		{
			$sAttCode = substr($sAttSpec, 0, $iPos);
			$sSubSpec = substr($sAttSpec, $iPos + 2);

			if (!MetaModel::IsValidAttCode($sClass, $sAttCode))
			{
				throw new Exception("Invalid attribute code '$sClass/$sAttCode' in condition specification '$sAttSpec'");
			}

			$oAttDef = MetaModel::GetAttributeDef($sClass, $sAttCode);
			if ($oAttDef->IsLinkSet())
			{
				$sTargetClass = $oAttDef->GetLinkedClass();
				$sExtKeyToMe = $oAttDef->GetExtKeyToMe();

				$oNewFilter = new DBObjectSearch($sTargetClass);
				$oNewFilter->AddConditionAdvanced($sSubSpec, $value);

				$this->AddCondition_ReferencedBy($oNewFilter, $sExtKeyToMe);
			}
			elseif ($oAttDef->IsExternalKey(EXTKEY_ABSOLUTE))
			{
				$sTargetClass = $oAttDef->GetTargetClass(EXTKEY_ABSOLUTE);

				$oNewFilter = new DBObjectSearch($sTargetClass);
				$oNewFilter->AddConditionAdvanced($sSubSpec, $value);

				$this->AddCondition_PointingTo($oNewFilter, $sAttCode);
			}
			else
			{
				throw new Exception("Attribute specification '$sAttSpec', '$sAttCode' should be either a link set or an external key");
			}
		}
		else
		{
			// $sAttSpec is an attribute code
			//
			if (is_array($value))
			{
				$oField = new FieldExpression($sAttSpec, $this->GetClass());
				$oListExpr = ListExpression::FromScalars($value);
				$oInValues = new BinaryExpression($oField, 'IN', $oListExpr);

				$this->AddConditionExpression($oInValues);
			}
			else
			{
				$this->AddCondition($sAttSpec, $value);
			}
		}
	}

	public function AddCondition_FullText($sFullText)
	{
		$this->m_aFullText[] = $sFullText;
	}

	protected function AddToNameSpace(&$aClassAliases, &$aAliasTranslation, $bTranslateMainAlias = true)
	{
		if ($bTranslateMainAlias)
		{
			$sOrigAlias = $this->GetFirstJoinedClassAlias();
			if (array_key_exists($sOrigAlias, $aClassAliases))
			{
				$sNewAlias = MetaModel::GenerateUniqueAlias($aClassAliases, $sOrigAlias, $this->GetFirstJoinedClass());
				if (isset($this->m_aSelectedClasses[$sOrigAlias]))
				{
					$this->m_aSelectedClasses[$sNewAlias] = $this->GetFirstJoinedClass();
					unset($this->m_aSelectedClasses[$sOrigAlias]);
				}

				// TEMPORARY ALGORITHM (m_aClasses is not correctly updated, it is not possible to add a subtree onto a subnode)
				// Replace the element at the same position (unset + set is not enough because the hash array is ordered)
				$aPrevList = $this->m_aClasses;
				$this->m_aClasses = array();
				foreach ($aPrevList as $sSomeAlias => $sSomeClass)
				{
					if ($sSomeAlias == $sOrigAlias)
					{
						$this->m_aClasses[$sNewAlias] = $sSomeClass; // note: GetFirstJoinedClass now returns '' !!!
					}
					else
					{
						$this->m_aClasses[$sSomeAlias] = $sSomeClass;
					}
				}
	
				// Translate the condition expression with the new alias
				$aAliasTranslation[$sOrigAlias]['*'] = $sNewAlias;
			}
	
			// add the alias into the filter aliases list
			$aClassAliases[$this->GetFirstJoinedClassAlias()] = $this->GetFirstJoinedClass();
		}
		
		foreach($this->m_aPointingTo as $sExtKeyAttCode=>$aPointingTo)
		{
			foreach($aPointingTo as $iOperatorCode => $aFilter)
			{
				foreach($aFilter as $oFilter)
				{
					$oFilter->AddToNameSpace($aClassAliases, $aAliasTranslation);
				}
			}
		}

		foreach($this->m_aReferencedBy as $sForeignClass=>$aReferences)
		{
			foreach($aReferences as $sForeignExtKeyAttCode=>$oForeignFilter)
			{
				$oForeignFilter->AddToNameSpace($aClassAliases, $aAliasTranslation);
			}
		}
	}


	// Browse the tree nodes recursively
	//
	protected function GetNode($sAlias)
	{
		if ($this->GetFirstJoinedClassAlias() == $sAlias)
		{
			return $this;
		}
		else
		{
			foreach($this->m_aPointingTo as $sExtKeyAttCode=>$aPointingTo)
			{
				foreach($aPointingTo as $iOperatorCode => $aFilter)
				{
					foreach($aFilter as $oFilter)
					{
						$ret = $oFilter->GetNode($sAlias);
						if (is_object($ret))
						{
							return $ret;
						}
					}
				}
			}
			foreach($this->m_aReferencedBy as $sForeignClass=>$aReferences)
			{
				foreach($aReferences as $sForeignExtKeyAttCode=>$oForeignFilter)
				{
					$ret = $oForeignFilter->GetNode($sAlias);
					if (is_object($ret))
					{
						return $ret;
					}
				}
			}
		}
		// Not found
		return null;
	}


	public function AddCondition_PointingTo(DBObjectSearch $oFilter, $sExtKeyAttCode, $iOperatorCode = TREE_OPERATOR_EQUALS)
	{
		if (!MetaModel::IsValidKeyAttCode($this->GetClass(), $sExtKeyAttCode))
		{
			throw new CoreWarning("The attribute code '$sExtKeyAttCode' is not an external key of the class '{$this->GetClass()}'");
		}
		$oAttExtKey = MetaModel::GetAttributeDef($this->GetClass(), $sExtKeyAttCode);
		if(!MetaModel::IsSameFamilyBranch($oFilter->GetClass(), $oAttExtKey->GetTargetClass()))
		{
			throw new CoreException("The specified filter (pointing to {$oFilter->GetClass()}) is not compatible with the key '{$this->GetClass()}::$sExtKeyAttCode', which is pointing to {$oAttExtKey->GetTargetClass()}");
		}
		if(($iOperatorCode != TREE_OPERATOR_EQUALS) && !($oAttExtKey instanceof AttributeHierarchicalKey))
		{
			throw new CoreException("The specified tree operator $iOperatorCode is not applicable to the key '{$this->GetClass()}::$sExtKeyAttCode', which is not a HierarchicalKey");
		}
		// Note: though it seems to be a good practice to clone the given source filter
		//       (as it was done and fixed an issue in Intersect())
		//       this was not implemented here because it was causing a regression (login as admin, select an org, click on any badge)
		//       root cause: FromOQL relies on the fact that the passed filter can be modified later 
		// NO: $oFilter = $oFilter->DeepClone();
		// See also: Trac #639, and self::AddCondition_ReferencedBy()
		$aAliasTranslation = array();
		$res = $this->AddCondition_PointingTo_InNameSpace($oFilter, $sExtKeyAttCode, $this->m_aClasses, $aAliasTranslation, $iOperatorCode);
		$this->TransferConditionExpression($oFilter, $aAliasTranslation);
		return $res;
	}

	protected function AddCondition_PointingTo_InNameSpace(DBObjectSearch $oFilter, $sExtKeyAttCode, &$aClassAliases, &$aAliasTranslation, $iOperatorCode)
	{
		// Find the node on which the new tree must be attached (most of the time it is "this")
		$oReceivingFilter = $this->GetNode($this->GetClassAlias());

		$oFilter->AddToNamespace($aClassAliases, $aAliasTranslation);
		$oReceivingFilter->m_aPointingTo[$sExtKeyAttCode][$iOperatorCode][] = $oFilter;
	}

	public function AddCondition_ReferencedBy(DBObjectSearch $oFilter, $sForeignExtKeyAttCode)
	{
		$sForeignClass = $oFilter->GetClass();
		if (!MetaModel::IsValidKeyAttCode($sForeignClass, $sForeignExtKeyAttCode))
		{
			throw new CoreException("The attribute code '$sForeignExtKeyAttCode' is not an external key of the class '{$sForeignClass}'");
		}
		$oAttExtKey = MetaModel::GetAttributeDef($sForeignClass, $sForeignExtKeyAttCode);
		if(!MetaModel::IsSameFamilyBranch($this->GetClass(), $oAttExtKey->GetTargetClass()))
		{
			// à refaire en spécifique dans FromOQL
			throw new CoreException("The specified filter (objects referencing an object of class {$this->GetClass()}) is not compatible with the key '{$sForeignClass}::$sForeignExtKeyAttCode', which is pointing to {$oAttExtKey->GetTargetClass()}");
		}
		// Note: though it seems to be a good practice to clone the given source filter
		//       (as it was done and fixed an issue in Intersect())
		//       this was not implemented here because it was causing a regression (login as admin, select an org, click on any badge)
		//       root cause: FromOQL relies on the fact that the passed filter can be modified later 
		// NO: $oFilter = $oFilter->DeepClone();
		// See also: Trac #639, and self::AddCondition_PointingTo()
		$aAliasTranslation = array();
		$res = $this->AddCondition_ReferencedBy_InNameSpace(DBObjectSearch, $sForeignExtKeyAttCode, $this->m_aClasses, $aAliasTranslation);
		$this->TransferConditionExpression($oFilter, $aAliasTranslation);
		return $res;
	}

	protected function AddCondition_ReferencedBy_InNameSpace(DBSearch $oFilter, $sForeignExtKeyAttCode, &$aClassAliases, &$aAliasTranslation)
	{
		$sForeignClass = $oFilter->GetClass();

		// Find the node on which the new tree must be attached (most of the time it is "this")
		$oReceivingFilter = $this->GetNode($this->GetClassAlias());

		if (array_key_exists($sForeignClass, $this->m_aReferencedBy) && array_key_exists($sForeignExtKeyAttCode, $this->m_aReferencedBy[$sForeignClass]))
		{
			$oReceivingFilter->m_aReferencedBy[$sForeignClass][$sForeignExtKeyAttCode]->MergeWith_InNamespace($oFilter, $aClassAliases, $aAliasTranslation);
		}
		else
		{
			$oFilter->AddToNamespace($aClassAliases, $aAliasTranslation);

			// #@# The condition expression found in that filter should not be used - could be another kind of structure like a join spec tree !!!!
			//$oNewFilter = $oFilter->DeepClone();
			//$oNewFilter->ResetCondition();

			$oReceivingFilter->m_aReferencedBy[$sForeignClass][$sForeignExtKeyAttCode]= $oFilter;
		}
	}

	public function Intersect(DBSearch $oFilter)
	{
		if ($oFilter instanceof DBUnionSearch)
		{
			// Develop! 
			$aFilters = $oFilter->GetSearches();
		}
		else
		{
			$aFilters = array($oFilter);
		}

		$aSearches = array();
		foreach ($aFilters as $oRightFilter)
		{
			$oLeftFilter = $this->DeepClone();
			$oRightFilter = $oRightFilter->DeepClone();
	
			if ($oLeftFilter->GetClass() != $oRightFilter->GetClass())
			{
				if (MetaModel::IsParentClass($oLeftFilter->GetClass(), $oRightFilter->GetClass()))
				{
					// Specialize $oLeftFilter
					$oLeftFilter->ChangeClass($oRightFilter->GetClass());
				}
				elseif (MetaModel::IsParentClass($oRightFilter->GetClass(), $oLeftFilter->GetClass()))
				{
					// Specialize $oRightFilter
					$oRightFilter->ChangeClass($oLeftFilter->GetClass());
				}
				else
				{
					throw new CoreException("Attempting to merge a filter of class '{$oLeftFilter->GetClass()}' with a filter of class '{$oRightFilter->GetClass()}'");
				}
			}

			$aAliasTranslation = array();
			$oLeftFilter->MergeWith_InNamespace($oRightFilter, $oLeftFilter->m_aClasses, $aAliasTranslation);
			$oLeftFilter->TransferConditionExpression($oRightFilter, $aAliasTranslation);
			$aSearches[] = $oLeftFilter;
		}
		if (count($aSearches) == 1)
		{
			// return a DBObjectSearch
			return $aSearches[0];
		}
		else
		{
			return new DBUnionSearch($aSearches);
		}
	}

	protected function MergeWith_InNamespace($oFilter, &$aClassAliases, &$aAliasTranslation)
	{
		if ($this->GetClass() != $oFilter->GetClass())
		{
			throw new CoreException("Attempting to merge a filter of class '{$this->GetClass()}' with a filter of class '{$oFilter->GetClass()}'");
		}

		// Translate search condition into our aliasing scheme
		$aAliasTranslation[$oFilter->GetClassAlias()]['*'] = $this->GetClassAlias(); 

		$this->m_aFullText = array_merge($this->m_aFullText, $oFilter->m_aFullText);

		foreach($oFilter->m_aPointingTo as $sExtKeyAttCode=>$aPointingTo)
		{
			foreach($aPointingTo as $iOperatorCode => $aFilter)
			{
				foreach($aFilter as $oExtFilter)
				{
					$this->AddCondition_PointingTo_InNamespace($oExtFilter, $sExtKeyAttCode, $aClassAliases, $aAliasTranslation, $iOperatorCode);
				}
			}
		}
		foreach($oFilter->m_aReferencedBy as $sForeignClass => $aReferences)
		{
			foreach($aReferences as $sForeignExtKeyAttCode => $oForeignFilter)
			{
				$this->AddCondition_ReferencedBy_InNamespace($oForeignFilter, $sForeignExtKeyAttCode, $aClassAliases, $aAliasTranslation);
			}
		}
	}

	public function GetCriteria() {return $this->m_oSearchCondition;}
	public function GetCriteria_FullText() {return $this->m_aFullText;}
	public function GetCriteria_PointingTo($sKeyAttCode = "")
	{
		if (empty($sKeyAttCode))
		{
			return $this->m_aPointingTo;
		}
		if (!array_key_exists($sKeyAttCode, $this->m_aPointingTo)) return array();
		return $this->m_aPointingTo[$sKeyAttCode];
	}
	public function GetCriteria_ReferencedBy($sRemoteClass = "", $sForeignExtKeyAttCode = "")
	{
		if (empty($sRemoteClass))
		{
			return $this->m_aReferencedBy;
		}
		if (!array_key_exists($sRemoteClass, $this->m_aReferencedBy)) return null;
		if (empty($sForeignExtKeyAttCode))
		{
			return $this->m_aReferencedBy[$sRemoteClass];
		}
		if (!array_key_exists($sForeignExtKeyAttCode, $this->m_aReferencedBy[$sRemoteClass])) return null;
		return $this->m_aReferencedBy[$sRemoteClass][$sForeignExtKeyAttCode];
	}

	public function SetInternalParams($aParams)
	{
		return $this->m_aParams = $aParams;
	}

	public function GetInternalParams()
	{
		return $this->m_aParams;
	}

	public function GetQueryParams()
	{
		$aParams = array();
		$this->m_oSearchCondition->Render($aParams, true);
		return $aParams;
	}

	public function ListConstantFields()
	{
		return $this->m_oSearchCondition->ListConstantFields();
	}
	
	/**
	 * Turn the parameters (:xxx) into scalar values in order to easily
	 * serialize a search
	 */
	public function ApplyParameters($aArgs)
	{
		return $this->m_oSearchCondition->ApplyParameters(array_merge($this->m_aParams, $aArgs));
	}
	
	public function ToOQL($bDevelopParams = false, $aContextParams = null)
	{
		// Currently unused, but could be useful later
		$bRetrofitParams = false;

		if ($bDevelopParams)
		{
			if (is_null($aContextParams))
			{
				$aParams = array_merge($this->m_aParams);
			}
			else
			{
				$aParams = array_merge($aContextParams, $this->m_aParams);
			}
			$aParams = MetaModel::PrepareQueryArguments($aParams);
		}
		else
		{
			// Leave it as is, the rendering will be made with parameters in clear
			$aParams = null;
		}
	
		$sSelectedClasses = implode(', ', array_keys($this->m_aSelectedClasses));
		$sRes = 'SELECT '.$sSelectedClasses.' FROM';

		$sRes .= ' '.$this->GetFirstJoinedClass().' AS '.$this->GetFirstJoinedClassAlias();
		$sRes .= $this->ToOQL_Joins();
		$sRes .= " WHERE ".$this->m_oSearchCondition->Render($aParams, $bRetrofitParams);

		// Temporary: add more info about other conditions, necessary to avoid strange behaviors with the cache
		foreach($this->m_aFullText as $sFullText)
		{
			$sRes .= " AND MATCHES '$sFullText'";
		}
		return $sRes;
	}

	protected function ToOQL_Joins()
	{
		$sRes = '';
		foreach($this->m_aPointingTo as $sExtKey => $aPointingTo)
		{
			foreach($aPointingTo as $iOperatorCode => $aFilter)
			{
				foreach($aFilter as $oFilter)
				{
					switch($iOperatorCode)
					{
						case TREE_OPERATOR_EQUALS:
						$sOperator = ' = ';
						break;
						
						case TREE_OPERATOR_BELOW:
						$sOperator = ' BELOW ';
						break;
						
						case TREE_OPERATOR_BELOW_STRICT:
						$sOperator = ' BELOW STRICT ';
						break;
						
						case TREE_OPERATOR_NOT_BELOW:
						$sOperator = ' NOT BELOW ';
						break;
						
						case TREE_OPERATOR_NOT_BELOW_STRICT:
						$sOperator = ' NOT BELOW STRICT ';
						break;
						
						case TREE_OPERATOR_ABOVE:
						$sOperator = ' ABOVE ';
						break;
						
						case TREE_OPERATOR_ABOVE_STRICT:
						$sOperator = ' ABOVE STRICT ';
						break;
						
						case TREE_OPERATOR_NOT_ABOVE:
						$sOperator = ' NOT ABOVE ';
						break;
						
						case TREE_OPERATOR_NOT_ABOVE_STRICT:
						$sOperator = ' NOT ABOVE STRICT ';
						break;
						
					}
					$sRes .= ' JOIN '.$oFilter->GetFirstJoinedClass().' AS '.$oFilter->GetFirstJoinedClassAlias().' ON '.$this->GetFirstJoinedClassAlias().'.'.$sExtKey.$sOperator.$oFilter->GetFirstJoinedClassAlias().'.id';
					$sRes .= $oFilter->ToOQL_Joins();				
				}
			}
		}
		foreach($this->m_aReferencedBy as $sForeignClass=>$aReferences)
		{
			foreach($aReferences as $sForeignExtKeyAttCode=>$oForeignFilter)
			{
				$sRes .= ' JOIN '.$oForeignFilter->GetFirstJoinedClass().' AS '.$oForeignFilter->GetFirstJoinedClassAlias().' ON '.$oForeignFilter->GetFirstJoinedClassAlias().'.'.$sForeignExtKeyAttCode.' = '.$this->GetFirstJoinedClassAlias().'.id';
				$sRes .= $oForeignFilter->ToOQL_Joins();
			}
		}
		return $sRes;
	}

	protected function OQLExpressionToCondition($sQuery, $oExpression, $aClassAliases)
	{
		if ($oExpression instanceof BinaryOqlExpression)
		{
			$sOperator = $oExpression->GetOperator();
			$oLeft = $this->OQLExpressionToCondition($sQuery, $oExpression->GetLeftExpr(), $aClassAliases);
			$oRight = $this->OQLExpressionToCondition($sQuery, $oExpression->GetRightExpr(), $aClassAliases);
			return new BinaryExpression($oLeft, $sOperator, $oRight);
		}
		elseif ($oExpression instanceof FieldOqlExpression)
		{
			$sClassAlias = $oExpression->GetParent();
			$sFltCode = $oExpression->GetName();
			if (empty($sClassAlias))
			{
				// Need to find the right alias
				// Build an array of field => array of aliases
				$aFieldClasses = array();
				foreach($aClassAliases as $sAlias => $sReal)
				{
					foreach(MetaModel::GetFiltersList($sReal) as $sAnFltCode)
					{
						$aFieldClasses[$sAnFltCode][] = $sAlias;
					}
				}
				$sClassAlias = $aFieldClasses[$sFltCode][0];
			}
			return new FieldExpression($sFltCode, $sClassAlias);
		}
		elseif ($oExpression instanceof VariableOqlExpression)
		{
			return new VariableExpression($oExpression->GetName());
		}
		elseif ($oExpression instanceof TrueOqlExpression)
		{
			return new TrueExpression;
		}
		elseif ($oExpression instanceof ScalarOqlExpression)
		{
			return new ScalarExpression($oExpression->GetValue());
		}
		elseif ($oExpression instanceof ListOqlExpression)
		{
			$aItems = array();
			foreach ($oExpression->GetItems() as $oItemExpression)
			{
				$aItems[] = $this->OQLExpressionToCondition($sQuery, $oItemExpression, $aClassAliases);
			}
			return new ListExpression($aItems);
		}
		elseif ($oExpression instanceof FunctionOqlExpression)
		{
			$aArgs = array();
			foreach ($oExpression->GetArgs() as $oArgExpression)
			{
				$aArgs[] = $this->OQLExpressionToCondition($sQuery, $oArgExpression, $aClassAliases);
			}
			return new FunctionExpression($oExpression->GetVerb(), $aArgs);
		}
		elseif ($oExpression instanceof IntervalOqlExpression)
		{
			return new IntervalExpression($oExpression->GetValue(), $oExpression->GetUnit());
		}
		else
		{
			throw new CoreException('Unknown expression type', array('class'=>get_class($oExpression), 'query'=>$sQuery));
		}
	}

	public function InitFromOqlQuery(OqlQuery $oOqlQuery, $sQuery)
	{
		$sClass = $oOqlQuery->GetClass();
		$sClassAlias = $oOqlQuery->GetClassAlias();

		$aAliases = array($sClassAlias => $sClass);

		// Maintain an array of filters, because the flat list is in fact referring to a tree
		// And this will be an easy way to dispatch the conditions
		// $this will be referenced by the other filters, or the other way around...
		$aJoinItems = array($sClassAlias => $this);

		$aJoinSpecs = $oOqlQuery->GetJoins();
		if (is_array($aJoinSpecs))
		{
			foreach ($aJoinSpecs as $oJoinSpec)
			{
				$sJoinClass = $oJoinSpec->GetClass();
				$sJoinClassAlias = $oJoinSpec->GetClassAlias();

				// Assumption: ext key on the left only !!!
				// normalization should take care of this
				$oLeftField = $oJoinSpec->GetLeftField();
				$sFromClass = $oLeftField->GetParent();
				$sExtKeyAttCode = $oLeftField->GetName();

				$oRightField = $oJoinSpec->GetRightField();
				$sToClass = $oRightField->GetParent();

				$aAliases[$sJoinClassAlias] = $sJoinClass;
				$aJoinItems[$sJoinClassAlias] = new DBObjectSearch($sJoinClass, $sJoinClassAlias);

				if ($sFromClass == $sJoinClassAlias)
				{
					$oReceiver = $aJoinItems[$sToClass];
					$oNewComer = $aJoinItems[$sFromClass];

					$aAliasTranslation = array();
					$oReceiver->AddCondition_ReferencedBy_InNameSpace($oNewComer, $sExtKeyAttCode, $oReceiver->m_aClasses, $aAliasTranslation);
				}
				else
				{
					$sOperator = $oJoinSpec->GetOperator();
					switch($sOperator)
					{
						case '=':
						$iOperatorCode = TREE_OPERATOR_EQUALS;
						break;
						case 'BELOW':
						$iOperatorCode = TREE_OPERATOR_BELOW;
						break;
						case 'BELOW_STRICT':
						$iOperatorCode = TREE_OPERATOR_BELOW_STRICT;
						break;
						case 'NOT_BELOW':
						$iOperatorCode = TREE_OPERATOR_NOT_BELOW;
						break;
						case 'NOT_BELOW_STRICT':
						$iOperatorCode = TREE_OPERATOR_NOT_BELOW_STRICT;
						break;
						case 'ABOVE':
						$iOperatorCode = TREE_OPERATOR_ABOVE;
						break;
						case 'ABOVE_STRICT':
						$iOperatorCode = TREE_OPERATOR_ABOVE_STRICT;
						break;
						case 'NOT_ABOVE':
						$iOperatorCode = TREE_OPERATOR_NOT_ABOVE;
						break;
						case 'NOT_ABOVE_STRICT':
						$iOperatorCode = TREE_OPERATOR_NOT_ABOVE_STRICT;
						break;
					}
					$oReceiver = $aJoinItems[$sFromClass];
					$oNewComer = $aJoinItems[$sToClass];

					$aAliasTranslation = array();
					$oReceiver->AddCondition_PointingTo_InNameSpace($oNewComer, $sExtKeyAttCode, $oReceiver->m_aClasses, $aAliasTranslation, $iOperatorCode);
				}
			}
		}

		// Check and prepare the select information
		$this->m_aSelectedClasses = array();
		foreach ($oOqlQuery->GetSelectedClasses() as $oClassDetails)
		{
			$sClassToSelect = $oClassDetails->GetValue();
			$this->m_aSelectedClasses[$sClassToSelect] = $aAliases[$sClassToSelect];
		}
		$this->m_aClasses = $aAliases;

		$oConditionTree = $oOqlQuery->GetCondition();
		if ($oConditionTree instanceof Expression)
		{
			$this->m_oSearchCondition = $this->OQLExpressionToCondition($sQuery, $oConditionTree, $aAliases);
		}
	}

	////////////////////////////////////////////////////////////////////////////
	//
	// Construction of the SQL queries
	//
	////////////////////////////////////////////////////////////////////////////

	public function MakeDeleteQuery($aArgs = array())
	{
		$aModifierProperties = MetaModel::MakeModifierProperties($this);
		$oBuild = new QueryBuilderContext($this, $aModifierProperties);
		$oSQLQuery = $this->MakeSQLObjectQuery($oBuild, null, array());
		$oSQLQuery->SetCondition($oBuild->m_oQBExpressions->GetCondition());
		$oSQLQuery->SetSelect($oBuild->m_oQBExpressions->GetSelect());
		$aScalarArgs = array_merge(MetaModel::PrepareQueryArguments($aArgs), $this->GetInternalParams());
		return $oSQLQuery->RenderDelete($aScalarArgs);
	}

	public function MakeUpdateQuery($aValues, $aArgs = array())
	{
		// $aValues is an array of $sAttCode => $value
		$aModifierProperties = MetaModel::MakeModifierProperties($this);
		$oBuild = new QueryBuilderContext($this, $aModifierProperties);
		$oSQLQuery = $this->MakeSQLObjectQuery($oBuild, null, $aValues);
		$oSQLQuery->SetCondition($oBuild->m_oQBExpressions->GetCondition());
		$oSQLQuery->SetSelect($oBuild->m_oQBExpressions->GetSelect());
		$aScalarArgs = array_merge(MetaModel::PrepareQueryArguments($aArgs), $this->GetInternalParams());
		return $oSQLQuery->RenderUpdate($aScalarArgs);
	}

	public function MakeSQLQuery($aAttToLoad, $bGetCount, $aModifierProperties, $aGroupByExpr = null, $aSelectedClasses = null)
	{
		$oBuild = new QueryBuilderContext($this, $aModifierProperties, $aGroupByExpr, $aSelectedClasses);

		$oSQLQuery = $this->MakeSQLObjectQuery($oBuild, $aAttToLoad, array());
		$oSQLQuery->SetCondition($oBuild->m_oQBExpressions->GetCondition());
		if ($aGroupByExpr)
		{
			$aCols = $oBuild->m_oQBExpressions->GetGroupBy();
			$oSQLQuery->SetGroupBy($aCols);
			$oSQLQuery->SetSelect($aCols);
		}
		else
		{
			$oSQLQuery->SetSelect($oBuild->m_oQBExpressions->GetSelect());
		}

		if (self::$m_bOptimizeQueries)
		{
			if ($bGetCount)
			{
				// Simplify the query if just getting the count
				$oSQLQuery->SetSelect(array());
			}
			$oBuild->m_oQBExpressions->GetMandatoryTables($aMandatoryTables);
			$oSQLQuery->OptimizeJoins($aMandatoryTables);
		}

		return $oSQLQuery;
	}


	protected function MakeSQLObjectQuery(&$oBuild, $aAttToLoad = null, $aValues = array())
	{
		// Note: query class might be different than the class of the filter
		// -> this occurs when we are linking our class to an external class (referenced by, or pointing to)
		$sClass = $this->GetFirstJoinedClass();
		$sClassAlias = $this->GetFirstJoinedClassAlias();

		$bIsOnQueriedClass = array_key_exists($sClassAlias, $oBuild->GetRootFilter()->GetSelectedClasses());

		self::DbgTrace("Entering: ".$this->ToOQL().", ".($bIsOnQueriedClass ? "MAIN" : "SECONDARY"));

		$sRootClass = MetaModel::GetRootClass($sClass);
		$sKeyField = MetaModel::DBGetKey($sClass);

		if ($bIsOnQueriedClass)
		{
			// default to the whole list of attributes + the very std id/finalclass
			$oBuild->m_oQBExpressions->AddSelect($sClassAlias.'id', new FieldExpression('id', $sClassAlias));
			if (is_null($aAttToLoad) || !array_key_exists($sClassAlias, $aAttToLoad))
			{
				$sSelectedClass = $oBuild->GetSelectedClass($sClassAlias);
				$aAttList = MetaModel::ListAttributeDefs($sSelectedClass);
			}
			else
			{
				$aAttList = $aAttToLoad[$sClassAlias];
			}
			foreach ($aAttList as $sAttCode => $oAttDef)
			{
				if (!$oAttDef->IsScalar()) continue;
				// keep because it can be used for sorting - if (!$oAttDef->LoadInObject()) continue;
				
				foreach ($oAttDef->GetSQLExpressions() as $sColId => $sSQLExpr)
				{
					$oBuild->m_oQBExpressions->AddSelect($sClassAlias.$sAttCode.$sColId, new FieldExpression($sAttCode.$sColId, $sClassAlias));
				}
			}

			// Transform the full text condition into additional condition expression
			$aFullText = $this->GetCriteria_FullText();
			if (count($aFullText) > 0)
			{
				$aFullTextFields = array();
				foreach (MetaModel::ListAttributeDefs($sClass) as $sAttCode => $oAttDef)
				{
					if (!$oAttDef->IsScalar()) continue;
					if ($oAttDef->IsExternalKey()) continue;
					$aFullTextFields[] = new FieldExpression($sAttCode, $sClassAlias);
				}
				$oTextFields = new CharConcatWSExpression(' ', $aFullTextFields);
				
				foreach($aFullText as $sFTNeedle)
				{
					$oNewCond = new BinaryExpression($oTextFields, 'LIKE', new ScalarExpression("%$sFTNeedle%"));
					$oBuild->m_oQBExpressions->AddCondition($oNewCond);
				}
			}
		}
//echo "<p>oQBExpr ".__LINE__.": <pre>\n".print_r($oBuild->m_oQBExpressions, true)."</pre></p>\n";
		$aExpectedAtts = array(); // array of (attcode => fieldexpression)
//echo "<p>".__LINE__.": GetUnresolvedFields($sClassAlias, ...)</p>\n";
		$oBuild->m_oQBExpressions->GetUnresolvedFields($sClassAlias, $aExpectedAtts);

		// Compute a clear view of required joins (from the current class)
		// Build the list of external keys:
		// -> ext keys required by an explicit join
		// -> ext keys mentionned in a 'pointing to' condition
		// -> ext keys required for an external field
		// -> ext keys required for a friendly name
		//
		$aExtKeys = array(); // array of sTableClass => array of (sAttCode (keys) => array of (sAttCode (fields)=> oAttDef))
		//
		// Optimization: could be partially computed once for all (cached) ?
		//  

		if ($bIsOnQueriedClass)
		{
			// Get all Ext keys for the queried class (??)
			foreach(MetaModel::GetKeysList($sClass) as $sKeyAttCode)
			{
				$sKeyTableClass = MetaModel::GetAttributeOrigin($sClass, $sKeyAttCode);
				$aExtKeys[$sKeyTableClass][$sKeyAttCode] = array();
			}
		}
		// Get all Ext keys used by the filter
		foreach ($this->GetCriteria_PointingTo() as $sKeyAttCode => $aPointingTo)
		{
			if (array_key_exists(TREE_OPERATOR_EQUALS, $aPointingTo))
			{
				$sKeyTableClass = MetaModel::GetAttributeOrigin($sClass, $sKeyAttCode);
				$aExtKeys[$sKeyTableClass][$sKeyAttCode] = array();
			}
		}

		$aFNJoinAlias = array(); // array of (subclass => alias)
		if (array_key_exists('friendlyname', $aExpectedAtts))
		{
			// To optimize: detect a restriction on child classes in the condition expression
			//    e.g. SELECT FunctionalCI WHERE finalclass IN ('Server', 'VirtualMachine')
			$oNameExpression = self::GetExtendedNameExpression($sClass);

			$aNameFields = array();
			$oNameExpression->GetUnresolvedFields('', $aNameFields);
			$aTranslateNameFields = array();
			foreach($aNameFields as $sSubClass => $aFields)
			{
				foreach($aFields as $sAttCode => $oField)
				{
					$oAttDef = MetaModel::GetAttributeDef($sSubClass, $sAttCode);
					if ($oAttDef->IsExternalKey())
					{
						$sClassOfAttribute = MetaModel::GetAttributeOrigin($sSubClass, $sAttCode);
						$aExtKeys[$sClassOfAttribute][$sAttCode] = array();
					}				
					elseif ($oAttDef->IsExternalField() || ($oAttDef instanceof AttributeFriendlyName))
					{
						$sKeyAttCode = $oAttDef->GetKeyAttCode();
						$sClassOfAttribute = MetaModel::GetAttributeOrigin($sSubClass, $sKeyAttCode);
						$aExtKeys[$sClassOfAttribute][$sKeyAttCode][$sAttCode] = $oAttDef;
					}
					else
					{
						$sClassOfAttribute = MetaModel::GetAttributeOrigin($sSubClass, $sAttCode);
					}

					if (MetaModel::IsParentClass($sClassOfAttribute, $sClass))
					{
						// The attribute is part of the standard query
						//
						$sAliasForAttribute = $sClassAlias;
					}
					else
					{
						// The attribute will be available from an additional outer join
						// For each subclass (table) one single join is enough
						//
						if (!array_key_exists($sClassOfAttribute, $aFNJoinAlias))
						{
							$sAliasForAttribute = $oBuild->GenerateClassAlias($sClassAlias.'_fn_'.$sClassOfAttribute, $sClassOfAttribute);
							$aFNJoinAlias[$sClassOfAttribute] = $sAliasForAttribute;
						}
						else
						{
							$sAliasForAttribute = $aFNJoinAlias[$sClassOfAttribute];
						}
					}

					$aTranslateNameFields[$sSubClass][$sAttCode] = new FieldExpression($sAttCode, $sAliasForAttribute);
				}
			}
			$oNameExpression = $oNameExpression->Translate($aTranslateNameFields, false);

			$aTranslateNow = array();
			$aTranslateNow[$sClassAlias]['friendlyname'] = $oNameExpression;
			$oBuild->m_oQBExpressions->Translate($aTranslateNow, false);
		}

		// Add the ext fields used in the select (eventually adds an external key)
		foreach(MetaModel::ListAttributeDefs($sClass) as $sAttCode=>$oAttDef)
		{
			if ($oAttDef->IsExternalField() || ($oAttDef instanceof AttributeFriendlyName))
			{
				if (array_key_exists($sAttCode, $aExpectedAtts))
				{
					$sKeyAttCode = $oAttDef->GetKeyAttCode();
					if ($sKeyAttCode != 'id')
					{
						// Add the external attribute
						$sKeyTableClass = MetaModel::GetAttributeOrigin($sClass, $sKeyAttCode);
						$aExtKeys[$sKeyTableClass][$sKeyAttCode][$sAttCode] = $oAttDef;
					}
				}
			}
		}

		// First query built upon on the leaf (ie current) class
		//
		self::DbgTrace("Main (=leaf) class, call MakeSQLObjectQuerySingleTable()");
		if (MetaModel::HasTable($sClass))
		{
			$oSelectBase = $this->MakeSQLObjectQuerySingleTable($oBuild, $aAttToLoad, $sClass, $aExtKeys, $aValues);
		}
		else
		{
			$oSelectBase = null;

			// As the join will not filter on the expected classes, we have to specify it explicitely
			$sExpectedClasses = implode("', '", MetaModel::EnumChildClasses($sClass, ENUM_CHILD_CLASSES_ALL));
			$oFinalClassRestriction = Expression::FromOQL("`$sClassAlias`.finalclass IN ('$sExpectedClasses')");
			$oBuild->m_oQBExpressions->AddCondition($oFinalClassRestriction);
		}

		// Then we join the queries of the eventual parent classes (compound model)
		foreach(MetaModel::EnumParentClasses($sClass) as $sParentClass)
		{
			if (!MetaModel::HasTable($sParentClass)) continue;

			self::DbgTrace("Parent class: $sParentClass... let's call MakeSQLObjectQuerySingleTable()");
			$oSelectParentTable = $this->MakeSQLObjectQuerySingleTable($oBuild, $aAttToLoad, $sParentClass, $aExtKeys, $aValues);
			if (is_null($oSelectBase))
			{
				$oSelectBase = $oSelectParentTable;
			}
			else
			{
				$oSelectBase->AddInnerJoin($oSelectParentTable, $sKeyField, MetaModel::DBGetKey($sParentClass));
			}
		}

		// Filter on objects referencing me
		foreach ($this->GetCriteria_ReferencedBy() as $sForeignClass => $aKeysAndFilters)
		{
			foreach ($aKeysAndFilters as $sForeignKeyAttCode => $oForeignFilter)
			{
				$oForeignKeyAttDef = MetaModel::GetAttributeDef($sForeignClass, $sForeignKeyAttCode);
	
				self::DbgTrace("Referenced by foreign key: $sForeignKeyAttCode... let's call MakeSQLObjectQuery()");
				//self::DbgTrace($oForeignFilter);
				//self::DbgTrace($oForeignFilter->ToOQL());
				//self::DbgTrace($oSelectForeign);
				//self::DbgTrace($oSelectForeign->RenderSelect(array()));

				$sForeignClassAlias = $oForeignFilter->GetFirstJoinedClassAlias();
				$oBuild->m_oQBExpressions->PushJoinField(new FieldExpression($sForeignKeyAttCode, $sForeignClassAlias));

				if ($oForeignKeyAttDef instanceof AttributeObjectKey)
				{
					$sClassAttCode = $oForeignKeyAttDef->Get('class_attcode');

					// Add the condition: `$sForeignClassAlias`.$sClassAttCode IN (subclasses of $sClass')
					$oClassListExpr = ListExpression::FromScalars(MetaModel::EnumChildClasses($sClass, ENUM_CHILD_CLASSES_ALL));
					$oClassExpr = new FieldExpression($sClassAttCode, $sForeignClassAlias);
					$oClassRestriction = new BinaryExpression($oClassExpr, 'IN', $oClassListExpr);
					$oBuild->m_oQBExpressions->AddCondition($oClassRestriction);
				}

				$oSelectForeign = $oForeignFilter->MakeSQLObjectQuery($oBuild, $aAttToLoad);

				$oJoinExpr = $oBuild->m_oQBExpressions->PopJoinField();
				$sForeignKeyTable = $oJoinExpr->GetParent();
				$sForeignKeyColumn = $oJoinExpr->GetName();
				$oSelectBase->AddInnerJoin($oSelectForeign, $sKeyField, $sForeignKeyColumn, $sForeignKeyTable);
			}
		}

		// Additional JOINS for Friendly names
		//
		foreach ($aFNJoinAlias as $sSubClass => $sSubClassAlias)
		{
			$oSubClassFilter = new DBObjectSearch($sSubClass, $sSubClassAlias);
			$oSelectFN = $oSubClassFilter->MakeSQLObjectQuerySingleTable($oBuild, $aAttToLoad, $sSubClass, $aExtKeys, array());
			$oSelectBase->AddLeftJoin($oSelectFN, $sKeyField, MetaModel::DBGetKey($sSubClass));
		}

		// That's all... cross fingers and we'll get some working query

		//MyHelpers::var_dump_html($oSelectBase, true);
		//MyHelpers::var_dump_html($oSelectBase->RenderSelect(), true);
		if (self::$m_bDebugQuery) $oSelectBase->DisplayHtml();
		return $oSelectBase;
	}

	protected function MakeSQLObjectQuerySingleTable(&$oBuild, $aAttToLoad, $sTableClass, $aExtKeys, $aValues)
	{
		// $aExtKeys is an array of sTableClass => array of (sAttCode (keys) => array of sAttCode (fields))
//echo "MakeSQLObjectQuery($sTableClass)-liste des clefs externes($sTableClass): <pre>".print_r($aExtKeys, true)."</pre><br/>\n";

		// Prepare the query for a single table (compound objects)
		// Ignores the items (attributes/filters) that are not on the target table
		// Perform an (inner or left) join for every external key (and specify the expected fields)
		//
		// Returns an SQLQuery
		//
		$sTargetClass = $this->GetFirstJoinedClass();
		$sTargetAlias = $this->GetFirstJoinedClassAlias();
		$sTable = MetaModel::DBGetTable($sTableClass);
		$sTableAlias = $oBuild->GenerateTableAlias($sTargetAlias.'_'.$sTable, $sTable);

		$aTranslation = array();
		$aExpectedAtts = array();
		$oBuild->m_oQBExpressions->GetUnresolvedFields($sTargetAlias, $aExpectedAtts);
		
		$bIsOnQueriedClass = array_key_exists($sTargetAlias, $oBuild->GetRootFilter()->GetSelectedClasses());
		
		self::DbgTrace("Entering: tableclass=$sTableClass, filter=".$this->ToOQL().", ".($bIsOnQueriedClass ? "MAIN" : "SECONDARY"));

		// 1 - SELECT and UPDATE
		//
		// Note: no need for any values nor fields for foreign Classes (ie not the queried Class)
		//
		$aUpdateValues = array();


		// 1/a - Get the key and friendly name
		//
		// We need one pkey to be the key, let's take the first one available
		$oSelectedIdField = null;
		$oIdField = new FieldExpressionResolved(MetaModel::DBGetKey($sTableClass), $sTableAlias);
		$aTranslation[$sTargetAlias]['id'] = $oIdField;

		if ($bIsOnQueriedClass)
		{
			// Add this field to the list of queried fields (required for the COUNT to work fine)
			$oSelectedIdField = $oIdField;
		}

		// 1/b - Get the other attributes
		// 
		foreach(MetaModel::ListAttributeDefs($sTableClass) as $sAttCode=>$oAttDef)
		{
			// Skip this attribute if not defined in this table
			if (MetaModel::GetAttributeOrigin($sTargetClass, $sAttCode) != $sTableClass) continue;

			// Skip this attribute if not made of SQL columns 
			if (count($oAttDef->GetSQLExpressions()) == 0) continue;

			// Update...
			//
			if ($bIsOnQueriedClass && array_key_exists($sAttCode, $aValues))
			{
				assert ($oAttDef->IsDirectField());
				foreach ($oAttDef->GetSQLValues($aValues[$sAttCode]) as $sColumn => $sValue)
				{
					$aUpdateValues[$sColumn] = $sValue;
				}
			}
		}

		// 2 - The SQL query, for this table only
		//
		$oSelectBase = new SQLObjectQuery($sTable, $sTableAlias, array(), $bIsOnQueriedClass, $aUpdateValues, $oSelectedIdField);

		// 3 - Resolve expected expressions (translation table: alias.attcode => table.column)
		//
		foreach(MetaModel::ListAttributeDefs($sTableClass) as $sAttCode=>$oAttDef)
		{
			// Skip this attribute if not defined in this table
			if (MetaModel::GetAttributeOrigin($sTargetClass, $sAttCode) != $sTableClass) continue;

			// Select...
			//
			if ($oAttDef->IsExternalField())
			{
				// skip, this will be handled in the joined tables (done hereabove)
			}
			else
			{
//echo "<p>MakeSQLObjectQuerySingleTable: Field $sAttCode is part of the table $sTable (named: $sTableAlias)</p>";
				// standard field, or external key
				// add it to the output
				foreach ($oAttDef->GetSQLExpressions() as $sColId => $sSQLExpr)
				{
					if (array_key_exists($sAttCode.$sColId, $aExpectedAtts))
					{
						$oFieldSQLExp = new FieldExpressionResolved($sSQLExpr, $sTableAlias);
						foreach (MetaModel::EnumPlugins('iQueryModifier') as $sPluginClass => $oQueryModifier)
						{
							$oFieldSQLExp = $oQueryModifier->GetFieldExpression($oBuild, $sTargetClass, $sAttCode, $sColId, $oFieldSQLExp, $oSelectBase);
						}
						$aTranslation[$sTargetAlias][$sAttCode.$sColId] = $oFieldSQLExp;
					}
				}
			}
		}

//echo "MakeSQLObjectQuery- Classe $sTableClass<br/>\n";
		// 4 - The external keys -> joins...
		//
		$aAllPointingTo = $this->GetCriteria_PointingTo();

		if (array_key_exists($sTableClass, $aExtKeys))
		{
			foreach ($aExtKeys[$sTableClass] as $sKeyAttCode => $aExtFields)
			{
				$oKeyAttDef = MetaModel::GetAttributeDef($sTableClass, $sKeyAttCode);

				$aPointingTo = $this->GetCriteria_PointingTo($sKeyAttCode);
//echo "MakeSQLObjectQuery-Cle '$sKeyAttCode'<br/>\n";
				if (!array_key_exists(TREE_OPERATOR_EQUALS, $aPointingTo))
				{
//echo "MakeSQLObjectQuery-Ajoutons l'operateur TREE_OPERATOR_EQUALS pour $sKeyAttCode<br/>\n";
					// The join was not explicitely defined in the filter,
					// we need to do it now
					$sKeyClass =  $oKeyAttDef->GetTargetClass();
					$sKeyClassAlias = $oBuild->GenerateClassAlias($sKeyClass.'_'.$sKeyAttCode, $sKeyClass);
					$oExtFilter = new DBObjectSearch($sKeyClass, $sKeyClassAlias);

					$aAllPointingTo[$sKeyAttCode][TREE_OPERATOR_EQUALS][$sKeyClassAlias] = $oExtFilter;
				}
			}
		}
//echo "MakeSQLObjectQuery-liste des clefs de jointure: <pre>".print_r(array_keys($aAllPointingTo), true)."</pre><br/>\n";
				
		foreach ($aAllPointingTo as $sKeyAttCode => $aPointingTo)
		{
			foreach($aPointingTo as $iOperatorCode => $aFilter)
			{
				foreach($aFilter as $oExtFilter)
				{
					if (!MetaModel::IsValidAttCode($sTableClass, $sKeyAttCode)) continue; // Not defined in the class, skip it
					// The aliases should not conflict because normalization occured while building the filter
					$oKeyAttDef = MetaModel::GetAttributeDef($sTableClass, $sKeyAttCode);
					$sKeyClass =  $oExtFilter->GetFirstJoinedClass();
					$sKeyClassAlias = $oExtFilter->GetFirstJoinedClassAlias();

//echo "MakeSQLObjectQuery-$sTableClass::$sKeyAttCode Foreach PointingTo($iOperatorCode) <span style=\"color:red\">$sKeyClass (alias:$sKeyClassAlias)</span><br/>\n";
				
					// Note: there is no search condition in $oExtFilter, because normalization did merge the condition onto the top of the filter tree 

//echo "MakeSQLObjectQuery-array_key_exists($sTableClass, \$aExtKeys)<br/>\n";
					if ($iOperatorCode == TREE_OPERATOR_EQUALS)
					{
						if (array_key_exists($sTableClass, $aExtKeys) && array_key_exists($sKeyAttCode, $aExtKeys[$sTableClass]))
						{
							// Specify expected attributes for the target class query
							// ... and use the current alias !
							$aTranslateNow = array(); // Translation for external fields - must be performed before the join is done (recursion...)
							foreach($aExtKeys[$sTableClass][$sKeyAttCode] as $sAttCode => $oAtt)
							{
//echo "MakeSQLObjectQuery aExtKeys[$sTableClass][$sKeyAttCode] => $sAttCode-oAtt: <pre>".print_r($oAtt, true)."</pre><br/>\n";
								if ($oAtt instanceof AttributeFriendlyName)
								{
									// Note: for a given ext key, there is one single attribute "friendly name"
									$aTranslateNow[$sTargetAlias][$sAttCode] = new FieldExpression('friendlyname', $sKeyClassAlias);
//echo "<p><b>aTranslateNow[$sTargetAlias][$sAttCode] = new FieldExpression('friendlyname', $sKeyClassAlias);</b></p>\n";
								}
								else
								{
									$sExtAttCode = $oAtt->GetExtAttCode();
									// Translate mainclass.extfield => remoteclassalias.remotefieldcode
									$oRemoteAttDef = MetaModel::GetAttributeDef($sKeyClass, $sExtAttCode);
									foreach ($oRemoteAttDef->GetSQLExpressions() as $sColId => $sRemoteAttExpr)
									{
										$aTranslateNow[$sTargetAlias][$sAttCode.$sColId] = new FieldExpression($sExtAttCode, $sKeyClassAlias);
//echo "<p><b>aTranslateNow[$sTargetAlias][$sAttCode.$sColId] = new FieldExpression($sExtAttCode, $sKeyClassAlias);</b></p>\n";
									}
//echo "<p><b>ExtAttr2: $sTargetAlias.$sAttCode to $sKeyClassAlias.$sRemoteAttExpr (class: $sKeyClass)</b></p>\n";
								}
							}

							if ($oKeyAttDef instanceof AttributeObjectKey)
							{
								// Add the condition: `$sTargetAlias`.$sClassAttCode IN (subclasses of $sKeyClass')
								$sClassAttCode = $oKeyAttDef->Get('class_attcode');
								$oClassAttDef = MetaModel::GetAttributeDef($sTargetClass, $sClassAttCode);
								foreach ($oClassAttDef->GetSQLExpressions() as $sColId => $sSQLExpr)
								{
									$aTranslateNow[$sTargetAlias][$sClassAttCode.$sColId] = new FieldExpressionResolved($sSQLExpr, $sTableAlias);
								}

								$oClassListExpr = ListExpression::FromScalars(MetaModel::EnumChildClasses($sKeyClass, ENUM_CHILD_CLASSES_ALL));
								$oClassExpr = new FieldExpression($sClassAttCode, $sTargetAlias);
								$oClassRestriction = new BinaryExpression($oClassExpr, 'IN', $oClassListExpr);
								$oBuild->m_oQBExpressions->AddCondition($oClassRestriction);
							}

							// Translate prior to recursing
							//
//echo "<p>oQBExpr ".__LINE__.": <pre>\n".print_r($oBuild->m_oQBExpressions, true)."\n".print_r($aTranslateNow, true)."</pre></p>\n";
							$oBuild->m_oQBExpressions->Translate($aTranslateNow, false);
//echo "<p>oQBExpr ".__LINE__.": <pre>\n".print_r($oBuild->m_oQBExpressions, true)."</pre></p>\n";
		
//echo "<p>External key $sKeyAttCode (class: $sKeyClass), call MakeSQLObjectQuery()/p>\n";
							self::DbgTrace("External key $sKeyAttCode (class: $sKeyClass), call MakeSQLObjectQuery()");
							$oBuild->m_oQBExpressions->PushJoinField(new FieldExpression('id', $sKeyClassAlias));
			
//echo "<p>Recursive MakeSQLObjectQuery ".__LINE__.": <pre>\n".print_r($oBuild->GetRootFilter()->GetSelectedClasses(), true)."</pre></p>\n";
							$oSelectExtKey = $oExtFilter->MakeSQLObjectQuery($oBuild, $aAttToLoad);
			
							$oJoinExpr = $oBuild->m_oQBExpressions->PopJoinField();
							$sExternalKeyTable = $oJoinExpr->GetParent();
							$sExternalKeyField = $oJoinExpr->GetName();
			
							$aCols = $oKeyAttDef->GetSQLExpressions(); // Workaround a PHP bug: sometimes issuing a Notice if invoking current(somefunc())
							$sLocalKeyField = current($aCols); // get the first column for an external key
			
							self::DbgTrace("External key $sKeyAttCode, Join on $sLocalKeyField = $sExternalKeyField");
							if ($oKeyAttDef->IsNullAllowed())
							{
								$oSelectBase->AddLeftJoin($oSelectExtKey, $sLocalKeyField, $sExternalKeyField, $sExternalKeyTable);
							}
							else
							{
								$oSelectBase->AddInnerJoin($oSelectExtKey, $sLocalKeyField, $sExternalKeyField, $sExternalKeyTable);
							}
						}
					}
					elseif(MetaModel::GetAttributeOrigin($sKeyClass, $sKeyAttCode) == $sTableClass)
					{
						$oBuild->m_oQBExpressions->PushJoinField(new FieldExpression($sKeyAttCode, $sKeyClassAlias));
						$oSelectExtKey = $oExtFilter->MakeSQLObjectQuery($oBuild, $aAttToLoad);
						$oJoinExpr = $oBuild->m_oQBExpressions->PopJoinField();
						$sExternalKeyTable = $oJoinExpr->GetParent();
						$sExternalKeyField = $oJoinExpr->GetName();
						$sLeftIndex = $sExternalKeyField.'_left'; // TODO use GetSQLLeft()
						$sRightIndex = $sExternalKeyField.'_right'; // TODO use GetSQLRight()
	
						$LocalKeyLeft = $oKeyAttDef->GetSQLLeft();
						$LocalKeyRight = $oKeyAttDef->GetSQLRight();
	
						$oSelectBase->AddInnerJoinTree($oSelectExtKey, $LocalKeyLeft, $LocalKeyRight, $sLeftIndex, $sRightIndex, $sExternalKeyTable, $iOperatorCode);
					}
				}
			}
		}

		// Translate the selected columns
		//
//echo "<p>oQBExpr ".__LINE__.": <pre>\n".print_r($oBuild->m_oQBExpressions, true)."</pre></p>\n";
		$oBuild->m_oQBExpressions->Translate($aTranslation, false);
//echo "<p>oQBExpr ".__LINE__.": <pre>\n".print_r($oBuild->m_oQBExpressions, true)."</pre></p>\n";

		//MyHelpers::var_dump_html($oSelectBase->RenderSelect());
		return $oSelectBase;
	}

	/**
	 *	Get the friendly name for the class and its subclasses (if finalclass = 'subclass' ...)
	 *	Simplifies the final expression by grouping classes having the same name expression	 
	 *	Used when querying a parent class 	 
	*/
	static protected function GetExtendedNameExpression($sClass)
	{
		// 1st step - get all of the required expressions (instantiable classes)
		//            and group them using their OQL representation
		//
		$aFNExpressions = array(); // signature => array('expression' => oExp, 'classes' => array of classes)
		foreach (MetaModel::EnumChildClasses($sClass, ENUM_CHILD_CLASSES_ALL) as $sSubClass)
		{
			if (($sSubClass != $sClass) && MetaModel::IsAbstract($sSubClass)) continue;

			$oSubClassName = MetaModel::GetNameExpression($sSubClass);
			$sSignature = $oSubClassName->Render();
			if (!array_key_exists($sSignature, $aFNExpressions))
			{
				$aFNExpressions[$sSignature] = array(
					'expression' => $oSubClassName,
					'classes' => array(),
				);
			}
			$aFNExpressions[$sSignature]['classes'][] = $sSubClass;
		}

		// 2nd step - build the final name expression depending on the finalclass
		//
		if (count($aFNExpressions) == 1)
		{
			$aExpData = reset($aFNExpressions);
			$oNameExpression = $aExpData['expression'];
		}
		else
		{
			$oNameExpression = null;
			foreach ($aFNExpressions as $sSignature => $aExpData)
			{
				$oClassListExpr = ListExpression::FromScalars($aExpData['classes']);
				$oClassExpr = new FieldExpression('finalclass', $sClass);
				$oClassInList = new BinaryExpression($oClassExpr, 'IN', $oClassListExpr);

				if (is_null($oNameExpression))
				{
					$oNameExpression = $aExpData['expression'];
				}
				else
				{
					$oNameExpression = new FunctionExpression('IF', array($oClassInList, $aExpData['expression'], $oNameExpression));
				}
			}
		}
		return $oNameExpression;
	}

}
