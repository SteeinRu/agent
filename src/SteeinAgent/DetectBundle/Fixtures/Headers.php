<?php
namespace SteeinAgent\DetectBundle\Fixtures;


class Headers extends AbstractProvider
{
    /**
     * Все возможные заголовки HTTP, которые представляют строку агента пользователя.
     *
     * @var array
     */
    protected $data = array(
        // По умолчанию User-Agent строка.
        'HTTP_USER_AGENT',
        // Заголовок может происходить на устройствах с помощью Opera Mini.
        'HTTP_X_OPERAMINI_PHONE_UA',
        // Vodafone specific header: http://www.seoprinciple.com/mobile-web-community-still-angry-at-vodafone/24/
        'HTTP_X_DEVICE_USER_AGENT',
        'HTTP_X_ORIGINAL_USER_AGENT',
        'HTTP_X_SKYFIRE_PHONE',
        'HTTP_X_BOLT_PHONE_UA',
        'HTTP_DEVICE_STOCK_UA',
        'HTTP_X_UCBROWSER_DEVICE_UA',
        // Иногда ботов (особенно Google) используйте оригинальный пользовательский агент, но заполнить этот заголовок в их адрес электронной почты
        'HTTP_FROM',
    );
}