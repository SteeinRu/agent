<?php
namespace SteeinAgent;

use BadMethodCallException;
use SteeinAgent\DetectBundle\DetectBundle;
use Mobile_Detect as Detect;

class Agent extends Detect
{
    /**
     * Список настольных устройств.
     *
     * @var array
     */
    protected static $additionalDevices = [
        'Macintosh' => 'Macintosh',
    ];

    /**
     * Список дополнительных операционных систем.
     *
     * @var array
     */
    protected static $additionalOperatingSystems = [
        'Windows'       => 'Windows',
        'Windows NT'    => 'Windows NT',
        'OS X'          => 'Mac OS X',
        'Debian'        => 'Debian',
        'Ubuntu'        => 'Ubuntu',
        'Macintosh'     => 'PPC',
        'OpenBSD'       => 'OpenBSD',
        'Linux'         => 'Linux',
        'ChromeOS'      => 'CrOS',
    ];

    /**
     * Список дополнительных браузеров.
     *
     * @var array
     */
    protected static $additionalBrowsers = [
        'Opera'     => 'Opera|OPR',
        'Edge'      => 'Edge',
        'Vivaldi'   => 'Vivaldi',
        'Chrome'    => 'Chrome',
        'Firefox'   => 'Firefox',
        'Safari'    => 'Safari',
        'IE'        => 'MSIE|IEMobile|MSIEMobile|Trident/[.0-9]+',
        'Netscape'  => 'Netscape',
        'Mozilla'   => 'Mozilla',
    ];

    /**
     * Перечень дополнительных свойств.
     *
     * @var array
     */
    protected static $additionalProperties = [
        // Операционные системы
        'Windows'       => 'Windows NT [VER]',
        'Windows NT'    => 'Windows NT [VER]',
        'OS X'          => 'OS X [VER]',
        'BlackBerryOS'  => ['BlackBerry[\w]+/[VER]', 'BlackBerry.*Version/[VER]', 'Version/[VER]'],
        'AndroidOS'     => 'Android [VER]',
        'ChromeOS'      => 'CrOS x86_64 [VER]',
        // Браузеры
        'Opera'         => [' OPR/[VER]', 'Opera Mini/[VER]', 'Version/[VER]', 'Opera [VER]'],
        'Netscape'      => 'Netscape/[VER]',
        'Mozilla'       => 'rv:[VER]',
        'IE'            => ['IEMobile/[VER];', 'IEMobile [VER]', 'MSIE [VER];', 'rv:[VER]'],
        'Edge'          => 'Edge/[VER]',
        'Vivaldi'       => 'Vivaldi/[VER]',
    ];

    /**
     * @var \SteeinAgent\DetectBundle\DetectBundle
     */
    protected static $detectBundle;

    /**
     * Получить все правила обнаружения. Эти правила включают в себя дополнительные
     * Платформ и браузеров.
     *
     * @return array
     */
    public function getDetectionRulesExtended()
    {
        static $rules;
        if (! $rules) {
            $rules = $this->mergeRules(
                static::$additionalDevices,
                static::$phoneDevices,
                static::$tabletDevices,
                static::$operatingSystems,
                static::$additionalOperatingSystems,
                static::$browsers,
                static::$additionalBrowsers,
                static::$utilities
            );
        }
        return $rules;
    }

    /**
     * @inheritdoc
     */
    public function getRules()
    {
        if ($this->detectionType == static::DETECTION_TYPE_EXTENDED) {
            return static::getDetectionRulesExtended();
        } else {
            return static::getMobileDetectionRules();
        }
    }

    /**
     * @return \SteeinAgent\DetectBundle\DetectBundle
     */
    public function getCrawlerDetect()
    {
        if (self::$detectBundle === null) {
            self::$detectBundle = new DetectBundle();
        }
        return self::$detectBundle;
    }

    /**
     * Получите языки.
     *
     * @param string $acceptLanguage
     * @return array
     */
    public function languages($acceptLanguage = null)
    {
        if (! $acceptLanguage) {
            $acceptLanguage = $this->getHttpHeader('HTTP_ACCEPT_LANGUAGE');
        }
        if ($acceptLanguage) {
            $languages = [];
            // Разбираем принимает язык строку.
            foreach (explode(',', $acceptLanguage) as $piece) {
                $parts = explode(';', $piece);
                $language = strtolower($parts[0]);
                $priority = empty($parts[1]) ? 1. : floatval(str_replace('q=', '', $parts[1]));
                $languages[$language] = $priority;
            }
            // Сортировать языки по приоритетам.
            arsort($languages);
            return array_keys($languages);
        }
        return [];
    }

