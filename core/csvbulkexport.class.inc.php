<?php
// Copyright (C) 2015 Combodo SARL
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
 * Bulk export: CSV export
 *
 * @copyright   Copyright (C) 2015 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

class CSVBulkExport extends TabularBulkExport
{
	public function DisplayUsage(Page $oP)
	{
		$oP->p(" * csv format options:");
		$oP->p(" *\tfields: (mandatory) the comma separated list of field codes to export (e.g: name,org_id,service_name...).");
		$oP->p(" *\tseparator: (optional) character to be used as the separator (default is ',').");
		$oP->p(" *\tcharset: (optional) character set for encoding the result (default is 'UTF-8').");
		$oP->p(" *\ttext-qualifier: (optional) character to be used around text strings (default is '\"').");
		$oP->p(" *\tno_localize: set to 1 to retrieve non-localized values (for instance for ENUM values). Default is 0 (= localized values)");
	}

	public function ReadParameters()
	{
		parent::ReadParameters();
		$this->aStatusInfo['separator'] = utils::ReadParam('separator', ',', true, 'raw_data');
		if (strtolower($this->aStatusInfo['separator']) == 'tab')
		{
			$this->aStatusInfo['separator'] = "\t";
		}
		else if (strtolower($this->aStatusInfo['separator']) == 'other')
		{
			$this->aStatusInfo['separator'] = utils::ReadParam('other-separator', ',', true, 'raw_data');
		}
			
		$this->aStatusInfo['text_qualifier'] = utils::ReadParam('text-qualifier', '"', true, 'raw_data');
		if (strtolower($this->aStatusInfo['text_qualifier']) == 'other')
		{
			$this->aStatusInfo['text_qualifier'] = utils::ReadParam('other-text-qualifier', '"', true, 'raw_data');
		}
		$this->aStatusInfo['localize'] = !((bool)utils::ReadParam('no_localize', 0, true, 'integer'));
		$this->aStatusInfo['charset'] = strtoupper(utils::ReadParam('charset', 'UTF-8', true, 'raw_data'));
	}


	protected function SuggestField($aAliases, $sClass, $sAlias, $sAttCode)
	{
		switch($sAttCode)
		{
			case 'id': // replace 'id' by 'friendlyname'
				$sAttCode = 'friendlyname';
				break;
					
			default:
				$oAttDef = MetaModel::GetAttributeDef($sClass, $sAttCode);
				if ($oAttDef instanceof AttributeExternalKey)
				{
					$sAttCode .= '_friendlyname';
				}
		}

		return parent::SuggestField($aAliases, $sClass, $sAlias, $sAttCode);
	}

	public function EnumFormParts()
	{
		return array_merge(parent::EnumFormParts(), array('csv_options' => array('separator', 'charset', 'text-qualifier', 'no_localize') ,'interactive_fields_csv' => array('interactive_fields_csv')));
	}

