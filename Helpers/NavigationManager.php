<?php
// require_once APP_DIR.'/helpers/Utilities.php';

// TODO this helper can be as part of aya framework but then Aya yaml loader also
class NavigationManager {

    private static $_aNavigation;

    public static function getNavigation() {
        $sNavigationCacheFile = TMP_DIR . '/navigation.obj';
        if (file_exists($sNavigationCacheFile)) {
            self::$_aNavigation = unserialize(file_get_contents($sNavigationCacheFile));
        } else {
            require_once dirname(ROOT_DIR) . '/XhtmlTable/Aya/Yaml/AyaYamlLoader.php';

            $sNavigationConfFile = ROOT_DIR . '/app/conf/navigation.yml';

            self::$_aNavigation = AyaYamlLoader::parse($sNavigationConfFile);

            // foreach ($aSections as $sk => &$section) {
            //     $section['url'] = 'postman/index/' . $sk;
            // }

            // cache won't work at all
            // file_put_contents($sNavigationCacheFile, serialize(self::$_aNavigation));
        }

        return self::$_aNavigation['urls'];
    }
}