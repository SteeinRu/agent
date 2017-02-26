<?php
namespace SteeinAgent\DetectBundle;

use SteeinAgent\DetectBundle\Fixtures\Crawlers;
use SteeinAgent\DetectBundle\Fixtures\Exclusions;
use SteeinAgent\DetectBundle\Fixtures\Headers;

class DetectBundle
{
    /**
     * The user agent.
     *
     * @var null
     */
    protected $userAgent = null;

    /**
     * Headers that contain a user agent.
     *
     * @var array
     */
    protected $httpHeaders = array();

    /**
     * Store regex matches.
     *
     * @var array
     */
    protected $matches = array();

    /**
     * Crawlers object.
     *
     * @var \SteeinAgent\DetectBundle\Fixtures\Crawlers
     */
    protected $crawlers;

    /**
     * Exclusions object.
     *
     * @var \SteeinAgent\DetectBundle\Fixtures\Exclusions
     */
    protected $exclusions;

    /**
     * Headers object.
     *
     * @var \SteeinAgent\DetectBundle\Fixtures\Headers
     */
    protected $uaHttpHeaders;

    /**
     * The compiled regex string.
     *
     * @var string
     */
    protected $compiledRegex;

    /**
     * The compiled exclusions regex string.
     *
     * @var string
     */
    protected $compiledExclusions;

    /**
     * Class constructor.
     */
    public function __construct(array $headers = null, $userAgent = null)
    {
        $this->crawlers = new Crawlers();
        $this->exclusions = new Exclusions();
        $this->uaHttpHeaders = new Headers();

        $this->compiledRegex = $this->compileRegex($this->crawlers->getAll());
        $this->compiledExclusions = $this->compileRegex($this->exclusions->getAll());

        $this->setHttpHeaders($headers);
        $this->setUserAgent($userAgent);
    }

    /**
     * Compile the regex patterns into one regex string.
     *
     * @param array
     *
     * @return string
     */
    public function compileRegex($patterns)
    {
        return '('.implode('|', $patterns).')';
    }

    /**
     * Set HTTP headers.
     *
     * @param array $httpHeaders
     */
    public function setHttpHeaders($httpHeaders = null)
    {
        // Use global _SERVER if $httpHeaders aren't defined.
        if (! is_array($httpHeaders) || ! count($httpHeaders)) {
            $httpHeaders = $_SERVER;
        }

        // Clear existing headers.
        $this->httpHeaders = array();

        // Only save HTTP headers. In PHP land, that means
        // only _SERVER vars that start with HTTP_.
        foreach ($httpHeaders as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $this->httpHeaders[$key] = $value;
            }
        }
    }

    /**
     * Return user agent headers.
     *
     * @return array
     */
    public function getUaHttpHeaders()
    {
        return $this->uaHttpHeaders->getAll();
    }

    /**
     * Set the user agent.
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent = null)
    {
        if (false === empty($userAgent)) {
            $this->userAgent = $userAgent;
        } else {
            $this->userAgent = null;
            foreach ($this->getUaHttpHeaders() as $altHeader) {
                if (false === empty($this->httpHeaders[$altHeader])) { // @todo: should use getHttpHeader(), but it would be slow.
                    $this->userAgent .= $this->httpHeaders[$altHeader].' ';
                }
            }

            $this->userAgent = (! empty($this->userAgent) ? trim($this->userAgent) : null);
        }
    }

    /**
     * Check user agent string against the regex.
     *
     * @param string $userAgent
     *
     * @return bool
     */
    public function isCrawler($userAgent = null)
    {
        $agent = $userAgent ?: $this->userAgent;

        $agent = preg_replace('/'.$this->compiledExclusions.'/i', '', $agent);

        if (strlen(trim($agent)) == 0) {
            return false;
        }

        $result = preg_match('/'.$this->compiledRegex.'/i', trim($agent), $matches);

        if ($matches) {
            $this->matches = $matches;
        }

        return (bool) $result;
    }

    /**
     * Return the matches.
     *
     * @return string|null
     */
    public function getMatches()
    {
        return isset($this->matches[0]) ? $this->matches[0] : null;
    }
}