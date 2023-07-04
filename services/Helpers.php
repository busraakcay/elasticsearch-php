<?php

function modifyKeyword($keyword)
{
    $needModifyKeywords = array(
        array("keyword" => "tostorna", "modify" => "tos torna")
    );
    $keywordsArray = explode(' ', $keyword);
    foreach ($needModifyKeywords as $entry) {
        foreach ($keywordsArray as &$kw) {
            if ($entry['keyword'] === $kw) {
                $kw = $entry['modify'];
            }
        }
    }
    return implode(' ', $keywordsArray);
}

function filterKeyword($keyword)
{
    $result = modifyKeyword($keyword);
    $keywords = $result;
    $returnvalue    = '';
    if ($keywords != "") {
        $excludekeywords = [
            'sıfır',
            'işleme',
            'makine',
            'makıne',
            'makina',
            'makına',
            'makinesi',
            'makınesi',
            'makınesı',
            'makinesı',
            'makinası',
            'makınası',
            'makınasi',
            'makinasi',
            'makinas',
            'makineleri',
            'makıneleri',
            'makınelerı',
            'makıneleri',
            'makinaları',
            'makınaları',
            'makınalari',
            'makinalari',
            'fiyatı',
            'fiyati',
            'fiyatları',
            'fiyatlari',
            'sahibinden',
            'machine',
            'machinery',
            'kiralık',
            'kiralik',
            'satılık',
            'satilik',
            'satlık',
            'satlik',
            'ikinci',
            'el',
            'ikinciel',
            'ikinci el',
            '2el',
            '2 el',
            '2ci el',
            '2ci',
            '2.ci el',
            '2.ci',
            '2.el',
            'second hand',
            'used',
            'dijital',
            'hesaplı',
            '100',
            '120',
            '125',
            '150',
            '160',
            '200',
            '250',
            '300',
            '400',
            '500',
            'lik',
            'lık',
            'lük',
            'ful',
            'full',
            'fabrikası',
            'hem',
            'uzun',
            'alım',
            'satım',
            'bar',
            'imalatı',
            'imalat',
            'üretim',
        ];

        $keywords         = mb_strtolower($keywords, 'UTF-8');
        $keywords         = str_replace('i̇', 'i', $keywords);
        $exploded         = explode(' ', $keywords);

        $exploded = array_unique($exploded);

        foreach ($exploded as $exp) {
            $trimmed = trim($exp);
            if (($trimmed != "") && (strlen($trimmed) > 2)) {
                if ((!in_array($trimmed, $excludekeywords))) {
                    $returnvalue .= $trimmed . ' ';
                } else {
                    $returnvalue .= ' ';
                }
            }
        }
        $returnvalue = trim($returnvalue);
    }
    return $returnvalue;
}

function console($obj)
{
    $js = json_encode($obj);
    print_r('<script>console.log(' . $js . ')</script>');
}

function printArray($obj)
{
    echo "<pre>";
    print_r($obj);
    echo "</pre>";
}

function getIndexName()
{
    return "makinecim_" . $_SESSION['lang'];
}
