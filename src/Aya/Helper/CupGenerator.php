<?php

namespace Aya\Helper;

use Aya\Core\Db;

class CupGenerator {

    public function generate($mId) {



        
        // $aPost = $this->preInsert();

        // print_r($aPost['tournament']);
        $aPost['tournament']['creation_date'] = '2018-05-01';
        $aPost['tournament']['groups'] = 8;
        $aPost['tournament']['players'] = 32;

        $oDb = Db::getInstance(unserialize(DB_SOURCE));

        // adding tournement conditionally;
        $sql = 'SELECT * FROM cup WHERE id_cup=13';
        // if ($oDb->execute($sql) !== false) {
        $cup = $oDb->getArray('SELECT * FROM cup WHERE id_cup="'.$mId.'" ');
        // print_r($cup);
        if ($cup) {
            echo 'Tournament exists...' . "<br>";
        } else {
            echo 'Tournament does not exists...' . "<br>";
            $sql = 'INSERT INTO cup(`id_cup`, `name`, `slug`) VALUES(NULL, "Heroine Cup 2018", "heroine-cup-2018")';
            if ($oDb->execute($sql) !== false) {
                echo 'Cup added...' . "<br>";
            }
        }


            // die('generate in CupGenerator...');

        // generate players
        if (isset($aPost['tournament']['creation_date']) && isset($aPost['tournament']['groups']) && isset($aPost['tournament']['players'])) {
            // 'add tournament players' initial sql
            $sql = 'INSERT INTO cup_player(`id_cup`, `name`, `slug`, `group`) VALUES';
            // letters for groups
            $aLetters = range('A', 'Z');

            $aValues = array();

            $iGroups = (int)$aPost['tournament']['groups'];
            $iPlayers = (int)$aPost['tournament']['players'];

            $iSingleGroupPlayers = $iPlayers / $iGroups; // players in one group

            $group = 0;
            // checking does single group integer
            if (($iSingleGroupPlayers * $iGroups) == $iPlayers) {
                for ($i = 0; $i < $iPlayers; $i++) {
                    if ($i > 0 && ($i % $iSingleGroupPlayers) == 0) {
                        $group++;
                    }
                    $aValues[] = '('.$mId.', "Player'.($i+1).'", "player'.($i+1).'", "'.$aLetters[$group].'")';
                }

            }

            // 'add tournament players' final sql
            $sql .= implode(',', $aValues);


            // echo $sql;
            // players query
            //INSERT INTO cup_player(`id_cup`, `name`, `slug`, `group`) VALUES(13, "Player3", "player3", "A"),(13, "Player4", "player4", "A"),(13, "Player5", "player5", "B"),(13, "Player6", "player6", "B"),(13, "Player7", "player7", "B"),(13, "Player8", "player8", "B"),(13, "Player9", "player9", "C"),(13, "Player10", "player10", "C"),(13, "Player11", "player11", "C"),(13, "Player12", "player12", "C"),(13, "Player13", "player13", "D"),(13, "Player14", "player14", "D"),(13, "Player15", "player15", "D"),(13, "Player16", "player16", "D"),(13, "Player17", "player17", "E"),(13, "Player18", "player18", "E"),(13, "Player19", "player19", "E"),(13, "Player20", "player20", "E"),(13, "Player21", "player21", "F"),(13, "Player22", "player22", "F"),(13, "Player23", "player23", "F"),(13, "Player24", "player24", "F"),(13, "Player25", "player25", "G"),(13, "Player26", "player26", "G"),(13, "Player27", "player27", "G"),(13, "Player28", "player28", "G"),(13, "Player29", "player29", "H"),(13, "Player30", "player30", "H"),(13, "Player31", "player31", "H"),(13, "Player32", "player32", "H");

            $players = $oDb->getArray('SELECT * FROM cup_player WHERE id_cup="'.$mId.'" ');
            // print_r($cup);
            if ($players) {
                echo 'Players exists...' .count($players). "<br>";
            } else {
                echo 'Players does not exists...' . "<br>";
                // $sql = 'INSERT INTO cup(`id_cup`, `name`, `slug`) VALUES(NULL, "Heroine Cup 2018", "heroine-cup-2018")';
                // if ($oDb->execute($sql) !== false) {
                //     echo 'Players added' . "<br>";
                // }
            }


            $sql = '';
            // update players
            foreach ($players as $player) {
                $sql .= 'UPDATE cup_player SET `name`="", `slug`="" WHERE id_cup_player='.$player['id_cup_player'].';';
            }

            // echo $sql;
            /*
            UPDATE cup_player SET `name`="Chloe", `slug`="chloe" WHERE id_cup_player=385;
UPDATE cup_player SET `name`="Reimi", `slug`="reimi" WHERE id_cup_player=386;
UPDATE cup_player SET `name`="B2", `slug`="b2" WHERE id_cup_player=387;
UPDATE cup_player SET `name`="Ashe", `slug`="ashe" WHERE id_cup_player=388;
UPDATE cup_player SET `name`="Aeris", `slug`="aeris" WHERE id_cup_player=389;
UPDATE cup_player SET `name`="Aya", `slug`="aya" WHERE id_cup_player=390;
UPDATE cup_player SET `name`="Marle", `slug`="marle" WHERE id_cup_player=391;
UPDATE cup_player SET `name`="Lulu", `slug`="lulu" WHERE id_cup_player=392;
UPDATE cup_player SET `name`="Emma", `slug`="emma" WHERE id_cup_player=393;
UPDATE cup_player SET `name`="Deuce", `slug`="ceuce" WHERE id_cup_player=394;
UPDATE cup_player SET `name`="Fran", `slug`="fran" WHERE id_cup_player=395;
UPDATE cup_player SET `name`="Rinoa", `slug`="rinoa" WHERE id_cup_player=396;
UPDATE cup_player SET `name`="Lunafreya", `slug`="lunafreya" WHERE id_cup_player=397;
UPDATE cup_player SET `name`="Callo", `slug`="callo" WHERE id_cup_player=398;
UPDATE cup_player SET `name`="Yuffie", `slug`="yuffie" WHERE id_cup_player=399;
UPDATE cup_player SET `name`="Jessica", `slug`="jessica" WHERE id_cup_player=400;
UPDATE cup_player SET `name`="Kairi", `slug`="kairi" WHERE id_cup_player=401;
UPDATE cup_player SET `name`="Yuna", `slug`="yuna" WHERE id_cup_player=402;
UPDATE cup_player SET `name`="Rydia", `slug`="rydia" WHERE id_cup_player=403;
UPDATE cup_player SET `name`="Lara", `slug`="lara" WHERE id_cup_player=404;
UPDATE cup_player SET `name`="Lightning", `slug`="lightning" WHERE id_cup_player=405;
UPDATE cup_player SET `name`="Cindy", `slug`="cindy" WHERE id_cup_player=406;
UPDATE cup_player SET `name`="Kid", `slug`="kid" WHERE id_cup_player=407;
UPDATE cup_player SET `name`="Rikku", `slug`="rikku" WHERE id_cup_player=408;
UPDATE cup_player SET `name`="Cater", `slug`="cater" WHERE id_cup_player=409;
UPDATE cup_player SET `name`="Tifa", `slug`="tifa" WHERE id_cup_player=410;
UPDATE cup_player SET `name`="Aki", `slug`="aki" WHERE id_cup_player=411;
UPDATE cup_player SET `name`="Vanille", `slug`="vanille" WHERE id_cup_player=412;
UPDATE cup_player SET `name`="Garnet", `slug`="garnet" WHERE id_cup_player=413;
UPDATE cup_player SET `name`="Terra", `slug`="terra" WHERE id_cup_player=414;
UPDATE cup_player SET `name`="Lenneth", `slug`="lenneth" WHERE id_cup_player=415;
UPDATE cup_player SET `name`="Serah", `slug`="serah" WHERE id_cup_player=416;

*/


            // generating matches sql

            // group matches
            $iOneGroupMatches = ($iSingleGroupPlayers * ($iSingleGroupPlayers - 1)) / 2;
            $iAllGroupMatches = $iOneGroupMatches * $iGroups;

            // echo '$iOneGroupMatches: '.$iOneGroupMatches;
            // echo '$iAllGroupMatches: '.$iAllGroupMatches;

            
            // 'add group matches' initial sql
            $sql = 'INSERT INTO cup_battle(`id_cup_battle`, `id_cup`, `player1`, `player2`) VALUES';

            // for 8 groups with 32 players
            $aMatchesDefinitions = array(
                array('0-1', '2-3'),
                array('0-2', '3-1'),
                array('3-0', '1-2')
            );
            
            $iRounds = $iOneGroupMatches / ($iSingleGroupPlayers / 2);
            $oDate = date_create($aPost['tournament']['creation_date']);

            // echo $iRounds;

            // echo '$iOneGroupMatches:  '.$iOneGroupMatches;
            // echo '$iSingleGroupPlayers'.$iSingleGroupPlayers;

            $aMatches = array();
            // temporary fix
            $aUpdates = array();


            $aPlayers = $players;

            // group matches
            for ($i = 0; $i < $iRounds; $i++) {
                for ($j = 0; $j < $iGroups; $j++) {
                    for ($k = 0; $k < ($iSingleGroupPlayers / 2); $k++) {
                        // date
                        $sDate = date_format($oDate, 'Y-m-d');

                        // players
                        $aMatchParts = explode('-', $aMatchesDefinitions[$i][$k]);
                        $iPlayer1 = ($j * $iSingleGroupPlayers) + $aMatchParts[0];
                        $iPlayer2 = ($j * $iSingleGroupPlayers) + $aMatchParts[1];
                        
                        // match
                        $aMatches[] = '("'.$sDate.'", "'.$mId.'", "'.$aPlayers[$iPlayer1]['id_cup_player'].'", "'.$aPlayers[$iPlayer2]['id_cup_player'].'")';

                        $aUpdates[] = 'UPDATE cup_battle SET player1="'.$aPlayers[$iPlayer1]['id_cup_player'].'", player2="'.$aPlayers[$iPlayer2]['id_cup_player'].'" WHERE id_cup_battle="'.$sDate.'"'; 

                        date_modify($oDate, '+1 day');
                    }
                }
            }
            // 'add tournament matches' final sql
            $sql .= implode(',', $aMatches);
            // 
            // echo implode('; ', $aUpdates);

            // print_r($aMatches);


            echo $sql;
            // group phhase
            // INSERT INTO cup_battle(`id_cup_battle`, `id_cup`, `player1`, `player2`) VALUES("2018-05-01", "13", "385", "386"),("2018-05-02", "13", "387", "388"),("2018-05-03", "13", "389", "390"),("2018-05-04", "13", "391", "392"),("2018-05-05", "13", "393", "394"),("2018-05-06", "13", "395", "396"),("2018-05-07", "13", "397", "398"),("2018-05-08", "13", "399", "400"),("2018-05-09", "13", "401", "402"),("2018-05-10", "13", "403", "404"),("2018-05-11", "13", "405", "406"),("2018-05-12", "13", "407", "408"),("2018-05-13", "13", "409", "410"),("2018-05-14", "13", "411", "412"),("2018-05-15", "13", "413", "414"),("2018-05-16", "13", "415", "416"),("2018-05-17", "13", "385", "387"),("2018-05-18", "13", "388", "386"),("2018-05-19", "13", "389", "391"),("2018-05-20", "13", "392", "390"),("2018-05-21", "13", "393", "395"),("2018-05-22", "13", "396", "394"),("2018-05-23", "13", "397", "399"),("2018-05-24", "13", "400", "398"),("2018-05-25", "13", "401", "403"),("2018-05-26", "13", "404", "402"),("2018-05-27", "13", "405", "407"),("2018-05-28", "13", "408", "406"),("2018-05-29", "13", "409", "411"),("2018-05-30", "13", "412", "410"),("2018-05-31", "13", "413", "415"),("2018-06-01", "13", "416", "414"),("2018-06-02", "13", "388", "385"),("2018-06-03", "13", "386", "387"),("2018-06-04", "13", "392", "389"),("2018-06-05", "13", "390", "391"),("2018-06-06", "13", "396", "393"),("2018-06-07", "13", "394", "395"),("2018-06-08", "13", "400", "397"),("2018-06-09", "13", "398", "399"),("2018-06-10", "13", "404", "401"),("2018-06-11", "13", "402", "403"),("2018-06-12", "13", "408", "405"),("2018-06-13", "13", "406", "407"),("2018-06-14", "13", "412", "409"),("2018-06-15", "13", "410", "411"),("2018-06-16", "13", "416", "413"),("2018-06-17", "13", "414", "415");
            // die('generate in CupGenerator...');
            $sql .= ';';

            // if ($oDb->execute($sql) == false) {
            //     $this->raiseError('Błąd podczas dodawania meczów grupowych.');
            // }

            $aMatches = array();

            // 'add tournament matches' initial sql
            $sql .= 'INSERT INTO cup_battle(`id_cup_battle`, `id_cup`) VALUES';
            
            for ($i = 0; $i < 16; $i++) {
                // date
                $sDate = date_format($oDate, 'Y-m-d');

                $aMatches[] = '("'.$sDate.'", "'.$mId.'")';

                date_modify($oDate, '+1 day');
            }
            // 'add tournament matches' final sql
            $sql .= implode(',', $aMatches);

            // finals
            echo $sql;
            // if ($oDb->execute($sql) == false) {
            //     $this->raiseError('Błąd podczas dodawania meczów pucharowych.');
            // }

            // $this->raiseInfo('Zawodnicy i mecze turnieju zostały stworzone.');
            // Message::raiseInfo('all fine');

            $matches = $oDb->getArray('SELECT * FROM cup_battle WHERE id_cup="'.$mId.'" ');
            // print_r($cup);
            echo 'FINAL QUERY FOR BATTLES...<br>'.$sql;
            // INSERT INTO cup_battle(`id_cup_battle`, `id_cup`, `player1`, `player2`) VALUES("2018-05-02", "13", "387", "388"),("2018-05-03", "13", "389", "390"),("2018-05-04", "13", "391", "392"),("2018-05-05", "13", "393", "394"),("2018-05-06", "13", "395", "396"),("2018-05-07", "13", "397", "398"),("2018-05-08", "13", "399", "400"),("2018-05-09", "13", "401", "402"),("2018-05-10", "13", "403", "404"),("2018-05-11", "13", "405", "406"),("2018-05-12", "13", "407", "408"),("2018-05-13", "13", "409", "410"),("2018-05-14", "13", "411", "412"),("2018-05-15", "13", "413", "414"),("2018-05-16", "13", "415", "416"),("2018-05-17", "13", "385", "387"),("2018-05-18", "13", "388", "386"),("2018-05-19", "13", "389", "391"),("2018-05-20", "13", "392", "390"),("2018-05-21", "13", "393", "395"),("2018-05-22", "13", "396", "394"),("2018-05-23", "13", "397", "399"),("2018-05-24", "13", "400", "398"),("2018-05-25", "13", "401", "403"),("2018-05-26", "13", "404", "402"),("2018-05-27", "13", "405", "407"),("2018-05-28", "13", "408", "406"),("2018-05-29", "13", "409", "411"),("2018-05-30", "13", "412", "410"),("2018-05-31", "13", "413", "415"),("2018-06-01", "13", "416", "414"),("2018-06-02", "13", "388", "385"),("2018-06-03", "13", "386", "387"),("2018-06-04", "13", "392", "389"),("2018-06-05", "13", "390", "391"),("2018-06-06", "13", "396", "393"),("2018-06-07", "13", "394", "395"),("2018-06-08", "13", "400", "397"),("2018-06-09", "13", "398", "399"),("2018-06-10", "13", "404", "401"),("2018-06-11", "13", "402", "403"),("2018-06-12", "13", "408", "405"),("2018-06-13", "13", "406", "407"),("2018-06-14", "13", "412", "409"),("2018-06-15", "13", "410", "411"),("2018-06-16", "13", "416", "413"),("2018-06-17", "13", "414", "415");INSERT INTO cup_battle(`id_cup_battle`, `id_cup`) VALUES("2018-06-18", "13"),("2018-06-19", "13"),("2018-06-20", "13"),("2018-06-21", "13"),("2018-06-22", "13"),("2018-06-23", "13"),("2018-06-24", "13"),("2018-06-25", "13"),("2018-06-26", "13"),("2018-06-27", "13"),("2018-06-28", "13"),("2018-06-29", "13"),("2018-06-30", "13"),("2018-07-01", "13"),("2018-07-02", "13"),("2018-07-03", "13");
            if ($matches) {
                echo 'matches exists...' .count($matches). "<br>";
            } else {
                echo 'matches does not exists...' . "<br>";
                // $sql = 'INSERT INTO cup(`id_cup`, `name`, `slug`) VALUES(NULL, "Heroine Cup 2018", "heroine-cup-2018")';
                // echo 'FINAL QUERY FOR BATTLES...<br>'.$sql;
                // if ($oDb->execute($sql) !== false) {
                //     echo 'matches added' . "<br>";
                // }
            }



        }
    }
}