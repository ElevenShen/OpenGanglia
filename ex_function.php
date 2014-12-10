<?php
function sbc2dbc($str) {
        $sbc = Array('０','１','２','３','４','５','６','７','８','９','。');
        $dbc = Array('0','1','2','3','4','5','6','7','8','9','.');

        return str_replace($sbc, $dbc, $str);

}
function get_zone_list() {
	global $zone_list;

        $zone_file = './zone.all.list.php';

        $file = file($zone_file);
        foreach ($file as $v) {
                if (preg_match("/^dns view:/", $v)) {
                        $view = preg_split("/:/", $v);
                        $view = trim($view[1]);
                }
                if (preg_match("/\sIN\s/i", $v)) {
                        $a = preg_split("/\s+/", $v);
                        $domain = preg_replace('/\.$/','',$a[0]);
                        $type = $a[3];
                        $value = $a[4];
                        if ($type == 'A') $zone_list[$value][] = $domain;
                }

        }
	
}


?>
