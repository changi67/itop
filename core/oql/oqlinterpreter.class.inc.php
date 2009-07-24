<?

class OqlNormalizeException extends OQLException
{
	public function __construct($sIssue, $sInput, OqlName $oName, $aExpecting = null)
	{
		parent::__construct($sIssue, $sInput, 0, $oName->GetPos(), $oName->GetValue(), $aExpecting);
	}
}

class OqlInterpreterException extends OQLException
{
}


class OqlInterpreter
{
	public $m_sQuery;

	public function __construct($sQuery)
	{
		$this->m_sQuery = $sQuery;
	}

	protected function Parse()
	{
		$oLexer = new OQLLexer($this->m_sQuery);
		$oParser = new OQLParser($this->m_sQuery);

		while($oLexer->yylex())
		{
			$oParser->doParse($oLexer->token, $oLexer->value, $oLexer->getTokenPos());
		}
		$res = $oParser->doFinish();
		return $res;
	}

	public function ParseObjectQuery()
	{
		$oRes = $this->Parse();
		if (!$oRes instanceof OqlObjectQuery)
		{
			throw new OqlException('Expecting an OQL query', $this->m_sQuery, 0, 0, get_class($oRes), array('OqlObjectQuery'));
		}
		return $oRes;
	}

/*
	public function ParseValueSetQuery()
	{
		$oRes = $this->Parse();
		if (!$oRes instanceof OqlValueSetQuery)
		{
			throw new OqlException('Expecting a value set query', $this->m_sQuery, 0, 0, get_class($oRes), array('OqlValueSetQuery'));
		}
		return $oRes;
	}
*/

	public function ParseExpression()
	{
		$oRes = $this->Parse();
		if (!$oRes instanceof Expression)
		{
			throw new OqlException('Expecting an OQL expression', $this->m_sQuery, 0, 0, get_class($oRes), array('Expression'));
		}
		return $oRes;
	}
}

?>