	public function DisplayFormPart(WebPage $oP, $sPartId)
	{
		switch($sPartId)
		{
			case 'interactive_fields_csv':
				$this->GetInteractiveFieldsWidget($oP, 'interactive_fields_csv');
				break;

			case 'csv_options':
				$oP->add('<fieldset><legend>'.Dict::S('Core:BulkExport:CSVOptions').'</legend>');
				$oP->add('<table class="export_parameters"><tr><td style="vertical-align:top">');
				$oP->add('<h3>'.Dict::S('UI:CSVImport:SeparatorCharacter').'</h3>');
				$sRawSeparator = utils::ReadParam('separator', ',', true, 'raw_data');
				$aSep = array(
					';' => Dict::S('UI:CSVImport:SeparatorSemicolon+'),
					',' => Dict::S('UI:CSVImport:SeparatorComma+'),
					'tab' => Dict::S('UI:CSVImport:SeparatorTab+'),
				);
				$sOtherSeparator = '';
				if (!array_key_exists($sRawSeparator, $aSep))
				{
					$sOtherSeparator = $sRawSeparator;
					$sRawSeparator = 'other';
				}
				$aSep['other'] = Dict::S('UI:CSVImport:SeparatorOther').' <input type="text" size="3" name="other-separator" value="'.htmlentities($sOtherSeparator, ENT_QUOTES, 'UTF-8').'"/>';

				foreach($aSep as $sVal => $sLabel)
				{
					$sChecked = ($sVal == $sRawSeparator) ? 'checked' : '';
					$oP->add('<input type="radio" name="separator" value="'.htmlentities($sVal, ENT_QUOTES, 'UTF-8').'" '.$sChecked.'/>&nbsp;'.$sLabel.'<br/>');
				}
					
				$oP->add('</td><td style="vertical-align:top">');
					
				$oP->add('<h3>'.Dict::S('UI:CSVImport:TextQualifierCharacter').'</h3>');

				$sRawQualifier = utils::ReadParam('text-qualifier', '"', true, 'raw_data');
				$aQualifiers = array(
					'"' => Dict::S('UI:CSVImport:QualifierDoubleQuote+'),
					'\'' => Dict::S('UI:CSVImport:QualifierSimpleQuote+'),
				);
				$sOtherQualifier = '';
				if (!array_key_exists($sRawQualifier, $aQualifiers))
				{
					$sOtherQualifier = $sRawQualifier;
					$sRawQualifier = 'other';
				}
				$aQualifiers['other'] = Dict::S('UI:CSVImport:QualifierOther').' <input type="text" size="3" name="other-text-qualifier" value="'.htmlentities($sOtherQualifier, ENT_QUOTES, 'UTF-8').'"/>';
					
				foreach($aQualifiers as $sVal => $sLabel)
				{
					$sChecked = ($sVal == $sRawQualifier) ? 'checked' : '';
					$oP->add('<input type="radio" name="text-qualifier" value="'.htmlentities($sVal, ENT_QUOTES, 'UTF-8').'" '.$sChecked.'/>&nbsp;'.$sLabel.'<br/>');
				}
				
				$sChecked = (utils::ReadParam('no_localize', 0) == 1) ? ' checked ' : '';
				$oP->add('</td><td style="vertical-align:top">');
				$oP->add('<h3>'.Dict::S('Core:BulkExport:CSVLocalization').'</h3>');
				$oP->add('<input type="checkbox" id="csv_no_localize" name="no_localize" value="1"'.$sChecked.'><label for="csv_no_localize"> '.Dict::S('Core:BulkExport:OptionNoLocalize').'</label>');
				$oP->add('<br/>');
				$oP->add('<br/>');
				$oP->add(Dict::S('UI:CSVImport:Encoding').': <select name="charset" style="font-family:Arial,Helvetica,Sans-serif">'); // IE 8 has some troubles if the font is different
				$aPossibleEncodings = utils::GetPossibleEncodings(MetaModel::GetConfig()->GetCSVImportCharsets());
				$sDefaultEncoding = MetaModel::GetConfig()->Get('csv_file_default_charset');
				foreach($aPossibleEncodings as $sIconvCode => $sDisplayName )
				{
					$sSelected  = '';
					if ($sIconvCode == $sDefaultEncoding)
					{
						$sSelected = ' selected';
					}
					$oP->add('<option value="'.$sIconvCode.'"'.$sSelected.'>'.$sDisplayName.'</option>');
				}
				$oP->add('</select>');

				$oP->add('</td></tr></table>');
				
				$oP->add('</fieldset>');
				break;
					
					
			default:
				return parent:: DisplayFormPart($oP, $sPartId);
		}
	}

	protected function GetSampleData(DBObject $oObj, $sAttCode)
	{
		return trim($oObj->GetAsCSV($sAttCode), '"');
	}

