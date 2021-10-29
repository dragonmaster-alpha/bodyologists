<?php


namespace App;


use PDO;
use Plugins\Members\Classes\Members;

class Stats extends Format
{
    /**
     * @var string
     */
    private $table = 'page_views';
    /**
     * @var array
     */
    private $periods;
    /**
     * @var array
     */
    private $default_period;

    function __construct()
    {
        $this->periods = [
            'today' => date('Y-m-d'),
            'yesterday' => date('Y-m-d', strtotime('-1 day')),
            'last_week' => date('Y-m-d', strtotime('-1 week')),
            'last_month' => date('Y-m-d', strtotime('-1 month')),
            'last_3_months' => date('Y-m-d', strtotime('-3 month')),
            'last_6_months' => date('Y-m-d', strtotime('-6 month')),
            'last_year' => date('Y-m-d', strtotime('-1 year')),
        ];

        $this->default_period = ['last_week' => $this->periods['last_week']];
    }

    /**
     * @return string
     */
    private function table(): string
    {
        return "{$this->prefix}_{$this->table}";
    }

    /**
     * @param int $object_type
     * @param int $object_id
     * @param int|null $owner
     */
    public function track(int $object_type, int $object_id, int $owner = null): void
    {
        $url = $_REQUEST['url'] ?? null;
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        if ($object_type === ObjectType::PROFILE) {
            $object_owner = $object_id;
            // If object is NOT a profile and not owner ID given, get that value
        } elseif (!$owner) {
            $fk_table = [
                ObjectType::DEAL => "{$this->prefix}_deals",
                ObjectType::EVENT => "{$this->prefix}_events",
                ObjectType::ARTICLE => "{$this->prefix}_blog",
            ];
            $fk_column = [
                ObjectType::DEAL => 'owner',
                ObjectType::EVENT => 'owner',
                ObjectType::ARTICLE => 'poster',
            ];

            $prepared = $this->prepare(
                "SELECT `{$fk_column[$object_type]}` as `owner` FROM `{$fk_table[$object_type]}` WHERE `id` = ?"
            );
            $prepared->execute([$object_id]);
            $result = $prepared->fetchAll(PDO::FETCH_ASSOC);
            $object_owner = (int)($result[0]['owner'] ?? 0);
        }

        $current_user = Members::currentUserId();

        // Avoid tracking self-visits
        if ($current_user !== $object_owner) {
            $this->savePageView($object_type, $object_id, $object_owner, $url, $referer, $this->getClientIp());
        }
    }

    /**
     * @param int $object_type
     * @param int $object_id
     * @param int $object_owner
     * @param string|null $url
     * @param string|null $referer
     * @param string|null $visitor_ip
     * @return int
     */
    public function savePageView(
        int $object_type,
        int $object_id,
        int $object_owner,
        ?string $url = null,
        ?string $referer = null,
        ?string $visitor_ip = null
    ): int {
        $stmt = "INSERT INTO {$this->table()} (`date`, `time`, `object_type`, `object_id`, `object_owner`, `url`, `referer`, `visitor_ip`) " .
            'VALUES (' .
            'DATE(NOW()), TIME(NOW()), :object_type, :object_id, :object_owner, :url, :referer, :visitor_ip' .
            ');';

        $values = [
            ':object_type' => $object_type,
            ':object_id' => $object_id,
            ':object_owner' => $object_owner,
            ':url' => $url,
            ':referer' => $referer,
            ':visitor_ip' => $visitor_ip,

        ];

        $prepared = $this->prepare($stmt);
        $prepared->execute($values);

        return $this->sql_nextid();
    }

    public function isValidPeriodString($string): bool
    {
        return array_key_exists(strtolower($string), $this->periods);
    }

    public function getValidPeriod($string): array
    {
        return $this->isValidPeriodString(strtolower($string)) ?
            [$string => $this->periods[$string]] :
            $this->default_period;
    }

    /**
     * @param int $user_id
     * @param string $since
     * @return array
     */
    public function getPageViewsByPeriod(int $user_id, string $since): array
    {
        $from = current($this->getValidPeriod($since));

        $sql = 'SELECT DISTINCT(object_type) , COUNT(1) as count '.
                "FROM {$this->table()} ".
                'WHERE object_owner = :owner AND `date` >= :date '.
                'GROUP BY object_type '.
                'ORDER BY object_type ASC';

        $values = [
            ':owner' => $user_id,
            ':date' => $from
        ];

        $prepared = $this->prepare($sql);
        $prepared->execute($values);

        $data = [
            ObjectType::PROFILE => 0,
            ObjectType::DEAL => 0,
            ObjectType::EVENT => 0,
            ObjectType::ARTICLE => 0,
        ];
        $results = $prepared->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $row) {
            $data[$row['object_type']] = (int) $row['count'];
        }

        return $data;
    }

    public function getProfileViewSource(int $user_id, string $since): array
    {
        $from = current($this->getValidPeriod($since));

        $config = new Config();
        $domain = $config('domain');
        $search = $config('siteurl').'search';

        $sql =
            'SELECT '.
            '    COUNT(1) as total, '.
            '    SUM(CASE WHEN ISNULL(referer) THEN 1 ELSE 0 END) as `direct`, '.
            '    SUM(CASE WHEN INSTR(referer, :search) > 0 THEN 1 ELSE 0 END) as `search`, '.
            '    SUM(CASE WHEN NOT ISNULL(referer) AND INSTR(referer, :domain) = 0 THEN 1 ELSE 0 END) as `external`'.
            "FROM {$this->table()} ".
            'WHERE object_owner = :owner '.
            '    AND `date` >= :date '.
            '    AND object_type = :type '.
            'GROUP BY object_type';

        $values = [
            ':domain' => $domain,
            ':search' => $search,
            ':owner' => $user_id,
            ':date' => $from,
            ':type' => ObjectType::PROFILE
        ];

        $prepared = $this->prepare($sql);
        $prepared->execute($values);

        $data = [
            'total' => 0,
            'direct' => 0,
            'search' => 0,
            'external' => 0,
        ];
        $results = $prepared->fetchAll(PDO::FETCH_ASSOC)[0];

        if ($results) {
            $data = array_merge($data, $results);
        }

        return $data;
    }

    /**
     * @return string
     */
    protected function getClientIp(): string
    {
        if ($_SERVER['HTTP_CLIENT_IP']) {
            $ip_addr = $_SERVER['HTTP_CLIENT_IP'];

        } elseif($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];

        } elseif($_SERVER['HTTP_X_FORWARDED']) {
            $ip_addr = $_SERVER['HTTP_X_FORWARDED'];

        } elseif($_SERVER['HTTP_FORWARDED_FOR']) {
            $ip_addr = $_SERVER['HTTP_FORWARDED_FOR'];

        } elseif($_SERVER['HTTP_FORWARDED']) {
            $ip_addr = $_SERVER['HTTP_FORWARDED'];

        } elseif($_SERVER['REMOTE_ADDR']) {
            $ip_addr = $_SERVER['REMOTE_ADDR'];

        } else {
            $ip_addr = 'UNKNOWN';
        }

        return $ip_addr;
    }
}