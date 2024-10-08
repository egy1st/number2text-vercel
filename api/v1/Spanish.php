<?php
class Spanish
{
    public function translateNumber($strNumber, $aCur)
    {
        $strNum = "";

        NumberingSystem::getLanguage($aUnit, $aTen, $aHundred, $aId, $aNum, "Spanish");
        for ($x = 7; $x <= 12; $x++) {
            $aId[$x] = $aCur[$x - 7];
        }

        $strForma = Number2Text::prepareNumber($strNumber, $aNum);
        $cycle = 0;
        for ($cycle = 1; $cycle <= 5; $cycle++) {
            $id1 = $aId[($cycle * 2) - 1];
            $id2 = $aId[$cycle * 2];

            if ($cycle === 1) {
                $x = 1;
                $nSum = NumberingSystem::getSum($aNum, 1);
            } else if ($cycle === 2) {
                $x = 4;
                $nSum = NumberingSystem::getSum($aNum, 2);
            } else if ($cycle === 3) {
                $x = 7;
                $nSum = NumberingSystem::getSum($aNum, 3);
            } else if ($cycle === 4) {
                $x = 10;
            } else if ($cycle === 5) {
                $x = 14;
            }

            $nUnit = ($aNum[$x + 1] * 10) + $aNum[$x + 2];
            $nAll = $aNum[$x] + $nUnit;

            if ($nUnit > 0 && $nUnit < 31) {
                $strUnit = $aUnit[$nUnit];
            } else if ($aNum[$x + 2] == 0) {
                $strUnit = $aTen[$aNum[$x + 1]];
            } else {
                $strUnit = $aTen[$aNum[$x + 1]] . " " . $aId[0] . " " . $aUnit[$aNum[$x + 2]];
            }

            if ($nAll != 0) {
                if ($aNum[$x] == 1 && $aNum[$x + 1] + $aNum[$x + 2] == 0) {
                    $strNum .= "cien" . " " . $strUnit . " " . $id2 . " ";
                } else {
                    $strNum .= $aHundred[$aNum[$x]] . " " . $strUnit . " " . $id2 . " ";
                }
            }

            if (NumberingSystem::NoCurrency($cycle, $strForma)) {
                $strNum = NumberingSystem::removeAnd($strNum, $aId[0]);
                $strNum .= " " . $id2;
            }
        }

        $strNum = NumberingSystem::removeSpaces($strNum);

        return $strNum;
    }
}
?>