	public function GetHeader()
	{
		$oSet = new DBObjectSet($this->oSearch);
		$this->aStatusInfo['status'] = 'running';
		$this->aStatusInfo['position'] = 0;
		$this->aStatusInfo['total'] = $oSet->Count();

		$aSelectedClasses = $this->oSearch->GetSelectedClasses();
		$aAuthorizedClasses = array();
		foreach($aSelectedClasses as $sAlias => $sClassName)
		{
			if (UserRights::IsActionAllowed($sClassName, UR_ACTION_BULK_READ, $oSet) && (UR_ALLOWED_YES || UR_ALLOWED_DEPENDS))
			{
				$aAuthorizedClasses[$sAlias] = $sClassName;
			}
		}
		$aAliases = array_keys($aAuthorizedClasses);
		$aData = array();
		foreach($this->aStatusInfo['fields'] as $sExtendedAttCode)
		{
			if (preg_match('/^([^\.]+)\.(.+)$/', $sExtendedAttCode, $aMatches))
			{
				$sAlias = $aMatches[1];
				$sAttCode = $aMatches[2];
			}
			else
			{
				$sAlias = reset($aAliases);
				$sAttCode = $sExtendedAttCode;
			}
			if (!in_array($sAlias, $aAliases))
			{
				throw new Exception("Invalid alias '$sAlias' for the column '$sExtendedAttCode'. Availables aliases: '".implode("', '", $aAliases)."'");
			}
			$sClass = $aAuthorizedClasses[$sAlias];
				
			switch($sAttCode)
			{
				case 'id':
				$sLabel = 'id';
				break;
						
				default:
				$oAttDef = MetaModel::GetAttributeDef($sClass, $sAttCode);
				if (($oAttDef instanceof AttributeExternalField) || (($oAttDef instanceof AttributeFriendlyName) && ($oAttDef->GetKeyAttCode() != 'id')))
				{
					$oKeyAttDef = MetaModel::GetAttributeDef($sClass, $oAttDef->GetKeyAttCode());
					$oExtAttDef = MetaModel::GetAttributeDef($oKeyAttDef->GetTargetClass(), $oAttDef->GetExtAttCode());
					if ($this->aStatusInfo['localize'])
					{
						$sStar = $oAttDef->IsNullAllowed() ? '' : '*';
						$sLabel = $oKeyAttDef->GetLabel().$sStar.'->'.$oExtAttDef->GetLabel();
					}
					else
					{
						$sLabel =  $oKeyAttDef->GetCode().'->'.$oExtAttDef->GetCode();
					}						
				}
				else
				{
					$sLabel = $this->aStatusInfo['localize'] ? $oAttDef->GetLabel() : $sAttCode;
				}
			}
			if (count($aAuthorizedClasses) > 1)
			{
				$aData[] = $sAlias.'.'.$sLabel;
			}
			else
			{
				$aData[] = $sLabel;
			}
		}
		$sFrom = array("\r\n", $this->aStatusInfo['text_qualifier']);
		$sTo = array("\n", $this->aStatusInfo['text_qualifier'].$this->aStatusInfo['text_qualifier']);
		foreach($aData as $idx => $sData)
		{
			// Escape and encode (if needed) the headers
			$sEscaped = str_replace($sFrom, $sTo, (string)$sData);
			$aData[$idx] = $this->aStatusInfo['text_qualifier'].$sEscaped.$this->aStatusInfo['text_qualifier'];
			if ($this->aStatusInfo['charset'] != 'UTF-8')
			{
				// Note: due to bugs in the glibc library it's safer to call iconv on the smallest possible string
				// and thus to convert field by field and not the whole row or file at once (see ticket #991)
				$aData[$idx] = iconv('UTF-8', $this->aStatusInfo['charset'].'//IGNORE//TRANSLIT', $aData[$idx]);
			}
		}
		$sData = implode($this->aStatusInfo['separator'], $aData)."\n";

		return $sData;
	}

