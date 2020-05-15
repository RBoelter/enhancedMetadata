<?php


class EMDataService
{

	function getJsonScheme($name)
	{
		return json_decode(file_get_contents($name), true);
	}


	function getFieldValues($node, $data)
	{
		$res = [];
		switch ($node['type']) {
			case 'select':
			case 'radio':
				$res = [$data[$node['name']]];
				break;
			default:
				if (isset($node['fields']))
					foreach ($node['fields'] as $field)
						$res = array_merge($res, explode(PHP_EOL, $data[$field['name']]));
		}
		return $res;
	}

	function getNameParam($data)
	{
		$res = [];
		foreach ($data as $itm) {
			switch ($itm['type']) {
				case 'select':
				case 'radio':
					$res[] = $itm['name'];
					break;
				default:
					if ($itm['fields'])
						foreach ($itm['fields'] as $field)
							$res[] = $field['name'];
			}
		}
		return array_unique($res);
	}

}