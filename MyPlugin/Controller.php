<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MyPlugin;




/**
 * A controller lets you for example create a page that can be added to a menu. For more information read our guide
 * http://developer.piwik.org/guides/mvc-in-piwik or have a look at the our API references for controller and view:
 * http://developer.piwik.org/api-reference/Piwik/Plugin/Controller and
 * http://developer.piwik.org/api-reference/Piwik/View
 */
class UserInfo
{
//    public $visitId;
    public $visitorID;
    public $configID;
    public $ipLocation;
    public function __construct($visitorID, $configID, $ipLocation)
    {
//        $this->visitId = $visitId;
        $this->visitorID = $visitorID;
        $this->configID = $configID;
        $this->ipLocation = $ipLocation;
    }
}

class UserDomains
{
    public $domains = array();
    public $userID;


    //регулярное выржение вычлиняющее домен из url
    public function patternURL($url)
    {
        $pattern = '/(?:http[s]?:\/\/)?([^\/]+)/';
        if (preg_match($pattern, $url, $matches)) {
            $fullUrl = $matches[1];
            return $fullUrl;
        } else {
            return false;
        }
    }
    //получаем домены, на которых был пользователь
    public function getUserAndSites($db, $idRow)
    {

        $urls = $db->fetchAll('SELECT DISTINCT idvisit, idaction, name FROM matomo_log_link_visit_action 
                                INNER JOIN matomo_log_action ON matomo_log_link_visit_action.idaction_url = matomo_log_action.idaction 
                                WHERE matomo_log_link_visit_action.idvisit = ? 
                                ORDER BY idaction', [$idRow['idvisit']]);
        foreach ($urls as $url) {
            $domain = $this->patternURL($url['name']);
            if ($domain && !in_array($domain, $this->domains)) {
                $this->domains[] = $domain;
            }
        }
        $this->userID = bin2hex($idRow['idvisitor']);
    }
}

class Controller extends \Piwik\Plugin\Controller
{
    public function patternURL($url)
    {
        $pattern = '/(?:http[s]?:\/\/)?([^\/]+)/';
        if (preg_match($pattern, $url, $matches)) {
            $fullUrl = $matches[1];
            return $fullUrl;
        } else {
            return false;
        }
    }

    public function index()
    {

//        $moya = array();
//        //$site = array();
//        $sitesUsers = array();
       // array_push($site, 3);
        //array_push($site, 2);

//        $db = \Piwik\Db::get();

//        $rows = $db->fetchAll('select * from matomo_log_visit');
//        foreach ($rows as $row) {
//            $p = bin2hex($row['idvisitor']) . " пробел " . @inet_ntop($row['location_ip']);
//            array_push($moya, $p);
//        }
//        $sites = $db->fetchAll('SELECT idsite, main_url
//                                    FROM matomo_site
//                                    WHERE idsite = ?', [1]);
//        array_push($sitesUsers, $sites[0]['main_url']);
//        echo $sites[0]['main_url'];
//        foreach ($site as $item) {
//              $sites = $db->fetchAll('SELECT idsite, main_url
//                                    FROM matomo_site
//                                    WHERE idsite = ?', [$item]);
//            $users = new UsersOnSites();
//            $users->id = $sites['idsite'];
//            $users->name = $sites['NAME'];
//            $users->url = $sites['main_url'];
//            foreach ($sites as $item) {
//                array_push($sitesUsers, $item['main_url']);
//            }
//        }
//        $newZN = $db->fetchAll('SELECT location_ip
//                                FROM matomo_log_visit
//                                WHERE location_ip IN (
//                                    SELECT location_ip
//                                    FROM matomo_log_visit
//                                    WHERE idsite = 1
//                                ) AND location_ip IN (
//                                    SELECT location_ip
//                                    FROM matomo_log_visit
//                                    WHERE idsite = 2
//                                ) GROUP BY location_ip;');
//        $newZN = $db->fetchAll('SELECT location_ip
//                                FROM matomo_log_visit
//                                WHERE location_ip IN (
//                                    SELECT location_ip
//                                    FROM matomo_log_visit
//                                    WHERE idsite = 3
//                                ) GROUP BY location_ip;');
//        foreach ($newZN as $item) {
//            array_push($moya, @inet_ntop($item['location_ip']));
//        }
        // Render the Twig template templates/index.twig and assign the view variable answerToLife to the view.

        $sitesUsers = 3;
        $idsite = 3;
        $users = array();
        $allSites = array();
        $db = \Piwik\Db::get();
        //получем все подключенные сайты по idsite и записываем их в $allSites
        $allSitesReq = $db->fetchAll('SELECT matomo_site.idsite, matomo_site.main_url, matomo_site_url.url FROM matomo_site 
                                        INNER JOIN matomo_site_url ON matomo_site.idsite = matomo_site_url.idsite 
                                        WHERE matomo_site.idsite = ?', [$idsite]);
        foreach ($allSitesReq as $site) {
            $mainUrl = $this->patternURL($site['main_url']);
            $url = $this->patternURL($site['url']);
            if ($mainUrl && !in_array($site['main_url'], $allSites)) {
                $allSites[] = $mainUrl;
            }
            if ($url && !in_array($site['url'], $allSites)) {
                $allSites[] = $url;
            }
        }
        //получаем всех пользователей, которые были одновременно на всех сайтах из $Allsites
        //исключаем доублирование config и visitor
        $idvisitors = $db->fetchAll('select idvisitor, idvisit, config_id, location_ip from matomo_log_visit');
        foreach ($idvisitors as $idRow) {
            $userInfo = new UserDomains();
            $userInfo->getUserAndSites($db, $idRow);
            if ($userInfo->domains == $allSites) {
                $currentUser = new UserInfo(bin2hex($idRow['idvisitor']), bin2hex($idRow['config_id']), @inet_ntop($idRow['location_ip']));
                $duplicate = false;
                if (count($users) < 1) {
                    //записываем в массив пользователя как объект
                    $users[] = $currentUser;
                } else {
                    //исключаем пользователя по id
                    //сделать так чтобы можно было развернуть пользователя и посмотреть какие записи ему принадлежат ещё (как раз те, что исключаются)
                    //при переборе сайтов будет 1 пользователь т.к. это будет осуществлять быстро, поэтому отловить его можно будет
                    foreach ($users as $existingUserInfo) {
                        if ($existingUserInfo->visitorID === $currentUser->visitorID || $existingUserInfo->configID === $currentUser->configID) {
                            $duplicate = true;
                            break;
                        }
                    }
                    if (!$duplicate) {
                        $users[] = $currentUser;
                    }
                }
            }
        }

        return $this->renderTemplate('index', array(
            'answerToLife' => $sitesUsers,
            'domains' => $allSites,
            'users' => $users
        ));
    }
}