    /**
     * Проверяем правило обнаружения и возвращаем совпавший ключ.
     *
     * @param  array $rules
     * @param  null  $userAgent
     * @return string
     */
    protected function findDetectionRulesAgainstUA(array $rules, $userAgent = null)
    {
        foreach ($rules as $key => $regex)
        {
            if (empty($regex)) {
                continue;
            }
            // Проверить
            if ($this->match($regex, $userAgent)) {
                return $key ?: reset($this->matchesArray);
            }
        }
        return false;
    }

    /**
     * Получить имя браузера.
     *
     * @param null $userAgent
     * @return string
     */
    public function browser($userAgent = null)
    {
        // Получить правила браузера
        // Здесь нам нужно проверить для дополнительного браузера во-первых, в противном случае
        // MobileDetect будет в основном обнаружить Chrome в качестве браузера.
        $rules = $this->mergeRules(
            static::$additionalBrowsers,
            static::$browsers
        );
        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }

    /**
     * Получить имя платформы.
     *
     * @param  string $userAgent
     * @return string
     */
    public function platform($userAgent = null)
    {
        // Получить правила платформы
        $rules = $this->mergeRules(
            static::$operatingSystems,
            static::$additionalOperatingSystems
        );
        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }

    /**
     * Получить имя устройства.
     *
     * @param  string $userAgent
     * @return string
     */
    public function device($userAgent = null)
    {
        // Получить правила устройства
        $rules = $this->mergeRules(
            static::$additionalDevices,
            static::$phoneDevices,
            static::$tabletDevices,
            static::$utilities
        );
        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }

    /**
     * Проверьте, если устройство является настольным компьютером.
     *
     * @param  string $userAgent   deprecated
     * @param  array  $httpHeaders deprecated
     * @return bool
     */
    public function isDesktop($userAgent = null, $httpHeaders = null)
    {
        return ! $this->isMobile($userAgent, $httpHeaders) && ! $this->isTablet($userAgent, $httpHeaders) && ! $this->isRobot($userAgent);
    }

    /**
     * Проверьте, если устройство является мобильным телефоном.
     *
     * @param  string $userAgent
     * @param  array  $httpHeaders
     * @return bool
     */
    public function isPhone($userAgent = null, $httpHeaders = null)
    {
        return $this->isMobile($userAgent, $httpHeaders) && ! $this->isTablet($userAgent, $httpHeaders);
    }

    /**
     * Получить имя робота.
     *
     * @param  string $userAgent
     * @return string|bool
     */
    public function robot($userAgent = null)
    {
        if ($this->getCrawlerDetect()->isCrawler($userAgent ?: $this->userAgent)) {
            return ucfirst($this->getCrawlerDetect()->getMatches());
        }
        return false;
    }

    /**
     * Проверьте, если устройство является роботом.
     *
     * @param  string $userAgent
     * @return bool
     */
    public function isRobot($userAgent = null)
    {
        return $this->getCrawlerDetect()->isCrawler($userAgent ?: $this->userAgent);
    }

    /**
     * @inheritdoc
     */
    public function version($propertyName, $type = self::VERSION_TYPE_STRING)
    {
        $check = key(static::$additionalProperties);

        // Проверьте, если дополнительные свойства были добавлены уже
        if (! array_key_exists($check, parent::$properties))
        {
            parent::$properties = array_merge(
                parent::$properties,
                static::$additionalProperties
            );
        }
        return parent::version($propertyName, $type);
    }

    /**
     * Объединение нескольких правил в один массив.
     *
     * @return array
     */
    protected function mergeRules()
    {
        $merged = [];
        foreach (func_get_args() as $rules)
        {
            foreach ($rules as $key => $value)
            {
                if (empty($merged[$key])) {
                    $merged[$key] = $value;
                } else {
                    if (is_array($merged[$key])) {
                        $merged[$key][] = $value;
                    } else {
                        $merged[$key] .= '|'.$value;
                    }
                }
            }
        }
        return $merged;
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        // Убедитесь, что имя начинается с "is", в противном случае
        if (substr($name, 0, 2) != 'is')
        {
            throw new BadMethodCallException("No such method exists: $name");
        }
        $this->setDetectionType(self::DETECTION_TYPE_EXTENDED);
        $key = substr($name, 2);
        return $this->matchUAAgainstKey($key);
    }
}