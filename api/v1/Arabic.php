<?php
/**
 * Arabic number to words converter
 * Handles numbers up to 999,999,999,999.99
 * Supports Arabic grammar rules for numbers and currencies
 */
class Arabic
{
    /**
     * Converts a number to Arabic words with currency
     *
     * @param string $strNumber The number to convert (max: 999,999,999,999.99)
     * @param array $aCur Currency names array containing singular, dual, and plural forms
     * @return string The number in Arabic words
     */
    public function translateNumber($strNumber, $aCur)
    {
        $strNum = "";

        // Get Arabic number system arrays
        NumberingSystem::getLanguage($aUnit, $aTen, $aHundred, $aId, $aNum, "Arabic");
        
        // Map currency names
        for ($x = 7; $x <= 12; $x++) {
            $aId[$x] = $aCur[$x - 7];
        }

        // Format number and prepare for processing
        $strForma = Number2Text::prepareNumber($strNumber, $aNum);
        
        // Process each numeral group (billions, millions, thousands, ones, decimals)
        for ($cycle = 1; $cycle <= 5; $cycle++) {
            $G = 0; // Grammar flag for number form

            // Set scale identifiers based on cycle
            if ($cycle === 1) {
                $x = 1;
                $id1 = " مليار ";
                $id2 = " مليارين ";
                $id3 = " مليارات ";
            } else if ($cycle === 2) {
                $x = 4;
                $id1 = " مليون ";
                $id2 = " مليونين ";
                $id3 = " ملايين ";
            } else if ($cycle === 3) {
                $x = 7;
                $id1 = " ألف ";
                $id2 = " ألفين ";
                $id3 = " آلاف ";
            } else if ($cycle === 4) {
                $x = 10;
                $id1 = " جنيه ";
                $id2 = " جنيهين ";
                $id3 = " جنيهات ";

                // Handle special cases for large round numbers
                $strNum = $this->handleSpecialCases($strForma, $strNum);

                if ($aNum[$x] == 0 && $aNum[$x + 1] == 0 && $aNum[$x + 2] == 0) {
                    $strNum .= $id1;
                }
            } else if ($cycle == 5) {
                $x = 14;
                $id1 = " قرش ";
                $id2 = " قرشين ";
                $id3 = " قروش ";
            }

            // Handle special case for مائتين/مائتي
            if (isset($aNum[$x + 1])) {
                $aHundred[2] = ($aNum[$x + 1] == 0 && $aNum[$x + 2] == 0) ? "مائتي " : "مائتين ";
            }

            // Process hundreds, tens and ones
            if ($aNum[$x] == 0 && $aNum[$x + 1] == 0 && $aNum[$x + 2] == 0) {
                $G = 1;
            } else if ($aNum[$x] == 0 && $aNum[$x + 1] == 0) {
                if ($aNum[$x + 2] == 1) {
                    $strNum = $strNum . " و " . $id1;
                    $G = 1;
                } else if ($aNum[$x + 2] == 2) {
                    $strNum = $strNum . " و " . $id2;
                    $G = 1;
                }
            }

            // Add hundreds
            if ($aNum[$x] > 0) {
                $strNum = $strNum . " و " . $aHundred[$aNum[$x]];
            }

            // Handle compound numbers (11-19)
            if ($aNum[$x + 1] == 1) {
                if ($aNum[$x + 2] == 0) {
                    $strNum = $strNum . " و " . $aTen[1] . $id3;
                    $G = 4;
                } else if ($aNum[$x + 2] == 1) {
                    $strNum = $strNum . " و " . $aUnit[11] . $aTen[1] . $id1;
                    $G = 4;
                } else if ($aNum[$x + 2] == 2) {
                    $strNum = $strNum . " و " . $aUnit[12] . $aTen[1] . $id1;
                    $G = 4;
                }
            }

            // Handle numbers 3-9 with proper plural forms
            if ($aNum[$x] == 0 && $aNum[$x + 1] == 0 && $aNum[$x + 2] > 2) {
                $strNum = $strNum . " و " . $aUnit[$aNum[$x + 2]] . $id3;
                $G = 1;
            }

            // Add units if not already handled
            if ($aNum[$x + 2] > 0 && $G != 4 && $G != 1) {
                $strNum = $strNum . " و " . $aUnit[$aNum[$x + 2]];
                $G = 2;
            }

            // Add tens
            if ($aNum[$x + 1] > 1) {
                $strNum = $strNum . " و " . $aTen[$aNum[$x + 1]];
                $G = 2;
            } else if ($aNum[$x + 1] == 1 && $G != 4) {
                $strNum = $strNum . $aTen[$aNum[$x + 1]];
                $G = 2;
            }

            // Add scale identifier if needed
            if ($G != 1 && $G != 4) {
                $strNum = $strNum . $id1;
            }
        }

        // Clean up the result
        $strNum = $this->cleanupResult($strNum);

        // Replace currency placeholders with actual currency names
        return $this->replaceCurrencyNames($strNum, $aId);
    }

    /**
     * Handle special cases for large round numbers
     */
    private function handleSpecialCases($strForma, $strNum)
    {
        $specialCases = [
            "001000000000" => " مليار ",
            "002000000000" => " ملياري ",
            "000001000000" => " مليون ",
            "000002000000" => " مليوني ",
            "000000001000" => " ألف ",
            "000000002000" => " ألفي "
        ];

        foreach ($specialCases as $number => $word) {
            if (substr($strForma, 0, 12) === $number) {
                return $word;
            }
        }

        return $strNum;
    }

    /**
     * Clean up the result string
     */
    private function cleanupResult($strNum)
    {
        // Remove leading و if present
        if (substr($strNum, 0, strlen(" و ")) == " و ") {
            $strNum = substr($strNum, strlen(" و "));
        }
        
        return $strNum;
    }

    /**
     * Replace currency placeholders with actual currency names
     */
    private function replaceCurrencyNames($strNum, $aId)
    {
        $replacements = [
            "جنيه" => $aId[7],
            "جنيهات" => $aId[8],
            "قرش" => $aId[9],
            "قروش" => $aId[10],
            "جنيهين" => $aId[11],
            "قرشين" => $aId[12]
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $strNum);
    }
}
?>