	public function GetNextChunk(&$aStatus)
	{
		$sRetCode = 'run';
		$iPercentage = 0;

		$oSet = new DBObjectSet($this->oSearch);
		$aSelectedClasses = $this->oSearch->GetSelectedClasses();
		$aAuthorizedClasses = array();
		foreach($aSelectedClasses as $sAlias => $sClassName)
		{
			if (UserRights::IsActionAllowed($sClassName, UR_ACTION_BULK_READ, $oSet) && (UR_ALLOWED_YES || UR_ALLOWED_DEPENDS))
			{
				$aAuthorizedClasses[$sAlias] = $sClassName;
			}
		}
		$aAliases = array_keys($aAuthorizedClasses);
		$oSet->SetLimit($this->iChunkSize, $this->aStatusInfo['position']);

		$aAliasByField = array();
		$aColumnsToLoad = array();

		// Prepare the list of aliases / columns to load
		foreach($this->aStatusInfo['fields'] as $sExtendedAttCode)
		{
			if (preg_match('/^([^\.]+)\.(.+)$/', $sExtendedAttCode, $aMatches))
			{
				$sAlias = $aMatches[1];
				$sAttCode = $aMatches[2];
			}
			else
			{
				$sAlias = reset($aAliases);
				$sAttCode = $sExtendedAttCode;
			}
				
			if (!in_array($sAlias, $aAliases))
			{
				throw new Exception("Invalid alias '$sAlias' for the column '$sExtendedAttCode'. Availables aliases: '".implode("', '", $aAliases)."'");
			}
				
			if (!array_key_exists($sAlias, $aColumnsToLoad))
			{
				$aColumnsToLoad[$sAlias] = array();
			}
			if ($sAttCode != 'id')
			{
				// id is not a real attribute code and, moreover, is always loaded
				$aColumnsToLoad[$sAlias][] = $sAttCode;
			}
			$aAliasByField[$sExtendedAttCode] = array('alias' => $sAlias, 'attcode' => $sAttCode);
		}

		$iCount = 0;
		$sData = '';
		$oSet->OptimizeColumnLoad($aColumnsToLoad);
		$iPreviousTimeLimit = ini_get('max_execution_time');
		$iLoopTimeLimit = MetaModel::GetConfig()->Get('max_execution_time_per_loop');
		while($aRow = $oSet->FetchAssoc())
		{
			set_time_limit($iLoopTimeLimit);
			$aData = array();
			foreach($aAliasByField as $aAttCode)
			{
				$sField = '';
				$oObj = $aRow[$aAttCode['alias']];
				if ($oObj != null)
				{
					switch($aAttCode['attcode'])
					{
						case 'id':
							$sField = $oObj->GetKey();
							break;
								
						default:
							$sField = $oObj->GetAsCSV($aAttCode['attcode'], $this->aStatusInfo['separator'], $this->aStatusInfo['text_qualifier'], $this->aStatusInfo['localize']);
					}
					if ($this->aStatusInfo['charset'] != 'UTF-8')
					{
						// Note: due to bugs in the glibc library it's safer to call iconv on the smallest possible string
						// and thus to convert field by field and not the whole row or file at once (see ticket #991)
						$aData[] = iconv('UTF-8', $this->aStatusInfo['charset'].'//IGNORE//TRANSLIT', $sField);
					}
					else
					{
						$aData[] = $sField;
					}
				}
			}
			$sData .= implode($this->aStatusInfo['separator'], $aData)."\n";
			$iCount++;
		}
		set_time_limit($iPreviousTimeLimit);
		$this->aStatusInfo['position'] += $this->iChunkSize;
		if ($this->aStatusInfo['total'] == 0)
		{
			$iPercentage = 100;
		}
		else
		{
			$iPercentage = floor(min(100.0, 100.0*$this->aStatusInfo['position']/$this->aStatusInfo['total']));
		}

		if ($iCount < $this->iChunkSize)
		{
			$sRetCode = 'done';
		}

		$aStatus = array('code' => $sRetCode, 'message' => Dict::S('Core:BulkExport:RetrievingData'), 'percentage' => $iPercentage);
		return $sData;
	}

	public function GetSupportedFormats()
	{
		return array('csv' => Dict::S('Core:BulkExport:CSVFormat'));
	}

	public function GetMimeType()
	{
		return 'text/csv';
	}

	public function GetFileExtension()
	{
		return 'csv';
	}

}
