<?php
class IDSConverter
{
	 /**
     * Converts listed UTF-7 tags to UTF-8
     *
     * @param string  $data
     * @return string
     */
    function ConvertFromUTF7($data)
    {
		$schemes=array
		(
            '+AFwAIg'  => '"',
            '+AFw\''   => '\'',
            '+ADw-'     => '<',
            '+AD4-'     => '>',
            '+AFs'     => '[',
            '+AF0'     => ']',
            '+AHs'     => '{',
            '+AH0'     => '}',
            '+AFwAXA'  => '\\',
            '+ADs'     => ';',
            '+ACM'     => '#',
            '+ACY'     => '&',
            '+ACU'     => '%',
            '+ACQ'     => '$',
            '+AD0'     => '=',
            '+AGA'     => '`',
            '+ALQ'     => '"',
            '+IBg'     => '"',
            '+IBk'     => '"',
            '+AHw'     => '|',
            '+ACo'     => '*',
            '+AF4'     => '^'
        );
        $data=str_replace(array_keys($schemes), array_values($schemes), $data);
        $data=str_replace(array_keys(array_map("strtolower", $schemes)), array_values($schemes), $data);
        return $data;
    }

    /**
     * Checks for common charcode pattern and decodes them
     *
     * @return  string
     */
     function ConvertFromJSCharcode($value)
     {
        # check if value matches typical charCode pattern
        if (preg_match_all('/(?:[\w+-=\/\* ]*(?:\s?,\s?[\w+-=\/\* ]+)+)/s', $value, $matches))
        {
            $converted='';
            $string=implode(',', $matches[0]);
            $string=preg_replace('/\s/', '', $string);
            $string=preg_replace('/\w+=/', '', $string);
            $charcode=explode(',', $string);

            foreach($charcode as $char)
            {
                $char=preg_replace('/[\W]0/s', '', $char);
                if(preg_match_all('/\d*[+-\/\* ]\d+/', $char, $matches))
                {
                    $match=preg_split('/([\W]?\d+)/', (implode('', $matches[0])), null, PREG_SPLIT_DELIM_CAPTURE);
                    $converted.=chr(array_sum($match));

                }
                else if(!empty($char)) $converted.=chr($char);
            }
            $value.=' [' . $converted . '] ';
        }

        # check for octal charcode pattern
        if (preg_match_all('/(?:(?:[\\\]+\d+\s*){2,})/iDs', $value, $matches))
        {
            $converted='';
            $charcode=explode('\\', preg_replace('/\s/', '', implode(',', $matches[0])));
            foreach($charcode as $char)
            {
                if(!empty($char)) $converted .= chr(octdec($char));
            }
            $value.=' [' . $converted . '] ';
        }

        # check for hexadecimal charcode pattern
        if (preg_match_all('/(?:(?:[\\\]+\w+\s*){2,})/iDs', $value, $matches))
        {
            $converted='';
            $charcode=explode('\\', preg_replace('/[ux]/', '', implode(',', $matches[0])));
            foreach($charcode as $char)
            {
                if(!empty($char)) $converted .= chr(hexdec($char));
            }
            $value.=' [' . $converted . '] ';
        }
        return $value;
     }
}
?>